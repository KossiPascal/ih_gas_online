<?php

namespace App\Http\Controllers;

use App\Models\Assurance;
use App\Models\Categorie;
use App\Models\Centre;
use App\Models\ProduitVente;
use App\Exports\VenteExport;
use App\Models\Magasin;
use App\Models\Mouvement;
use App\Models\Produit;
use App\Models\Patient;

use App\Models\StockProduit;
use App\Models\Reglement;
use App\Models\User;
use App\Models\Usercon;
use App\Models\Vente;
use Barryvdh\DomPDF\PDF;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;

class VenteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function ven_code(){
        $debut = date('Y').'-'.date('m').'-01';
        $fin = date('Y-m-d');
        //dd($debut,$fin);
        $nb_ven = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('user_id','=',Auth::user()->id)
            ->where('centre_id','=',Auth::user()->centre_id)
            ->count()+1;

        $vente_id = '0'.$nb_ven.date('m').date('y').Auth::user()->id.Auth::user()->centre_id;
        $old_num = Vente::find($vente_id);
        //dd($old_num);
        $control='';
        while ($old_num!=null){
            $control=$control.'1';
            $form_prod = array(
                'code' =>  $vente_id.'INCREMENTE'.$control.rand(5,20).Auth::user()->centre_id,
                'date_vente'  =>  $fin,
                'montant_total'  =>  0,
                'prise_en_charge'  =>  0,
                'net_apayer'  =>  0,
                'montant_paye'  =>  0,
                'montant_recu'  =>  0,
                'reliquat'  =>  0,
                'patient_id'  =>  '00',
                'assurance_id'  =>  1,
                'user_id'   =>  Auth::user()->id,
                'centre_id'   =>  Auth::user()->centre_id
            );
            try {
                DB::beginTransaction();
                    Vente::create($form_prod);
                    DB::connection('vps')->table('ventes')->insert($form_prod);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
            }

            $nb_ven = DB::table('ventes')
                    ->whereBetween('date_vente', array($debut, $fin))
                    ->where('user_id','=',Auth::user()->id)
                    ->where('centre_id','=',Auth::user()->centre_id)
                    ->count()+1;

            $vente_id = '0'.$nb_ven.date('m').date('y').Auth::user()->id.Auth::user()->centre_id;
            $old_num = Vente::find($vente_id);
        }/*else{
            $vente_id = '0'.$nb_ven.date('m').date('y').Auth::user()->id;
        }*/
        return $vente_id;
    }

    private function code_patient()
    {
        $nb_pat = DB::table('patients')->count()+1;
        $centre = Centre::find(Auth::user()->centre_id);
        $code = '0'.$nb_pat.substr($centre->nom_centre,4,4).date('y').Auth::user()->centre_id;
        return $code;
    }

    public function index()
    {
        $this->authorize('manage-action',['vente','lister']);
        if(Session::get('magasin_id')){
            $magasin_id = Session::get('magasin_id');
            $magasin = Magasin::find($magasin_id);
            $code = $this->ven_code();
            $patient = Patient::pluck('nom_prenom','patient_id');
            $assurances = [];
            $produits = [];
            $autres = DB::table('produits')
                ->join('categories','categories.categorie_id','=','produits.categorie_id')
                ->where('categories.type','=','Non_Stockable')
                ->where('categories.statut','=','true')
                ->where('produits.statut','=','true')
                ->get();

            foreach ($autres as $pdt){
                $produit = new \stdClass();
                $produit->categorie_id = $pdt->categorie_id;
                $produit->produit_id = $pdt->produit_id;
                $produit->libelle = $pdt->nom_commercial;
                $produit->pv = $pdt->prix_vente;
                $produit->qte = 1;
                array_push($produits,$produit);
            }


            $vente = new Vente();
            $allProduits = DB::table('stock_produits')
                ->join('produits','produits.produit_id','=','stock_produits.produit_id')
                ->join('magasins','magasins.magasin_id','=','stock_produits.magasin_id')
                ->where('stock_produits.etat','<>','Delete')
                //->where('magasins.type','=','Depot_vente')
                ->where('magasins.magasin_id','=',$magasin_id)
                ->where('stock_produits.qte','>','0')
                ->where('produits.statut','=','true')
                ->select('produits.produit_id')->distinct()
                ->get();
            foreach ($allProduits as $pdt){
                array_push($produits,$this->rechpdtPQ($pdt->produit_id,$magasin_id));
            }

            $pdtcon = [];
            $code_patient = $this->code_patient();

            return view ('vente.index', compact('vente','produits','code','patient','assurances','magasin','code_patient'));
        }else{
            return redirect()->route('vente.select_mag');
        }

    }

    public function ventes()
    {
        $ventes = DB::table('ventes')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->where('ventes.etat','=','Encours')
            ->get();
        return view ('vente.ventes', compact('ventes'));
    }

    public function selectionner($id)
    {
        $vente = Vente::findOrfail($id);
        if ($vente){
            Session::put('vente',$vente);
            return redirect()->route('vente.encaisse');
        }else{
            Alert::error('Erreur','Vente inexistante');
            return back();
        }
    }

    public function select_mag(){
        $magasins = DB::table('magasins')
            ->where('statut','=','true')
            ->where('centre_id','=',Auth::user()->centre_id)
            ->get();

        return view ('vente.select_mag', compact('magasins'));
    }

    public function mag_source($magasin_id){
        Session::put('magasin_id',$magasin_id);

        return redirect()->route('vente.index');
    }

    public function encaisse()
    {
        $this->authorize('manage-action',['vente','encaisser']);
        $vent = Session::get('vente');
        $unevente = DB::table('ventes')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->where('ventes.vente_id','=',$vent->vente_id)
            ->get();
        $vente = (object) $unevente[0];

        $pdtcon = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->where('produit_ventes.vente_id','=',$vent->vente_id)
            ->get();

        return view ('vente.encaisse', compact('vente','pdtcon'));
    }

    private function rechpdtPQ($produit_id,$magasin_id){
        $pdtqp = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->where('stock_produits.etat','<>','Delete')
            ->where('stock_produits.magasin_id','=',$magasin_id)
            ->where('stock_produits.produit_id','=',$produit_id)
            ->where('stock_produits.qte','>','0')
            ->get();
        $produit = new \stdClass();
        $pv = Produit::find($produit_id)->prix_vente;
        $pdt = (object) $pdtqp[0];
        $qte = DB::table('stock_produits')
            ->where('etat','<>','Delete')
            ->where('produit_id','=',$produit_id)
            ->where('magasin_id','=',$magasin_id)
            ->where('qte','>','0')
            ->sum('qte');

        $produit->categorie_id = $pdt->categorie_id;
        $produit->produit_id = $pdt->produit_id;
        $produit->libelle = $pdt->libelle;
        $produit->pv = $pv;
        $produit->qte = $qte;
        return $produit;
    }

    public function assurances(){
        if (\request()->ajax()){
            $assurances = DB::table('assurances')
                ->where('statut','=','true')
                //->where('centre_id','=',Auth::user()->centre_id)
                ->get();
            return $assurances;
        }
    }

    public function rech_pdtcon($code)
    {
        $pdtcon = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->where('produit_ventes.code','=',$code)
            ->get();
        if (count($pdtcon)==0){
            $output='<table class="table table-striped table-bordered contour_table" id="pdt_selected">
               <thead>
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <td class="description">Produit</td>
                       <td class="price">Prix</td>
                       <td class="quantity">Qte</td>
                       <td class="total">Total</td>
                       <td class="total">PEC</td>
                       <td class="total">Net</td>
                       <td></td>
                   </tr>
               </thead>
                <tbody>
                    <tr>
                         <td class="cart_title"></td>
                         <td class="cart_price"></td>
                         <td class="cart_price"></td>
                         <td class="cart_total"></td>
                         <td class="cart_total"></td>
                         <td class="cart_delete"></td>
                         <td class="cart_delete"></td>
                     </tr>
                 </body>
                </table>';
        }else{
            $output='<table class="table table-striped table-bordered contour_table" id="pdt_selected">
               <thead>
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <td class="description">Code</td>
                       <td class="description">Produit</td>
                       <td class="price">Prix</td>
                       <td class="quantity">Qte</td>
                       <td class="total">Total</td>
                       <td class="total">PEC</td>
                       <td class="total">Net</td>
                       <td colspan="2"></td>
                   </tr>
               </thead>
                <tbody>';
                    foreach($pdtcon as $produit){
                        $button_edit = '<button type="button" name="edit" id="'.$produit->produit_vente_id.'" class="edit btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                        $button_delete = '<button type="button" name="delete" id="'.$produit->produit_vente_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                        $output .='<tr>
                         <td class="cart_title">'.$produit->reference.'</td>
                         <td class="cart_title">'.$produit->libelle.'</td>
                         <td class="cart_price">'.$produit->pu.'</td>
                         <td class="cart_price">'.$produit->qte.'</td>
                         <td class="cart_total">'.$produit->mont.'</td>
                         <td class="cart_total">'.$produit->pec.'</td>
                         <td class="cart_total">'.$produit->net.'</td>
                         <td class="cart_delete">'.$button_edit.'</td>
                         <td class="cart_delete">'.$button_delete.'</td>
                     </tr>';
                    }
                    $output.='</body>
                </table>';
        }
        return $output;
    }

    public function rech_mont($vente_id)
    {
        if(request()->ajax())
        {
            $mont = ProduitVente::where('code','=',$vente_id)->sum('mont');
            $pec = ProduitVente::where('code','=',$vente_id)->sum('pec');
            $net = ProduitVente::where('code','=',$vente_id)->sum('net');
            return response()->json(['mont' => $mont,'pec' => $pec,'net' => $net]);
        }
    }

    public function rechtaux($assurance_id){
        if(request()->ajax()) {
            $taux = assurance::find($assurance_id)->taux;
            return response()->json($taux);
        }
    }

    public function rech_code($date_vente)
    {
        $mois = substr($date_vente,5,2);
        $annee = substr($date_vente,0,4);
        $ar = substr($date_vente,2,2);
        $nbrjour = cal_days_in_month(CAL_GREGORIAN,$mois,$annee);
        $debut = $annee.'-'.$mois.'-01';
        $fin = $annee.'-'.$mois.'-'.$nbrjour;
        $ventep = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('user_id','=',Auth::user()->id)
            ->get();
        $nb_ven = $ventep->count()+1;

        $vente_id = '0'.$nb_ven.$mois.$ar.Auth::user()->id.Auth::user()->centre_id;

        $old_num = Vente::find($vente_id);
        if ($old_num){
            $form_prod = array(
                'code' =>  $vente_id.'-INCREMENTE',
                'date_vente'  =>  $date_vente,
                'montant_total'  =>  0,
                'prise_en_charge'  =>  0,
                'net_apayer'  =>  0,
                'montant_recu'  =>  0,
                'montant_paye' => 0,
                'reliquat'  =>  0,
                'patient_id'  =>  '0',
                'assurance_id'  =>  1,
                'user_id'   =>  Auth::user()->id,
                'centre_id'   =>  Auth::user()->centre_id
            );
            try {
                DB::beginTransaction();
                    Vente::create($form_prod);
                    DB::connection('vps')->table('ventes')->insert($form_prod);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
            }
            $ventep = DB::table('ventes')
                ->whereBetween('date_vente', array($debut, $fin))
                ->where('user_id','=',Auth::user()->id)
                ->get();
            $nb_ven = $ventep->count()+1;

            $vente_id = '0'.$nb_ven.$mois.$ar.Auth::user()->id.Auth::user()->centre_id;
        }else{
            $vente_id = '0'.$nb_ven.$mois.$ar.Auth::user()->id.Auth::user()->centre_id;
        }

        if(request()->ajax())
        {
            return response()->json(['code' => $vente_id,'Nbre Jours'=>$nbrjour]);
        }
    }

    public function select($id)
    {
        if(request()->ajax())
        {
            $produit = new \stdClass();
            $pdt = Produit::find($id);
            $categorie = Categorie::find($pdt->categorie_id);
            if ($categorie->type=='Non_stockable'){
                $produit->categorie_id = $pdt->categorie_id;
                $produit->produit_id = $pdt->produit_id;
                $produit->libelle = $pdt->libelle;
                $produit->pv = $pdt->prix_vente;
                $produit->qte = 1;

            }else{
                $produit = $this->rechpdtPQ($id,Session::get('magasin_id'));
            }
            return response()->json($produit);
        }

    }

    public function select_mut($id_mut,$produit_id)
    {
        if(request()->ajax())
        {
            $produit = new \stdClass();
            $pdt = Produit::find($produit_id);
            $pdt_mut = Assurance::find($id_mut);

            $produit->categorie_id = $pdt->categorie_id;
            $produit->produit_id = $pdt->produit_id;
            $produit->libelle = $pdt->libelle;
            $produit->base = $pdt_mut->base;
            $produit->taux = $pdt_mut->taux;

            $categorie = Categorie::find($pdt->categorie_id);
            if ($categorie->type=='Non_stockable'){
                $produit->pv = $pdt->prix_vente;
                $produit->qte = 1;

            }else{
                $pdtqp = DB::table('stock_produits')
                    ->join('produits','produits.produit_id','=','stock_produits.produit_id')
                    ->join('magasins','magasins.magasin_id','=','stock_produits.magasin_id')
                    ->where('stock_produits.etat','<>','Delete')
                    ->where('magasins.type','=','Depot_vente')
                    ->where('stock_produits.produit_id','=',$produit_id)
                    ->where('stock_produits.qte','>','0')
                    ->get();

                $pdt = (object) $pdtqp[0];

                if (count($pdtqp)==1){
                    $produit->pv = $pdt->pv;
                    $produit->qte = $pdt->qte;
                }else{
                    $qte = DB::table('stock_produits')
                        ->where('etat','<>','Delete')
                        ->where('produit_id','=',$produit_id)
                        ->where('magasin_id','=',$pdt->magasin_id)
                        ->where('qte','>','0')
                        ->sum('qte');
                    $rech_pv = DB::table('stock_produits')
                        ->where('produit_id','=',$produit_id)
                        ->where('magasin_id','=',$pdt->magasin_id)
                        ->where('etat','=','Encours')
                        ->get();

                    $rech_prix = (object) $rech_pv[0];
                    $pv = $rech_prix->pv;

                    $produit->pv = $pv;
                    $produit->qte = $qte;
                }
            }
            return response()->json($produit);
        }

    }

    public function rechPdtMut($id,$mut)
    {
        if(request()->ajax()) {
            $produit = new \stdClass();
            $pdt = Produit::find($id);
            $lesproduits = DB::table('assurances')
                ->where('libelle','LIKE','%'.$pdt->libelle.'%')
                //->orWhere('dci','LIKE','%'.$pdt->libelle.'%')
                ->get();

            //dd($lesproduits);
            $output = '';

            if (count($lesproduits)==0){
                $output = response()->json($output);
            }else{
                $output='<table class="table table-striped table-bordered contour_table" id="pdt_assur">
               <thead>
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <th>Libelle</th>
                       <th>DCI</th>
                        <th>Prix Public</th>
                        <th>Base</th>
                        <th>Taux</th>
                        <th>Choisir</th>
                   </tr>
               </thead>
                <tbody>';
                foreach($lesproduits as $produit){
                    $output .='<tr>
                     <td class="cart_title">'.$produit->libelle.'</td>
                     <td class="cart_title">'.$produit->dci.'</td>
                     <td class="cart_total" style="color: #95124e;font-weight: bold">'.$produit->prix.'</td>
                     <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$produit->base.'</td>
                     <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$produit->taux.'</td>
                     <td class="select_mut btn btn-danger" id="'.$produit->id.'"><i class="fa fa-check"></i><input type="text" id="produit_id" name="produit_id" value="'.$pdt->produit_id.'"></td>
                 </tr>';
                }
                $output.='</body>
                </table>';
            }
            return $output;
        }

    }

    public function select_edit($id)
    {
        if(request()->ajax())
        {
            return response()->json(ProduitVente::find($id));
        }
    }

    public function add(Request $request)
    {
        $this->authorize('manage-action',['vente','creer']);
        $rules = array(
            'pu'     =>  'required|numeric|min:0',
            'base'     =>  'required|numeric|min:0',
            'marge'     =>  'required|numeric|min:0',
            'taux_pdt'     =>  'required|numeric|min:0',
            'qte'     =>  'required|numeric|min:0'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $mont = ($request->pu*$request->qte);
        $pec = ($request->base*$request->qte)*$request->taux_pdt/100;
        $net = $mont - $pec;
        $form_data = array(
            'code' =>  $request->hidden_code,
            'produit_id'  =>  $request->produit_id,
            'categorie_id'  =>  $request->categorie_id,
            'libelle'  =>  $request->libelle,
            'lot'  =>  $request->lot,
            'pu'  =>  $request->pu,
            'base'  =>  $request->base,
            'ini'  =>  $request->ini,
            'qte'   =>  $request->qte,
            'mont'   =>  $mont,
            'pec'   =>  $pec,
            'net'   =>  $net,
            'taux_pdt'  =>  $request->taux_pdt
        );

        $con_ven = ProduitVente::where('code','=',$request->hidden_code)
            ->where('produit_id','=',$request->produit_id)->get();

        if ($request->produit_vente_id==null){
            $categorie = Categorie::find($request->categorie_id);
            if (count($con_ven)==0) {
                DB::beginTransaction();
                try {
                    if ($categorie->type=='Stockable'){
                        if ($request->ini-$request->qte>=0){
                            ProduitVente::create($form_data);
                            DB::connection('vps')->table('produit_ventes')->insert($form_data);
                            DB::commit();
                            return response()->json(['success' => 'Produit ajoutet']);
                        }else{
                            return response()->json(['error' => 'Quantite saisie depasse la quantite disponible']);
                        }
                    }else{
                        ProduitVente::create($form_data);
                        DB::connection('vps')->table('produit_ventes')->insert($form_data);
                        DB::commit();
                        return response()->json(['success' => 'Produit ajoutet']);
                    }
                }catch (\PDOException $se) {
                    DB::rollBack();
                    return response()->json(['error' => 'Erreur survenu lors de l execution. produit non ajoute'.$se]);
                }
            }else{
                return response()->json(['error' => 'Produit existe deja dans la selection']);
            }
        }else{
            $concerne = DB::table('produit_ventes')
                //->join('produit_ventes','produit_ventes.produit_id','=','produits.produit_id')
                ->join('categories','categories.categorie_id','=','produit_ventes.categorie_id')
                ->where('produit_ventes.produit_vente_id','=',$request->produit_vente_id)
                ->get();
            $categorie = (object) $concerne[0];
            DB::beginTransaction();
            try {
                if ($categorie->type=='Stockable'){
                    if ($request->ini-$request->qte>=0){
                        ProduitVente::find($request->produit_vente_id)->update($form_data);
                        DB::connection('vps')->table('produit_ventes')->where('produit_vente_id',$request->produit_vente_id)->update($form_data);
                        DB::commit();
                        return response()->json(['success' => 'Produit ajoutet']);
                    }else{
                        return response()->json(['error' => 'Quantite saisie depasse la quantite disponible']);
                    }
                }else{
                    ProduitVente::find($request->produit_vente_id)->update($form_data);
                    DB::connection('vps')->table('produit_ventes')->where('produit_vente_id',$request->produit_vente_id)->update($form_data);
                    DB::commit();
                    return response()->json(['success' => 'Produit modifie']);
                }
            }catch (\PDOException $se) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur survenu lors de l execution. produit non modifier']);
            }
        }
    }

    public function delete($id){
        if (\request()->ajax()){
            try{
                DB::beginTransaction();
                    ProduitVente::find($id)->delete();
                    DB::connection('vps')->table('produit_ventes')->where('produit_vente_id',$id)->delete();
                DB::commit();
                return back()->with('success', 'Produit retire');
            }catch(\Throwable $th){
                DB::rollBack();
            }
        }
    }

    public function annuler($id){
        if (\request()->ajax()){
            try{
                DB::beginTransaction();
                    DB::table('produit_ventes')
                        ->where('code','=',$id)
                        ->delete();
                    DB::connection('vps')->table('produit_ventes')->where('code',$id)->delete();
                DB::commit();
                return back()->with('success', 'Vente annulee');
            }catch(\Throwable $th){
                DB::rollBack();
            }
        }

    }

    public function annulervente($id){
        if (\request()->ajax()){
            $produits = DB::table('produit_ventes')
                //->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->where('vente_id','=',$id)
                ->get();
            $vente = Vente::find($id);

            DB::beginTransaction();
            try {
                foreach ($produits as $produit){
                    $categorie = Categorie::find($produit->categorie_id);

                    if ($categorie->type=='Stockable'){
                        $pdtqtes = DB::table('stock_produits')
                            ->join('magasins','magasins.magasin_id','=','stock_produits.magasin_id')
                            ->where('magasins.type','=','Depot_vente')
                            ->where('stock_produits.etat','<>','Delete')
                            ->where('stock_produits.produit_id','=',$produit->produit_id)
                            ->orderBy('stock_produits.date_peremption')
                            ->get();

                        $qteIni = DB::table('stock_produits')
                            ->join('magasins','magasins.magasin_id','=','stock_produits.magasin_id')
                            ->where('magasins.type','=','Depot_vente')
                            ->where('stock_produits.etat','<>','Delete')
                            ->where('stock_produits.produit_id','=',$produit->produit_id)
                            ->sum('stock_produits.qte');

                        $qte = $produit->qte;

                        for ($i = 0; $i < count($pdtqtes); $i++){
                            if ($pdtqtes[$i]->qte<$qte){
                                StockProduit::find($pdtqtes[$i]->id)->update(['qte'=>0]);
                                DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdtqtes[$i]->id)->update(['qte'=>0]);
                                $qte = $qte-$pdtqtes[$i]->qte;
                            }else{
                                StockProduit::find($pdtqtes[$i]->id)->update(['qte'=>$pdtqtes[$i]->qte-$qte]);
                                DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdtqtes[$i]->id)->update(['qte'=>$pdtqtes[$i]->qte-$qte]);
                                break;
                            }
                        }
                        Mouvement::create([
                            'date' =>  $vente->date_vente,
                            'magasin_id' =>  $pdtqtes[0]->magasin_id,
                            'produit_id' =>  $produit->produit_id,
                            'mv_lib' =>  'Vente numero '.$vente->vente_id,
                            'qte_initiale' =>  $qteIni,
                            'qte_sortie' =>  $qte,
                            'qte_reelle' =>  $qteIni-$qte,
                            'idop' =>  $vente->vente_id,
                            'idcon' =>  $produit->id
                        ]);
                        DB::connection('vps')->table('mouvements')->insert([
                            'date' =>  $vente->date_vente,
                            'magasin_id' =>  $pdtqtes[0]->magasin_id,
                            'produit_id' =>  $produit->produit_id,
                            'mv_lib' =>  'Vente numero '.$vente->vente_id,
                            'qte_initiale' =>  $qteIni,
                            'qte_sortie' =>  $qte,
                            'qte_reelle' =>  $qteIni-$qte,
                            'idop' =>  $vente->vente_id,
                            'idcon' =>  $produit->id
                        ]);
                    }
                }
                /*Reglement::create([
                    'date_reglement'=> $request->date_vente,
                    'montant_reglement'=> $mont_paye,
                    'code'=> $request->vente_id,
                    'user_id'=> Auth::user()->id
                ]);
                Vente::create($form_prod);*/
                DB::commit();

            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur','Ereur survenu lors de la sauvegarde'.$se);
                return back();
            }
        }

    }

    public function supprimer($id){
        $this->authorize('manage-action',['vente','supprimer']);
        $vente = Vente::findOrfail($id);
        if ($vente->etat!='Encours'){
            Alert::error('Erreur','Impossible d annuler cette vente. Deja encaisse');
            return redirect()->route('vente.index');
        }else{
            DB::beginTransaction();
            try {
                DB::table('produit_ventes')
                    ->where('code','=',$id)
                    ->delete();
                DB::connection('vps')->table('produit_ventes')->where('code','=',$id)->delete();

                $vente->update(['etat'=>'Annulee',
                    'montant_total'  =>  0,
                    'prise_en_charge'  =>  0,
                    'net_apayer'  =>  0,
                    'montant_paye'  =>  0,
                    'montant_recu'  =>  0,
                    'reliquat'  =>  0
                ]);
                DB::connection('vps')->table('ventes')->where('vente_id', $id)->update([
                    'montant_total'  =>  0,
                    'prise_en_charge'  =>  0,
                    'net_apayer'  =>  0,
                    'montant_paye'  =>  0,
                    'montant_recu'  =>  0,
                    'reliquat'  =>  0
                ]);
                DB::commit();
                Alert::warning('Infos','Vente Vente annulee');
                return redirect()->route('vente.index');
            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur','Erreur survenu lors de la sauvegarde');
                return redirect()->route('vente.index');
            }
        }
    }

    public function store(Request $request){
        $this->authorize('manage-action',['vente','creer']);
        $etat = 'Soldee';
        $texte = 'RELIQUAT';
        $entete = '';
        $magasin_id = Session::get('magasin_id');
        $reste = $request->reliquat;
        $mont_paye = $request->net_apayer;
        if ($request->reliquat<0) {
            $etat = 'Credit';
            $mont_paye = $request->montant_recu;

            $texte = 'RESTE A PAYER';
            $reste = $request->reliquat*(-1);
            $entete = 'ACHAT A CREDIT';
        }
        $date_vente = $request->date_vente . ' ' . date("H:i:s");
        $heure_vente = date("H:i:s");
        $form_prod = array(
            'code' => $request->code,
            'date_vente' => $date_vente,
            'heure_vente' => $heure_vente,
            'montant_total' => $request->montant_total,
            'prise_en_charge' => $request->prise_en_charge,
            'net_apayer' => $request->net_apayer,
            'montant_paye' => $mont_paye,
            'montant_recu' => $request->montant_recu,
            'reliquat' => $request->reliquat,
            'etat' => $etat,
            'patient_id' => $request->patient_id,
            'assurance_id' => $request->assurance_id,
            'user_id' => Auth::user()->id,
            'centre_id' => Auth::user()->centre_id
        );
        $produits = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->where('produit_ventes.code','=',$request->code)
            ->get();
        if (count($produits)>0){
            DB::beginTransaction();
            try {
                foreach ($produits as $produit){
                    $categorie = Categorie::find($produit->categorie_id);

                    if ($categorie->type=='Stockable'){
                        $pdtqtes = DB::table('stock_produits')
                            ->where('magasin_id','=',$magasin_id)
                            ->where('etat','<>','Delete')
                            ->where('produit_id','=',$produit->produit_id)
                            ->orderBy('date_peremption')
                            ->get();

                        $qteIni = DB::table('stock_produits')
                            ->where('magasin_id','=',$magasin_id)
                            ->where('etat','<>','Delete')
                            ->where('produit_id','=',$produit->produit_id)
                            ->sum('qte');

                        $qte = $produit->qte;

                        for ($i = 0; $i < count($pdtqtes); $i++){
                            if ($pdtqtes[$i]->qte<$qte){
                                StockProduit::find($pdtqtes[$i]->stock_produit_id)->update(['qte'=>0]);
                                DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdtqtes[$i]->stock_produit_id)->update(['qte'=>0]);
                                $qte = $qte-$pdtqtes[$i]->qte;
                            }else{
                                StockProduit::find($pdtqtes[$i]->stock_produit_id)->update(['qte'=>$pdtqtes[$i]->qte-$qte]);
                                DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdtqtes[$i]->stock_produit_id)->update(['qte'=>$pdtqtes[$i]->qte-$qte]);
                                break;
                            }
                        }
                        Mouvement::create([
                            'date' =>  $request->date_vente,
                            'magasin_id' =>  $magasin_id,
                            'produit_id' =>  $produit->produit_id,
                            'libelle' =>  $produit->libelle,
                            'centre_id' =>  Auth::user()->centre_id,
                            'motif' =>  'Vente numero '.$request->code,
                            'qte_initiale' =>  $qteIni,
                            'qte_sortie' =>  $qte,
                            'qte_reelle' =>  $qteIni-$qte,
                            'idop' =>  $request->code,
                            'idcon' =>  $produit->produit_vente_id
                        ]);
                        DB::connection('vps')->table('mouvements')->insert([
                            'date' =>  $request->date_vente,
                            'magasin_id' =>  $magasin_id,
                            'produit_id' =>  $produit->produit_id,
                            'libelle' =>  $produit->libelle,
                            'centre_id' =>  Auth::user()->centre_id,
                            'motif' =>  'Vente numero '.$request->code,
                            'qte_initiale' =>  $qteIni,
                            'qte_sortie' =>  $qte,
                            'qte_reelle' =>  $qteIni-$qte,
                            'idop' =>  $request->code,
                            'idcon' =>  $produit->produit_vente_id
                        ]);
                    }
                }
                Vente::create($form_prod);
                DB::connection('vps')->table('ventes')->insert($form_prod);

                $vente_id = DB::getPdo()->lastInsertId();
                DB::table('produit_ventes')
                    ->where('code','=',$request->code)
                    ->update(['vente_id'=>$vente_id]);
                DB::connection('vps')->table('produit_ventes')->where('code','=',$request->code)->update(['vente_id'=>$vente_id]);

                Reglement::create([
                    'date_reglement'=> $request->date_vente,
                    'montant_reglement'=> $mont_paye,
                    'vente_id'=> $vente_id,
                    'reglement_source'=> 'Vente',
                    'user_id'=> Auth::user()->id,
                    'centre_id'=> Auth::user()->centre_id
                ]);
                DB::connection('vps')->table('reglements')->insert([
                    'date_reglement'=> $request->date_vente,
                    'montant_reglement'=> $mont_paye,
                    'vente_id'=> $vente_id,
                    'reglement_source'=> 'Vente',
                    'user_id'=> Auth::user()->id,
                    'centre_id'=> Auth::user()->centre_id
                ]);

                DB::commit();

                //$vente = Vente::where('code','=',$request->vente_id)->get();
                $vente = DB::table('ventes')
                    ->join('users','users.id','=','ventes.user_id')
                    ->join('patients','patients.patient_id','=','ventes.patient_id')
                    ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
                    ->where('ventes.code','=', $request->code)
                    ->get();

                $vente = (object) $vente[0];
                $date = new \DateTime($vente->date_vente);
                $date_vente = $date->format('d-m-Y');
                $heure_vente = $date->format('H:m:s');

                $produits = DB::table('produit_ventes')
                    ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                    ->where('produit_ventes.code','=',$request->code)
                    ->get();
                $centre  = Centre::find(Auth::user()->centre_id);
                $magasin = Magasin::find($magasin_id);
                if ($centre->impression=='Format_A5'){
                    return view('etat.printven_a5', compact('vente','date_vente','produits','centre','heure_vente','entete','texte','reste','magasin'));
                }else{
                    return view('etat.printven_ticket', compact('vente','date_vente','produits','centre','heure_vente','entete','texte','reste','magasin'));
                }
            }catch (\PDOException $se){
                DB::rollBack();
                dd($se);
                Alert::error('Erreur','Ereur survenu lors de la sauvegarde'.$se);
                return back();
            }
        }else{
            Alert::warning('Infos','Pas de produits ou acte selection');
            return back();
        }
    }

    public function savepersonnel(Request $request){
        $this->authorize('manage-action',['vente','creer']);
        $date_vente = $request->date_vente . ' ' . date("H:i:s");
        $heure_vente = date("H:i:s");
        $form_prod = array(
            'code' => $request->code,
            'date_vente' => $date_vente,
            'heure_vente' => $heure_vente,
            'montant_total' => $request->montant_total,
            'prise_en_charge' => $request->prise_en_charge,
            'net_apayer' => $request->net_apayer,
            'montant_paye' => $request->net_apayer,
            'montant_recu' => $request->montant_recu,
            'reliquat' => 0,
            'etat' => 'Encours',
            'patient_id' => $request->patient_id,
            'assurance_id' => $request->assurance_id,
            'user_id' => Auth::user()->id,
        );
        $produits = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->where('produit_ventes.vente_id','=',$request->vente_id)
            ->get();
        if (count($produits)>0){
            DB::beginTransaction();
            try {

                Vente::create($form_prod);
                DB::connection('vps')->table('ventes')->insert($form_prod);
                DB::commit();
                Alert::success('Success','Vente enregistree avec success');
                return redirect()->route('vente.index');
            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur','Erreur survenu lors de la sauvegarde'.$se);
                return redirect()->route('vente.index');
            }
        }else{
            Alert::warning('Infos','Pas de produits ou acte selection');
            return redirect()->route('vente.index');
        }
    }

    public function validerCaisse(Request $request){
        $this->authorize('manage-action',['vente','valider']);
        $etat = 'Soldee';
        $texte = 'RELIQUAT';
        $entete = '';
        $reste = $request->reliquat;
        $mont_paye = $request->net_apayer;
        if ($request->reliquat<0) {
            $etat = 'Credit';
            $mont_paye = $request->montant_recu;

            $texte = 'RESTE A PAYER';
            $reste = $request->reliquat*(-1);
            $entete = 'ACHAT A CREDIT';
        }
        $date_vente = $request->date_vente . ' ' . date("H:i:s");
        $heure_vente = date("H:i:s");
        $form_prod = array(
            'code' => $request->code,
            'date_vente' => $date_vente,
            'heure_vente' => $heure_vente,
            'montant_total' => $request->montant_total,
            'prise_en_charge' => $request->prise_en_charge,
            'net_apayer' => $request->net_apayer,
            'montant_paye' => $mont_paye,
            'montant_recu' => $request->montant_recu,
            'reliquat' => $request->reliquat,
            'etat' => $etat,
            'user_id' => Auth::user()->id,
            'centre_id' => Auth::user()->centre_id
        );

        $produits = DB::table('produit_ventes')
            //->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->where('code','=',$request->code)
            ->get();
        if (count($produits)>0){
            DB::beginTransaction();
            try {
                foreach ($produits as $produit){
                    $categorie = Categorie::find($produit->categorie_id);

                    if ($categorie->type=='Stockable'){
                        $pdtqtes = DB::table('stock_produits')
                            ->join('magasins','magasins.magasin_id','=','stock_produits.magasin_id')
                            ->where('magasins.type','=','Depot_vente')
                            ->where('stock_produits.etat','<>','Delete')
                            ->where('stock_produits.produit_id','=',$produit->produit_id)
                            ->orderBy('stock_produits.date_peremption')
                            ->get();

                        $qteIni = DB::table('stock_produits')
                            ->join('magasins','magasins.magasin_id','=','stock_produits.magasin_id')
                            ->where('magasins.type','=','Depot_vente')
                            ->where('stock_produits.etat','<>','Delete')
                            ->where('stock_produits.produit_id','=',$produit->produit_id)
                            ->sum('stock_produits.qte');

                        $qte = $produit->qte;

                        for ($i = 0; $i < count($pdtqtes); $i++){
                            if ($pdtqtes[$i]->qte<$qte){
                                StockProduit::find($pdtqtes[$i]->id)->update(['qte'=>0]);
                                DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdtqtes[$i]->id)->update(['qte'=>0]);
                                $qte = $qte-$pdtqtes[$i]->qte;
                            }else{
                                StockProduit::find($pdtqtes[$i]->id)->update(['qte'=>$pdtqtes[$i]->qte-$qte]);
                                DB::connection('vps')->table('stock_produits')->where('stock_produit_id',$pdtqtes[$i]->id)->update(['qte'=>$pdtqtes[$i]->qte-$qte]);
                                break;
                            }
                        }
                        Mouvement::create([
                            'date' =>  $request->date_vente,
                            'magasin_id' =>  $pdtqtes[0]->magasin_id,
                            'produit_id' =>  $produit->produit_id,
                            'libelle' =>  $produit->libelle,
                            'motif' =>  'Vente numero '.$request->code,
                            'qte_initiale' =>  $qteIni,
                            'qte_sortie' =>  $qte,
                            'qte_reelle' =>  $qteIni-$qte,
                            'idop' =>  $request->code,
                            'idcon' =>  $produit->produit_vente_id
                        ]);
                        DB::connection('vps')->table('mouvements')->insert([
                            'date' =>  $request->date_vente,
                            'magasin_id' =>  $pdtqtes[0]->magasin_id,
                            'produit_id' =>  $produit->produit_id,
                            'libelle' =>  $produit->libelle,
                            'motif' =>  'Vente numero '.$request->code,
                            'qte_initiale' =>  $qteIni,
                            'qte_sortie' =>  $qte,
                            'qte_reelle' =>  $qteIni-$qte,
                            'idop' =>  $request->code,
                            'idcon' =>  $produit->produit_vente_id
                        ]);
                    }
                }
                Reglement::create([
                    'date_reglement'=> $request->date_vente,
                    'montant_reglement'=> $mont_paye,
                    'vente_id'=> $request->code,
                    'reglement_source'=> 'Vente',
                    'user_id'=> Auth::user()->id,
                    'centre_id'=> Auth::user()->centre_id
                ]);
                DB::connection('vps')->table('reglements')->insert([
                    'date_reglement'=> $request->date_vente,
                    'montant_reglement'=> $mont_paye,
                    'vente_id'=> $request->code,
                    'reglement_source'=> 'Vente',
                    'user_id'=> Auth::user()->id,
                    'centre_id'=> Auth::user()->centre_id
                ]);
                Vente::find($request->vente_id)->update($form_prod);
                DB::connection('vps')->table('ventes')->where('vente_id',$request->vente_id)->update($form_prod);
                DB::commit();

                //$vente = Vente::where('code','=',$request->vente_id)->get();
                $vente = DB::table('ventes')
                    ->join('users','users.id','=','ventes.user_id')
                    ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
                    ->where('ventes.vente_id','=', $request->vente_id)
                    ->get();

                $vente = (object) $vente[0];
                $date = new \DateTime($vente->date_vente);
                $date_vente = $date->format('d-m-Y');
                $heure_vente = $date->format('H:m:s');

                $produits = DB::table('produit_ventes')
                    //->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                    ->where('vente_id','=',$vente->vente_id)
                    ->get();
                $centre  = Centre::find(Auth::user()->centre_id);
                if ($centre->impression=='Format_A5'){
                    return view('etat.printven_a5', compact('vente','date_vente','produits','centre','heure_vente','entete','texte','reste'));
                }else{
                    return view('etat.printven_ticket', compact('vente','date_vente','produits','centre','heure_vente','entete','texte','reste'));
                }
            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur','Erreur survenu lors de la sauvegarde'.$se);
                return back();
            }
        }else{
            Alert::warning('Infos','Pas de produits ou acte selection');
            return back();
        }
    }

    public function show($vente_id){
        $vente = DB::table('ventes')
            ->join('users','users.id','=','ventes.user_id')
            ->join('patients','patients.patient_id','=','ventes.patient_id')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->where('ventes.vente_id','=', $vente_id)
            ->get();

        $vente = (object) $vente[0];
        $date = new \DateTime($vente->date_vente);
        $date_vente = $date->format('d-m-Y');

        $produits = DB::table('produit_ventes')
            //->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->where('vente_id','=',$vente_id)
            ->get();
        $centre  = Centre::find(Auth::user()->centre_id);

        $texte = 'RELIQUAT';
        $entete = '';
        $reste = $vente->reliquat;

        if ($reste<0) {
            $texte = 'RESTE A PAYER';
            $reste = $reste*(-1);
            $entete = 'ACHAT A CREDIT';
        }

        if ($centre->impression=='Format_A5'){
            return view('etat.printdupplicata_a5', compact('vente','date_vente','produits','centre','entete','texte','reste'));
        }else{
            return view('etat.printdupplicata_ticket', compact('vente','date_vente','produits','centre','entete','texte','reste'));
        }
    }


    public function imprimerduplicata($vente_id){
        $vente = DB::table('ventes')
            ->join('users','users.id','=','ventes.user_id')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->where('ventes.vente_id','=', $vente_id)
            ->get();

        $vente = (object) $vente[0];
        $date = new \DateTime($vente->date_vente);
        $date_vente = $date->format('d-m-Y');

        $produits = DB::table('produit_ventes')
            //->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->where('vente_id','=',$vente_id)
            ->get();
        $centre  = Centre::find(Auth::user()->centre_id);

        return view('etat.printdupplicata', compact('vente','date_vente','produits','centre'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->authorize('manage-action',['vente','editer']);
        $vente_id = $id;
        $vente = Vente::findOrfail($id);
        $patient = Patient::where('patstatut','=','true')->pluck('pat_nom','patient_id');
        $assurance = assurance::where('mutstatut','=','true')->pluck('nom','assurance_id');

        $vente = new Vente();

        $produits = Produit::where('pdtstatut','=','true')->get();
        $pdtcon = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->where('produit_ventes.vente_id','=',$vente_id)
            ->get();
        $montant_total = ProduitVente::where('code','=',$vente_id)->sum('mont');
        $prise_en_charge = ProduitVente::where('code','=',$vente_id)->sum('pec');
        $net_apayer = ProduitVente::where('code','=',$vente_id)->sum('net');

        return view ('vente.edit', compact('vente','pdtcon','produits','montant_total','prise_en_charge','net_apayer','code','patient','assurance'));
    }

    public function update(Request $request, $id)
    {
        $vente = Vente::findOrfail($request->vente_id);
        $vente_id = $request->vente_id;

        $montant_total = ProduitVente::where('code','=',$vente_id)->sum('mont');
        $prise_en_charge = ProduitVente::where('code','=',$vente_id)->sum('pec');
        $net_apayer = $montant_total-$prise_en_charge;

        $form_prod = array(
            'code' =>  $vente_id,
            'montant_total'  =>  $montant_total,
            'prise_en_charge'  =>  $prise_en_charge,
            'net_apayer'  =>  $net_apayer,
            'montant_recu'  =>  $request->montant_recu,
            'reliquat'  =>  $request->reliquat,
            'patient_id'  =>  $request->patient_id,
            'assurance_id'  =>  $request->assurance_id,
            'user_id'   =>  Auth::user()->id
        );

        try{
            DB::beginTransaction();
                $vente->update($form_prod);
                DB::connection('vps')->table('ventes')->where('vente_id',$request->vente_id)->update($form_prod);
                Alert::success('Success !', 'La Vente a ete bien enregistree.');
                //$this->imprimer_ven($vente_id);
                return redirect()->route('vente.index');
            DB::commit();

        }catch(\Throwable $th){
            DB::rollBack();
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function etat(){
        if (Auth::user()->ut==1){
            return view('etat.etat');
        }elseif (Auth::user()->ut==2){
            return view('etat.etatcomota');
        }elseif (Auth::user()->ut==3){
            return view('etat.etatmag');
        }elseif (Auth::user()->ut==4){
            return view('etat.etatcaisse');
        }else{
            //Rien faire
        }
    }

    public function histo(Request $request){
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('ventes')
                ->join('users','users.id','=','ventes.user_id')
                ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
                ->whereBetween('ventes.date_vente', array($request->from_date, $request->to_date))
                ->where('ventes.user_id','=',Auth::user()->id)
                ->where('ventes.centre_id','=',Auth::user()->centre_id)
                ->orderBy('ventes.date_vente','asc')
                ->get();
        }
        else
        {
            $historiques = DB::table('ventes')
                ->join('users','users.id','=','ventes.user_id')
                ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
                ->whereBetween('ventes.date_vente', array(date('Y-m-d'), date('Y-m-d')))
                ->where('ventes.user_id','=',Auth::user()->id)
                ->where('ventes.centre_id','=',Auth::user()->centre_id)
                ->orderBy('ventes.date_vente','asc')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        $centre = Centre::find(Auth::user()->centre_id);
        return view('vente.histo', compact('historiques','centre'));

    }

    public function histoenc(Request $request){
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('ventes')
                ->join('users','users.id','=','ventes.userid')
                ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
                ->whereBetween('ventes.date_vente', array($request->from_date, $request->to_date))
                ->where('ventes.userid','=',Auth::user()->id)
                ->orderBy('ventes.date_vente','asc')
                ->get();
        }
        else
        {
            $historiques = DB::table('ventes')
                ->join('users','users.id','=','ventes.userid')
                ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
                ->whereBetween('ventes.date_vente', array(date('Y-m-d'), date('Y-m-d')))
                ->where('ventes.userid','=',Auth::user()->id)
                ->orderBy('ventes.date_vente','asc')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        $centre = Centre::find(Auth::user()->centre_id);

        return view('ventepharmacie.histo', compact('historiques','centre'));
    }

    public function parproduit(Request $request){
        $historiques = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->selectRaw('produits.mat_lib,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as montant')
            ->whereBetween('ventes.date_vente', array($request->from_date, $request->to_date))
            ->groupBy('produits.mat_lib','produit_ventes.pu')
            ->get();

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }

        return view('etat.parproduit', compact('historiques'));
    }

    protected function print_histo($debut, $fin){
        $historiques = DB::table('ventes')
            ->join('clients','clients.clt_num','=','ventes.clt_num')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->get();

        $comptant = DB::table('ventes')
            ->join('clients','clients.clt_num','=','ventes.clt_num')
            ->where('ventes.ven_mode','=','Comptant')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->get();

        $montant_comptant= DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->where('ven_mode','=','Comptant')
            ->sum('ven_ttc');

        $virement = DB::table('ventes')
            ->join('clients','clients.clt_num','=','ventes.clt_num')
            ->where('ventes.ven_mode','=','Virement')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->get();

        $montant_virement= DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->where('ven_mode','=','Virement')
            ->sum('ven_ttc');

        //dd($historiques);

        $montant= DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->sum('ven_ttc');

        //dd($montant);

        $centre = Centre::find(Auth::user()->centre_id);

        $output ='<table>
                            <tr>
                                <td>
                                    <img src="../public/images/ceco.jpg" width="100" height="94">
                                </td>
                                <td colspan="3">

                                </td>
                            </tr>
                        </table>
                        <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 10px">
                            <tr>
                                <td width="100%">HISTORIQUE DES VENTES DU '.$debut.' AU '.$fin.' DE '.$centre->nom_centre_centre.'</td>
                            </tr>

                        </table>

                        <br>
                        <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                            <thead>
                                <tr>
                                    <th colspan="6">PAYEMENT AU COMPTANT</th>
                                </tr>
                                <tr style="border-radius: 10px; background-color: #27a5de";>
                                    <th width="16%">Date</th>
                                    <th width="16%">Client</th>
                                    <th width="16%">Montant HT</th>
                                    <th width="16%">Montant TTC</th>
                                    <th width="16%">Montant Paye</th>
                                    <th width="16%">Mode Payement</th>
                                </tr>
                            </thead>
                            <tbody>';
                                foreach($comptant as $vente){
                                    $output .='
                                        <tr style="border-collapse: collapse; border: 1px solid">
                                            <td  width="16%" style="border: 1px solid;">'.$vente->date_vente.'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.$vente->clt_nom.'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($vente->montant_total).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($vente->ven_ttc).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($vente->ven_mp).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.$vente->ven_mode.'</td>
                                        </tr>';
                                }

                                $output .='
                                    <tr>
                                        <th colspan="3">PAYEMENT TOTAL AU COMPTANT</th>
                                        <th colspan="3">'.($montant_comptant).'</th>
                                    </tr>
                                    </tbody>
                                    </table><br>

                        <br>
                        <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                            <thead>
                                <tr>
                                    <th colspan="6">PAYEMENT PAR VIREMENT</th>
                                </tr>
                                <tr style="border-radius: 10px; background-color: #27a5de";>
                                    <th width="16%">Date</th>
                                    <th width="16%">Client</th>
                                    <th width="16%">Montant HT</th>
                                    <th width="16%">Montant TTC</th>
                                    <th width="16%">Montant Paye</th>
                                    <th width="16%">Reference</th>
                                </tr>
                            </thead>
                            <tbody>';
                                foreach($virement as $vente){
                                    $output .='
                                        <tr style="border-collapse: collapse; border: 1px solid">
                                            <td  width="16%" style="border: 1px solid;">'.$vente->date_vente.'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.$vente->clt_nom.'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($vente->montant_total).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($vente->ven_ttc).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($vente->ven_mp).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.$vente->ven_rmp.'</td>
                                        </tr>';
                                }

                                $output .='
                                    <tr>
                                        <th colspan="3">PAYEMENT TOTAL PAR VIREMENT</th>
                                        <th colspan="3">'.($montant_virement).'</th>
                                    </tr>
                                    </tbody>
                                    </table><br>
                                    <table class="table-bordered float-right" style="width: 80%; border: 1px solid; border-color: #0b2e13; border-radius: 10px">
                                            <tr>
                                                <td colspan="4">MONTANT TOTAL  </td>
                                                <td ><b>'.($montant).'</b></td>
                                            </tr>
                                    </table><br>
                                    <br>';
        return $output;
    }

    public function imprimer_ven($id){
        $vente = DB::table('ventes')
            ->join('users','users.id','=','ventes.user_id')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->where('ventes.vente_id','=', $id)
            ->get();

        $vente = (object) $vente[0];
        $date = new \DateTime($vente->date_vente);
        $date_vente = $date->format('d-m-Y');
        $heure_vente = $date->format('h:m:s');

        $produits = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->where('produit_ventes.vente_id','=',$vente->vente_id)
            ->get();
        $centre  = Centre::find(Auth::user()->centre_id);
        //$obj = (object) $vente[0];
        $output ='
                <table width="100%" border="0">
                    <tr>
                        <td width="33%">
                            <table>
                                <tr>
                                    <td width="15%">
                                        <img src="images/logo.png" width="80" height="80">
                                    </td>
                                    <td width="85%">
                                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                                        <div style="font-size: 5px;">'.$centre->services.'</div>
                                        <div style="font-size: 6;">'.$centre->adresse.'</div>
                                        <div style="font-size: 7;">'.$centre->telephone.'</div>
                                    </td>
                                </tr>
                            </table>
                            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                                <tr>
                                    <td width="21%" style="font-size: 10px;">RECU N°</td>
                                    <td width="43%" style="font-size: 10px;"><b>' .$vente->code.'</b></td>
                                    <td width="10%" style="font-size: 10px;"><b>Date</b></td>
                                    <td width="26%" style="font-size: 10px;">'.$date_vente.'</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px">Patient </td>
                                    <td colspan="3" style="font-size: 10px"><b>'.$vente->patient_id.'</b> / assurance :<b>'.$vente->nom.'</td>
                                </tr>
                            </table>

                            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                                <thead>
                                    <tr style="border-radius: 10px; background-color: #27a5de";>
                                        <th style="font-size: 10px;" width="50%">Produit</th>
                                        <th style="font-size: 10px;" width="15%">P U</th>
                                        <th style="font-size: 10px;" width="15%">Qte</th>
                                        <th style="font-size: 10px;" width="20%">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>';
        foreach($produits as $produit){
            $output .='
                                    <tr style="border-collapse: collapse; border: 1px solid">
                                        <td  width="51%" style="font-size:10px; border: 1px solid;">'.$produit->libelle.'</td>
                                        <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->pu).'</td>
                                        <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->qte).'</td>
                                        <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->mont).'</td>
                                    </tr>';
        }
        $output .='</tbody>
                            </table>
                            <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
                                <tr>
                                    <td colspan="4" style="font-size:10px">MONTANT : <b>'.($vente->montant_total).' - - - Prise en charge :'.($vente->prise_en_charge).' </td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="font-size:10px">Part du patient : <b>'.($vente->net_apayer).'</b></td>
                                </tr>
                            </table>
                            <table border="0">
                                <tr>
                                    <td colspan="4" style="font-size:10px; text-align: center">Bonne guerison </td>
                                </tr>
                            </table>
                        </td>
                        <td width="33%">
                            <table>
                                <tr>
                                    <td width="15%">
                                        <img src="images/logo.png" width="80" height="80">
                                    </td>
                                    <td width="85%">
                                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                                        <div style="font-size: 5px;">'.$centre->services.'</div>
                                        <div style="font-size: 6;">'.$centre->adresse.'</div>
                                        <div style="font-size: 7;">'.$centre->telephone.'</div>
                                    </td>
                                </tr>
                            </table>
                            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                                <tr>
                                    <td width="21%" style="font-size: 10px;">RECU N° </td>
                                    <td width="43%" style="font-size: 10px;"><b>' .$vente->code.'</b></td>
                                    <td width="10%" style="font-size: 10px;"><b>Date</b></td>
                                    <td width="26%" style="font-size: 10px;">'.$date_vente.'</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px">Patient </td>
                                    <td colspan="3" style="font-size: 10px"><b>'.$vente->patient_id.'</b> / assurance :<b>'.$vente->nom.'</td>
                                </tr>
                            </table>

                            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                                <thead>
                                    <tr style="border-radius: 10px; background-color: #27a5de";>
                                        <th style="font-size: 10px;" width="50%">Produit</th>
                                        <th style="font-size: 10px;" width="15%">P U</th>
                                        <th style="font-size: 10px;" width="15%">Qte</th>
                                        <th style="font-size: 10px;" width="20%">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>';
        foreach($produits as $produit){
            $output .='
                                    <tr style="border-collapse: collapse; border: 1px solid">
                                        <td  width="51%" style="font-size:10px; border: 1px solid;">'.$produit->libelle.'</td>
                                        <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->pu).'</td>
                                        <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->qte).'</td>
                                        <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->net).'</td>
                                    </tr>';
        }
        $output .='</tbody>
                            </table>
                            <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
                                <tr>
                                    <td colspan="4" style="font-size:10px">MONTANT : <b>'.($vente->montant_total).' - - - Prise en charge :'.($vente->prise_en_charge).' </td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="font-size:10px">Part du patient : <b>'.($vente->net_apayer).'</b></td>
                                </tr>
                            </table>
                            <table border="0">
                                <tr>
                                    <td colspan="4" style="font-size:10px; text-align: center">Bonne guerison </td>
                                </tr>
                            </table>
                        </td>
                        <td width="33%">
                            <table>
                                <tr>
                                    <td width="15%">
                                        <img src="images/logo.png" width="80" height="80">
                                    </td>
                                    <td width="85%">
                                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                                        <div style="font-size: 5px;">'.$centre->services.'</div>
                                        <div style="font-size: 6;">'.$centre->adresse.'</div>
                                        <div style="font-size: 7;">'.$centre->telephone.'</div>
                                    </td>
                                </tr>
                            </table>
                            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                                <tr>
                                    <td width="21%" style="font-size: 10px;">RECU N° </td>
                                    <td width="43%" style="font-size: 10px;"><b>' .$vente->code.'</b></td>
                                    <td width="10%" style="font-size: 10px;"><b>Date</b></td>
                                    <td width="26%" style="font-size: 10px;">'.$date_vente.'</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px">Patient </td>
                                    <td colspan="3" style="font-size: 10px"><b>'.$vente->patient_id.'</b> / assurance :<b>'.$vente->nom.'</td>
                                </tr>
                            </table>

                            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                                <thead>
                                    <tr style="border-radius: 10px; background-color: #27a5de";>
                                        <th style="font-size: 10px;" width="50%">Produit</th>
                                        <th style="font-size: 10px;" width="15%">P U</th>
                                        <th style="font-size: 10px;" width="15%">Qte</th>
                                        <th style="font-size: 10px;" width="20%">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>';
        foreach($produits as $produit){
            $output .='
                                    <tr style="border-collapse: collapse; border: 1px solid">
                                        <td  width="51%" style="font-size:10px; border: 1px solid;">'.$produit->libelle.'</td>
                                        <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->pu).'</td>
                                        <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->qte).'</td>
                                        <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->net).'</td>
                                    </tr>';
        }
        $output .='</tbody>
                            </table>
                            <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
                                <tr>
                                    <td colspan="4" style="font-size:10px">MONTANT : <b>'.($vente->montant_total).' - - -Prise en charge :'.($vente->prise_en_charge).' </td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="font-size:10px">Part du patient : <b>'.($vente->net_apayer).'</b></td>
                                </tr>
                            </table>
                            <table border="0">
                                <tr>
                                    <td colspan="4" style="font-size:10px; text-align: center">Bonne guerison </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                ';
        /*//$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);*/
        return $output;
    }

    public function print_ef($debut,$fin){
        $vmont = 0;
        $vpec = 0;
        $vnet = 0;

        $encaisses = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('ventes.user_id','=',Auth::user()->id)
            ->sum('montant_paye');

        $categories = DB::table('produit_ventes')
            ->join('categories','categories.categorie_id','=','produit_ventes.categorie_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.user_id','=',Auth::user()->id)
            ->select('categories.categorie_id','categories.libelle','categories.type')->distinct()
            ->orderby('categories.libelle')
            ->get();

        $recap_mut = DB::table('ventes')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->selectRaw('assurances.nom,sum(ventes.prise_en_charge) as prise_en_charge')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.user_id','=',Auth::user()->id)
            ->groupBy('assurances.nom')
            ->get();

        $reglements = DB::table('reglements')
            ->join('ventes','ventes.vente_id','=','reglements.vente_id')
            ->where('reglements.reglement_source', '=','REGLEMENT')
            ->whereBetween('reglements.date_reglement', array($debut, $fin))
            ->where('reglements.user_id','=',Auth::user()->id)
            ->get();

        $total = DB::table('reglements')
            ->whereBetween('date_reglement', array($debut, $fin))
            ->where('user_id','=',Auth::user()->id)
            ->where('reglement_source', '=','REGLEMENT')
            ->sum('montant_reglement');

        $centre  = Centre::find(Auth::user()->centre_id);
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                        <div style="font-size:10px;">'.$centre->services.'</div>
                        <div style="font-size:15px;">'.$centre->adresse.'</div>
                        <div style="font-size:15px;">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">ETAT FINANCIER DE LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
                </tr>
                <tr>
                    <td width="100%" style="font-size: 15px;">USER : '.Auth::user()->name.' </td>
                </tr>
            </table>';

                foreach($categories as $categorie){
                    $ventes = DB::table('produit_ventes')
                        ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                        ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                        ->selectRaw('produits.categorie_id,produits.produit_id,produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                        ->whereBetween('ventes.date_vente', array($debut, $fin))
                        ->where('ventes.user_id','=',Auth::user()->id)
                        ->where('produits.categorie_id','=',$categorie->categorie_id)
                        ->groupBy('produits.categorie_id','produits.produit_id','produit_ventes.libelle','produit_ventes.pu')
                        ->orderby('produit_ventes.libelle')
                        ->get();

                    $lesventes = DB::table('ventes')
                        ->whereBetween('date_vente', array($debut, $fin))
                        ->where('ventes.user_id','=',Auth::user()->id)
                        ->get();
                    $vente_ids = array();
                    foreach ($lesventes as $vente){
                        array_push($vente_ids, $vente->vente_id);
                    }

                    $total_cat = 0;
                    $mont = 0;
                    $pec = 0;
                    $net = 0;
                    $output .='
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">

                            <tr style="border-radius: 15px; background-color: #27a5de";>
                                <th style="font-size: 15px; font-color:#fff" width="50%">'.$categorie->libelle.'</th>
                            </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #d1d73f";>
                                <th style="font-size: 15px;" width="30%">Produit</th>
                                <th style="font-size: 15px;" width="12%">P U</th>
                                <th style="font-size: 15px;" width="10%">Qte</th>
                                <th style="font-size: 15px;" width="12%">Montant</th>
                                <th style="font-size: 15px;" width="12%">Prise en charge</th>
                                <th style="font-size: 15px;" width="13%">Part du patient</th>
                                <th style="font-size: 11px;" width="15%">Stock final</th>
                            </tr>
                        </thead>
                        <tbody>';

                        foreach($ventes as $produit){
                            $qte_las=0;
                            $mont += $produit->mont;
                            $pec += $produit->pec;
                            $net += $produit->net;
                            $output .='
                               <tr style="border-collapse: collapse; border: 1px solid">
                                   <td  width="30%" style="font-size:15px; border: 1px solid;">'.$produit->libelle.'</td>
                                   <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->pu).'</td>
                                   <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->qte).'</td>
                                   <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->mont).'</td>
                                   <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->pec).'</td>
                                   <td  width="13%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->net).'</td>
                                   <td  width="11%" style="font-size:15px; border: 1px solid; text-align: right">'.($qte_las).'</td>
                               </tr>';
                        }
                    $vmont+=$mont;
                    $vpec+=$pec;
                    $vnet+=$net;
                    $output .='<tr style="border-collapse: collapse; border: 1px solid; background-color: #C5C8CE">
                                   <td colspan="3"  width="35%" style="font-size:15px; border: 1px solid"><b>Total Categorie</b></td>
                                   <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($mont,'0','.',' ').'</b></td>
                                   <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($pec,'0','.',' ').'</b></td>
                                   <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($net,'0','.',' ').'</b></td>
                                   <td   style="font-size:15px; border: 1px solid; text-align: right"><b></td>
                               </tr>
                               </tbody>
                    </table>';
                }
            $output .='

            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="33%" style="font-size: 17px;">Recette Totale : <b>'.number_format($vmont,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px;">Prise en  charge : <b>'.number_format($vpec,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px;">Recette net : <b>'.number_format($vnet,'0','.',' ').'</b> </td>
                </tr>
                <tr>
                    <td width="33%" style="font-size: 17px; color: #0b304e">Recette net vendue : <b>'.number_format($vnet,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px; color: rgba(21,168,10,0.92)">Total encaisse : <b>'.number_format($encaisses,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px; color: #95124e">Total Credit : <b>'.number_format($vnet-$encaisses,'0','.',' ').'</b> </td>
                </tr>
            </table>
            <p></p>
            <p></p>
            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                <thead>
                    <tr style="border-radius: 15px;";>
                        <th colspan="2" style="font-size: 15px;" width="100%">ETAT DES ASSURANCES</th>
                    </tr>
                </thead>
                <tr style="border-radius: 15px; background-color: #d1d73f";>
                        <th style="font-size: 15px;" width="55%">ASSURANCE / assurance</th>
                        <th style="font-size: 15px;" width="45%">MONTANT</th>
                    </tr>
                <tbody>';

                foreach($recap_mut as $assurance){
                    $output .='
                       <tr style="border-collapse: collapse; border: 1px solid">
                           <td  width="55%" style="font-size:15px; border: 1px solid;">'.$assurance->nom.'</td>
                           <td  width="45%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($assurance->prise_en_charge,'0','.',' ').'</td>

                       </tr>';
                }
            $output.='<tr style="border-radius: 15px";>
                                <th style="font-size: 15px;" width="55%">TOTAL</th>
                                <th style="font-size: 15px;" width="45%; text-align: right">'.number_format($vpec,'0','.',' ').'</th>
                            </tr></body>
                </table>';

            $output.='
                <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                  <tr>
                      <td width="100%" style="font-size: 15px; text-align: center"> REGLEMENTS CREDITS DE LA PERIODE</td>
                  </tr>
                </table>
                <br>
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                      <tr style="border-radius: 12px; border: 1px solid";>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="11%">Date Reg</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="21%">Patient</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="12%">Vente Num</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="11%">Date Vente</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Montant Vente</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Montant Reg</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Total Reg</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Reste a payer</th>
                      </tr>
                    </thead>
                    <tbody></tbody>';
                    foreach ($reglements as $reglement){
                        $total_reg = DB::table('reglements')
                            ->where('code','=',$reglement->vente_id)
                            ->where('user_id','=',Auth::user()->id)
                            ->sum('montant_reglement');
                        $reste = $reglement->net_apayer-$total_reg;
                        $output.='<tr style="border-radius: 12px";>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->date_reglement.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->patient_id.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->vente_id.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->date_vente.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reglement->net_apayer,'0','.',' ').'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reglement->montant_reglement,'0','.',' ').'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($total_reg,'0','.',' ').'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reste,'0','.',' ').'</td>
                            </tr>';
                        }
                        $output.='
                        <tr style="border-radius: 5px; background-color: #27a5de";>
                          <td colspan="3" style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL REGLE</td>
                          <td colspan="5" style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total,'0','.',' ').'</td>
                        </tr>
                        </body>
                    </table>
                    <br><br>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                       <tr>
                          <tr style="border-radius: 5px; background-color: #27a5de";>
                          <td style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL ENCAISSE DE LA PERIODE</td>
                          <td style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total+$encaisses,'0','.',' ').' Franc CFA</td>
                        </tr>
                    </table>
                    ';

        return response()->json($output);

    }

    public function print_ef_personnel($debut,$fin){
        $vmont = 0;
        $vpec = 0;
        $vnet = 0;

        $encaisses = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('ventes.userid','=',Auth::user()->id)
            ->sum('montant_paye');

        $categories = DB::table('produit_ventes')
            ->join('categories','categories.categorie_id','=','produit_ventes.categorie_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.userid','=',Auth::user()->id)
            ->select('categories.categorie_id','categories.libelle','categories.type')->distinct()
            ->orderby('categories.libelle')
            ->get();

        $recap_mut = DB::table('ventes')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->selectRaw('assurances.nom,sum(ventes.prise_en_charge) as prise_en_charge')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.userid','=',Auth::user()->id)
            ->groupBy('assurances.nom')
            ->get();


        $centre  = Centre::find(Auth::user()->centre_id);
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                        <div style="font-size:10px;">'.$centre->services.'</div>
                        <div style="font-size:15px;">'.$centre->adresse.'</div>
                        <div style="font-size:15px;">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">ETAT FINANCIER DE LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
                </tr>
                <tr>
                    <td width="100%" style="font-size: 15px;">USER : '.Auth::user()->name.' </td>
                </tr>
            </table>';

        foreach($categories as $categorie){
            $ventes = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produits.categorie_id,produits.produit_id,produits.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('ventes.userid','=',Auth::user()->id)
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->groupBy('produits.categorie_id','produits.produit_id','produits.libelle','produit_ventes.pu')
                ->orderby('produits.libelle')
                ->get();

            $lesventes = DB::table('ventes')
                ->whereBetween('date_vente', array($debut, $fin))
                ->where('ventes.userid','=',Auth::user()->id)
                ->get();
            $vente_ids = array();
            foreach ($lesventes as $vente){
                array_push($vente_ids, $vente->vente_id);
            }

            $total_cat = 0;
            $mont = 0;
            $pec = 0;
            $net = 0;
            $output .='
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">

                            <tr style="border-radius: 15px; background-color: #27a5de";>
                                <th style="font-size: 15px;" width="50%">'.$categorie->libelle.'</th>
                            </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #d1d73f";>
                                <th style="font-size: 15px;" width="30%">Produit</th>
                                <th style="font-size: 15px;" width="12%">P U</th>
                                <th style="font-size: 15px;" width="10%">Qte</th>
                                <th style="font-size: 15px;" width="12%">Montant</th>
                                <th style="font-size: 15px;" width="12%">Prise en charge</th>
                                <th style="font-size: 15px;" width="13%">Part du patient</th>
                                <th style="font-size: 11px;" width="15%">Stock final</th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach($ventes as $produit){
                $qte_las=0;
                $mont += $produit->mont;
                $pec += $produit->pec;
                $net += $produit->net;
                $output .='
                               <tr style="border-collapse: collapse; border: 1px solid">
                                   <td  width="30%" style="font-size:15px; border: 1px solid;">'.$produit->libelle.'</td>
                                   <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->pu).'</td>
                                   <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->qte).'</td>
                                   <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->mont).'</td>
                                   <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->pec).'</td>
                                   <td  width="13%" style="font-size:15px; border: 1px solid; text-align: right">'.($produit->net).'</td>
                                   <td  width="11%" style="font-size:15px; border: 1px solid; text-align: right">'.($qte_las).'</td>
                               </tr>';
            }
            $vmont+=$mont;
            $vpec+=$pec;
            $vnet+=$net;
            $output .='<tr style="border-collapse: collapse; border: 1px solid; background-color: #C5C8CE">
                                   <td colspan="3"  width="35%" style="font-size:15px; border: 1px solid"><b>Total Categorie</b></td>
                                   <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($mont,'0','.',' ').'</b></td>
                                   <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($pec,'0','.',' ').'</b></td>
                                   <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($net,'0','.',' ').'</b></td>
                                   <td   style="font-size:15px; border: 1px solid; text-align: right"><b></td>
                               </tr>
                               </tbody>
                    </table>';
        }
        $output .='

            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="33%" style="font-size: 17px;">Recette Totale : <b>'.number_format($vmont,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px;">Prise en  charge : <b>'.number_format($vpec,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px;">Recette net : <b>'.number_format($vnet,'0','.',' ').'</b> </td>
                </tr>
                <tr>
                    <td width="33%" style="font-size: 17px; color: #0b304e">Recette net vendue : <b>'.number_format($vnet,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px; color: rgba(21,168,10,0.92)">Total encaisse : <b>'.number_format($encaisses,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px; color: #95124e">Total Credit : <b>'.number_format($vnet-$encaisses,'0','.',' ').'</b> </td>
                </tr>
            </table>
            <p></p>
            <p></p>
            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                <thead>
                    <tr style="border-radius: 15px;";>
                        <th colspan="2" style="font-size: 15px;" width="100%">ETAT DES ASSURANCES</th>
                    </tr>
                </thead>
                <tr style="border-radius: 15px; background-color: #d1d73f";>
                        <th style="font-size: 15px;" width="55%">ASSURANCE / assurance</th>
                        <th style="font-size: 15px;" width="45%">MONTANT</th>
                    </tr>
                <tbody>';

        foreach($recap_mut as $assurance){
            $output .='
                       <tr style="border-collapse: collapse; border: 1px solid">
                           <td  width="55%" style="font-size:15px; border: 1px solid;">'.$assurance->nom.'</td>
                           <td  width="45%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($assurance->prise_en_charge,'0','.',' ').'</td>

                       </tr>';
        }
        $output.='<tr style="border-radius: 15px";>
                                <th style="font-size: 15px;" width="55%">TOTAL</th>
                                <th style="font-size: 15px;" width="45%; text-align: right">'.number_format($vpec,'0','.',' ').'</th>
                            </tr></body>
                </table>';


        return response()->json($output);

    }

    public function adduser($id){
        $user = Usercon::find($id);
        $user_select = User::find($id);
        $resultat = '';
        if ($user){
            return \response()->json(['error'=>$user_select->name.' est deja selectionne']);
        }else{
            try{
                DB::beginTransaction();
                Usercon::create(['id'=>$id]);
                DB::connection('vps')->table('usercons')->insert(['id'=>$id]);
                DB::commit();
                return \response()->json(['success'=>$user_select->name.' ajoutee']);
            }catch(\Throwable $th){
                DB::rollBack();
            }

        }
    }

    public function etatphar(Request $request){
        $users = DB::table('users')
            ->where('statut','=','true')
            ->where('ut','=',3)
            ->get();

        $usercon = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->where('users.statut','=','true')
            ->get();

        if (Auth::user()->ut == 2) {
            return view('etat.etatphar', compact('users','usercon'));
        }else{
            return view('etat.etatpharpp', compact('users','usercon'));
        }
    }

    public function rech_usercon()
    {
        $usercon = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->where('users.etat','=','OK')
            ->get();
        $output='<table class="table table-striped table-bordered contour_table" id="user_selected">
                   <thead>
                       <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                           <td class="description">Nom</td>
                           <td></td>
                       </tr>
                   </thead>
                   <tbody>';
        foreach($usercon as $user){
            //$lien ="<a href="{{route('appromag.delete',[$produit->am_num,$produit->produit_id])}}" class="btn btn-danger"><i class="fa fa-trash"></i></a>";
            $button = '<button type="button" name="delete" id="'.$user->id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
            $output .='<tr>
                                 <td class="cart_title">'.$user->name.'</td>
                                 <td class="cart_delete">'.$button.'</td>
                             </tr>';
        }
        $output.='</body>
                    </table>';
        return $output;
    }

    public function deleteuser($id){
        $user = User::find($id);
        try{
            DB::beginTransaction();
            Usercon::find($id)->delete();
            DB::connection('vps')->table('usercons')->where('id',$id)->delete();
            DB::commit();
            return response()->json($user->name.' a ete retire');
        }catch(\Throwable $th){
            DB::rollBack();
        }
    }

    public function printetatphar($debut,$fin){
        $users = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->get();
        $uc = array();
        $utilisateurs = '';
        foreach ($users as $user){
            array_push($uc, $user->id);
            $utilisateurs .= $user->name.'-';
        }

        $utilisateurs= rtrim($utilisateurs,'-');

        $lesventes = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->get();
        $vente_ids = array();
        foreach ($lesventes as $vente){
            array_push($vente_ids, $vente->vente_id);
        }

        $vmomt = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->whereIn('ventes.user_id',$uc)
            ->where('produits.categorie_id','=',1)
            ->sum('produit_ventes.mont');

        $vpec = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->whereIn('ventes.user_id',$uc)
            ->where('produits.categorie_id','=',1)
            ->sum('produit_ventes.pec');
        $vnet = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->whereIn('ventes.user_id',$uc)
            ->where('produits.categorie_id','=',1)
            ->sum('produit_ventes.net');


        $centre  = Centre::find(Auth::user()->centre_id);
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                        <div style="font-size:10px;">'.$centre->services.'</div>
                        <div style="font-size:15px;">'.$centre->adresse.'</div>
                        <div style="font-size:15px;">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">ETAT FINANCIER DE LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
                </tr>
                <tr>
                    <td width="100%" style="font-size: 15px;">CAISSIERS : '.$utilisateurs.' </td>
                </tr>
            </table>';

            $ventes = DB::table('produit_ventes')
                ->join('produits', 'produits.produit_id', '=', 'produit_ventes.produit_id')
                ->join('ventes', 'ventes.vente_id', '=', 'produit_ventes.vente_id')
                ->selectRaw('produits.produit_id,produits.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('ventes.centre_id','=',Auth::user()->centre_id)
                ->whereIn('ventes.user_id',$uc)
                ->where('produits.categorie_id', '=', 1)
                ->groupBy('produits.produit_id','produits.libelle', 'produit_ventes.pu')
                ->orderby('produits.libelle')
                ->get();


            $output .= '
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <tr style="border-radius: 15px; background-color: #27a5de";>
                        <th style="font-size: 15px;" width="50%">PRODUITS PHARMACEUTIQUES</th>
                    </tr>
                </table>
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                   <thead>
                       <tr style="border-radius: 15px; background-color: #d1d73f";>
                           <th style="font-size: 15px;" width="30%">Produit</th>
                           <th style="font-size: 15px;" width="10%">PU</th>
                           <th style="font-size: 15px;" width="10%">Qte vendu</th>
                           <th style="font-size: 15px;" width="15%">Montant</th>
                           <th style="font-size: 15px;" width="10%">PEC</th>
                           <th style="font-size: 15px;" width="15%">Net</th>
                           <th style="font-size: 15px;" width="10%">Stock final</th>
                       </tr>
                   </thead>
                   <tbody>';

            foreach ($ventes as $produit) {
                $pdt = DB::table('mouvements')
                    ->whereIn('ad_num', $vente_ids)
                    ->where('produit_id','=',$produit->produit_id)
                    ->orderby('date')
                    ->get();
                $las = count($pdt)-1;
                $qte_las = $pdt[$las]->qte_reelle;

                $output .= '
                      <tr style="border-collapse: collapse; border: 1px solid">
                          <td  width="30%" style="font-size:15px; border: 1px solid;">' . $produit->libelle . '</td>
                          <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">' . ($produit->pu) . '</td>
                          <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">' . ($produit->qte) . '</td>
                          <td  width="15%" style="font-size:15px; border: 1px solid; text-align: right">' . ($produit->mont) . '</td>
                          <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">' . ($produit->pec) . '</td>
                          <td  width="15%" style="font-size:15px; border: 1px solid; text-align: right">' . ($produit->net) . '</td>
                          <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">' . ($qte_las) . '</td>
                      </tr>';
            }
        $output .='
                    <tr style="border-collapse: collapse; border: 1px solid">
                          <td  colspan="3" style="font-size:15px; border: 1px solid;">TOTAL</td>
                          <td  width="15%" style="font-size:15px; border: 1px solid; text-align: right">' . ($vmomt) . '</td>
                          <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">' . ($vpec) . '</td>
                          <td  width="15%" style="font-size:15px; border: 1px solid; text-align: right">' . ($vnet) . '</td>
                      </tr>
                    </tbody>
                </table>';

        ////$pdf = \App::make('dompdf.wrapper');
        ////$pdf->loadHTML($output);
        //return $output;
        try{
            DB::beginTransaction();
            DB::table('usercons')->delete();
            DB::connection('vps')->table('usercons')->delete();
            DB::commit();
            return response()->json(['data' => $output]);
        }catch(\Throwable $th){
            DB::rollBack();
        }
    }

    public function etatcaisse(){
        $users = DB::table('users')
            ->where('statut','=','true')
            ->where('centre_id','=',Auth::user()->centre_id)
            //->whereIn('ut',[1,4])
            ->get();

        $usercon = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->where('users.statut','=','true')
            ->get();
        return view('etat.etatcaisse', compact('users','usercon'));
    }

    protected function printetatcaisse($debut,$fin){
        $users = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->get();
        $uc = array();
        $utilisateurs = '';
        foreach ($users as $user){
            array_push($uc, $user->id);
            $utilisateurs .= $user->name.'-';
        }

        $utilisateurs= rtrim($utilisateurs,'-');

        $vmomt = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('centre_id','=',Auth::user()->centre_id)
            ->whereIn('user_id',$uc)
            ->sum('montant_total');

        $vpec = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('centre_id','=',Auth::user()->centre_id)
            ->whereIn('user_id',$uc)
            ->sum('prise_en_charge');
        $vnet = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('centre_id','=',Auth::user()->centre_id)
            ->whereIn('user_id',$uc)
            ->sum('net_apayer');

        $encaisses = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('centre_id','=',Auth::user()->centre_id)
            ->whereIn('user_id',$uc)
            ->sum('montant_paye');

        $reglements = DB::table('reglements')
            ->join('ventes','ventes.vente_id','=','reglements.vente_id')
            ->where('reglements.centre_id','=',Auth::user()->centre_id)
            ->where('reglements.reglement_source', '=','REGLEMENT')
            ->whereBetween('reglements.date_reglement', array($debut, $fin))
            ->whereIn('reglements.user_id',$uc)
            ->get();

        $total = DB::table('reglements')
            ->where('centre_id','=',Auth::user()->centre_id)
            ->whereBetween('date_reglement', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->where('reglement_source', '=','REGLEMENT')
            ->sum('montant_reglement');

        $catcon = DB::table('produits')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->join('produit_ventes','produit_ventes.produit_id','=','produits.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereIn('ventes.user_id',$uc)
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->select('produits.categorie_id','categories.libelle')->distinct()->get();

        $recap_mut = DB::table('ventes')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->selectRaw('assurances.nom,sum(ventes.prise_en_charge) as prise_en_charge')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->whereIn('ventes.user_id',$uc)
            ->groupBy('assurances.nom')
            ->get();

        $produit_ventes = DB::table('produit_ventes')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->whereIn('ventes.user_id',$uc)
            ->get();
        $marge = 0;
        $magasin = DB::table('magasins')
            ->where('centre_id','=',Auth::user()->centre_id)
            ->Where('statut','=','true')
            ->where('type','=','Depot_vente')
            ->get();
        //$depot = (object) $magasin[0];
        foreach ($produit_ventes as $con_ven){
            $qp = DB::table('stock_produits')
                ->where('centre_id','=',Auth::user()->centre_id)
                ->where('etat','=','Encours')
                //->where('magasin_id','=',$depot->magasin_id)
                ->where('produit_id','=',$con_ven->produit_id)
                ->get();
            if (count($qp)!=0){
                $pdtcon = (object) $qp[0];
                $marge+=$pdtcon->marge*$con_ven->qte;
            }

        }

        $centre  = Centre::find(Auth::user()->centre_id);
        //dd($centre);
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="/images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                        <div style="font-size:10px;">'.$centre->services.'</div>
                        <div style="font-size:15px;">'.$centre->adresse.'</div>
                        <div style="font-size:15px;">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">ETAT FINANCIER DE LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
                </tr>
                <tr>
                    <td width="100%" style="font-size: 15px;">USERS : '.$utilisateurs.' </td>
                </tr>
            </table>';

        foreach($catcon as $categorie){
            $ventes = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->whereIn('ventes.user_id',$uc)
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();

            $total_cat = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produits.categorie_id, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->whereIn('ventes.user_id',$uc)
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->groupBy('produits.categorie_id')
                ->get();

            $mont = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->whereIn('ventes.user_id',$uc)
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->sum('produit_ventes.mont');

            $pec = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->whereIn('ventes.user_id',$uc)
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->sum('produit_ventes.pec');

            $net = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->whereIn('ventes.user_id',$uc)
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->sum('produit_ventes.net');


            $output .='
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">

                            <tr style="border-radius: 15px; background-color: #27a5de";>
                                <th style="font-size: 15px;" width="50%">'.$categorie->libelle.'</th>
                            </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #d1d73f";>
                                <th style="font-size: 15px;" width="35%">Produit</th>
                                <th style="font-size: 15px;" width="12%">P U</th>
                                <th style="font-size: 15px;" width="10%">Qte</th>
                                <th style="font-size: 15px;" width="14%">Montant</th>
                                <th style="font-size: 15px;" width="14%">Prise en charge</th>
                                <th style="font-size: 15px;" width="15%">Part du patient</th>
                            </tr>
                        </thead>
                        <tbody>';

                    foreach($ventes as $produit){
                        $output .='
                           <tr style="border-collapse: collapse; border: 1px solid">
                               <td  width="35%" style="font-size:15px; border: 1px solid;">'.$produit->libelle.'</td>
                               <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pu,'0','.',' ').'</td>
                               <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                               <td  width="14%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->mont,'0','.',' ').'</td>
                               <td  width="14%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pec,'0','.',' ').'</td>
                               <td  width="15%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->net,'0','.',' ').'</td>
                           </tr>';
                    }
                    $output .='<tr style="border-collapse: collapse; border: 1px solid; background-color: #C5C8CE">
                           <td colspan="3"  width="35%" style="font-size:15px; border: 1px solid"><b>Total Categorie</b></td>
                           <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($mont,'0','.',' ').'</b></td>
                           <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($pec,'0','.',' ').'</b></td>
                           <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($net,'0','.',' ').'</b></td>
                       </tr>
                       </tbody>
                    </table>';
        }
        $output .='

            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="33%" style="font-size: 17px;">Recette Totale : <b>'.number_format($vmomt,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px;">Prise en  charge : <b>'.number_format($vpec,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px;">Recette net : <b>'.number_format($vnet,'0','.',' ').'</b> </td>
                </tr>
                <tr>
                    <td width="33%" style="font-size: 17px; color: #0b304e">Recette net vendue : <b>'.number_format($vnet,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px; color: rgba(21,168,10,0.92)">Encaisse : <b>'.number_format($encaisses,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px; color: #95124e">Total Credit : <b>'.number_format($vnet-$encaisses,'0','.',' ').'</b> </td>
                </tr>
            </table>
            <p></p>
            <p></p>
            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                <thead>
                    <tr style="border-radius: 15px;";>
                        <th colspan="2" style="font-size: 15px;" width="100%">ETAT DES ASSURANCES</th>
                    </tr>
                </thead>
                <tr style="border-radius: 15px; background-color: #d1d73f";>
                        <th style="font-size: 15px;" width="55%">ASSURANCE / assurance</th>
                        <th style="font-size: 15px;" width="45%">MONTANT</th>
                    </tr>
                <tbody>';

            foreach($recap_mut as $assurance){
                $output .='
                   <tr style="border-collapse: collapse; border: 1px solid">
                       <td  width="55%" style="font-size:15px; border: 1px solid;">'.$assurance->nom.'</td>
                       <td  width="45%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($assurance->prise_en_charge,'0','.',' ').'</td>
                   </tr>';
            }
            $output.='<tr style="border-radius: 15px";>
                    <th style="font-size: 15px;" width="55%">TOTAL</th>
                    <th style="font-size: 15px;" width="45%; text-align: right">'.number_format($vpec,'0','.',' ').'</th>
                </tr></body>
            </table>';

            $output.='
                <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                  <tr>
                      <td width="100%" style="font-size: 15px; text-align: center"> REGLEMENTS CREDITS DE LA PERIODE</td>
                  </tr>
                </table>
                <br>
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                      <tr style="border-radius: 12px; border: 1px solid";>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="11%">Date Reg</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="21%">Patient</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="12%">Vente Num</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="11%">Date Vente</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Montant Vente</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Montant Reg</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Total Reg</th>
                          <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Reste a payer</th>
                      </tr>
                    </thead>
                    <tbody></tbody>';
        foreach ($reglements as $reglement){
            $total_reg = DB::table('reglements')
                ->where('code','=',$reglement->vente_id)
                ->sum('montant_reglement');
            $reste = $reglement->net_apayer-$total_reg;
            $output.='<tr style="border-radius: 12px";>
              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->date_reglement.'</td>
              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->patient_id.'</td>
              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->vente_id.'</td>
              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->date_vente.'</td>
              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reglement->net_apayer,'0','.',' ').'</td>
              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reglement->montant_reglement,'0','.',' ').'</td>
              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($total_reg,'0','.',' ').'</td>
              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reste,'0','.',' ').'</td>
            </tr>';
        }
        $output.='
            <tr style="border-radius: 5px; background-color: #27a5de";>
              <td colspan="3" style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL REGLE</td>
              <td colspan="5" style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total,'0','.',' ').'</td>
            </tr>
            </body>
        </table>
        <br><br>
        <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
           <tr>
              <tr style="border-radius: 5px; background-color: #27a5de";>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL ENCAISSE</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total+$encaisses,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">BENEFICE SUR LES VENTES</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($marge,'0','.',' ').' Franc CFA</td>
            </tr>
        </table> ';
        try{
            DB::beginTransaction();
            DB::table('usercons')->delete();
            DB::connection('vps')->table('usercons')->delete();
            DB::commit();
            return response()->json(['data' => $output]);
        }catch(\Throwable $th){
            DB::rollBack();
        }
    }

    public function user_selected()
    {
        $usercon = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->where('users.statut','=','true')
            ->where('users.centre_id','=',Auth::user()->centre_id)
            ->get();
        $output='
        <table class="table table-striped table-bordered contour_table" id="pdt_rec">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">User</td>
               <td>Retirer</td>
           </tr>
           </thead>
           <tbody>';
        foreach($usercon as $user){
            $button_supp = '<button type="button" name="delete" id="'.$user->id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';;

            $output .='<tr>
                 <td class="cart_title">'.$user->name.'</td>
                 <td class="cart_delete">'.$button_supp.'</td>
             </tr>';
        }
        $output.='</body>
                    </table>';
        return $output;
    }

    public function printdetailscaisse($debut,$fin){
        $users = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->where('users.centre_id','=',Auth::user()->centre_id)
            ->get();
        $uc = array();
        $utilisateurs = '';
        foreach ($users as $user){
            array_push($uc, $user->id);
            $utilisateurs .= $user->name.'-';
        }

        $utilisateurs= rtrim($utilisateurs,'-');

        $vmomt = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('montant_total');

        $vpec = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('prise_en_charge');

        $vnet = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('net_apayer');

        $total = DB::table('reglements')
            ->whereBetween('date_reglement', array($debut, $fin))
            ->whereIn('user_id',$uc)
            //->where('reglement_source', '=','REGLEMENT')
            ->sum('montant_reglement');

        $produit_ventes = DB::table('produit_ventes')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            //->selectRaw('produits.libelle')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->whereIn('ventes.user_id',$uc)
            ->orderBy('ventes.date_vente')
            ->orderBy('produits.libelle')
            ->orderBy('ventes.user_id')
            ->get();
        $ventes = DB::table('ventes')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->whereIn('ventes.user_id',$uc)
            ->orderBy('ventes.vente_id')
            ->get();

        $centre  = Centre::find(Auth::user()->centre_id);
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                        <div style="font-size:10px;">'.$centre->services.'</div>
                        <div style="font-size:15px;">'.$centre->adresse.'</div>
                        <div style="font-size:15px;">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">DETAILS DES TICKETS LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
                </tr>
                <tr>
                    <td width="100%" style="font-size: 15px;">USERS : '.$utilisateurs.' </td>
                </tr>
            </table><br>';

            $output.='
            <div class="conteneur">
            <table style="width: 33%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                <thead>
                    <tr style="border-radius: 15px; background-color: #d1d73f";>
                        <th style="font-size: 15px;" width="10%">Date</th>
                        <th style="font-size: 15px;" width="10%">Vente N°</th>
                        <th style="font-size: 15px;" width="25%">Produit</th>
                        <th style="font-size: 15px;" width="10%">Qte</th>
                        <th style="font-size: 15px;" width="10%">Montant</th>
                        <th style="font-size: 15px;" width="10%">PEC</th>
                        <th style="font-size: 15px;" width="10%">Part du patient</th>
                        <th style="font-size: 15px;" width="15%">User</th>
                    </tr>
                </thead>
                <tbody>';

            foreach($produit_ventes as $vente){
                $produits = DB::table('produit_ventes')
                    ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                    ->where('produit_ventes.vente_id','=',$vente->vente_id)
                    ->orderBy('produits.libelle')
                    ->get();
                $output .='
                <table style="width: 3%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <tr style="border-radius: 15px; background-color: #d1d73f";>
                        <th style="font-size: 20px;font-weight: bold" width="10%">Vente N° : '.$vente->vente_id.'</th>
                        <th style="font-size: 15px;" width="10%">Date : '.$vente->date_vente.'</th>
                        <th style="font-size: 15px;" width="10%">Patient : '.$vente->vente_id.'</th>
                        <th style="font-size: 15px;" width="10%">Montant : '.$vente->montant_total.'</th>
                    </tr>
                </table>
                <table style="width: 33%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                        <tr style="border-radius: 15px;";>
                            <th style="font-size: 15px;" width="10%">Produit</th>
                            <th style="font-size: 15px;" width="10%">PU</th>
                            <th style="font-size: 15px;" width="10%">QTE</th>
                            <th style="font-size: 15px;" width="10%">Mont</th>
                        </tr>
                    </thead>
                <tbody>';
                foreach($produits as $produit){
                    $output.='
                        <tr style="border-radius: 15px;";>
                            <th style="font-size: 15px;font-weight: bold" width="10%">'.$produit->libelle.'</th>
                            <th style="font-size: 15px;" width="10%">'.$produit->pu.'</th>
                            <th style="font-size: 15px;" width="10%">'.$produit->qte.'</th>
                            <th style="font-size: 15px;" width="10%">'.$produit->mont.'</th>
                        </tr>
                    ';
                }
            }
        $output.='</body></table><br>
        <table style="width: 33%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
           <tr>
              <tr style="border-radius: 5px; background-color: #27a5de";>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">VENTE : '.number_format($vmomt,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">ASSURANCE : '.number_format($vpec,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">NET : '.number_format($vnet,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">ENCAISSE : '.number_format($total,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">CREDIT : '.number_format($total-$vnet,'0','.',' ').' Franc CFA</td>
            </tr>
        </table> </div>';
        try{
            DB::beginTransaction();
            DB::table('usercons')->delete();
            DB::connection('vps')->table('usercons')->delete();
            DB::commit();
            return response()->json(['data' => $output]);
        }catch(\Throwable $th){
            DB::rollBack();
        }

    }

    public function etatassurance(Request $request){
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('ventes')
                ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
                ->selectRaw('assurances.assurance_id,assurances.nom,sum(ventes.prise_en_charge) as prise_en_charge')
                ->whereBetween('ventes.date_vente', array($request->from_date, $request->to_date))
                ->where('ventes.centre_id','=',Auth::user()->centre_id)
                ->groupBy('assurances.assurance_id','assurances.nom')
                ->get();
        }
        else
        {
            $historiques = DB::table('ventes')
                ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
                ->selectRaw('assurances.assurance_id,assurances.nom,sum(ventes.montant_total) as montant,sum(ventes.prise_en_charge) as pec')
                ->whereBetween('ventes.date_vente', array(date('Y-m-d'), date('Y-m-d')))
                ->where('ventes.centre_id','=',Auth::user()->centre_id)
                ->groupBy('assurances.assurance_id','assurances.nom')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        return view('etat.etatassurance', compact('historiques'));


    }

    protected function print_etatassurance($debut,$fin,$mut){
        $assurance = assurance::find($mut);

        $ventes = DB::table('ventes')
            ->join('patients','patients.patient_id','=','ventes.patient_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.assurance_id','=',$mut)
            ->get();

        $vmomt = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('assurance_id','=',$mut)
            ->sum('montant_total');

        $vpec = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('assurance_id','=',$mut)
            ->sum('prise_en_charge');
        $vnet = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('assurance_id','=',$mut)
            ->sum('net_apayer');

        $centre  = Centre::find(Auth::user()->centre_id);
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                        <div style="font-size:10px;">'.$centre->services.'</div>
                        <div style="font-size:15px;">'.$centre->adresse.'</div>
                        <div style="font-size:15px;">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">DETAILS DES VENTES DE LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
                </tr>
                <tr>
                    <td width="100%" style="font-size: 15px;">DE LA assurance : '.$assurance->nom.' </td>
                </tr>
            </table>

            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
               <thead>
                   <tr style="border-radius: 15px; background-color: #d1d73f";>
                       <th style="font-size: 15px;" width="14%">Date</th>
                       <th style="font-size: 15px;" width="16%">Vente num</th>
                       <th style="font-size: 15px;" width="12%">Montant</th>
                       <th style="font-size: 15px;" width="12%">Prise en charge</th>
                       <th style="font-size: 15px;" width="12%">Part du patient</th>
                       <th style="font-size: 15px;" width="34%">Patient</th>
                   </tr>
               </thead>
               <tbody>';
                foreach($ventes as $vente){
                    $output .='
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td  width="14%" style="font-size:15px; border: 1px solid;">'.$vente->date_vente.'</td>
                        <td  width="16%" style="font-size:15px; border: 1px solid;">'.$vente->code.'</td>
                        <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($vente->montant_total,'0','.',' ').'</td>
                        <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($vente->prise_en_charge,'0','.',' ').'</td>
                        <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($vente->net_apayer,'0','.',' ').'</td>
                        <td  width="34%" style="font-size:15px; border: 1px solid; text-align: left">'.$vente->code_patient.'/'.$vente->nom_prenom.'</td>
                    </tr>';
                }
            $output .='
                </body>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="33%" style="font-size: 17px;">Vente Totale : <b>'.number_format($vmomt,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px;">Prise en  charge : <b>'.number_format($vpec,'0','.',' ').'</b> </td>
                    <td width="33%" style="font-size: 17px;">Montant payer par les patients : <b>'.number_format($vnet,'0','.',' ').'</b> </td>
                </tr>
            </table>
            ';

        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        //return $output;
        return response()->json(['data' => $output]);

    }

    public function credit(){
        $credits = DB::table('ventes')
            ->where('etat','=','Credit')
            ->get();
        $les_credits = [];

        foreach ($credits as $credit){
            $total_reg = DB::table('reglements')
                ->where('code','=',$credit->vente_id)
                ->sum('montant_reglement');
            $reste = $credit->net_apayer-$total_reg;
            $element  = new \stdClass();
            $element->date_vente = $credit->date_vente;
            $element->vente_id = $credit->vente_id;
            $element->net_apayer = $credit->net_apayer;
            $element->total_reg = $total_reg;
            $element->reste = $reste;
            $element->patient_id = $credit->patient_id;

            array_push($les_credits,$element);
        }

        if (Auth::user()->ut==1){
            return view ('vente.credit', compact('les_credits'));
        }elseif (Auth::user()->ut==2){
            return view ('vente.creditcompta', compact('les_credits',));
        }elseif (Auth::user()->ut==4){
            return view ('vente.creditcaisse', compact('les_credits',));
        }else{
            //
        }
    }

    public function credit_liste(){
        $credits = DB::table('ventes')
            ->where('etat','=','Credit')
            ->get();
        $centre  = Centre::find(Auth::user()->centre_id);
        $total=0;
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom_centre.'</div>
                        <div style="font-size:10px;">'.$centre->services.'</div>
                        <div style="font-size:15px;">'.$centre->adresse.'</div>
                        <div style="font-size:15px;">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">LISTE DES CREDITS ENCOURS</b> </td>
                </tr>
                <tr>
                    <td width="100%" style="font-size: 15px;">USER : '.Auth::user()->name.' </td>
                </tr>
            </table><br>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000;" cellspacing="0" cellpadding="3">
                <thead>
                    <tr>
                        <th style="font-size:15px; border: 1px solid;">Patient</th>
                        <th style="font-size:15px; border: 1px solid;">Date</th>
                        <th style="font-size:15px; border: 1px solid;">Vente</th>
                        <th style="font-size:15px; border: 1px solid;">Montant</th>
                        <th style="font-size:15px; border: 1px solid;">Deja paye</th>
                        <th style="font-size:15px; border: 1px solid;">Reste</th>
                    </tr>
                </thead>
                <tbody>';

                foreach ($credits as $credit){
                    $total_reg = DB::table('reglements')
                        ->where('code','=',$credit->vente_id)
                        ->sum('montant_reglement');
                    $reste = $credit->net_apayer-$total_reg;

                    $output.='<tr style="border-radius: 12px";>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$credit->patient_id.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$credit->date_vente.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$credit->vente_id.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($credit->net_apayer,'0','.',' ').'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($total_reg,'0','.',' ').'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reste,'0','.',' ').'</td>
                            </tr>';
                    $total+=$reste;
                    }
                    $output.='
                        <tr style="border-radius: 5px; background-color: #27a5de";>
                          <td colspan="3" style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL DES CREDITS</td>
                          <td colspan="3" style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total,'0','.',' ').' Fr CFA</td>
                        </tr>
                        </body>
                    </table>
                    ';
        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        return $output;
    }
}
