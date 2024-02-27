<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\commande;
use App\Models\ProduitReception;
use App\Models\Mouvement;
use App\Models\reception;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Produitcommande;
use App\Models\ProduitReceptionSi;
use App\Models\StockProduit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class ReceptionController extends Controller
{
    protected function code(){
        $debut = date('Y').'-'.date('m').'-01';
        $fin = date('Y-m-d');
        $achatp = DB::table('receptions')
            ->whereBetween('date_reception', array($debut, $fin))
            ->where('centre_id', '=', Auth::user()->centre_id)
            ->get();
        $nb_cmde = $achatp->count()+1;
        $code = '00'.$nb_cmde.'REC'.date('m').date('Y').Auth::user()->id.Auth::user()->centre_id;
        return $code;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage-action',['reception_commande','lister']);
        $reception = new Reception();
        $code = $this->code();
        $magasins = [];
        $commandes = [];
        $receptions = [];
        Session::forget('code');
        return view('reception.index', compact('reception','code','magasins','commandes','receptions'));
    }

    public function commandes(){
        if (\request()->ajax()){
            $etat = ['Encours','Partielle','Transferee'];
            $transferts = DB::table('transferts')
                //->join('commandes','commandes.commande_id','=','receptions.commande_id')
                //->join('transferts','transferts.reception_id','=','receptions.reception_id')
                ->where('transferts.centre_id', '=', Auth::user()->centre_id)
                ->where('transferts.etat','=', 'Encours')
                ->get();
            return $transferts;
        }
    }

    public function getreception($cmde_id){
        $pdtcon = DB::table('receptions')
            ->where('commande_id', '=', $cmde_id)
            ->get();
        $reception = (object) $pdtcon[0];
        return response()->json($reception);
    }

    public function receptions(){
        if (\request()->ajax()){
            $receptions = DB::table('receptions')
                ->join('commandes','commandes.commande_id','=','receptions.commande_id')
                ->join('transferts','transferts.transfert_si_id','=','receptions.transfert_si_id')
                ->where('receptions.centre_id', '=', Auth::user()->centre_id)
                ->where('transferts.etat','=', 'Encours')
                ->get();
            return $receptions;
        }
    }

    public function magasins(){
        if (\request()->ajax()){
            $magasins = DB::table('magasins')
                ->where('statut','=','true')
                ->where('type','=','Magasin_Stockage')
                ->where('centre_id', '=', Auth::user()->centre_id)
                ->get();
            return $magasins;
        }
    }


    public function rech_montant($id){
        if (\request()->ajax()){
            $rec_momt = DB::table('produit_receptions')
                ->where('etat','=',$id)
                ->sum('montant');
            return $rec_momt;
        }
    }

    public function pdt_cmde($commande_id)
    {

        $pdtcon = DB::table('produit_receptions')
            ->where('commande_id', '=', $commande_id)
            ->get();

        DB::table('produit_receptions')
            ->where('code', '=', $this->code())
            ->delete();

        $output='<table class="table table-striped table-bordered contour_table" id="pdt_cmde">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">Produit</td>
               <td class="price">Qte Cmdee</td>
               <td class="price">Qtee Recue</td>
           </tr>
           </thead>
           <tbody>';
        foreach($pdtcon as $produit){
            $output .='<tr>
                 <td><a href="#" id="'.$produit->produit_reception_id.'" class="select">'.$produit->libelle.'</a></td>
                 <td><a href="#" id="'.$produit->produit_reception_id.'">'.$produit->qte_commandee.'</a></td>
                 <td><a href="#" id="'.$produit->produit_reception_id.'">'.$produit->qte_recue.'</a></td>
             </tr>';
        }

        $output.='</body>
            </table><br><br>';

        return $output;
    }

    public function pdt_rec($code,$commande_id){
        DB::table('produit_receptions')
            ->where('code', '=', $this->code())
            ->delete();

        $pdtcon = DB::table('produit_receptions')
            ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
            ->where('produit_receptions.commande_id', '=', $commande_id)
            ->get();

        foreach($pdtcon as $produit){
            $qteliv = DB::table('produit_receptions')
                ->where('commande_id', '=', $commande_id)
                ->where('produit_id', '=', $produit->produit_id)
                ->sum('qte');
            if($produit->qte_transferee-$qteliv>0){
                ProduitReception::create([
                    'code' => $this->code(),
                    'produit_id' => $produit->produit_id,
                    'libelle' => $produit->libelle,
                    'qte' => $produit->qte_transferee-$qteliv,
                    'pa' => $produit->prix_achat,
                    'pv' => $produit->prix_vente,
                    'unite' => $produit->unite_achat,
                    'montant' => ($produit->qte_transferee-$qteliv)*$produit->prix_achat,
                    'commande_id' => $produit->commande_id,
                    'reception_id' => $produit->reception_id
                ]);
            }
        }
        $pdtcon = DB::table('produit_receptions')
            ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
            ->where('produit_receptions.code','=',$code)
            //->where('produit_receptions.commande_id','=',$commande_id)
            ->get();

        $output='<table class="table table-striped table-bordered contour_table" id="pdt_rec">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">Produit</td>
               <td class="description">Lot</td>
               <td class="price">Qte</td>
               <td class="price">Unite</td>
               <td class="price">Expire le</td>
               <td colspan="2"></td>
           </tr>
           </thead>
           <tbody>';
            foreach($pdtcon as $produit){
                $button_edit = '<button type="button" name="editer" id="'.$produit->produit_reception_id.'" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                $button_supp = '<button type="button" name="delete" id="'.$produit->produit_reception_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';

                $output .='<tr>
                 <td class="cart_title">'.$produit->libelle.'</td>
                 <td class="cart_price">'.$produit->lot.'</td>
                 <td class="cart_price">'.$produit->qte.'</td>
                 <td class="cart_price">'.$produit->unite_achat.'</td>
                 <td class="cart_price">'.$produit->date_expiration.'</td>
                 <td class="cart_delete">'.$button_edit.'</td>
                 <td class="cart_delete">'.$button_supp.'</td>
             </tr>';
            }
            $output.='</body>
                    </table>';
        return $output;
    }

    public function pdt_rec_af($code){

        $pdtcon = DB::table('produit_receptions')
            ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
            ->where('produit_receptions.code','=',$code)
            //->where('produit_receptions.commande_id','=',$commande_id)
            ->get();

        $output='<table class="table table-striped table-bordered contour_table" id="pdt_rec">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">Produit</td>
               <td class="description">Lot</td>
               <td class="price">Qte</td>
               <td class="price">Unite</td>
               <td class="price">Expire le</td>
               <td colspan="2"></td>
           </tr>
           </thead>
           <tbody>';
            foreach($pdtcon as $produit){
                $button_edit = '<button type="button" name="editer" id="'.$produit->produit_reception_id.'" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                $button_supp = '<button type="button" name="delete" id="'.$produit->produit_reception_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';

                $output .='<tr>
                 <td class="cart_title">'.$produit->libelle.'</td>
                 <td class="cart_price">'.$produit->lot.'</td>
                 <td class="cart_price">'.$produit->qte.'</td>
                 <td class="cart_price">'.$produit->unite_achat.'</td>
                 <td class="cart_price">'.$produit->date_expiration.'</td>
                 <td class="cart_delete">'.$button_edit.'</td>
                 <td class="cart_delete">'.$button_supp.'</td>
             </tr>';
            }
            $output.='</body>
                    </table>';
        return $output;
    }

    public function select($id){
        if(request()->ajax()) {
            $pdt_cons = DB::table('produit_receptions')
                ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
                ->where('produit_reception_id','=',$id)
                ->get();
            $pdt_con = (object) $pdt_cons[0];

            $qte_liv = DB::table('produit_receptions')
                ->where('commande_id','=',$pdt_con->commande_id)
                ->where('produit_id','=',$pdt_con->produit_id)
                ->sum('qte');

            return response()->json(['produit'=>$pdt_con,'qte_liv'=>$qte_liv]);
        }
    }

    public function select_edit($id){
        if(request()->ajax()){
            $data = DB::table('produit_receptions')
                ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
                ->where('produit_reception_id','=',$id)
                ->get();
            $pdt_con = (object) $data[0];

            $cmdes = DB::table('produit_commandes')
                ->where('commande_id','=',$pdt_con->commande_id)
                ->where('produit_id','=',$pdt_con->produit_id)
                ->get();

            $cmde = (object) $cmdes[0];
            $qte_cmde = $cmde->qte;

            $qte_liv = DB::table('produit_receptions')
                ->where('commande_id','=',$pdt_con->commande_id)
                ->where('produit_id','=',$pdt_con->produit_id)
                ->sum('qte');

            return response()->json(['produit'=>$pdt_con,'qte_cmde'=>$qte_cmde,'qte_liv'=>$qte_liv]);
        }
    }

    public function add(Request $request)
    {
        $this->authorize('manage-action',['reception_commande','creer']);
        $rules = array(
            'produit_id'     =>  'required',
            'qte'     =>  'required|numeric|min:1',
            'pa'     =>  'required|numeric|min:0',
            'pv'     =>  'required|numeric|min:'.$request->pa,
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $form_data = array(
            'code' => $request->hidden_code,
            'produit_id' => $request->produit_id,
            'libelle' => $request->libelle,
            'lot' => $request->lot,
            'qte' => $request->qte,
            'pa' => $request->pa,
            'pv' => $request->pv,
            'unite' => $request->unite,
            'montant' => $request->qte*$request->pa,
            'date_expiration' => $request->date_expiration,
            'commande_id' => $request->hidden_commande_id,
            'reception_id' => $request->reception_id
        );

        $con_ini = ProduitReception::where('code','=',$request->hidden_code)
            ->where('produit_id','=',$request->produit_id)
            ->where('lot','=',$request->lot)
            ->get();

        if ($request->produit_reception_id==null){
            if (count($con_ini)==0){
                DB::beginTransaction();
                try {
                    if ($request->qte_cmde>=($request->qte+$request->qte_liv)){
                        ProduitReception::create($form_data);
                        //DB::connection('vps')->table('produit_receptions')->insert($form_data);
                        DB::table('produit_commandes')
                            ->where('commande_id','=',$request->hidden_commande_id)
                            ->where('produit_id','=',$request->produit_id)
                            ->update(['qte_liv'=>$request->qte+$request->qte_liv]);
                        DB::commit();
                        return response()->json(['success' => 'Produit ajoutet']);
                    }else{
                        return response()->json(['error' => 'Quantite saisie depasse la quantite commandee']);
                    }

                }catch (\PDOException $se) {
                    DB::rollBack();
                    return response()->json(['error' => 'Erreur survenu lors de l execution. produit non ajouter '.$se]);
                }
            }else{
                return response()->json(['error' => 'Vous aviez deja selectionner ce produit']);
            }
        }else{
            DB::beginTransaction();
            try {
                if ($request->qte_cmde>=($request->qte+$request->qte_liv)){
                    ProduitReception::find($request->produit_reception_id)->update($form_data);
                    //DB::connection('vps')->table('produit_receptions')->where('produit_reception_id',$request->produit_reception_id)->update($form_data);
                    DB::table('produit_commandes')
                            ->where('commande_id','=',$request->hidden_commande_id)
                            ->where('produit_id','=',$request->produit_id)
                            ->update(['qte_liv'=>$request->qte+$request->qte_liv]);
                    // DB::connection('vps')->table('produit_commandes')
                    //         ->where('commande_id','=',$request->hidden_commande_id)
                    //         ->where('produit_id','=',$request->produit_id)
                    //         ->update(['qte_liv'=>$request->qte+$request->qte_liv]);
                    DB::commit();
                    //dd($pr);
                    return response()->json(['success' => 'Produit modifie avec success']);
                }else{
                    return response()->json(['error' => 'Quantite saisie depasse la quantite commandee']);
                }
            }catch (\PDOException $se) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur survenu lors de l execution. produit non ajouter ']);
            }
        }
    }

    public function delete($id){
        if(request()->ajax()) {
            try {
                DB::beginTransaction();
                ProduitReception::find($id)->delete();
                //DB::connection('vps')->table('produit_receptions')->where('produit_reception_id',$id)->delete();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
            }
        }
    }

    public function store(Request $request){
        $this->authorize('manage-action',['reception_commande','creer']);
        $rules = array(
            'magasin_id'     =>  'required|numeric|min:1',
            'date_reception'     =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            Alert::error('Erreur','Merci de definir la date et le magasin');
            return back();
        }
        $montant = DB::table('produit_receptions')
            ->where('code','=',$request->code)
            ->sum('montant');

        $pdtcons = DB::table('produit_receptions')
            ->where('code','=',$request->code)
            ->where('commande_id','=',$request->cmdenum)
            ->get();
        $qte_cmde = DB::table('produit_commandes')
            ->where('commande_id','=',$request->cmdenum)
            ->sum('qte');
        $qte_liv = DB::table('produit_receptions')
            ->where('commande_id','=',$request->cmdenum)
            ->sum('qte');
        if ($qte_liv!=$qte_cmde){
            $etat='Partielle';
        }else{
            $etat='Livree';
        }

        $qte_trans = DB::table('produit_receptions')
            ->where('reception_id','=',$request->reception_id)
            ->sum('qte_transferee');
        $qte_recue = DB::table('produit_receptions')
            ->where('reception_id','=',$request->reception_id)
            ->sum('qte');
        $res = null;
        if($qte_trans==$qte_liv){
            try{
                DB::beginTransaction();
                DB::table('transferts')
                    ->where('reception_id','=',$request->reception_id)
                    ->update(['etat'=>'Livree']);
                // DB::connection('vps')->table('transferts')
                //     ->where('reception_id','=',$request->reception_id)
                //     ->update(['etat'=>'Livree']);
                DB::commit();
            }catch(\Throwable $th){
                DB::rollBack();
            }
        }

        $taux_liv = ($qte_liv/$qte_cmde)*100;
        $taux = (int)round($taux_liv);

        $form_data = array(
            'code' =>  $this->code(),
            'date_reception' =>  $request->date_reception,
            'montant' =>  $montant,
            'etat' =>  $etat,
            'commande_id' =>  $request->cmdenum,
            'magasin_id' =>  $request->magasin_id,
            'user_id'   =>  Auth::user()->id,
            'centre_id'   =>  Auth::user()->centre_id
        );
        //dd($qte_cmde,$qte_liv,$qte_trans,$qte_recue,$form_data,$res);
        DB::beginTransaction();
        try {
            DB::table('produit_receptions')
                ->where('code','=',$request->code)
                ->where('commande_id','<>',$request->cmdenum)
                ->delete();
            // DB::connection('vps')->table('produit_receptions')
            //     ->where('code','=',$request->code)
            //     ->where('commande_id','<>',$request->cmdenum)
            //     ->delete();

            Commande::find($request->cmdenum)->update(['etat'=>$etat,'taux'=>$taux]);
            //DB::connection('vps')->table('commandes')->where('commande_id',$request->cmdenum)->update(['etat'=>$etat,'taux'=>$taux]);

            foreach ($pdtcons as $pdtcon){
                $qte_pdt = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$request->magasin_id)
                    ->where('centre_id','=',Auth::user()->centre_id)
                    ->sum('qte');
                Mouvement::create([
                    'date' =>  $request->date_reception,
                    'centre_id'   =>  Auth::user()->centre_id,
                    'user_id'   =>  Auth::user()->id,
                    'magasin_id' =>  $request->magasin_id,
                    'produit_id' =>  $pdtcon->produit_id,
                    'libelle' =>  $pdtcon->libelle,
                    'motif' =>  'Reception cmde '.$request->cmdenum.' du lot '.$pdtcon->lot,
                    'qte_initiale' =>  $qte_pdt,
                    'qte_entree' =>  $pdtcon->qte*$pdtcon->unite,
                    'qte_reelle' =>  ($pdtcon->qte*$pdtcon->unite)+$qte_pdt,
                    'idop' =>  $pdtcon->code,
                    'idcon' =>  $pdtcon->produit_reception_id
                ]);
                // DB::connection('vps')->table('mouvements')->insert([
                //     'date' =>  $request->date_reception,
                //     'centre_id'   =>  Auth::user()->centre_id,
                //     'user_id'   =>  Auth::user()->id,
                //     'magasin_id' =>  $request->magasin_id,
                //     'produit_id' =>  $pdtcon->produit_id,
                //     'libelle' =>  $pdtcon->libelle,
                //     'motif' =>  'Reception cmde '.$request->cmdenum.' du lot '.$pdtcon->lot,
                //     'qte_initiale' =>  $qte_pdt,
                //     'qte_entree' =>  $pdtcon->qte*$pdtcon->unite,
                //     'qte_reelle' =>  ($pdtcon->qte*$pdtcon->unite)+$qte_pdt,
                //     'idop' =>  $pdtcon->code,
                //     'idcon' =>  $pdtcon->produit_reception_id
                // ]);

                Produit::find($pdtcon->produit_id)->update([
                    'prix_achat' =>  $pdtcon->pa,
                    'prix_vente'   =>  $pdtcon->pv
                ]);
                // DB::connection('vps')->table('produits')->where('produit_id',$pdtcon->produit_id)->update([
                //     'prix_achat' =>  $pdtcon->pa,
                //     'prix_vente'   =>  $pdtcon->pv
                // ]);

                /*$rech_pdt = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$request->magasin_id)
                    ->where('centre_id','=',Auth::user()->centre_id)
                    ->get();

                if ($qte_pdt!=0){
                    DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasin_id)
                        ->where('centre_id','=',Auth::user()->centre_id)
                        ->where('etat','=','Encours')
                        ->update(['etat'=>'Ancien']);
                }*/

                $produit = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$request->magasin_id)
                    ->where('centre_id','=',Auth::user()->centre_id)
                    ->where('lot','=',$pdtcon->lot)
                    ->get();
                if (count($produit)==0){
                    StockProduit::create([
                        'centre_id'=>Auth::user()->centre_id,
                        'magasin_id' => $request->magasin_id,
                        'produit_id' => $pdtcon->produit_id,
                        'libelle' => $pdtcon->libelle,
                        'lot' => $pdtcon->lot,
                        'qtea' => $pdtcon->qte*$pdtcon->unite,
                        'qte' => $pdtcon->qte*$pdtcon->unite,
                        'date_peremption' => $pdtcon->date_expiration,
                    ]);
                    // DB::connection('vps')->table('stock_produits')->insert([
                    //     'centre_id'=>Auth::user()->centre_id,
                    //     'magasin_id' => $request->magasin_id,
                    //     'produit_id' => $pdtcon->produit_id,
                    //     'libelle' => $pdtcon->libelle,
                    //     'lot' => $pdtcon->lot,
                    //     'qtea' => $pdtcon->qte*$pdtcon->unite,
                    //     'qte' => $pdtcon->qte*$pdtcon->unite,
                    //     'date_peremption' => $pdtcon->date_expiration,
                    // ]);
                }else{
                    $pdt = (object) $produit[0];
                    StockProduit::find($pdt->stock_produit_id)->update(['qte'=>$pdt->qte+($pdtcon->qte*$pdtcon->unite),'qtea'=>$pdt->qtea+($pdtcon->qte*$pdtcon->unite)]);
                    //DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdt->stock_produit_id)->update(['qte'=>$pdt->qte+($pdtcon->qte*$pdtcon->unite),'qtea'=>$pdt->qtea+($pdtcon->qte*$pdtcon->unite)]);
                }
            }
            Reception::create($form_data);
            //DB::connection('vps')->table('receptions')->insert($form_data);
            $reception_id = DB::getPdo()->lastInsertId();
            DB::table('produit_receptions')
                ->where('code','=',$request->code)
                ->update(['reception_id'=>$reception_id]);
            // DB::connection('vps')->table('produit_receptions')
            //     ->where('code','=',$request->code)
            //     ->update(['reception_id'=>$reception_id]);

            Alert::success('Success !', 'reception enregistre avec success.');
            DB::commit();
            return redirect()->route('rec.index');
        }catch (\PDOException $se){
            DB::rollBack();
            dd($se);
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.'.$se);
            return redirect()->route('rec.index');
        }
    }

    public function edit($id)
    {
        $this->authorize('manage-action',['reception','editer']);
        Session::put('reception_id',$id);
        return redirect()->route('rec.editer');
    }

    public function editer()
    {
        $reception_id = Session::get('reception_id');

        if (Session::get('reception_id')){
            $reception = Reception::find($reception_id);
            $code = $reception->code;
            $magasins = Magasin::find($reception->magasin_id);
            $commandes = DB::table('commandes')
                ->where('commande_id','=',$reception->commande_id)
                ->get();
            if (Auth::user()->ut==1){
                return view('reception.edit', compact('reception','code','magasins','commandes'));
            }elseif (Auth::user()->ut=2){
                return view('reception.editc', compact('reception','code','magasins','commandes'));
            }elseif (Auth::user()->ut==3){
                return view('reception.editp', compact('reception','code','magasins','commandes'));
            }else{
                //Rien a faire
            }
        }else{
            return redirect()->route('rec.histo');
        }

    }

    public function update(Request $request, $id)
    {
        $this->authorize('manage-action',['reception','editer']);
        $rules = array(
            'date_reception'     =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            Alert::error('Erreur','Merci de definir la date');
            return back();
        }
        $montant = DB::table('produit_receptions')
            ->where('code','=',$request->code)
            ->sum('montant');

        $pdtcons = DB::table('produit_receptions')
            ->where('code','=',$request->code)
            ->where('commande_id','=',$request->commande_id)
            ->get();
        $qte_cmde = DB::table('produit_commandes')
            ->where('commande_id','=',$request->cmd_enum)
            ->sum('qte');
        $qte_liv = DB::table('produit_receptions')
            ->where('commande_id','=',$request->commande_id)
            ->sum('qte');

        $form_data = array(
            'code' =>  $request->code,
            'date_reception' =>  $request->date_reception,
            'montant' =>  $montant
        );
        DB::beginTransaction();
        try {

            foreach ($pdtcons as $pdtcon){
                $qte_pdt = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$request->magasin_id)
                    ->sum('qter');

                ProduitReception::create([
                    'code' => $request->code,
                    'produit_id' => $pdtcon->produit_id,
                    'lot' => $pdtcon->lot,
                    'qte' => $pdtcon->qte,
                    'pa' => $pdtcon->pa,
                    'coef' => $pdtcon->coef,
                    'pv' => $pdtcon->pv,
                    'montant' => $pdtcon->qte*$pdtcon->pa,
                    'unite' => $pdtcon->unite,
                    'date_expiration' => $pdtcon->date_expiration,
                    'commande_id' => $request->commande_id,
                ]);
                // DB::connection('vps')->table('produit_receptions')->insert([
                //     'code' => $request->code,
                //     'produit_id' => $pdtcon->produit_id,
                //     'lot' => $pdtcon->lot,
                //     'qte' => $pdtcon->qte,
                //     'pa' => $pdtcon->pa,
                //     'coef' => $pdtcon->coef,
                //     'pv' => $pdtcon->pv,
                //     'montant' => $pdtcon->qte*$pdtcon->pa,
                //     'unite' => $pdtcon->unite,
                //     'date_expiration' => $pdtcon->date_expiration,
                //     'commande_id' => $request->commande_id,
                // ]);
                $lastId = DB::getPdo()->lastInsertId();
                Mouvement::create([
                    'date' =>  $request->date_reception,
                    'magasin_id' =>  $request->magasin_id,
                    'produit_id' =>  $pdtcon->produit_id,
                    'mv_lib' =>  'Reception cmde '.$request->cmdenum.' du lot '.$pdtcon->lot,
                    'mv_ini' =>  $qte_pdt,
                    'mv_ent' =>  $pdtcon->qte,
                    'mv_act' =>  $pdtcon->qte+$qte_pdt,
                    'idop' =>  $pdtcon->code,
                    'idcon' =>  $lastId,
                ]);
                // DB::connection('vps')->table('mouvements')->insert([
                //     'date' =>  $request->date_reception,
                //     'magasin_id' =>  $request->magasin_id,
                //     'produit_id' =>  $pdtcon->produit_id,
                //     'mv_lib' =>  'Reception cmde '.$request->cmdenum.' du lot '.$pdtcon->lot,
                //     'mv_ini' =>  $qte_pdt,
                //     'mv_ent' =>  $pdtcon->qte,
                //     'mv_act' =>  $pdtcon->qte+$qte_pdt,
                //     'idop' =>  $pdtcon->code,
                //     'idcon' =>  $lastId,
                // ]);

                $rech_pdt = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$request->magasin_id)
                    ->get();
                if (count($rech_pdt)!=0){
                    DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasin_id)
                        ->where('etat','=','Encours')
                        ->update(['etat'=>'Ancien']);
                    // DB::connection('vps')->table('stock_produits')
                    //     ->where('produit_id','=',$pdtcon->produit_id)
                    //     ->where('magasin_id','=',$request->magasin_id)
                    //     ->where('etat','=','Encours')
                    //     ->update(['etat'=>'Ancien']);
                }
                StockProduit::create([
                    'magasin_id' => $request->magasin_id,
                    'produit_id' => $pdtcon->produit_id,
                    'lot' => $pdtcon->lot,
                    'qtea' => $pdtcon->qte,
                    'qter' => $pdtcon->qte,
                    'pa' => $pdtcon->pa,
                    'coef' => $pdtcon->coef,
                    'pv' => $pdtcon->pv,
                    'marge' => $pdtcon->pv-$pdtcon->pa,
                    'unite' => $pdtcon->unite,
                    'date_expiration' => $pdtcon->date_expiration,
                    'idop' => $request->code,
                ]);
                // DB::connection('vps')->table('stock_produits')->insert([
                //     'magasin_id' => $request->magasin_id,
                //     'produit_id' => $pdtcon->produit_id,
                //     'lot' => $pdtcon->lot,
                //     'qtea' => $pdtcon->qte,
                //     'qter' => $pdtcon->qte,
                //     'pa' => $pdtcon->pa,
                //     'coef' => $pdtcon->coef,
                //     'pv' => $pdtcon->pv,
                //     'marge' => $pdtcon->pv-$pdtcon->pa,
                //     'unite' => $pdtcon->unite,
                //     'date_expiration' => $pdtcon->date_expiration,
                //     'idop' => $request->code,
                // ]);
            }
            Reception::find($request->code)->update($form_data);
           // DB::connection('vps')->table('receptions')->where('reception_id',$request->code)->update($form_data);
            if ($qte_liv!=$qte_cmde){
                commande::find($request->commande_id)->update(['etat'=>'Partielle']);
                //DB::connection('vps')->table('commandes')->where('commande_id',$request->commande_id)->update(['etat'=>'Partielle']);
                commande::find($request->commande_id)->update(['etat'=>'Livree']);
                //DB::connection('vps')->table('commandes')->where('commande_id',$request->commande_id)->update(['etat'=>'Livree']);
            }

            Session::forget('code');
            Alert::success('Success !', 'Reception modifiee avec success.');
            DB::commit();
            return redirect()->route('rec.histo');
        }catch (\PDOException $se){
            DB::rollBack();
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.'.$se);
            return redirect()->route('rec.histo');
        }
    }

    public function histo(Request $request){
        Session::forget('code');
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('receptions')
                ->join('magasins','magasins.magasin_id','=','receptions.magasin_id')
                ->join('commandes','commandes.commande_id','=','receptions.commande_id')
                ->join('users','users.id','=','receptions.user_id')
                ->where('commandes.centre_id', '=', Auth::user()->centre_id)
                ->whereBetween('receptions.date_reception', array($request->from_date, $request->to_date))
                ->get();
        }
        else
        {
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('receptions')
                ->join('users','users.id','=','receptions.user_id')
                ->join('magasins','magasins.magasin_id','=','receptions.magasin_id')
                ->join('commandes','commandes.commande_id','=','receptions.commande_id')
                ->where('commandes.centre_id', '=', Auth::user()->centre_id)
                ->whereBetween('receptions.date_reception', array($debut, date('Y-m-d')))
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        if (Auth::user()->ut==1){
            return view('reception.histo', compact('historiques'));
        }elseif(Auth::user()->ut==2){
            return view('reception.histoc', compact('historiques'));
        }elseif (Auth::user()->ut==3){
            return view('reception.histop', compact('historiques'));
        }else{
            //Rien a faire
        }
    }


    protected function show($id){
        $reception = DB::table('receptions')
            ->join('magasins','magasins.magasin_id','=','receptions.magasin_id')
            ->join('users','users.id','=','receptions.user_id')
            ->join('commandes','commandes.commande_id','=','receptions.commande_id')
            ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
            ->where('receptions.reception_id','=', $id)
            ->get();

        if (count($reception)==0){
            Alert::error('Erreur:','Livraison inexistante');
            return back();
        }else{
            $reception = (object) $reception[0];
            $date = new \DateTime($reception->date_reception);
            $date_reception = $date->format('d-m-Y');

            $categories = DB::table('produit_receptions')
                ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
                ->join('categories','categories.categorie_id','=','produits.categorie_id')
                ->where('produit_receptions.reception_id','=',$id)
                ->select('produits.categorie_id','categories.libelle')->distinct()
                ->get();
            $cout_achat=0;
            $cout_achat_total=0;
            $centre  = Centre::find('1');

            $output ='<table>
                        <tr>
                            <td width="15%">
                                <img src="/images/logo.png" width="100" height="50">
                            </td>
                            <td width="85%">
                                <div>'.$centre->nom_centre.'</div>
                                <div style="font-size: 10px">'.$centre->services.'</div>
                                <div style="font-style: italic">'.$centre->adr.'</div>
                                <div style="font-style: italic">'.$centre->telephone.'</div>
                            </td>
                        </tr>
                    </table>
                    <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 10px">
                        <tr>
                            <td width="50%">Reception N° <b>' .$reception->code.'</b></td>
                            <td width="50%">Date  <b>'.$date_reception.'</b></td>
                        </tr>
                        <tr>
                            <td width="50%">Utilisateur: <b>' .$reception->name.'</b></td>
                            <td width="50%">Magasin: <b>' .$reception->libelle.'</b></td>
                        </tr>
                        <tr>
                            <td width="50%">commande N: <b>' .$reception->commande_id.'</b></td>
                            <td width="50%">Fournisseur: <b>' .$reception->nom.'</b></td>
                        </tr>
                    </table>
                    <br>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">';
            foreach($categories as $categorie){
                $produits = DB::table('produit_receptions')
                    ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
                    ->where('produit_receptions.reception_id','=',$id)
                    ->where('produits.categorie_id','=',$categorie->categorie_id)
                    ->get();
                $cout_achat = DB::table('produit_receptions')
                    ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
                    ->where('produit_receptions.reception_id','=',$id)
                    ->where('produits.categorie_id','=',$categorie->categorie_id)
                    ->sum('produit_receptions.montant');

                $cout_achat_total+=$cout_achat;
                $output .='
                <tr style="border-collapse: collapse; border: 1px solid; background-color: #fffde7; text-align: center; size: 20px">
                    <td style="border: 1px solid;">'.$categorie->libelle.'</td>
                </tr>
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                        <tr style="border-radius: 10px; background-color: #F7F4F3";>
                            <th width="26%">Libelle</th>
                            <th width="12%">Type</th>
                            <th width="9%">Lot</th>
                            <th width="7%">Qte</th>
                            <th width="7%">Prix Achat</th>
                            <th width="7%">Prix Vente</th>
                            <th width="11%">Unite</th>
                            <th width="11%">Expire le</th>
                        </tr>
                    </thead>
                    <tbody>';
                foreach($produits as $produit){
                    $output .='
                    <tr style="border-collapse: collapse; border: 1px solid;">
                        <td style="border: 1px solid;">'.$produit->libelle.'</td>
                        <td style="border: 1px solid; text-align: left">'.($produit->type).'</td>
                        <td style="border: 1px solid; text-align: left">'.($produit->lot).'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->pa,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->pv,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.($produit->unite).'</td>
                        <td style="border: 1px solid; text-align: right">'.($produit->date_expiration).'</td>
                    </tr>';
                }
                $output .='
                <tr style="border-collapse: collapse; border: 1px solid;text-align: center;font-style: italic; font-size: 16px">
                    <td colspan="3" style="border: 1px solid;">Sous Total</td>
                    <td colspan="5" style="border: 1px solid; text-align: right">Cout Achat => '.number_format($cout_achat,'0','.',' ').' Fr CFA</td>
                </tr>
            </tbody>
            </table><br>';
            }
            $output .='
            <tr style="border-collapse: collapse; border: 1px solid;">
                <td style="text-align: center;font-weight: bold; font-size: 16px">Cout Achat => '.number_format($cout_achat_total,'0','.',' ').' Fr CFA</td>
            </tr>
        </tbody>
       </table><br>';
            /*$pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($output);

            return $pdf->stream();*/
            return $output;
        }
    }

    protected function rec_cmde($id){
        Alert::error('Erreur:','Pade non disponible. Reessayer plut tard');
            return back();
        $commande = DB::table('commandes')
            ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
            ->where('commandes.commande_id','=', $id)
            ->get();
        if (count($commande)==0){
            Alert::error('Erreur:','commande inexistante');
            return back();
        }else{
            $commande = (object) $commande[0];
            $receptions = DB::table('receptions')
                ->join('magasins','magasins.magasin_id','=','receptions.magasin_id')
                ->join('users','users.id','=','receptions.user_id')
                ->where('receptions.commande_id','=', $commande->commande_id)
                ->get();

            $date = new \DateTime($commande->date_commande);
            $cmde_date = $date->format('d-m-Y');
            $cout_total = 0;

            $centre  = Centre::find('1');
            $output ='<table>
                <tr>
                    <td width="15%">
                        <img src="../public/images/logo.png" width="100" height="50">
                    </td>
                    <td width="85%">
                        <div>'.$centre->nom_centre.'</div>
                        <div style="font-size: 10px">'.$centre->service.'</div>
                        <div style="font-style: italic">'.$centre->adr.'</div>
                        <div style="font-style: italic">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 10px">
                <tr>
                    <td colspan="2">LES LIVRAISONS DE LA commande N° <b>' .$commande->commande_id.' DU ' .$cmde_date.' ADRESSEE A '.$commande->nom.'</b></td>
                </tr>
            </table>
            <br>

            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">';
            foreach($receptions as $reception){
                $produits = DB::table('produit_receptions')
                    ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
                    ->where('produit_receptions.code','=',$reception->code)
                    ->get();
                $cout_total+=$reception->montant;
                $output .='
                <tr style="border-collapse: collapse; border: 1px solid; background-color: #fffde7; text-align: center; size: 20px">
                    <td style="border: 1px solid;">Reception N '.$reception->code.' / Date : '.$reception->date_reception.'</td>
                </tr>
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                        <tr style="border-radius: 10px; background-color: #F7F4F3";>

                            <th width="11%">Code</th>
                            <th width="26%">Libelle</th>
                            <th width="9%">Lot</th>
                            <th width="7%">Qte</th>
                            <th width="7%">Prix Achat</th>
                            <th width="7%">Prix Vente</th>
                            <th width="11%">Expire le</th>
                        </tr>
                    </thead>
                    <tbody>';
                foreach($produits as $produit){
                    $output .='
                    <tr style="border-collapse: collapse; border: 1px solid;">
                        <td style="border: 1px solid;">'.$produit->reference.'</td>
                        <td style="border: 1px solid;">'.$produit->nom_commercial.'</td>
                        <td style="border: 1px solid; text-align: left">'.($produit->lot).'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->pa,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->pv,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.($produit->date_expiration).'</td>
                    </tr>';
                }
                $output .='
                <tr style="border-collapse: collapse; border: 1px solid;text-align: center;font-style: italic; font-size: 16px">
                    <td colspan="3" style="border: 1px solid;">Cout reception</td>
                    <td colspan="5" style="border: 1px solid; text-align: right">Cout Achat => '.number_format($reception->montant,'0','.',' ').' Fr CFA</td>
                </tr>
            </tbody>
            </table><br>';
            }
            $output .='
            <tr style="border-collapse: collapse; border: 1px solid;">
                <td style="text-align: center;font-weight: bold; font-size: 16px">Cout Achat => '.number_format($cout_total,'0','.',' ').' Fr CFA</td>
            </tr>
        </tbody>
        </table><br>';
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($output);

            return $pdf->stream();
        }
    }

    public function rech_cmde($id){
        if (\request()->ajax()){
            $cmde = commande::find($id);
            return $cmde;
        }
    }

}
