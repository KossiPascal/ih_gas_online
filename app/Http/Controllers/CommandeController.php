<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Centre;
use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\ProduitCommande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function commande_id(){
        $debut = date('Y').'-'.date('m').'-01';
        $fin = date('Y-m-d');
        $achatp = DB::table('commandes')
            ->whereBetween('date_commande', array($debut, $fin))
            ->where('centre_id', '=', Auth::user()->centre_id)
            ->get();
        $nb_cmde = $achatp->count()+1;
        $commande_id = '00'.$nb_cmde.'CMDE'.date('m').date('Y').Auth::user()->id.Auth::user()->centre_id;
        return $commande_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage-action',['commande','lister']);
        $commande = new Commande();
        $code = $this->commande_id();

        $fournisseurs = [];
        $produits = DB::table('produits')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('produits.statut','=','true')
            ->where('categories.type','=','Stockable')
            ->get();

        $categories = Categorie::where('statut','=','true')->pluck('libelle','categorie_id');

        $pdtcon = DB::table('produit_commandes')
            ->join('produits','produits.produit_id','=','produit_commandes.produit_id')
            ->where('produit_commandes.code','=',$code)
            ->get();

        return view('commande.index', compact('produits','pdtcon','code','commande','fournisseurs','categories'));
    }

    public function fournisseurs(){
        if (\request()->ajax()){
            $fournisseurs = DB::table('fournisseurs')
                ->where('statut','=','true')
                ->get();
            return response()->json($fournisseurs);
        }
    }

    public function four_edit($fournisseur_id){
        if (\request()->ajax()){
            $four = Fournisseur::find($fournisseur_id);

            $fournisseurs = DB::table('fournisseurs')
                ->where('fournisseur_id','<>',$fournisseur_id)
                ->where('statut','=','true')
                ->get();
            return response()->json(['four'=>$four,'fours'=>$fournisseurs]);
        }
    }

    public function rech_pdtcon($code)
    {
        $pdtcon = DB::table('produit_commandes')
            //->join('produits','produits.produit_id','=','produit_commandes.produit_id')
            ->where('code','=',$code)
            ->get();
        $output='<table class="table table-striped table-bordered contour_table" id="pdt_selected">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">Produit</td>
               <td class="price">Qte</td>
               <td class="price">PU</td>
               <td class="price">Mont</td>
               <td></td>
               <td></td>
           </tr>
           </thead>
           <tbody>';
        foreach($pdtcon as $produit){
            $button_edit = '<button type="button" name="editer" id="'.$produit->produit_commande_id.'" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';;
            $button_supp = '<button type="button" name="delete" id="'.$produit->produit_commande_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';;

            $output .='<tr>
                 <td class="cart_title">'.$produit->libelle.'</td>
                 <td class="cart_price">'.$produit->qte.'</td>
                 <td class="cart_price">'.$produit->prix_achat.'</td>
                 <td class="cart_price">'.$produit->montant.'</td>
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
            return response()->json(Produit::find($id));
        }
    }

    public function select_edit($id){
        if(request()->ajax()) {
            return response()->json(ProduitCommande::find($id));
        }
    }

    public function rech_mont($code){
        if(request()->ajax())
        {
            $montant = DB::table('produit_commandes')
                ->where('code','=',$code)
                ->sum('montant');
            return response()->json($montant);
        }

    }

    public function add(Request $request)
    {
        $this->authorize('manage-action',['commande','creer']);
        $rules = array(
            'produit_id'     =>  'required',
            'qte'     =>  'required|numeric|min:0',
            'prix_achat'     =>  'required|numeric|min:0'
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
            'reference' => $request->reference,
            'qte' => $request->qte,
            'prix_achat' => $request->prix_achat,
            'montant' => $request->prix_achat*$request->qte
        );

        $con_cmde = ProduitCommande::where('code','=',$request->hidden_code)
            ->where('produit_id','=',$request->produit_id)
            ->get();

        if ($request->hidden_idcon==null){
            if (count($con_cmde)==0){
                DB::beginTransaction();
                try {
                    ProduitCommande::create($form_data);
                    //DB::connection('vps')->table('produit_commandes')->insert($form_data);
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
                ProduitCommande::find($request->hidden_idcon)->update($form_data);
                //DB::connection('vps')->table('produit_commandes')->where('produit_commande_id',$request->hidden_idcon)->update($form_data);
                DB::commit();
                return response()->json(['success' => 'Produit modifie avec success']);
            }catch (\PDOException $se) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur survenu lors de l execution. produit non ajouter '.$se]);
            }
        }
    }

    public function delete($id){
        if(request()->ajax()) {
            try {
                DB::beginTransaction();
                ProduitCommande::find($id)->delete();
                //DB::connection('vps')->table('produit_commandes')->where('produit_commande_id',$id)->delete();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Alert::error('Erreur !', 'Erreur survenu lors de l execution.');
            }
        }
    }

    public function store(Request $request)
    {
        $this->authorize('manage-action',['commande','creer']);
        $rules = array(
            'fournisseur_id'     =>  'required|numeric|min:1',
            'date_commande'     =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        //dd($error->fails());
        if($error->fails())
        {
            dd('erreur');
            Alert::error('Erreur','Merci de definir la date et le fournisseur');
            return redirect()->route('cmde.index');
        }
        $montant = DB::table('produit_commandes')
            ->where('code','=',$request->code)
            ->sum('montant');
        $form_data = array(
            'code' =>  $request->code,
            'date_commande' =>  $request->date_commande,
            'montant' =>  $montant,
            'fournisseur_id' =>  $request->fournisseur_id,
            'etat' => 'Encours',
            'user_id'   =>  Auth::user()->id,
            'centre_id'   =>  Auth::user()->centre_id,
            'dps_id'   =>  Auth::user()->dps_id,
        );

        DB::beginTransaction();
        try {
            Commande::create($form_data);
            //DB::connection('vps')->table('commandes')->insert($form_data);
            $id = DB::getPdo()->lastInsertId();
            DB::table('produit_commandes')
                ->where('code','=',$request->code)
                ->update(['commande_id'=>$id]);
            // DB::connection('vps')->table('commandes')
            //     ->where('code','=',$request->code)
            //     ->update(['commande_id'=>$id]);
            Alert::success('Success !', 'commande enregistree avec success.');
            DB::commit();
            return redirect()->route('cmde.index');
        }catch (\PDOException $se){
            DB::rollBack();
            //dd($se);
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.');
            return redirect()->route('cmde.index');
        }
    }

    public function edit($id)
    {
        $this->authorize('manage-action',['commande','editer']);
        $cmde = Commande::find($id);
        if ($cmde->etat=='Annulee'){
            Alert::error('Info','Impossible d editer une commande annulee');
            return back();
        }elseif ($cmde->etat=='Livree'){
            Alert::info('Info','Impossible d editer une commande livree');
            return back();
        }elseif ($cmde->etat=='Partielle'){
            Alert::warning('Info','Impossible d editer une commande encours de livraison');
            return back();
        }else{
            Session::put('commande_id',$id);
            return redirect()->route('cmde.editer');
        }


    }

    public function editer()
    {
        $this->authorize('manage-action',['commande','editer']);
        $commande_id = Session::get('commande_id');
        Session::forget('commande_id');
        if ($commande_id==null){
            return redirect()->route('cmde.histo');
        }
        $commande = Commande::find($commande_id);
        //dd($commande);

        $fournisseurs = [];
        $produits = DB::table('produits')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('produits.statut','=','true')
            ->where('categories.type','=','Stockable')
            ->get();

        $categories = Categorie::where('statut','=','true')->pluck('libelle','categorie_id');

        $pdtcon = DB::table('produit_commandes')
            ->join('produits','produits.produit_id','=','produit_commandes.produit_id')
            ->where('produit_commandes.commande_id','=',$commande_id)
            ->get();
        $code = $commande->code;
        if (Auth::user()->ut==1){
            return view('commande.edit', compact('produits','pdtcon','commande_id','commande','fournisseurs','code','categories'));
        }elseif (Auth::user()->ut=2){
            return view('commande.editc', compact('produits','pdtcon','commande_id','commande','fournisseurs'));
        }elseif (Auth::user()->ut==3){
            return view('commande.editp', compact('produits','pdtcon','commande_id','commande','fournisseurs'));
        }else{
            //Rien a faire
        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Commande  $commande
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$commande_id)
    {
        $this->authorize('manage-action',['commande','editer']);
        $rules = array(
            'fournisseur_id'     =>  'required|numeric|min:1',
            'date_commande'     =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        //dd($error->fails());
        if($error->fails())
        {
            Alert::error('Erreur','Merci de definir la date et le fournisseur');
            return redirect()->route('cmde.index');
        }
        $montant = DB::table('produit_commandes')
            ->where('code','=',$request->code)
            ->sum('montant');
        $form_data = array(
            'date_commande' =>  $request->date_commande,
            'montant' =>  $montant,
            'fournisseur_id' =>  $request->fournisseur_id,
            'user_id'   =>  Auth::user()->id,
        );
        $commande = Commande::find($commande_id);
        //dd($commande);
        DB::beginTransaction();
        try {
            $commande->update($form_data);
            //DB::connection('vps')->table('commandes')->where('commande_id',$commande_id)->update($form_data);
            DB::table('produit_commandes')
                ->where('code','=',$request->code)
                ->update(['commande_id'=>$commande_id]);
            // DB::connection('vps')->table('commandes')
            //     ->where('code','=',$request->code)
            //     ->update(['commande_id'=>$commande_id]);
            Alert::success('Success !', 'commande modifiee avec success.');
            DB::commit();
            return redirect()->route('cmde.histo');
        }catch (\PDOException $se){
            DB::rollBack();
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.'.$se);
            return redirect()->route('cmde.histo');
        }
    }

    public function histo(Request $request){
        $this->authorize('manage-action',['commande','lister']);
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('commandes')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->join('users','users.id','=','commandes.user_id')
                ->where('commandes.centre_id', '=', Auth::user()->centre_id)
                ->whereBetween('commandes.date_commande', array($request->from_date, $request->to_date))
                //->where('commandes.etat','<>','Annulee')
                ->get();
        }
        else
        {
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('commandes')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->join('users','users.id','=','commandes.user_id')
                ->where('commandes.centre_id', '=', Auth::user()->centre_id)
                ->whereBetween('commandes.date_commande', array($debut, date('Y-m-d')))
                //->where('commandes.etat','<>','Annulee')
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        return view('commande.histo', compact('historiques'));
    }

    public function commande(Request $request){
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('commandes')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->join('users','users.id','=','commandes.user_id')
                ->where('commandes.centre_id', '=', Auth::user()->centre_id)
                ->whereBetween('commandes.date_commande', array($request->from_date, $request->to_date))
                //->where('commandes.etat','<>','Annulee')
                ->get();
        }
        else
        {
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('commandes')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->join('users','users.id','=','commandes.user_id')
                ->where('commandes.centre_id', '=', Auth::user()->centre_id)
                ->whereBetween('commandes.date_commande', array($debut, date('Y-m-d')))
                //->where('commandes.etat','<>','Annulee')
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        return view('commande.histo', compact('historiques'));

    }


    protected function imprimer($id){
        $commande = DB::table('commandes')
            ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
            ->join('users','users.id','=','commandes.user_id')
            ->where('commandes.commande_id','=', $id)
            ->get();

        $commande = (object) $commande[0];
        if ($commande->etat=='Annulee'){
            Alert::error('Erreur','Impossible d imprimer une commande annulee');
            return back();
        }else{
            $date = new \DateTime($commande->date_commande);
            $date_commande = $date->format('d-m-Y');

            $categories = DB::table('produit_commandes')
                ->join('produits','produits.produit_id','=','produit_commandes.produit_id')
                ->join('categories','categories.categorie_id','=','produits.categorie_id')
                ->where('produit_commandes.commande_id','=',$id)
                ->select('produits.categorie_id','categories.libelle')->distinct()
                ->get();
            $cout_achat=0;
            $cout_vente=0;
            $cout_achat_total=0;
            $cout_vente_total=0;

            $centre  = Centre::find(Auth::user()->centre_id);

            $output ='<table>
                        <tr>
                            <td width="15%">
                                <img src="/images/logo.png" width="100" height="50">
                            </td>
                            <td width="85%">
                                <div>'.$centre->nom_centre.'</div>
                                <div style="font-size: 10px">'.$centre->services.'</div>
                                <div style="font-style: italic">'.$centre->adresse.'</div>
                                <div style="font-style: italic">'.$centre->telephone.'</div>
                            </td>
                        </tr>
                    </table>
                    <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 10px">
                        <tr>
                            <td width="50%">Commande N° <b>' .$commande->code.'</b></td>
                            <td width="50%">Date  <b>'.$date_commande.'</b></td>
                        </tr>
                        <tr>
                            <td width="50%">Utilisateur: <b>' .$commande->name.'</b></td>
                            <td width="50%">Fournisseur: <b>'.$commande->nom.'</b> / '.$commande->telephone.'</td>
                        </tr>
                    </table>
                    <br>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">';
            foreach($categories as $categorie){
                $produits = DB::table('produit_commandes')
                    ->join('produits','produits.produit_id','=','produit_commandes.produit_id')
                    ->where('produit_commandes.commande_id','=',$id)
                    ->where('produits.categorie_id','=',$categorie->categorie_id)
                    ->get();
                $cout_achat = DB::table('produit_commandes')
                    ->join('produits','produits.produit_id','=','produit_commandes.produit_id')
                    ->where('produit_commandes.commande_id','=',$id)
                    ->where('produits.categorie_id','=',$categorie->categorie_id)
                    ->sum('produit_commandes.montant');

                $cout_achat_total+=$cout_achat;
                $output .='
                            <tr style="border-collapse: collapse; border: 1px solid; background-color: #fffde7; text-align: center; size: 20px">
                                <td colspan="2" style="border: 1px solid;">'.$categorie->libelle.'</td>
                            </tr>
                            <tr style="border-collapse: collapse; border: 1px solid; text-align: center; size: 20px">
                                <td colspan="2" style="border: 1px solid;">
                                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                                        <thead>
                                            <tr style="border-radius: 10px; background-color: #F7F4F3";>
                                                <th width="10%">Reference</th>
                                                <th width="43%">Libelle</th>
                                                <th width="12%">Type</th>
                                                <th width="10%">Qte</th>
                                                <th width="10%">Prix Achat</th>
                                                <th width="15%">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                                        foreach($produits as $produit){
                                            $output .='
                                            <tr style="border-collapse: collapse; border: 1px solid;">
                                                <td style="border: 1px solid;">'.$produit->reference.'</td>
                                                <td style="border: 1px solid;">'.$produit->libelle.'</td>
                                                <td style="border: 1px solid; text-align: left">'.($produit->famille_therapeutique).'</td>
                                                <td style="border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                                                <td style="border: 1px solid; text-align: right">'.number_format($produit->prix_achat,'0','.',' ').'</td>
                                                <td style="border: 1px solid; text-align: right">'.number_format($produit->montant,'0','.',' ').'</td>
                                            </tr>';
                                        }
                                        $output .='
                                            <tr style="border-collapse: collapse; border: 1px solid;text-align: center;font-style: italic; font-size: 16px">
                                                <td colspan="2" style="border: 1px solid;">Sous Total</td>
                                                <td colspan="4" style="border: 1px solid; text-align: right">Cout Achat => '.number_format($cout_achat,'0','.',' ').' Fr CFA</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            ';
                                }
                                $output .='

                                <tr style="border-collapse: collapse; border: 1px solid;">
                                <td colspan="2" style="text-align: center;font-weight: bold; font-size: 16px">COUT TOTAL DE LA COMMANDE => '.number_format($cout_achat_total,'0','.',' ').' Fr CFA </td>
                                </tr>
                            </tbody>
                        </table><br><br>
                       <table>
                            <tr style="border-collapse: collapse; border: 0px solid;">
                                <td style="text-align: center; font-size: 18px">Responsable Achat </td>
                            </tr>
                       </table>';

            return $output;
        }
    }

    protected function show($id){
        $output = $this->imprimer($id);
        /*$pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($output);
        return $pdf->stream();*/
        return $output;
    }

    protected function infos($id){
        $output = $this->imprimer($id);

        return $output;
    }

    public function rech_cmde($id){
        if (\request()->ajax()){
            $cmde = Commande::find($id);
            return $cmde;
        }
    }

    public function delete_cmde($id){
        $this->authorize('manage-action',['commande','supprimer']);
        if (\request()->ajax()){
            try {
               DB::beginTransaction();
               Commande::find($id)->update(['etat'=>'Annulee']);
               //DB::connection('vps')->table('commandes')->where('commande_id',$id)->update(['etat'=>'Annulee']);
               DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Alert::error('Erreur !', 'Erreur survenu lors de l execution.');
            }
        }
    }

    public function annuler_cmde($id){
        if (\request()->ajax()){
            try {
                DB::beginTransaction();
                DB::table('produit_commandes')->where('commande_id','=',$id)->delete();
                //DB::connection('vps')->table('produit_commandes')->where('commande_id','=',$id)->delete();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Alert::error('Erreur !', 'Erreur survenu lors de l execution.');
            }
        }
    }

}