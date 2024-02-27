<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\ConcernerCmde;
use App\Models\ConcernerRec;
use App\Models\ProduitTransfert;
use App\Models\Magasin;
use App\Models\Mouvement;
use App\Models\StockProduit;
use App\Models\Reception;
use App\Models\Transfert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class TransfertController extends Controller
{
    protected function transfert_id(){
        $debut = date('Y').'-'.date('m').'-01';
        $fin = date('Y-m-d');
        $achatp = DB::table('transferts')
            ->whereBetween('date', array($debut, $fin))
            ->where('centre_id', '=', Auth::user()->centre_id)
            ->get();
        $nb_cmde = $achatp->count()+1;
        $transfert_id = '00'.$nb_cmde.'TR'.date('m').date('Y').Auth::user()->id.Auth::user()->centre_id;
        return $transfert_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage-action',['transfert','lister']);
        $transfert = new transfert();
        $code = $this->transfert_id();
        $magasins = [];
        Session::forget('code');

        return view('transfert.index', compact('transfert','code','magasins'));
    }

    public function magasins(){
        if (\request()->ajax()){
            $magasins = DB::table('magasins')
                ->where('statut','=','true')
                //->where('type','=','Magasin_Stockage')
                ->where('centre_id', '=', Auth::user()->centre_id)
                ->get();
            return $magasins;
        }
    }

    public function rech_montant($id){
        if (\request()->ajax()){
            $cout = DB::table('produit_transferts')
                ->where('code','=',$id)
                ->sum('montant');
            return $cout;
        }
    }

    public function pdt_sour($magasin_id){
        $pdtcon = DB::table('stock_produits')
            //->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->where('stock_produits.magasin_id','=',$magasin_id)
            //->where('stock_produits.etat','<>','Delete')
            ->orderBy('stock_produits.libelle')
            ->get();
        return datatables()->of($pdtcon)
                ->addColumn('action', function($produit){})
                ->make(true);
        //return response()->json($pdtcon);
    }

    public function pdt_dest($transfert_id)
    {
        $pdtcon = DB::table('produit_transferts')
            //->join('produits','produits.produit_id','=','produit_transferts.produit_id')
            ->where('produit_transferts.code','=',$transfert_id)
            //->where('produit_transferts.magasin_id','=',$magasin_id)
            ->get();
        $output='<table class="table table-striped table-bordered contour_table" id="pdt_rec">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">Lot</td>
               <td class="description">Produit</td>
               <td class="price">Qte</td>
               <td colspan="2"></td>
           </tr>
           </thead>
           <tbody>';
        foreach($pdtcon as $produit){
            $button_edit = '<button type="button" name="editer" id="'.$produit->produit_transfert_id.'" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';;
            $button_supp = '<button type="button" name="delete" id="'.$produit->produit_transfert_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';;

            $output .='<tr>
                 <td class="cart_title">'.$produit->lot.'</td>
                 <td class="cart_price">'.$produit->libelle.'</td>
                 <td class="cart_price">'.$produit->qte.'</td>
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
            return response()->json(StockProduit::find($id));
        }
    }

    public function select_edit($id){
        if(request()->ajax())
        {
            /*$data = DB::table('produit_transferts')
                //->join('stock_produits','stock_produits.id','=','produit_transferts.idsp')
                ->where('produit_transfert_id','=',$id)
                ->get();
            $pdt_con = (object) $data[0];*/

            return response()->json(ProduitTransfert::find($id));
        }
    }

    public function add(Request $request)
    {
        $this->authorize('manage-action',['transfert','creer']);
        $rules = array(
            'produit_id'     =>  'required',
            'qte'     =>  'required|numeric|min:1',
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
            'ini' => $request->ini,
            'montant' => $request->prix*$request->qte,
            'idsp' => $request->stock_produit_id
        );

        $con_ini = ProduitTransfert::where('code','=',$request->hidden_code)
            ->where('produit_id','=',$request->produit_id)
            ->where('lot','=',$request->lot)
            ->get();

        if ($request->hidden_idcon==null){
            if (count($con_ini)==0){
                DB::beginTransaction();
                try {
                    if ($request->ini-$request->qte>=0){
                        ProduitTransfert::create($form_data);
                        DB::connection('vps')->table('produit_transferts')->insert($form_data);
                        DB::commit();
                        return response()->json(['success' => 'Produit ajoutet']);
                    }else{
                        return response()->json(['error' => 'Quantite saisie depasse la quantite disponible']);
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
                if ($request->ini-$request->qte>=0){
                    ProduitTransfert::find($request->hidden_idcon)->update($form_data);
                    DB::connection('vps')->table('produit_transferts')->where('produit_transfert_id',$request->hidden_idcon)->update($form_data);
                    DB::commit();
                    return response()->json(['success' => 'Produit modifie avec success']);
                }else{
                    return response()->json(['error' => 'Quantite saisie depasse la quantite disponible']);
                }
            }catch (\PDOException $se) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur survenu lors de l execution. produit non ajouter '.$se]);
            }
        }
    }

    public function delete($id){
        if(request()->ajax()) {
            try{
                DB::beginTransaction();
                ProduitTransfert::find($id)->delete();
                DB::connection('vps')->table('produit_transferts')->where('produit_transfert_id',$id)->delete();
                DB::commit();
            }catch(\Throwable $th){
                DB::rollBack();
            }

        }
    }

    public function store(Request $request){
        $this->authorize('manage-action',['transfert','creer']);
        $rules = array(
            'magasinsource'     =>  'required|numeric|min:1',
            'magasin_destination'     =>  'required|numeric|min:1',
            'date_transfert'     =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            Alert::error('Erreur','Merci de definir la date et le magasin');
            return back();
        }

        if ($request->magasinsource==$request->magasin_destination){
            Alert::warning('Erreur','Le magasin source doit etre different du magasin de destination');
            return back();
        }else{
            $form_data = array(
                'code' =>  $this->transfert_id(),
                'date' =>  $request->date_transfert,
                'cout' =>  0,
                'source' =>  $request->magasinsource,
                'destination' =>  $request->magasin_destination,
                'user_id'   =>  Auth::user()->id,
                'centre_id'   =>  Auth::user()->centre_id,
            );
            DB::beginTransaction();
            try {
                $pdtcons = DB::table('produit_transferts')
                    ->where('produit_transferts.code','=',$request->code)
                    ->get();

                /*DB::table('produit_transferts')
                    ->where('code','=',$request->code)
                    //->where('magasin_id','<>',$request->magasinsource)
                    ->delete();*/
                foreach ($pdtcons as $pdtcon){
                    //Magasin source
                    $qte_pdt = DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasinsource)
                        ->where('etat','<>','Delete')
                        ->sum('qte');

                    Mouvement::create([
                        'date' =>  $request->date_transfert,
                        'centre_id' =>  Auth::user()->centre_id,
                        'magasin_id' =>  $request->magasinsource,
                        'produit_id' =>  $pdtcon->produit_id,
                        'libelle' =>  $pdtcon->libelle,
                        'motif' =>  'Transfert d article '.$request->code.' du lot '.$pdtcon->lot,
                        'qte_initiale' =>  $qte_pdt,
                        'qte_sortie' =>  $pdtcon->qte,
                        'qte_reelle' =>  $qte_pdt-$pdtcon->qte,
                        'idop' =>  $request->code,
                        'idcon' =>  $pdtcon->produit_transfert_id,
                    ]);
                    DB::connection('vps')->table('mouvements')->insert(['date' =>  $request->date_transfert,
                        'centre_id' =>  Auth::user()->centre_id,
                        'magasin_id' =>  $request->magasinsource,
                        'produit_id' =>  $pdtcon->produit_id,
                        'libelle' =>  $pdtcon->libelle,
                        'motif' =>  'Transfert d article '.$request->code.' du lot '.$pdtcon->lot,
                        'qte_initiale' =>  $qte_pdt,
                        'qte_sortie' =>  $pdtcon->qte,
                        'qte_reelle' =>  $qte_pdt-$pdtcon->qte,
                        'idop' =>  $request->code,
                        'idcon' =>  $pdtcon->produit_transfert_id,
                    ]);

                    $produit = DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasinsource)
                        ->where('lot','=',$pdtcon->lot)
                        //->where('etat','<>','Delete')
                        ->get();

                    $pdt = (object) $produit[0];
                    StockProduit::find($pdt->stock_produit_id)->update(['qte'=>$pdt->qte-$pdtcon->qte]);
                    DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdt->stock_produit_id)->update(['qte'=>$pdt->qte-$pdtcon->qte]);


                    //Magasin Destination
                    $qte_pdt = DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasin_destination)
                        ->where('etat','<>','Delete')
                        ->sum('qte');
                    Mouvement::create([
                        'date' =>  $request->date_transfert,
                        'centre_id' =>  Auth::user()->centre_id,
                        'magasin_id' =>  $request->magasin_destination,
                        'produit_id' =>  $pdtcon->produit_id,
                        'libelle' =>  $pdtcon->libelle,
                        'motif' =>  'Reception Transfert d article '.$request->transfert_id.' du lot '.$pdtcon->lot,
                        'qte_initiale' =>  $qte_pdt,
                        'qte_entree' =>  $pdtcon->qte,
                        'qte_reelle' =>  $qte_pdt+$pdtcon->qte,
                        'idop' =>  $request->code,
                        'idcon' =>  $pdtcon->produit_transfert_id,
                    ]);
                    DB::connection('vps')->table('mouvements')->insert([
                        'date' =>  $request->date_transfert,
                        'centre_id' =>  Auth::user()->centre_id,
                        'magasin_id' =>  $request->magasin_destination,
                        'produit_id' =>  $pdtcon->produit_id,
                        'libelle' =>  $pdtcon->libelle,
                        'motif' =>  'Reception Transfert d article '.$request->transfert_id.' du lot '.$pdtcon->lot,
                        'qte_initiale' =>  $qte_pdt,
                        'qte_entree' =>  $pdtcon->qte,
                        'qte_reelle' =>  $qte_pdt+$pdtcon->qte,
                        'idop' =>  $request->code,
                        'idcon' =>  $pdtcon->produit_transfert_id,
                    ]);


                    $produit = DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasin_destination)
                        ->where('lot','=',$pdtcon->lot)
                        ->where('etat','<>','Delete')
                        ->get();
                    if (count($produit)==0){
                        $qtepdt= DB::table('stock_produits')
                            ->where('produit_id','=',$pdtcon->produit_id)
                            ->where('magasin_id','=',$request->magasin_destination)
                            ->where('etat','=','Encours')
                            ->get();
                        if (count($qtepdt)!=0){
                            $qp = (object) $qtepdt[0];
                            StockProduit::find($qp->stock_produit_id)->update(['etat'=>'Ancien']);
                            DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$qp->stock_produit_id)->update(['etat'=>'Ancien']);
                        }
                        $produit = DB::table('stock_produits')
                            ->where('produit_id','=',$pdtcon->produit_id)
                            ->where('magasin_id','=',$request->magasinsource)
                            ->where('lot','=',$pdtcon->lot)
                            //->where('etat','<>','Delete')
                            ->get();

                        $pdt = (object) $produit[0];

                        StockProduit::create([
                            'centre_id' => Auth::user()->centre_id,
                            'magasin_id' => $request->magasin_destination,
                            'produit_id' => $pdtcon->produit_id,
                            'libelle' => $pdtcon->libelle,
                            'lot' => $pdtcon->lot,
                            'qte' => $pdtcon->qte,
                            'qtea' => $pdtcon->qte,
                            'date_peremption' => $pdt->date_peremption,
                            'etat' => 'Encours'
                        ]);
                        DB::connection('vps')->table('stock_produits')->insert([
                            'centre_id' => Auth::user()->centre_id,
                            'magasin_id' => $request->magasin_destination,
                            'produit_id' => $pdtcon->produit_id,
                            'libelle' => $pdtcon->libelle,
                            'lot' => $pdtcon->lot,
                            'qte' => $pdtcon->qte,
                            'qtea' => $pdtcon->qte,
                            'date_peremption' => $pdt->date_peremption,
                            'etat' => 'Encours'
                        ]);

                    }else{
                        $pdt = (object) $produit[0];
                        StockProduit::find($pdt->stock_produit_id)->update(['qte'=>$pdt->qte+$pdtcon->qte,'qtea'=>$pdt->qtea+$pdtcon->qte]);
                        DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdt->stock_produit_id)->update(['qte'=>$pdt->qte+$pdtcon->qte,'qtea'=>$pdt->qtea+$pdtcon->qte]);
                    }
                }

                Transfert::create($form_data);
                DB::connection('vps')->table('transferts')->insert($form_data);
                $transfert_id = DB::getPdo()->lastInsertId();
                DB::table('produit_transferts')
                    ->where('code','=',$request->code)
                    ->update(['transfert_id'=>$transfert_id]);
                DB::connection('vps')->table('produit_transferts')
                    ->where('code','=',$request->code)
                    ->update(['transfert_id'=>$transfert_id]);
                Alert::success('Success !', 'Transfert enregistre avec success.');
                DB::commit();
                return redirect()->route('tr.index');
            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur !', 'Erreur survenu lors de l execution.'.$se);
                return redirect()->route('tr.index');
            }
        }
    }

    public function update(Request $request, $transfert_id){
        $this->authorize('manage-action',['transfert','editer']);
        $rules = array(
            'magasin_source'     =>  'required|numeric|min:1',
            'magasin_destination'     =>  'required|numeric|min:1',
            'date'     =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            Alert::error('Erreur','Merci de definir la date et le magasin');
            return back();
        }

        if ($request->magasinsource==$request->magasin_destination){
            Alert::warning('Erreur','Le magasin source doit etre different du magasin de destination');
            return back();
        }else{
            $form_data = array(
                'transfert_id' =>  $transfert_id,
                'date' =>  $request->date_transfert,
                'cout' =>  0,
                'magasin_source' =>  $request->magasinsource,
                'magasin_destination' =>  $request->magasin_destination,
                'user_id'   =>  Auth::user()->id
            );
            DB::beginTransaction();
            try {
                $pdtcons = DB::table('produit_transferts')
                    ->join('stock_produits','stock_produits.id','=','produit_transferts.idsp')
                    ->where('produit_transferts.transfert_id','=',$request->transfert_id)
                    ->where('produit_transferts.magasin_id','=',$request->magasinsource)
                    ->get();

                DB::table('produit_transferts')
                    ->where('transfert_id','=',$request->transfert_id)
                    ->where('magasin_id','<>',$request->magasinsource)
                    ->delete();
                DB::connection('vps')->table('produit_transferts')
                    ->where('transfert_id','=',$request->transfert_id)
                    ->where('magasin_id','<>',$request->magasinsource)
                    ->delete();
                //dd($pdtcons);
                foreach ($pdtcons as $pdtcon){
                    //Magasin source
                    $qte_pdt = DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasinsource)
                        ->where('etat','<>','Delete')
                        ->sum('qte');
                    Mouvement::create([
                        'date' =>  $request->date_transfert,
                        'magasin_id' =>  $request->magasinsource,
                        'produit_id' =>  $pdtcon->produit_id,
                        'libelle' =>  'Transfert d article numero '.$request->transfert_id.' du lot '.$pdtcon->lot,
                        'qte_initiale' =>  $qte_pdt,
                        'qte_sortie' =>  $pdtcon->qte,
                        'qte_reelle' =>  $qte_pdt-$pdtcon->qte,
                        'idop' =>  $request->code,
                        'idcon' =>  $pdtcon->produit_transfert_id,
                    ]);
                    DB::connection('vps')->table('mouvements')->insert([
                        'date' =>  $request->date_transfert,
                        'magasin_id' =>  $request->magasinsource,
                        'produit_id' =>  $pdtcon->produit_id,
                        'libelle' =>  'Transfert d article numero '.$request->transfert_id.' du lot '.$pdtcon->lot,
                        'qte_initiale' =>  $qte_pdt,
                        'qte_sortie' =>  $pdtcon->qte,
                        'qte_reelle' =>  $qte_pdt-$pdtcon->qte,
                        'idop' =>  $request->code,
                        'idcon' =>  $pdtcon->produit_transfert_id,
                    ]);

                    $produit = DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasinsource)
                        ->where('lot','=',$pdtcon->lot)
                        ->where('etat','<>','Delete')
                        ->get();

                    $pdt = (object) $produit[0];
                    StockProduit::find($pdt->id)->update(['qte'=>$pdt->qte-$pdtcon->qte]);
                    DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdt->id)->update(['qte'=>$pdt->qte-$pdtcon->qte]);


                    //Magasin Destination
                    $qte_pdt = DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasin_destination)
                        ->where('etat','<>','Delete')
                        ->sum('qte');
                    Mouvement::create([
                        'date' =>  $request->date_transfert,
                        'magasin_id' =>  $request->magasin_destination,
                        'produit_id' =>  $pdtcon->produit_id,
                        'libelle' =>  'Reception Transfert d article numero '.$request->transfert_id.' du lot '.$pdtcon->lot,
                        'qte_initiale' =>  $qte_pdt,
                        'qte_entree' =>  $pdtcon->qte,
                        'qte_reelle' =>  $qte_pdt+$pdtcon->qte,
                        'idop' =>  $request->transfert_id,
                        'idcon' =>  $pdtcon->id,
                    ]);
                    DB::connection('vps')->table('mouvements')->insert(['date' =>  $request->date_transfert,
                        'magasin_id' =>  $request->magasin_destination,
                        'produit_id' =>  $pdtcon->produit_id,
                        'libelle' =>  'Reception Transfert d article numero '.$request->transfert_id.' du lot '.$pdtcon->lot,
                        'qte_initiale' =>  $qte_pdt,
                        'qte_entree' =>  $pdtcon->qte,
                        'qte_reelle' =>  $qte_pdt+$pdtcon->qte,
                        'idop' =>  $request->transfert_id,
                        'idcon' =>  $pdtcon->id,
                    ]);

                    $produit = DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$request->magasin_destination)
                        ->where('lot','=',$pdtcon->lot)
                        ->where('etat','<>','Delete')
                        ->get();
                    if (count($produit)==0){
                        $qtepdt= DB::table('stock_produits')
                            ->where('produit_id','=',$pdtcon->produit_id)
                            ->where('magasin_id','=',$request->magasin_destination)
                            ->where('etat','=','Encours')
                            ->get();
                        if (count($qtepdt)!=0){
                            $qp = (object) $qtepdt[0];
                            StockProduit::find($qp->id)->update(['etat'=>'Ancien']);
                            DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$qp->id)->update(['etat'=>'Ancien']);

                        }

                        StockProduit::create([
                            'magasin_id' => $request->magasin_destination,
                            'produit_id' => $pdtcon->produit_id,
                            'lot' => $pdtcon->lot,
                            'qtea' => $pdtcon->qte,
                            'qte' => $pdtcon->qte,
                            'pa' => $pdtcon->pa,
                            'coef' => $pdtcon->coef,
                            'pv' => $pdtcon->coef*$pdtcon->pa,
                            'marge' => ($pdtcon->coef-1)*$pdtcon->pa,
                            'date_fab' => $pdtcon->date_transfert_fab,
                            'date_exp' => $pdtcon->date_transfert_exp,
                            'idop' => $request->transfert_id,
                        ]);
                        DB::connection('vps')->table('stock_produits')->insert([
                            'magasin_id' => $request->magasin_destination,
                            'produit_id' => $pdtcon->produit_id,
                            'lot' => $pdtcon->lot,
                            'qtea' => $pdtcon->qte,
                            'qte' => $pdtcon->qte,
                            'pa' => $pdtcon->pa,
                            'coef' => $pdtcon->coef,
                            'pv' => $pdtcon->coef*$pdtcon->pa,
                            'marge' => ($pdtcon->coef-1)*$pdtcon->pa,
                            'date_fab' => $pdtcon->date_transfert_fab,
                            'date_exp' => $pdtcon->date_transfert_exp,
                            'idop' => $request->transfert_id,
                        ]);
                    }else{
                        $pdt = (object) $produit[0];
                        StockProduit::find($pdt->id)->update(['qte'=>$pdt->qte+$pdtcon->qte]);
                        DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdt->id)->update(['qte'=>$pdt->qte+$pdtcon->qte]);
                    }
                }

                Transfert::find($request->transfert_id)->update($form_data);
                DB::connection('vps')->table('transferts')->where('transfert_id',$request->transfert_id)->update($form_data);
                Alert::success('Success !', 'Transfert modifie avec success.');
                DB::commit();
                return redirect()->route('tr.index');
            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur !', 'Erreur survenu lors de l execution.'.$se);
                return redirect()->route('tr.index');
            }
        }
    }

    public function edit($id){
        $this->authorize('manage-action',['transfert','editer']);
            Session::put('transfert_id',$id);
            return redirect()->route('tr.editer');
    }

    public function editer(){
        $this->authorize('manage-action',['transfert','editer']);
        $transfert_id = Session::get('transfert_id');

        if ($transfert_id!=null){
            $transfert = Transfert::find($transfert_id);

            $magasin_source = DB::table('magasins')
                ->where('magasin_id','=',$transfert->magasinsource)
                ->pluck('mag_lib','magasin_id');;

            $magasin_destination = DB::table('magasins')
                ->where('magasin_id','=',$transfert->magasin_destination)
                ->pluck('mag_lib','magasin_id');

            $produits = DB::table('stock_produits')
                ->join('produits','produits.produit_id','=','stock_produits.produit_id')
                ->where('produits.statut','=','true')
                ->where('stock_produits.etat','<>','Delete')
                ->where('stock_produits.magasin_id','=',$transfert->magasinsource)
                ->get();

            if (Auth::user()->ut==1){
                return view('transfert.edit', compact('transfert','transfert_id','magasin_source','magasin_destination','produits'));
            }elseif (Auth::user()->ut=2){
                return view('transfert.editc', compact('transfert','transfert_id','magasin_source','magasin_destination','produits'));
            }elseif (Auth::user()->ut==3){
                return view('transfert.editp', compact('transfert','transfert_id','magasin_source','magasin_destination','produits'));
            }else{
                //Rien a faire
            }
        }else{
            return redirect()->route('tr.histo');
        }

    }

    public function histo(Request $request){
        Session::forget('transfert_id');
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('transferts')
                ->join('magasins','magasins.magasin_id','=','transferts.source')
                //->join('magasins','magasins.magasin_id','=','transferts.destination')
                ->join('users','users.id','=','transferts.user_id')
                ->whereBetween('transferts.date', array($request->from_date, $request->to_date))
                ->where('transferts.centre_id', '=', Auth::user()->centre_id)
                ->get();
        }
        else
        {
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('transferts')
                ->join('magasins','magasins.magasin_id','=','transferts.source')
               // ->join('magasins','magasins.magasin_id','=','transferts.destination')
                ->join('users','users.id','=','transferts.user_id')
                ->whereBetween('transferts.date', array($debut, date('Y-m-d')))
                ->where('transferts.centre_id', '=', Auth::user()->centre_id)
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        if (Auth::user()->ut==1){
            return view('transfert.histo', compact('historiques'));
        }elseif(Auth::user()->ut==2){
            return view('transfert.histoc', compact('historiques'));
        }elseif (Auth::user()->ut==3){
            return view('transfert.histop', compact('historiques'));
        }else{
            //Rien a faire
        }
    }

    protected function show($id){
        $transferts = DB::table('transferts')
            ->join('users','users.id','=','transferts.user_id')
            ->where('transferts.transfert_id','=',$id)
            ->get();

        if (count($transferts)==0){
            Alert::error('Erreur:','Transfert inexistant');
            return back();
        }else{
            $transfert = (object) $transferts[0];
            $date = new \DateTime($transfert->date);
            $date = $date->format('d-m-Y');

            $magasin_source = Magasin::find($transfert->source);
            $magasin_desti = Magasin::find($transfert->destination);

            $categories = DB::table('produit_transferts')
                ->join('produits','produits.produit_id','=','produit_transferts.produit_id')
                ->join('categories','categories.categorie_id','=','produits.categorie_id')
                ->where('produit_transferts.transfert_id','=',$id)
                ->select('produits.categorie_id','categories.libelle')->distinct()
                ->get();

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
                            <td width="50%">Reception N° <b>' .$transfert->code.'</b></td>
                            <td width="50%">Date  <b>'.$date.'</b></td>
                        </tr>
                        <tr>
                            <td width="50%">Utilisateur: <b>' .$transfert->name.'</b></td>
                            <td width="50%"></td>
                        </tr>
                        <tr>
                            <td width="50%">Magasin Source <b>' .$magasin_source->libelle.'</b></td>
                            <td width="50%">Destination: <b>' .$magasin_desti->libelle.'</b></td>
                        </tr>
                    </table>
                    <br>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">';
            foreach($categories as $categorie){
                $produits = DB::table('produit_transferts')
                    ->join('produits','produits.produit_id','=','produit_transferts.produit_id')
                    //->join('stock_produits','stock_produits.id','=','produit_transferts.idsp')
                    ->where('produit_transferts.transfert_id','=',$id)
                    ->where('produits.categorie_id','=',$categorie->categorie_id)
                    ->get();
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
                            <th width="7%">Qte Initiale</th>
                            <th width="7%">Qte Transf</th>
                            <th width="7%">Qte Finale</th>
                        </tr>
                    </thead>
                    <tbody>';
                foreach($produits as $produit){
                    $output .='
                    <tr style="border-collapse: collapse; border: 1px solid;">
                        <td style="border: 1px solid;">'.$produit->nom_commercial.'</td>
                        <td style="border: 1px solid; text-align: left">'.($produit->type).'</td>
                        <td style="border: 1px solid; text-align: left">'.($produit->lot).'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->ini,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($produit->ini-$produit->qte,'0','.',' ').'</td>
                    </tr>';
                }
                $output .='
            </tbody>
            </table><br>';
            }
            $output .='
        </tbody>
       </table><br>';
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($output);

            return $pdf->stream();
        }
    }

    public function maj($transfert_id){
        $transfert = Transfert::find($transfert_id);
        DB::beginTransaction();
        try {
            $pdtcons = DB::table('produit_transferts')
                ->join('stock_produits','stock_produits.id','=','produit_transferts.idsp')
                ->where('produit_transferts.transfert_id','=',$transfert_id)
                ->where('produit_transferts.magasin_id','=',$transfert->magasinsource)
                ->get();
            //dd($pdtcons);
            foreach ($pdtcons as $pdtcon){
                //Magasin source
                $qte_pdt = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$transfert->magasinsource)
                    ->where('etat','<>','Delete')
                    ->sum('qte');
                Mouvement::create([
                    'date' =>  $transfert->date_transfert,
                    'magasin_id' =>  $transfert->magasinsource,
                    'produit_id' =>  $pdtcon->produit_id,
                    'libelle' =>  'Transfert d article numero '.$transfert->transfert_id.' du lot '.$pdtcon->lot,
                    'qte_initiale' =>  $qte_pdt,
                    'qte_sortie' =>  $pdtcon->qte,
                    'qte_reelle' =>  $qte_pdt-$pdtcon->qte,
                    'idop' =>  $transfert->code,
                    'idcon' =>  $pdtcon->id,
                ]);
                DB::connection('vps')->table('mouvements')->insert(['
                    date' =>  $transfert->date_transfert,
                    'magasin_id' =>  $transfert->magasinsource,
                    'produit_id' =>  $pdtcon->produit_id,
                    'libelle' =>  'Transfert d article numero '.$transfert->transfert_id.' du lot '.$pdtcon->lot,
                    'qte_initiale' =>  $qte_pdt,
                    'qte_sortie' =>  $pdtcon->qte,
                    'qte_reelle' =>  $qte_pdt-$pdtcon->qte,
                    'idop' =>  $transfert->code,
                    'idcon' =>  $pdtcon->id,
                ]);

                $produit = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$transfert->magasinsource)
                    ->where('lot','=',$pdtcon->lot)
                    ->where('etat','<>','Delete')
                    ->get();

                $pdt = (object) $produit[0];
                StockProduit::find($pdt->id)->update(['qte'=>$pdt->qte-$pdtcon->qte]);
                DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdt->id)->update(['qte'=>$pdt->qte-$pdtcon->qte]);

                //Magasin Destination
                $qte_pdt = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$transfert->magasin_destination)
                    ->where('etat','<>','Delete')
                    ->sum('qte');
                Mouvement::create([
                    'date' =>  $transfert->date_transfert,
                    'magasin_id' =>  $transfert->magasin_destination,
                    'produit_id' =>  $pdtcon->produit_id,
                    'libelle' =>  'Reception Transfert d article numero '.$transfert->transfert_id.' du lot '.$pdtcon->lot,
                    'qte_initiale' =>  $qte_pdt,
                    'qte_entree' =>  $pdtcon->qte,
                    'qte_reelle' =>  $qte_pdt+$pdtcon->qte,
                    'idop' =>  $transfert->transfert_id,
                    'idcon' =>  $pdtcon->id,
                ]);
                DB::connection('vps')->table('mouvements')->insert([
                    'date' =>  $transfert->date_transfert,
                    'magasin_id' =>  $transfert->magasin_destination,
                    'produit_id' =>  $pdtcon->produit_id,
                    'libelle' =>  'Reception Transfert d article numero '.$transfert->transfert_id.' du lot '.$pdtcon->lot,
                    'qte_initiale' =>  $qte_pdt,
                    'qte_entree' =>  $pdtcon->qte,
                    'qte_reelle' =>  $qte_pdt+$pdtcon->qte,
                    'idop' =>  $transfert->transfert_id,
                    'idcon' =>  $pdtcon->id,
                ]);

                $produit = DB::table('stock_produits')
                    ->where('produit_id','=',$pdtcon->produit_id)
                    ->where('magasin_id','=',$transfert->magasin_destination)
                    ->where('lot','=',$pdtcon->lot)
                    ->where('etat','<>','Delete')
                    ->get();
                if (count($produit)==0){
                    $qtepdt= DB::table('stock_produits')
                        ->where('produit_id','=',$pdtcon->produit_id)
                        ->where('magasin_id','=',$transfert->magasin_destination)
                        ->where('etat','=','Encours')
                        ->get();
                    if (count($qtepdt)!=0){
                        $qp = (object) $qtepdt[0];
                        StockProduit::find($qp->id)->update(['etat'=>'Ancien']);
                        DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$qp->id)->update(['etat'=>'Ancien']);
                    }

                    StockProduit::create([
                        'magasin_id' => $transfert->magasin_destination,
                        'produit_id' => $pdtcon->produit_id,
                        'lot' => $pdtcon->lot,
                        'qtea' => $pdtcon->qte,
                        'qte' => $pdtcon->qte,
                        'pa' => $pdtcon->pa,
                        'coef' => $pdtcon->coef,
                        'pv' => $pdtcon->coef*$pdtcon->pa,
                        'marge' => ($pdtcon->coef-1)*$pdtcon->pa,
                        'date_fab' => $pdtcon->date_transfert_fab,
                        'date_exp' => $pdtcon->date_transfert_exp,
                        'idop' => $transfert->transfert_id,
                    ]);
                    DB::connection('vps')->table('stock_produits')->insert([
                        'magasin_id' => $transfert->magasin_destination,
                        'produit_id' => $pdtcon->produit_id,
                        'lot' => $pdtcon->lot,
                        'qtea' => $pdtcon->qte,
                        'qte' => $pdtcon->qte,
                        'pa' => $pdtcon->pa,
                        'coef' => $pdtcon->coef,
                        'pv' => $pdtcon->coef*$pdtcon->pa,
                        'marge' => ($pdtcon->coef-1)*$pdtcon->pa,
                        'date_fab' => $pdtcon->date_transfert_fab,
                        'date_exp' => $pdtcon->date_transfert_exp,
                        'idop' => $transfert->transfert_id,
                    ]);
                }else{
                    $pdt = (object) $produit[0];
                    StockProduit::find($pdt->id)->update(['qte'=>$pdt->qte+$pdtcon->qte]);
                    DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdt->id)->update(['qte'=>$pdt->qte+$pdtcon->qte]);

                }
            }
            Alert::success('Success !', 'Transfert enregistre avec success.');
            DB::commit();
            return redirect()->route('tr.index');
        }catch (\PDOException $se){
            DB::rollBack();
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.'.$se);
            return redirect()->route('tr.index');
        }
    }
}
