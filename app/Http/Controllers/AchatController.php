<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\Categorie;
use App\Models\Centre;
use App\Models\Commande;
use App\Models\ProduitCommande;
use App\Models\ProduitReception;
use App\Models\Magasin;
use App\Models\Mouvement;
use App\Models\Produit;
use App\Models\QuantiteProduit;
use App\Models\Reception;
use App\Models\StockProduit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class AchatController extends Controller
{
    protected function reception_id(){
        $debut = date('Y').'-'.date('m').'-01';
        $fin = date('Y-m-d');
        $achatp = DB::table('receptions')->whereBetween('date_reception', array($debut, $fin))->get();
        $nb_cmde = $achatp->count()+1;
        $reception_id = '00'.$nb_cmde.'ACH'.date('m').date('Y').Auth::user()->id.Auth::user()->centre_id;
        return $reception_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('view', 'achat');
        $reception = new Reception();
        $reception_id = $this->reception_id();
        $magasins = [];
        $produits = DB::table('produits')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('produits.statut','=','true')
            ->where('categories.type','=','Stockable')
            ->get();

        Session::forget('reception_id');

        $categories = Categorie::where('statut','=','true')->pluck('libelle','categorie_id');

        return view('achat.index', compact('reception','reception_id','magasins','produits','categories'));
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
                ->where('cmde_etat','=',$id)
                ->sum('mont');
            return $rec_momt;
        }
    }

    public function pdt_rec($code)
    {
        $pdtcon = DB::table('produit_receptions')
            ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
            ->where('produit_receptions.code','=',$code)
            ->get();
        $output='<table class="table table-striped table-bordered contour_table" id="pdt_rec">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">Produit</td>
               <td class="description">Lot</td>
               <td class="price">Qte</td>
               <td class="price">Expire le</td>
               <td colspan="2"></td>
           </tr>
           </thead>
           <tbody>';
        foreach($pdtcon as $produit){
            $button_edit = '<button type="button" name="editer" id="'.$produit->produit_reception_id.'" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';;
            $button_supp = '<button type="button" name="delete" id="'.$produit->produit_reception_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';;

            $output .='<tr>
                 <td class="cart_title">'.$produit->nom_commercial.'</td>
                 <td class="cart_price">'.$produit->lot.'</td>
                 <td class="cart_price">'.$produit->qte.'</td>
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
            $produit = Produit::find($id);
            /*$pdtqtes = DB::table('stock_produits')
                ->join('magasins','magasins.magasin_id','=','stock_produits.magasin_id')
                ->where('stock_produits.produit_id','=',$id)
                ->where('magasins.statut','=','true')
                ->where('stock_produits.etat','=','Encours')
                ->orderBy('stock_produits.created_at','DESC')
                ->get();
            $produit = new \stdClass();
            $produit->produit_id = $pdt_con->produit_id;
            $produit->reference = $pdt_con->reference;
            $produit->unite = $pdt_con->unite;
            $produit->nom_commercial = $pdt_con->nom_commercial;
            $produit->type = $pdt_con->type;
            if (count($pdtqtes)>0){
                $produit->prix_achat = $pdtqtes[0]->prix_achat;
                $produit->prix_vente = $pdtqtes[0]->prix_vente;
            }else{
                $produit->prix_achat = ceil($pdt_con->prix_vente/1.3);
                $produit->prix_vente = $pdt_con->prix_vente;
            }*/
            return response()->json($produit);
        }
    }

    public function select_edit($id){
        if(request()->ajax())
        {
            //$produit = ProduitReception::find($id);
            $data = DB::table('produit_receptions')
                ->join('produits','produits.produit_id','=','produit_receptions.produit_id')
                ->where('produit_receptions.id','=',$id)
                ->get();
            $produit = (object) $data[0];
            return response()->json($produit);
        }
    }

    public function add(Request $request)
    {
        $rules = array(
            'produit_id'     =>  'required',
            'qte'     =>  'required|numeric|min:1',
            'prix_achat'     =>  'required|numeric|min:0',
            'prix_vente'     =>  'required|numeric|min:'.$request->prix_achat,
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $form_data = array(
            'code' => $request->hidden_code,
            'produit_id' => $request->produit_id,
            'libelle' => $request->nom_commercial,
            'lot' => $request->lot,
            'qte' => $request->qte,
            'pa' => $request->prix_achat,
            'pv' => $request->prix_vente,
            'unite' => $request->unite,
            'montant' => $request->qte*$request->prix_achat,
            'date_expiration' => $request->date_expiration,
        );

        $con_ini = ProduitReception::where('code','=',$request->hidden_code)
            ->where('produit_id','=',$request->produit_id)
            ->where('lot','=',$request->lot)
            ->get();

        if ($request->hidden_idcon==null){
            if (count($con_ini)==0){
                DB::beginTransaction();
                try {
                    ProduitReception::create($form_data);
                    //->table('produit_receptions')->insert($form_data);
                    DB::commit();
                    return response()->json(['success' => 'Produit ajoutet']);
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
                ProduitReception::find($request->hidden_idcon)->update($form_data);
                //->table('produit_receptions')->where('produit_reception_id', $request->hidden_idcon)->update($form_data);
                DB::commit();
                return response()->json(['success' => 'Produit modifie avec success']);
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
                    //->table('produit_receptions')->where('produit_reception_id', $id)->delete();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
            }

        }
    }

    public function store(Request $request){
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
        $rec_mont = DB::table('produit_receptions')
            ->where('code','=',$request->code)
            ->sum('montant');

        $pdtcons = DB::table('produit_receptions')
            ->where('code','=',$request->code)
            ->get();

        $form_data = array(
            'code' =>  $this->reception_id(),
            'date_reception' =>  $request->date_reception,
            'montant' =>  $rec_mont,
            'etat'   =>  'Livree',
            'magasin_id' =>  $request->magasin_id,
            'user_id'   =>  Auth::user()->id,
            'centre_id'   =>  Auth::user()->centre_id,
        );
        //dd($form_data);
        DB::beginTransaction();
        try {
            foreach ($pdtcons as $pdtcon){
                $qte_pdt = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$request->magasin_id)
                    ->where('centre_id','=',Auth::user()->centre_id)
                    ->sum('qte');

                Mouvement::create([
                    'date' =>  $request->date_reception,
                    'magasin_id' =>  $request->magasin_id,
                    'produit_id' =>  $pdtcon->produit_id,
                    'nom_commercial' =>  $pdtcon->libelle,
                    'libelle' =>  'Achat du lot '.$pdtcon->lot,
                    'qte_initiale' =>  $qte_pdt,
                    'qte_entree' =>  $pdtcon->qte*$pdtcon->unite,
                    'qte_reelle' =>  $pdtcon->qte+$qte_pdt,
                    'idop' =>  $pdtcon->code,
                    'idcon' =>  $pdtcon->produit_reception_id,
                    'centre_id'   =>  Auth::user()->centre_id
                ]);
                // //->table('mouvements')->insert([
                //     'date' =>  $request->date_reception,
                //     'magasin_id' =>  $request->magasin_id,
                //     'produit_id' =>  $pdtcon->produit_id,
                //     'nom_commercial' =>  $pdtcon->libelle,
                //     'libelle' =>  'Achat du lot '.$pdtcon->lot,
                //     'qte_initiale' =>  $qte_pdt,
                //     'qte_entree' =>  $pdtcon->qte*$pdtcon->unite,
                //     'qte_reelle' =>  $pdtcon->qte+$qte_pdt,
                //     'idop' =>  $pdtcon->code,
                //     'idcon' =>  $pdtcon->produit_reception_id,
                //     'centre_id'   =>  Auth::user()->centre_id
                // ]);

                $rech_pdt = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$request->magasin_id)
                    ->where('centre_id','=',Auth::user()->centre_id)
                    ->get();

                if (count($rech_pdt)!=0){
                    DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasin_id)
                        ->where('centre_id','=',Auth::user()->centre_id)
                        ->where('etat','=','Encours')
                        ->update(['etat'=>'Ancien']);
                    // //->table('stock_produits')
                    //     ->where('produit_id','=',$pdtcon->produit_id)
                    //     ->where('magasin_id','=',$request->magasin_id)
                    //     ->where('centre_id','=',Auth::user()->centre_id)
                    //     ->where('etat','=','Encours')
                    //     ->update(['etat'=>'Ancien']);
                }

                $produit = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$request->magasin_id)
                    ->where('centre_id','=',Auth::user()->centre_id)
                    ->where('lot','=',$pdtcon->lot)
                    ->get();
                if (count($produit)==0){
                    StockProduit::create([
                        'magasin_id' => $request->magasin_id,
                        'produit_id' => $pdtcon->produit_id,
                        'libelle' => $pdtcon->libelle,
                        'centre_id' => Auth::user()->centre_id,
                        'lot' => $pdtcon->lot,
                        'qte' => $pdtcon->qte*$pdtcon->unite,
                        'date_peremption' => $pdtcon->date_expiration,
                    ]);
                    // //->table('stock_produits')->insert([
                    //     'magasin_id' => $request->magasin_id,
                    //     'produit_id' => $pdtcon->produit_id,
                    //     'libelle' => $pdtcon->libelle,
                    //     'centre_id' => Auth::user()->centre_id,
                    //     'lot' => $pdtcon->lot,
                    //     'qte' => $pdtcon->qte*$pdtcon->unite,
                    //     'date_peremption' => $pdtcon->date_expiration,
                    ]);
                }else{
                    $pdt = (object) $produit[0];
                    StockProduit::find($pdt->stock_produit_id)->update(['qte'=>$pdt->qte+($pdtcon->qte*$pdtcon->unite)]);
                    //->table('stock_produits')->where('stock_produit_id',$pdt->stock_produit_id)->update(['qte'=>$pdt->qte+($pdtcon->qte*$pdtcon->unite)]);
                }
            }
            Reception::create($form_data);
            //->table('receptions')->insert($form_data);
            $reception_id = DB::getPdo()->lastInsertId();
            DB::table('produit_receptions')
                ->where('code','=',$request->code)
                ->update(['reception_id'=>$reception_id]);
            //->table('produit_receptions')->where('code','=',$request->code)->update(['reception_id'=>$reception_id]);
            Alert::success('Success !', 'Achat enregistre avec success.');
            DB::commit();
            return redirect()->route('ach.index');
        }catch (\PDOException $se){
            DB::rollBack();
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.');
            return redirect()->route('ach.index');
        }
    }

    public function edit($id)
    {
        $modifier = 'Non';
        $pdt_recs = DB::table('stock_produits')
            ->where('idop','=',$id)
            ->get();
        foreach ($pdt_recs as $pdt_rec){
            if ($pdt_rec->qtea>$pdt_rec->qter){
                $modifier='Modifier';
            }
        }

        if ($modifier=='Modifier'){
            Alert::warning('Infos','Les produits de cet achat ont ete utilisees. Merci de corriger le stock au besoin');
            return back();
        }else{
            Session::put('reception_id',$id);
            return redirect()->route('ach.editer');
        }
    }

    public function editer(){
        $reception_id = Session::get('reception_id');

        if (Session::get('reception_id')){
            $reception = Reception::find($reception_id);

            $magasins = DB::table('magasins')
                ->where('magasin_id','=',$reception->magasin_id)
                ->pluck('magasin_id','magasin_id');
            //dd($magasins);
            $produits = DB::table('produits')
                ->join('categories','categories.categorie_id','=','produits.categorie_id')
                ->where('produits.statut','=','true')
                ->where('categories.type','=','Stockable')
                ->get();

                return view('achat.edit', compact('reception','reception_id','magasins','produits'));
        }else{
            return redirect()->route('ach.histo');
        }

    }

    public function update(Request $request, $id)
    {
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
        $rec_mont = DB::table('produit_receptions')
            ->where('reception_id','=',$request->reception_id)
            ->sum('mont');

        $pdtcons = DB::table('produit_receptions')
            ->where('reception_id','=',$request->reception_id)
            ->get();

        $form_data = array(
            'reception_id' =>  $request->reception_id,
            'date_reception' =>  $request->date_reception,
            'rec_mont' =>  $rec_mont
        );
        DB::beginTransaction();
        try {
            DB::table('produit_receptions')
                ->where('reception_id','=',$request->reception_id)
                ->delete();
            //->table('produit_receptions')->where('reception_id','=',$request->reception_id)->delete();

            DB::table('mouvements')
                ->where('idop','=',$request->reception_id)
                ->delete();
                //->table('mouvements')->where('idop','=',$request->reception_id)->delete();

            DB::table('stock_produits')
                ->where('idop','=',$request->reception_id)
                ->delete();
                //->table('stock_produits')->where('idop','=',$request->reception_id)->delete();

            foreach ($pdtcons as $pdtcon){
                $qte_pdt = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$request->magasin_id)
                    ->sum('qter');

                ProduitReception::create([
                    'reception_id' => $request->reception_id,
                    'produit_id' => $pdtcon->produit_id,
                    'lot' => $pdtcon->lot,
                    'qte' => $pdtcon->qte,
                    'pa' => $pdtcon->prix_achat,
                    'coef' => $pdtcon->coef,
                    'pv' => $pdtcon->coef*$pdtcon->prix_achat,
                    'mont' => $pdtcon->qte*$pdtcon->prix_achat,
                    'date_fab' => $pdtcon->date_fab,
                    'date_exp' => $pdtcon->date_exp,
                    'cmde_num' => $request->cmde_num,
                ]);
                // //->table('produit_receptions')->insert([
                //     'reception_id' => $request->reception_id,
                //     'produit_id' => $pdtcon->produit_id,
                //     'lot' => $pdtcon->lot,
                //     'qte' => $pdtcon->qte,
                //     'pa' => $pdtcon->prix_achat,
                //     'coef' => $pdtcon->coef,
                //     'pv' => $pdtcon->coef*$pdtcon->prix_achat,
                //     'mont' => $pdtcon->qte*$pdtcon->prix_achat,
                //     'date_fab' => $pdtcon->date_fab,
                //     'date_exp' => $pdtcon->date_exp,
                //     'cmde_num' => $request->cmde_num,
                // ]);

                $lastId = DB::getPdo()->lastInsertId();

                Mouvement::create([
                    'mv_date' =>  $request->date_reception,
                    'magasin_id' =>  $request->magasin_id,
                    'produit_id' =>  $pdtcon->produit_id,
                    'mv_lib' =>  'Reception de l achat du lot '.$pdtcon->lot,
                    'mv_ini' =>  $qte_pdt,
                    'mv_ent' =>  $pdtcon->qte,
                    'mv_act' =>  $pdtcon->qte+$qte_pdt,
                    'idop' =>  $pdtcon->reception_id,
                    'idcon' =>  $lastId,
                ]);
                // //->table('mouvements')->insert([
                //     'mv_date' =>  $request->date_reception,
                //     'magasin_id' =>  $request->magasin_id,
                //     'produit_id' =>  $pdtcon->produit_id,
                //     'mv_lib' =>  'Reception de l achat du lot '.$pdtcon->lot,
                //     'mv_ini' =>  $qte_pdt,
                //     'mv_ent' =>  $pdtcon->qte,
                //     'mv_act' =>  $pdtcon->qte+$qte_pdt,
                //     'idop' =>  $pdtcon->reception_id,
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
                    // //->table('stock_produits')
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
                    'pa' => $pdtcon->prix_achat,
                    'coef' => $pdtcon->coef,
                    'pv' => $pdtcon->coef*$pdtcon->prix_achat,
                    'marge' => ($pdtcon->coef-1)*$pdtcon->prix_achat,
                    'date_fab' => $pdtcon->date_fab,
                    'date_exp' => $pdtcon->date_exp,
                    'idop' => $request->reception_id,
                ]);
                // //->table('stock_produits')->insert([
                //     'magasin_id' => $request->magasin_id,
                //     'produit_id' => $pdtcon->produit_id,
                //     'lot' => $pdtcon->lot,
                //     'qtea' => $pdtcon->qte,
                //     'qter' => $pdtcon->qte,
                //     'pa' => $pdtcon->prix_achat,
                //     'coef' => $pdtcon->coef,
                //     'pv' => $pdtcon->coef*$pdtcon->prix_achat,
                //     'marge' => ($pdtcon->coef-1)*$pdtcon->prix_achat,
                //     'date_fab' => $pdtcon->date_fab,
                //     'date_exp' => $pdtcon->date_exp,
                //     'idop' => $request->reception_id,
                // ]);
            }

            Reception::find($request->reception_id)->update($form_data);
            //->table('receptions')->where('reception',$request->reception_id)->update($form_data);

            Session::forget('reception_id');
            Alert::success('Success !', 'Achat modifie avec success.');
            DB::commit();
            return redirect()->route('ach.histo');
        }catch (\PDOException $se){
            DB::rollBack();
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.'.$se);
            return redirect()->route('ach.histo');
        }
    }

    public function histo(Request $request){
        Session::forget('reception_id');
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('receptions')
                ->join('users','users.id','=','receptions.user_id')
                ->join('magasins','magasins.magasin_id','=','receptions.magasin_id')
                ->where('receptions.commande_id','=',0)
                ->where('receptions.centre_id', '=', Auth::user()->centre_id)
                ->whereBetween('receptions.date_reception', array($request->from_date, $request->to_date))
                ->get();
        }
        else
        {
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('receptions')
                ->join('users','users.id','=','receptions.user_id')
                ->join('magasins','magasins.magasin_id','=','receptions.magasin_id')
                ->where('receptions.commande_id','=',0)
                ->where('receptions.centre_id', '=', Auth::user()->centre_id)
                ->whereBetween('receptions.date_reception', array($debut, date('Y-m-d')))
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
            return view('achat.histo', compact('historiques'));

    }


    protected function show($id){
        $reception = DB::table('receptions')
            ->join('magasins','magasins.magasin_id','=','receptions.magasin_id')
            ->join('users','users.id','=','receptions.user_id')
            ->where('receptions.reception_id','=', $id)
            ->get();

        if (count($reception)==0){
            Alert::error('Erreur:','Achat inexistant');
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
                                <img src="../public/images/logo.png" width="100" height="50">
                            </td>
                            <td width="85%">
                                <div>'.$centre->nom.'</div>
                                <div style="font-size: 10px">'.$centre->service.'</div>
                                <div style="font-style: italic">'.$centre->adr.'</div>
                                <div style="font-style: italic">'.$centre->telephone.'</div>
                            </td>
                        </tr>
                    </table>
                    <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 10px">
                        <tr>
                            <td width="50%">ACHAT  N° <b>' .$reception->code.'</b></td>
                            <td width="50%">Date  <b>'.$date_reception.'</b></td>
                        </tr>
                        <tr>
                            <td width="50%">Utilisateur: <b>' .$reception->name.'</b></td>
                            <td width="50%">Magasin: <b>' .$reception->libelle.'</b></td>
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
                            <th width="11%">Unite</th>
                            <th width="7%">Prix Achat</th>
                            <th width="7%">Prix Vente</th>
                            <th width="11%">Expire le</th>
                        </tr>
                    </thead>
                    <tbody>';
                foreach($produits as $produit){
                    $output .='
                    <tr style="border-collapse: collapse; border: 1px solid;">
                        <td style="border: 1px solid;">'.$produit->nom_commercial.'</td>
                        <td style="border: 1px solid; text-align: left">'.($produit->type).'</td>
                        <td style="border: 1px solid; text-align: left">'.($produit->lot).'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.($produit->unite).'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->prix_achat,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->prix_vente,'0','.',' ').'</td>
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
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($output);

            return $pdf->stream();
        }
    }
}
