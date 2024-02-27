<?php

namespace App\Http\Controllers;

use App\Models\Assurance;
use App\Models\Categorie;
use App\Models\Centre;
use App\Models\ConcernerVente;
use App\Exports\VenteExport;
use App\Models\Magasin;
use App\Models\Mouvement;
use App\Models\Mutuelle;
use App\Models\Produit;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\ProduitPrescription;
use App\Models\StockProduit;
use App\Models\Reglement;
//use App\Models\StockProduit;
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

class ASCController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function prescription_code(){
        $debut = date('Y').'-'.date('m').'-01';
        $fin = date('Y-m-d');
        //dd($debut,$fin);
        $nb_ven = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('user_id','=',Auth::user()->id)
            ->count()+1;

        $ven_num = '0'.$nb_ven.date('m').date('y').Auth::user()->id;
        $old_num = Prescription::find($ven_num);
        //dd($old_num);
        $control='';
        while ($old_num!=null){
            $control=$control.'1';
            $form_prod = array(
                'ven_num' =>  $ven_num.'INCREMENTE'.$control.rand(10),
                'ven_date'  =>  $fin,
                'ven_mont'  =>  0,
                'ven_pec'  =>  0,
                'ven_net'  =>  0,
                'ven_paye'  =>  0,
                'ven_rem'  =>  0,
                'ven_rel'  =>  0,
                'pat_num'  =>  'INCREMENTE',
                'mut_num'  =>  1,
                'user_id'   =>  Auth::user()->id,
                'userid'   =>  Auth::user()->id
            );

            try{
                DB::beginTransaction();
                    Prescription::create($form_prod);
                DB::commit();
            }catch(\Throwable $th){
                DB::rollBack();
            }
            $nb_ven = DB::table('prescriptions')
                    ->whereBetween('ven_date', array($debut, $fin))
                    ->where('user_id','=',Auth::user()->id)
                    ->count()+1;

            $ven_num = '0'.$nb_ven.date('m').date('y').Auth::user()->id;
            $old_num = Prescription::find($ven_num);
        }/*else{
            $ven_num = '0'.$nb_ven.date('m').date('y').Auth::user()->id;
        }*/
        return $ven_num;
    }

    public function index()
    {
        if(Session::get('mag_num')){
            $mag_num = Session::get('mag_num');
            $magasin = Magasin::find($mag_num);
            $ven_num = $this->prescription_code();
            $patient = Patient::where('pat_etat','=','OK')->pluck('pat_nom','pat_num');
            $mutuelles = [];
            $produits = [];

            $prescription = new Prescription();
            $allProduits = DB::table('quantite_produits')
                ->join('produits','produits.pdt_num','=','quantite_produits.pdt_num')
                ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
                ->where('quantite_produits.etat','<>','Delete')
                //->where('magasins.mag_type','=','Depot_vente')
                ->where('magasins.mag_num','=',$mag_num)
                ->where('quantite_produits.qter','>','0')
                ->where('produits.pdt_etat','=','OK')
                ->select('produits.pdt_num')->distinct()
                ->get();
            foreach ($allProduits as $pdt){
                array_push($produits,$this->rechpdtPQ($pdt->pdt_num,$mag_num));
            }
            //dd($produits);

            $pdtcon = [];

            if (Auth::user()->ut==1){
                return view ('asc.indexa', compact('vente','produits','ven_num','patient','mutuelles','magasin'));
            }elseif (Auth::user()->ut==4){
                return view ('asc.index', compact('vente','produits','ven_num','patient','mutuelles','magasin'));
            }elseif (Auth::user()->ut==5){
                return view ('ventepharmacie.index', compact('vente','produits','ven_num','patient','mutuelles','magasin'));
            }else{

            }
        }else{
            return redirect()->route('asc.select_mag');
        }

    }

    public function prescriptions()
    {
        $prescriptions = DB::table('prescriptions')
            ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
            ->where('prescriptions.ven_etat','=','Encours')
            ->get();
        return view ('asc.prescriptions', compact('prescriptions'));
    }

    public function selectionner($id)
    {
        $prescription = Prescription::findOrfail($id);
        if ($prescription){
            Session::put('vente',$prescription);
            return redirect()->route('asc.encaisse');
        }else{
            Alert::error('Erreur','Vente inexistante');
            return back();
        }
    }

    public function select_mag(){
        $magasins = DB::table('magasins')
            ->where('mag_etat','=','OK')
            ->get();

        if (Auth::user()->ut==1){
            return view ('asc.select_maga', compact('magasins'));
        }elseif (Auth::user()->ut==4){
            return view ('asc.select_mag', compact('magasins'));
        }else{

        }
        return view ('asc.select_mag', compact('magasins'));
    }

    public function mag_source($mag_num){
        Session::put('mag_num',$mag_num);

        return redirect()->route('asc.index');
    }

    public function encaisse()
    {
        $vent = Session::get('vente');
        $unevente = DB::table('prescriptions')
            ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
            ->where('prescriptions.ven_num','=',$vent->ven_num)
            ->get();
        $prescription = (object) $unevente[0];

        $pdtcon = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->where('concerner_prescriptions.ven_num','=',$vent->ven_num)
            ->get();

        return view ('asc.encaisse', compact('vente','pdtcon'));
    }

    private function rechpdtPQ($pdt_num,$mag_num){
        $pdtqp = DB::table('quantite_produits')
            ->join('produits','produits.pdt_num','=','quantite_produits.pdt_num')
            ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
            ->where('quantite_produits.etat','<>','Delete')
            //->where('magasins.mag_type','=','Depot_vente')
            ->where('magasins.mag_num','=',$mag_num)
            ->where('quantite_produits.pdt_num','=',$pdt_num)
            ->where('quantite_produits.qter','>','0')
            ->get();
        $produit = new \stdClass();
        if (count($pdtqp)==1){
            $pdt = (object) $pdtqp[0];
            $produit->cat_num = $pdt->cat_num;
            $produit->pdt_num = $pdt->pdt_num;
            $produit->pdt_lib = $pdt->pdt_lib;
            $produit->pv = $pdt->pv;
            $produit->qte = $pdt->qter;
        }else{
            $pdt = (object) $pdtqp[0];
            $qte = DB::table('quantite_produits')
                ->where('etat','<>','Delete')
                ->where('pdt_num','=',$pdt_num)
                ->where('mag_num','=',$mag_num)
                ->where('qter','>','0')
                ->sum('qter');
            $rech_pv = DB::table('quantite_produits')
                ->where('pdt_num','=',$pdt_num)
                ->where('mag_num','=',$mag_num)
                ->where('etat','=','Encours')
                ->get();

            $rech_prix = (object) $rech_pv[0];
            $pv = $rech_prix->pv;

            $produit->cat_num = $pdt->cat_num;
            $produit->pdt_num = $pdt->pdt_num;
            $produit->pdt_lib = $pdt->pdt_lib;
            $produit->pv = $pv;
            $produit->qte = $qte;
        }
        return $produit;
    }

    public function mutuelles(){
        if (\request()->ajax()){
            $mutuelles = DB::table('mutuelles')
                ->where('mut_etat','<>','Delete')
                ->get();
            return $mutuelles;
        }
    }

    public function rech_pdtcon($ven_num)
    {
        $pdtcon = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->where('concerner_prescriptions.ven_num','=',$ven_num)
            ->get();
        if (count($pdtcon)==0){
            $output='<table class="table table-striped table-bordered contour_table" id="pdt_selected">
               <thead>
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <td class="description">Produit</td>
                       <td class="price">Prix</td>
                       <td class="quantity">Qte</td>
                       <td></td>
                   </tr>
               </thead>
                <tbody>
                    <tr>
                         <td class="cart_title"></td>
                         <td class="cart_price"></td>
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
                       <td class="description">Quantite</td>
                       <td colspan="2"></td>
                   </tr>
               </thead>
                <tbody>';
                    foreach($pdtcon as $produit){
                        $button_edit = '<button type="button" name="edit" id="'.$produit->id.'" class="edit btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                        $button_delete = '<button type="button" name="delete" id="'.$produit->id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                        $output .='<tr>
                         <td class="cart_title">'.$produit->pdt_ref.'</td>
                         <td class="cart_title">'.$produit->pdt_lib.'</td>
                         <td class="cart_price">'.$produit->qte.'</td>
                         <td class="cart_delete">'.$button_edit.'</td>
                         <td class="cart_delete">'.$button_delete.'</td>
                     </tr>';
                    }
                    $output.='</body>
                </table>';
        }
        return $output;
    }

    public function rech_mont($ven_num)
    {
        if(request()->ajax())
        {
            $mont = ProduitPrescription::where('ven_num','=',$ven_num)->sum('mont');
            $pec = ProduitPrescription::where('ven_num','=',$ven_num)->sum('pec');
            $net = ProduitPrescription::where('ven_num','=',$ven_num)->sum('net');
            return response()->json(['mont' => $mont,'pec' => $pec,'net' => $net]);
        }
    }

    public function rechtaux($mut_num){
        if(request()->ajax()) {
            $mut_taux = Mutuelle::find($mut_num)->mut_taux;
            return response()->json($mut_taux);
        }
    }

    public function rech_code($ven_date)
    {
        $mois = substr($ven_date,5,2);
        $annee = substr($ven_date,0,4);
        $ar = substr($ven_date,2,2);
        $nbrjour = cal_days_in_month(CAL_GREGORIAN,$mois,$annee);
        $debut = $annee.'-'.$mois.'-01';
        $fin = $annee.'-'.$mois.'-'.$nbrjour;
        $prescriptionp = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('user_id','=',Auth::user()->id)
            ->get();
        $nb_ven = $prescriptionp->count()+1;

        $ven_num = '0'.$nb_ven.$mois.$ar.'-'.Auth::user()->id;

        $old_num = Prescription::find($ven_num);
        if ($old_num){
            $form_prod = array(
                'ven_num' =>  $ven_num.'-INCREMENTE',
                'ven_date'  =>  $ven_date,
                'ven_mont'  =>  0,
                'ven_pec'  =>  0,
                'ven_net'  =>  0,
                'ven_rem'  =>  0,
                'ven_rel'  =>  0,
                'pat_num'  =>  'INCREMENTE',
                'mut_num'  =>  1,
                'user_id'   =>  Auth::user()->id
            );

            Prescription::create($form_prod);
            $prescriptionp = DB::table('prescriptions')
                ->whereBetween('ven_date', array($debut, $fin))
                ->where('user_id','=',Auth::user()->id)
                ->get();
            $nb_ven = $prescriptionp->count()+1;

            $ven_num = '0'.$nb_ven.$mois.$ar.Auth::user()->id;
        }else{
            $ven_num = '0'.$nb_ven.$mois.$ar.Auth::user()->id;
        }

        if(request()->ajax())
        {
            return response()->json(['ven_num' => $ven_num,'Nbre Jours'=>$nbrjour]);
        }
    }

    public function select($id)
    {
        if(request()->ajax())
        {
            $produit = new \stdClass();
            $pdt = Produit::find($id);
            $categorie = Categorie::find($pdt->cat_num);
            if ($categorie->cat_type=='Non_stockable'){
                $produit->cat_num = $pdt->cat_num;
                $produit->pdt_num = $pdt->pdt_num;
                $produit->pdt_lib = $pdt->pdt_lib;
                $produit->pv = $pdt->pdt_pv;
                $produit->qte = 1;

            }else{
                $produit = $this->rechpdtPQ($id,Session::get('mag_num'));
            }
            return response()->json($produit);
        }

    }

    public function select_mut($id_mut,$pdt_num)
    {
        if(request()->ajax())
        {
            $produit = new \stdClass();
            $pdt = Produit::find($pdt_num);
            $pdt_mut = Assurance::find($id_mut);

            $produit->cat_num = $pdt->cat_num;
            $produit->pdt_num = $pdt->pdt_num;
            $produit->pdt_lib = $pdt->pdt_lib;
            $produit->base = $pdt_mut->base;
            $produit->taux = $pdt_mut->taux;

            $categorie = Categorie::find($pdt->cat_num);
            if ($categorie->cat_type=='Non_stockable'){
                $produit->pv = $pdt->pdt_pv;
                $produit->qte = 1;

            }else{
                $pdtqp = DB::table('quantite_produits')
                    ->join('produits','produits.pdt_num','=','quantite_produits.pdt_num')
                    ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
                    ->where('quantite_produits.etat','<>','Delete')
                    ->where('magasins.mag_type','=','Depot_vente')
                    ->where('quantite_produits.pdt_num','=',$pdt_num)
                    ->where('quantite_produits.qter','>','0')
                    ->get();

                $pdt = (object) $pdtqp[0];

                if (count($pdtqp)==1){
                    $produit->pv = $pdt->pv;
                    $produit->qte = $pdt->qter;
                }else{
                    $qte = DB::table('quantite_produits')
                        ->where('etat','<>','Delete')
                        ->where('pdt_num','=',$pdt_num)
                        ->where('mag_num','=',$pdt->mag_num)
                        ->where('qter','>','0')
                        ->sum('qter');
                    $rech_pv = DB::table('quantite_produits')
                        ->where('pdt_num','=',$pdt_num)
                        ->where('mag_num','=',$pdt->mag_num)
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
                ->where('libelle','LIKE','%'.$pdt->pdt_lib.'%')
                //->orWhere('dci','LIKE','%'.$pdt->pdt_lib.'%')
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
                     <td class="select_mut btn btn-danger" id="'.$produit->id.'"><i class="fa fa-check"></i><input type="text" id="pdt_num" name="pdt_num" value="'.$pdt->pdt_num.'"></td>
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
            $produits = DB::table('concerner_prescriptions')
                ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                ->where('concerner_prescriptions.id','=',$id)
                ->get();
            $data = (object) $produits[0];
            return response()->json($data);
        }
    }

    public function add(Request $request)
    {
        $rules = array(
            'pu'     =>  'required|numeric|min:0',
            'base'     =>  'required|numeric|min:0',
            'marge'     =>  'required|numeric|min:0',
            'taux'     =>  'required|numeric|min:0',
            'qte'     =>  'required|numeric|min:0'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $mont = ($request->pu*$request->qte);
        $pec = ($request->base*$request->qte)*$request->taux/100;
        $net = $mont - $pec;
        $form_data = array(
            'ven_num' =>  $request->hidden_ven_num,
            'pdt_num'  =>  $request->pdt_num,
            'cat_num'  =>  $request->cat_num,
            'pu'  =>  $request->pu,
            'base'  =>  $request->base,
            'ini'  =>  $request->ini,
            'qte'   =>  $request->qte,
            'mont'   =>  $mont,
            'pec'   =>  $pec,
            'net'   =>  $net
        );

        $con_ven = ProduitPrescription::where('ven_num','=',$request->hidden_ven_num)
            ->where('pdt_num','=',$request->pdt_num)->get();

        if ($request->hidden_idcon==null){
            $categorie = Categorie::find($request->cat_num);
            if (count($con_ven)==0) {
                DB::beginTransaction();
                try {
                    if ($categorie->cat_type=='Stockable'){
                        if ($request->ini-$request->qte>=0){
                            ProduitPrescription::create($form_data);
                            DB::commit();
                            return response()->json(['success' => 'Produit ajoutet']);
                        }else{
                            return response()->json(['error' => 'Quantite saisie depasse la quantite disponible']);
                        }
                    }else{
                        ProduitPrescription::create($form_data);
                        DB::commit();
                        return response()->json(['success' => 'Produit ajoutet']);
                    }
                }catch (\PDOException $se) {
                    DB::rollBack();
                    return response()->json(['error' => 'Erreur survenu lors de l execution. produit non ajoute']);
                }
            }else{
                return response()->json(['error' => 'Produit existe deja dans la selection']);
            }
        }else{
            $concerne = DB::table('produits')
                ->join('concerner_prescriptions','concerner_prescriptions.pdt_num','=','produits.pdt_num')
                ->join('categories','categories.cat_num','=','produits.cat_num')
                ->where('concerner_prescriptions.id','=',$request->hidden_idcon)
                ->get();
            $categorie = (object) $concerne[0];
            DB::beginTransaction();
            try {
                if ($categorie->cat_type=='Stockable'){
                    if ($request->ini-$request->qte>=0){
                        ProduitPrescription::find($request->hidden_idcon)->update($form_data);
                        DB::commit();
                        return response()->json(['success' => 'Produit ajoutet']);
                    }else{
                        return response()->json(['error' => 'Quantite saisie depasse la quantite disponible']);
                    }
                }else{
                    ProduitPrescription::find($request->hidden_idcon)->update($form_data);
                    DB::commit();
                    return response()->json(['success' => 'Produit ajoutet']);
                }
            }catch (\PDOException $se) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur survenu lors de l execution. produit non ajoute']);
            }
        }
    }

    public function delete($id){
        if (\request()->ajax()){
            ProduitPrescription::find($id)->delete();
            return back()->with('success', 'Produit retire');
        }

    }

    public function annuler($id){
        if (\request()->ajax()){
            DB::table('concerner_prescriptions')
                ->where('ven_num','=',$id)
                ->delete();
            return back()->with('success', 'Vente annuler');
        }

    }

    public function annulervente($id){
        if (\request()->ajax()){
            $produits = DB::table('concerner_prescriptions')
                ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                ->where('concerner_prescriptions.ven_num','=',$id)
                ->get();

            DB::beginTransaction();
            try {
                foreach ($produits as $produit){
                    $categorie = Categorie::find($produit->cat_num);

                    if ($categorie->cat_type=='Stockable'){
                        $pdtqtes = DB::table('quantite_produits')
                            ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
                            ->where('magasins.mag_type','=','Depot_vente')
                            ->where('quantite_produits.etat','<>','Delete')
                            ->where('quantite_produits.pdt_num','=',$produit->pdt_num)
                            ->orderBy('quantite_produits.date_exp')
                            ->get();

                        $qteIni = DB::table('quantite_produits')
                            ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
                            ->where('magasins.mag_type','=','Depot_vente')
                            ->where('quantite_produits.etat','<>','Delete')
                            ->where('quantite_produits.pdt_num','=',$produit->pdt_num)
                            ->sum('quantite_produits.qter');

                        $qte = $produit->qte;

                        for ($i = 0; $i < count($pdtqtes); $i++){
                            if ($pdtqtes[$i]->qter<$qte){
                                StockProduit::find($pdtqtes[$i]->id)->update(['qter'=>0]);
                                $qte = $qte-$pdtqtes[$i]->qter;
                            }else{
                                StockProduit::find($pdtqtes[$i]->id)->update(['qter'=>$pdtqtes[$i]->qter-$qte]);
                                break;
                            }
                        }
                        Mouvement::create([
                            'mv_date' =>  $request->ven_date,
                            'mag_num' =>  $pdtqtes[0]->mag_num,
                            'pdt_num' =>  $produit->pdt_num,
                            'mv_lib' =>  'Vente numero '.$request->ven_num,
                            'mv_ini' =>  $qteIni,
                            'mv_sor' =>  $qte,
                            'mv_act' =>  $qteIni-$qte,
                            'idop' =>  $request->ven_num,
                            'idcon' =>  $produit->id
                        ]);
                    }
                }
                Reglement::create([
                    'reg_date'=> $request->ven_date,
                    'reg_mont'=> $mont_paye,
                    'ven_num'=> $request->ven_num,
                    'user_id'=> Auth::user()->id
                ]);
                Prescription::create($form_prod);
                DB::commit();

                //$prescription = Prescription::where('ven_num','=',$request->ven_num)->get();
                $prescription = DB::table('prescriptions')
                    ->join('users','users.id','=','prescriptions.user_id')
                    ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
                    ->where('prescriptions.ven_num','=', $request->ven_num)
                    ->get();

                $prescription = (object) $prescription[0];
                $date = new \DateTime($prescription->ven_date);
                $ven_date = $date->format('d-m-Y');
                $ven_heure = $date->format('H:m:s');

                $produits = DB::table('concerner_prescriptions')
                    ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                    ->where('concerner_prescriptions.ven_num','=',$request->ven_num)
                    ->get();
                $centre  = Centre::find('1');
                if ($centre->impression=='Format_A5'){
                    return view('etat.printven_a5', compact('vente','ven_date','produits','centre','ven_heure','entete','texte','reste'));
                }else{
                    return view('etat.printven_ticket', compact('vente','ven_date','produits','centre','ven_heure','entete','texte','reste'));
                }
            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur','Ereur survenu lors de la sauvegarde'.$se);
                return back();
            }
        }

    }

    public function supprimer($id){
        $prescription = Prescription::findOrfail($id);
        if ($prescription->ven_etat!='Encours'){
            Alert::error('Erreur','Impossible d annuler cette asc. Deja encaisse');
            return redirect()->route('asc.index');
        }else{
            DB::beginTransaction();
            try {
                DB::table('concerner_prescriptions')
                    ->where('ven_num','=',$id)
                    ->delete();
                $prescription->update(['ven_etat'=>'Annulee',
                    'ven_mont'  =>  0,
                    'ven_pec'  =>  0,
                    'ven_net'  =>  0,
                    'ven_paye'  =>  0,
                    'ven_rem'  =>  0,
                    'ven_rel'  =>  0
                ]);
                DB::commit();
                Alert::warning('Infos','Vente Vente annulee');
                return redirect()->route('asc.index');
            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur','Erreur survenu lors de la sauvegarde');
                return redirect()->route('asc.index');
            }
        }
    }

    public function store(Request $request){
        $ven_etat = 'Soldee';
        $texte = 'RELIQUAT';
        $entete = '';
        $mag_num = Session::get('mag_num');
        $reste = $request->ven_rel;
        $mont_paye = $request->ven_net;
        if ($request->ven_rel<0) {
            $ven_etat = 'Credit';
            $mont_paye = $request->ven_rem;

            $texte = 'RESTE A PAYER';
            $reste = $request->ven_rel*(-1);
            $entete = 'ACHAT A CREDIT';
        }
        $ven_date = $request->ven_date . ' ' . date("H:i:s");
        $ven_heure = date("H:i:s");
        $form_prod = array(
            'ven_num' => $request->ven_num,
            'ven_date' => $ven_date,
            'ven_heure' => $ven_heure,
            'ven_mont' => $request->ven_mont,
            'ven_pec' => $request->ven_pec,
            'ven_net' => $request->ven_net,
            'ven_paye' => $mont_paye,
            'ven_rem' => $request->ven_rem,
            'ven_rel' => $request->ven_rel,
            'ven_etat' => $ven_etat,
            'pat_num' => $request->pat_num,
            'mut_num' => $request->mut_num,
            'user_id' => Auth::user()->id
        );
        $produits = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->where('concerner_prescriptions.ven_num','=',$request->ven_num)
            ->get();
        if (count($produits)>0){
            DB::beginTransaction();
            try {
                foreach ($produits as $produit){
                    $categorie = Categorie::find($produit->cat_num);

                    if ($categorie->cat_type=='Stockable'){
                        $pdtqtes = DB::table('quantite_produits')
                            ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
                            //->where('magasins.mag_type','=','Depot_vente')
                            ->where('magasins.mag_num','=',$mag_num)
                            ->where('quantite_produits.etat','<>','Delete')
                            ->where('quantite_produits.pdt_num','=',$produit->pdt_num)
                            ->orderBy('quantite_produits.date_exp')
                            ->get();

                        $qteIni = DB::table('quantite_produits')
                            ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
                            //->where('magasins.mag_type','=','Depot_vente')
                            ->where('magasins.mag_num','=',$mag_num)
                            ->where('quantite_produits.etat','<>','Delete')
                            ->where('quantite_produits.pdt_num','=',$produit->pdt_num)
                            ->sum('quantite_produits.qter');

                        $qte = $produit->qte;

                        for ($i = 0; $i < count($pdtqtes); $i++){
                            if ($pdtqtes[$i]->qter<$qte){
                                StockProduit::find($pdtqtes[$i]->id)->update(['qter'=>0]);
                                $qte = $qte-$pdtqtes[$i]->qter;
                            }else{
                                StockProduit::find($pdtqtes[$i]->id)->update(['qter'=>$pdtqtes[$i]->qter-$qte]);
                                /*Mouvement::create([
                                    'mv_date' =>  $request->ven_date,
                                    'mag_num' =>  $pdtqtes[$i]->mag_num,
                                    'pdt_num' =>  $pdtqtes[$i]->pdt_num,
                                    'mv_lib' =>  'Vente numero '.$request->ven_num.' du lot '.$pdtqtes[$i]->lot,
                                    'mv_ini' =>  $pdtqtes[$i]->qter,
                                    'mv_sor' =>  $qte,
                                    'mv_act' =>  $pdtqtes[$i]->qter-$qte,
                                    'idop' =>  $request->ven_num,
                                    'idcon' =>  $produit->id
                                ]);*/
                                break;
                            }
                        }
                        Mouvement::create([
                            'mv_date' =>  $request->ven_date,
                            'mag_num' =>  $pdtqtes[0]->mag_num,
                            'pdt_num' =>  $produit->pdt_num,
                            'mv_lib' =>  'Vente numero '.$request->ven_num,
                            'mv_ini' =>  $qteIni,
                            'mv_sor' =>  $qte,
                            'mv_act' =>  $qteIni-$qte,
                            'idop' =>  $request->ven_num,
                            'idcon' =>  $produit->id
                        ]);
                    }
                }
                Reglement::create([
                    'reg_date'=> $request->ven_date,
                    'reg_mont'=> $mont_paye,
                    'ven_num'=> $request->ven_num,
                    'user_id'=> Auth::user()->id
                ]);
                Prescription::create($form_prod);
                DB::commit();
                Alert::success('Success','Medicaments servis avec successe');
                return back();
            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur','Ereur survenu lors de la sauvegarde'.$se);
                return back();
            }
        }else{
            Alert::warning('Infos','Pas de produits ou acte selection');
            return back();
        }
    }

    public function savepersonnel(Request $request){
        $ven_date = $request->ven_date . ' ' . date("H:i:s");
        $ven_heure = date("H:i:s");
        $form_prod = array(
            'ven_num' => $request->ven_num,
            'ven_date' => $ven_date,
            'ven_heure' => $ven_heure,
            'ven_mont' => $request->ven_mont,
            'ven_pec' => $request->ven_pec,
            'ven_net' => $request->ven_net,
            'ven_paye' => $request->ven_net,
            'ven_rem' => $request->ven_rem,
            'ven_rel' => 0,
            'ven_etat' => 'Encours',
            'pat_num' => $request->pat_num,
            'mut_num' => $request->mut_num,
            'user_id' => Auth::user()->id,
            'userid' => Auth::user()->id
        );
        $produits = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->where('concerner_prescriptions.ven_num','=',$request->ven_num)
            ->get();
        if (count($produits)>0){
            DB::beginTransaction();
            try {

                Prescription::create($form_prod);
                DB::commit();
                Alert::success('Success','Vente enregistree avec success');
                return redirect()->route('asc.index');
            }catch (\PDOException $se){
                DB::rollBack();
                Alert::error('Erreur','Erreur survenu lors de la sauvegarde'.$se);
                return redirect()->route('asc.index');
            }
        }else{
            Alert::warning('Infos','Pas de produits ou acte selection');
            return redirect()->route('asc.index');
        }
    }

    public function validerCaisse(Request $request){
        $ven_etat = 'Soldee';
        $texte = 'RELIQUAT';
        $entete = '';
        $reste = $request->ven_rel;
        $mont_paye = $request->ven_net;
        if ($request->ven_rel<0) {
            $ven_etat = 'Credit';
            $mont_paye = $request->ven_rem;

            $texte = 'RESTE A PAYER';
            $reste = $request->ven_rel*(-1);
            $entete = 'ACHAT A CREDIT';
        }
        $ven_date = $request->ven_date . ' ' . date("H:i:s");
        $ven_heure = date("H:i:s");
        $form_prod = array(
            'ven_num' => $request->ven_num,
            'ven_date' => $ven_date,
            'ven_heure' => $ven_heure,
            'ven_mont' => $request->ven_mont,
            'ven_pec' => $request->ven_pec,
            'ven_net' => $request->ven_net,
            'ven_paye' => $mont_paye,
            'ven_rem' => $request->ven_rem,
            'ven_rel' => $request->ven_rel,
            'ven_etat' => $ven_etat,
            'user_id' => Auth::user()->id
        );

        $produits = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->where('concerner_prescriptions.ven_num','=',$request->ven_num)
            ->get();
        if (count($produits)>0){
            DB::beginTransaction();
            try {
                foreach ($produits as $produit){
                    $categorie = Categorie::find($produit->cat_num);

                    if ($categorie->cat_type=='Stockable'){
                        $pdtqtes = DB::table('quantite_produits')
                            ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
                            ->where('magasins.mag_type','=','Depot_vente')
                            ->where('quantite_produits.etat','<>','Delete')
                            ->where('quantite_produits.pdt_num','=',$produit->pdt_num)
                            ->orderBy('quantite_produits.date_exp')
                            ->get();

                        $qteIni = DB::table('quantite_produits')
                            ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
                            ->where('magasins.mag_type','=','Depot_vente')
                            ->where('quantite_produits.etat','<>','Delete')
                            ->where('quantite_produits.pdt_num','=',$produit->pdt_num)
                            ->sum('quantite_produits.qter');

                        $qte = $produit->qte;

                        for ($i = 0; $i < count($pdtqtes); $i++){
                            if ($pdtqtes[$i]->qter<$qte){
                                StockProduit::find($pdtqtes[$i]->id)->update(['qter'=>0]);
                                $qte = $qte-$pdtqtes[$i]->qter;
                            }else{
                                StockProduit::find($pdtqtes[$i]->id)->update(['qter'=>$pdtqtes[$i]->qter-$qte]);
                                break;
                            }
                        }
                        Mouvement::create([
                            'mv_date' =>  $request->ven_date,
                            'mag_num' =>  $pdtqtes[0]->mag_num,
                            'pdt_num' =>  $produit->pdt_num,
                            'mv_lib' =>  'Vente numero '.$request->ven_num,
                            'mv_ini' =>  $qteIni,
                            'mv_sor' =>  $qte,
                            'mv_act' =>  $qteIni-$qte,
                            'idop' =>  $request->ven_num,
                            'idcon' =>  $produit->id
                        ]);
                    }
                }
                Reglement::create([
                    'reg_date'=> $request->ven_date,
                    'reg_mont'=> $mont_paye,
                    'ven_num'=> $request->ven_num,
                    'user_id'=> Auth::user()->id
                ]);
                Prescription::find($request->ven_num)->update($form_prod);
                DB::commit();

                //$prescription = Prescription::where('ven_num','=',$request->ven_num)->get();
                $prescription = DB::table('prescriptions')
                    ->join('users','users.id','=','prescriptions.user_id')
                    ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
                    ->where('prescriptions.ven_num','=', $request->ven_num)
                    ->get();

                $prescription = (object) $prescription[0];
                $date = new \DateTime($prescription->ven_date);
                $ven_date = $date->format('d-m-Y');
                $ven_heure = $date->format('H:m:s');

                $produits = DB::table('concerner_prescriptions')
                    ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                    ->where('concerner_prescriptions.ven_num','=',$request->ven_num)
                    ->get();
                $centre  = Centre::find('1');
                if ($centre->impression=='Format_A5'){
                    return view('etat.printven_a5', compact('vente','ven_date','produits','centre','ven_heure','entete','texte','reste'));
                }else{
                    return view('etat.printven_ticket', compact('vente','ven_date','produits','centre','ven_heure','entete','texte','reste'));
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

    public function show($ven_num){
        $prescription = DB::table('prescriptions')
            ->join('users','users.id','=','prescriptions.user_id')
            //->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
            ->where('prescriptions.ven_num','=', $ven_num)
            ->get();

        $prescription = (object) $prescription[0];
        $date = new \DateTime($prescription->ven_date);
        $ven_date = $date->format('d-m-Y');

        $produits = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->where('concerner_prescriptions.ven_num','=',$ven_num)
            ->get();
        $centre  = Centre::find('1');

        $texte = 'RELIQUAT';
        $entete = '';
        $reste = $prescription->ven_rel;

        if ($reste<0) {
            $texte = 'RESTE A PAYER';
            $reste = $reste*(-1);
            $entete = 'ACHAT A CREDIT';
        }

            return view('etat.printdupplicata_a5', compact('vente','ven_date','produits','centre','entete','texte','reste'));

    }


    public function imprimerduplicata($ven_num){
        $prescription = DB::table('prescriptions')
            ->join('users','users.id','=','prescriptions.user_id')
            ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
            ->where('prescriptions.ven_num','=', $ven_num)
            ->get();

        $prescription = (object) $prescription[0];
        $date = new \DateTime($prescription->ven_date);
        $ven_date = $date->format('d-m-Y');

        $produits = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->where('concerner_prescriptions.ven_num','=',$ven_num)
            ->get();
        $centre  = Centre::find('1');

        return view('etat.printdupplicata', compact('vente','ven_date','produits','centre'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $ven_num = $id;
        $prescription = Prescription::findOrfail($id);
        $patient = Patient::where('pat_etat','=','OK')->pluck('pat_nom','pat_num');
        $mutuelle = Mutuelle::where('mut_etat','=','OK')->pluck('mut_lib','mut_num');

        $prescription = new Vente();

        $produits = Produit::where('pdt_etat','=','OK')->get();
        $pdtcon = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->where('concerner_prescriptions.ven_num','=',$ven_num)
            ->get();
        $ven_mont = ProduitPrescription::where('ven_num','=',$ven_num)->sum('mont');
        $ven_pec = ProduitPrescription::where('ven_num','=',$ven_num)->sum('pec');
        $ven_net = ProduitPrescription::where('ven_num','=',$ven_num)->sum('net');

        return view ('asc.edit', compact('vente','pdtcon','produits','ven_mont','ven_pec','ven_net','ven_num','patient','mutuelle'));
    }

    public function update(Request $request, $id)
    {
        $prescription = Prescription::findOrfail($request->ven_num);
        $ven_num = $request->ven_num;

        $ven_mont = ProduitPrescription::where('ven_num','=',$ven_num)->sum('mont');
        $ven_pec = ProduitPrescription::where('ven_num','=',$ven_num)->sum('pec');
        $ven_net = $ven_mont-$ven_pec;

        $form_prod = array(
            'ven_num' =>  $ven_num,
            'ven_mont'  =>  $ven_mont,
            'ven_pec'  =>  $ven_pec,
            'ven_net'  =>  $ven_net,
            'ven_rem'  =>  $request->ven_rem,
            'ven_rel'  =>  $request->ven_rel,
            'pat_num'  =>  $request->pat_num,
            'mut_num'  =>  $request->mut_num,
            'user_id'   =>  Auth::user()->id
        );

        $prescription->update($form_prod);

        Alert::success('Success !', 'La Vente a ete bien enregistree.');
        //$this->imprimer_ven($ven_num);

        return redirect()->route('asc.index');
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
            $historiques = DB::table('prescriptions')
                ->join('users','users.id','=','prescriptions.user_id')
                //->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
                ->whereBetween('prescriptions.ven_date', array($request->from_date, $request->to_date))
                ->where('prescriptions.user_id','=',Auth::user()->id)
                ->orderBy('prescriptions.ven_date','asc')
                ->get();
        }
        else
        {
            $historiques = DB::table('prescriptions')
                ->join('users','users.id','=','prescriptions.user_id')
                //->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
                ->whereBetween('prescriptions.ven_date', array(date('Y-m-d'), date('Y-m-d')))
                ->where('prescriptions.user_id','=',Auth::user()->id)
                ->orderBy('prescriptions.ven_date','asc')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        $centre = Centre::find(1);
        if (Auth::user()->ut==1){
            return view('asc.histoa', compact('historiques','centre'));
        }elseif(Auth::user()->ut==4){
            return view('asc.histo', compact('historiques','centre'));
        }elseif(Auth::user()->ut==5){
            return view('ventepharmacie.histo', compact('historiques','centre'));
        }else{
            //Riena faire
        }

    }

    public function histoenc(Request $request){
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('prescriptions')
                ->join('users','users.id','=','prescriptions.userid')
                ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
                ->whereBetween('prescriptions.ven_date', array($request->from_date, $request->to_date))
                ->where('prescriptions.userid','=',Auth::user()->id)
                ->orderBy('prescriptions.ven_date','asc')
                ->get();
        }
        else
        {
            $historiques = DB::table('prescriptions')
                ->join('users','users.id','=','prescriptions.userid')
                ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
                ->whereBetween('prescriptions.ven_date', array(date('Y-m-d'), date('Y-m-d')))
                ->where('prescriptions.userid','=',Auth::user()->id)
                ->orderBy('prescriptions.ven_date','asc')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        $centre = Centre::find(1);

        return view('ventepharmacie.histo', compact('historiques','centre'));
    }

    public function parproduit(Request $request){
        $historiques = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
            ->selectRaw('produits.mat_lib,concerner_prescriptions.pu,sum(concerner_prescriptions.qte) as qte, sum(concerner_prescriptions.mont) as montant')
            ->whereBetween('prescriptions.ven_date', array($request->from_date, $request->to_date))
            ->groupBy('produits.mat_lib','concerner_prescriptions.pu')
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
        $historiques = DB::table('prescriptions')
            ->join('clients','clients.clt_num','=','prescriptions.clt_num')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->get();

        $comptant = DB::table('prescriptions')
            ->join('clients','clients.clt_num','=','prescriptions.clt_num')
            ->where('prescriptions.ven_mode','=','Comptant')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->get();

        $montant_comptant= DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('ven_mode','=','Comptant')
            ->sum('ven_ttc');

        $virement = DB::table('prescriptions')
            ->join('clients','clients.clt_num','=','prescriptions.clt_num')
            ->where('prescriptions.ven_mode','=','Virement')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->get();

        $montant_virement= DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('ven_mode','=','Virement')
            ->sum('ven_ttc');

        //dd($historiques);

        $montant= DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->sum('ven_ttc');

        //dd($montant);

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
                                <td width="100%">HISTORIQUE DES prescriptions DU '.$debut.' AU '.$fin.'</td>
                            </tr>

                        </table>

                        <br>
                        <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                            <thead>
                                <tr>
                                    <th colspan="6">PAYEMENT AU COMPTANT</th>
                                </tr>
                                <tr style="border-radius: 10px; background-color: #E5CC75";>
                                    <th width="16%">Date</th>
                                    <th width="16%">Client</th>
                                    <th width="16%">Montant HT</th>
                                    <th width="16%">Montant TTC</th>
                                    <th width="16%">Montant Paye</th>
                                    <th width="16%">Mode Payement</th>
                                </tr>
                            </thead>
                            <tbody>';
                                foreach($comptant as $prescription){
                                    $output .='
                                        <tr style="border-collapse: collapse; border: 1px solid">
                                            <td  width="16%" style="border: 1px solid;">'.$prescription->ven_date.'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.$prescription->clt_nom.'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($prescription->ven_mont).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($prescription->ven_ttc).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($prescription->ven_mp).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.$prescription->ven_mode.'</td>
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
                                <tr style="border-radius: 10px; background-color: #E5CC75";>
                                    <th width="16%">Date</th>
                                    <th width="16%">Client</th>
                                    <th width="16%">Montant HT</th>
                                    <th width="16%">Montant TTC</th>
                                    <th width="16%">Montant Paye</th>
                                    <th width="16%">Reference</th>
                                </tr>
                            </thead>
                            <tbody>';
                                foreach($virement as $prescription){
                                    $output .='
                                        <tr style="border-collapse: collapse; border: 1px solid">
                                            <td  width="16%" style="border: 1px solid;">'.$prescription->ven_date.'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.$prescription->clt_nom.'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($prescription->ven_mont).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($prescription->ven_ttc).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.($prescription->ven_mp).'</td>
                                            <td  width="16%" style="border: 1px solid; text-align: right">'.$prescription->ven_rmp.'</td>
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
        $prescription = DB::table('prescriptions')
            ->join('users','users.id','=','prescriptions.user_id')
            ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
            ->where('prescriptions.ven_num','=', $id)
            ->get();

        $prescription = (object) $prescription[0];
        $date = new \DateTime($prescription->ven_date);
        $ven_date = $date->format('d-m-Y');
        $ven_heure = $date->format('h:m:s');

        $produits = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->where('concerner_prescriptions.ven_num','=',$prescription->ven_num)
            ->get();
        $centre  = Centre::find('1');
        //$obj = (object) $prescription[0];
        $output ='
                <table width="100%" border="0">
                    <tr>
                        <td width="33%">
                            <table>
                                <tr>
                                    <td width="15%">
                                        <img src="../public/images/logo.png" width="80" height="80">
                                    </td>
                                    <td width="85%">
                                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                                        <div style="font-size: 5px;">'.$centre->service.'</div>
                                        <div style="font-size: 6;">'.$centre->adresse.'</div>
                                        <div style="font-size: 7;">'.$centre->telephone.'</div>
                                    </td>
                                </tr>
                            </table>
                            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                                <tr>
                                    <td width="21%" style="font-size: 10px;">RECU N°</td>
                                    <td width="43%" style="font-size: 10px;"><b>' .$prescription->ven_num.'</b></td>
                                    <td width="10%" style="font-size: 10px;"><b>Date</b></td>
                                    <td width="26%" style="font-size: 10px;">'.$ven_date.'</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px">Patient </td>
                                    <td colspan="3" style="font-size: 10px"><b>'.$prescription->pat_num.'</b> / Mutuelle :<b>'.$prescription->mut_lib.'</td>
                                </tr>
                            </table>

                            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                                <thead>
                                    <tr style="border-radius: 10px; background-color: #E5CC75";>
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
                                        <td  width="51%" style="font-size:10px; border: 1px solid;">'.$produit->pdt_lib.'</td>
                                        <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->pu).'</td>
                                        <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->qte).'</td>
                                        <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->mont).'</td>
                                    </tr>';
        }
        $output .='</tbody>
                            </table>
                            <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
                                <tr>
                                    <td colspan="4" style="font-size:10px">MONTANT : <b>'.($prescription->ven_mont).' - - - Prise en charge :'.($prescription->ven_pec).' </td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="font-size:10px">NET PAYER : <b>'.($prescription->ven_net).'</b></td>
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
                                        <img src="../public/images/logo.png" width="80" height="80">
                                    </td>
                                    <td width="85%">
                                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                                        <div style="font-size: 5px;">'.$centre->service.'</div>
                                        <div style="font-size: 6;">'.$centre->adresse.'</div>
                                        <div style="font-size: 7;">'.$centre->telephone.'</div>
                                    </td>
                                </tr>
                            </table>
                            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                                <tr>
                                    <td width="21%" style="font-size: 10px;">RECU N° </td>
                                    <td width="43%" style="font-size: 10px;"><b>' .$prescription->ven_num.'</b></td>
                                    <td width="10%" style="font-size: 10px;"><b>Date</b></td>
                                    <td width="26%" style="font-size: 10px;">'.$ven_date.'</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px">Patient </td>
                                    <td colspan="3" style="font-size: 10px"><b>'.$prescription->pat_num.'</b> / Mutuelle :<b>'.$prescription->mut_lib.'</td>
                                </tr>
                            </table>

                            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                                <thead>
                                    <tr style="border-radius: 10px; background-color: #E5CC75";>
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
                                        <td  width="51%" style="font-size:10px; border: 1px solid;">'.$produit->pdt_lib.'</td>
                                        <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->pu).'</td>
                                        <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->qte).'</td>
                                        <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->net).'</td>
                                    </tr>';
        }
        $output .='</tbody>
                            </table>
                            <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
                                <tr>
                                    <td colspan="4" style="font-size:10px">MONTANT : <b>'.($prescription->ven_mont).' - - - Prise en charge :'.($prescription->ven_pec).' </td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="font-size:10px">NET PAYER : <b>'.($prescription->ven_net).'</b></td>
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
                                        <img src="../public/images/logo.png" width="80" height="80">
                                    </td>
                                    <td width="85%">
                                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                                        <div style="font-size: 5px;">'.$centre->service.'</div>
                                        <div style="font-size: 6;">'.$centre->adresse.'</div>
                                        <div style="font-size: 7;">'.$centre->telephone.'</div>
                                    </td>
                                </tr>
                            </table>
                            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                                <tr>
                                    <td width="21%" style="font-size: 10px;">RECU N° </td>
                                    <td width="43%" style="font-size: 10px;"><b>' .$prescription->ven_num.'</b></td>
                                    <td width="10%" style="font-size: 10px;"><b>Date</b></td>
                                    <td width="26%" style="font-size: 10px;">'.$ven_date.'</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px">Patient </td>
                                    <td colspan="3" style="font-size: 10px"><b>'.$prescription->pat_num.'</b> / Mutuelle :<b>'.$prescription->mut_lib.'</td>
                                </tr>
                            </table>

                            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                                <thead>
                                    <tr style="border-radius: 10px; background-color: #E5CC75";>
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
                                        <td  width="51%" style="font-size:10px; border: 1px solid;">'.$produit->pdt_lib.'</td>
                                        <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->pu).'</td>
                                        <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->qte).'</td>
                                        <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">'.($produit->net).'</td>
                                    </tr>';
        }
        $output .='</tbody>
                            </table>
                            <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
                                <tr>
                                    <td colspan="4" style="font-size:10px">MONTANT : <b>'.($prescription->ven_mont).' - - -Prise en charge :'.($prescription->ven_pec).' </td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="font-size:10px">NET PAYER : <b>'.($prescription->ven_net).'</b></td>
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
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($output);
        return $pdf->stream();
    }

    public function print_ef($debut,$fin){
        $vmont = 0;
        $vpec = 0;
        $vnet = 0;

        $encaisses = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('prescriptions.user_id','=',Auth::user()->id)
            ->sum('ven_paye');

        $categories = DB::table('concerner_prescriptions')
            ->join('categories','categories.cat_num','=','concerner_prescriptions.cat_num')
            ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->where('prescriptions.user_id','=',Auth::user()->id)
            ->select('categories.cat_num','categories.cat_lib','categories.cat_type')->distinct()
            ->orderby('categories.cat_lib')
            ->get();

        $recap_mut = DB::table('prescriptions')
            ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
            ->selectRaw('mutuelles.mut_lib,sum(prescriptions.ven_pec) as ven_pec')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->where('prescriptions.user_id','=',Auth::user()->id)
            ->groupBy('mutuelles.mut_lib')
            ->get();

        $reglements = DB::table('reglements')
            ->join('prescriptions','prescriptions.ven_num','=','reglements.ven_num')
            ->where('reglements.reg_source', '=','REGLEMENT')
            ->whereBetween('reglements.reg_date', array($debut, $fin))
            ->where('reglements.user_id','=',Auth::user()->id)
            ->get();

        $total = DB::table('reglements')
            ->whereBetween('reg_date', array($debut, $fin))
            ->where('user_id','=',Auth::user()->id)
            ->where('reg_source', '=','REGLEMENT')
            ->sum('reg_mont');

        $centre  = Centre::find('1');
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                        <div style="font-size:10px;">'.$centre->service.'</div>
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
                    $prescriptions = DB::table('concerner_prescriptions')
                        ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                        ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
                        ->selectRaw('produits.cat_num,produits.pdt_num,produits.pdt_lib,concerner_prescriptions.pu,sum(concerner_prescriptions.qte) as qte, sum(concerner_prescriptions.mont) as mont, sum(concerner_prescriptions.pec) as pec, sum(concerner_prescriptions.net) as net')
                        ->whereBetween('prescriptions.ven_date', array($debut, $fin))
                        ->where('prescriptions.user_id','=',Auth::user()->id)
                        ->where('produits.cat_num','=',$categorie->cat_num)
                        ->groupBy('produits.cat_num','produits.pdt_num','produits.pdt_lib','concerner_prescriptions.pu')
                        ->orderby('produits.pdt_lib')
                        ->get();

                    $lesprescriptions = DB::table('prescriptions')
                        ->whereBetween('ven_date', array($debut, $fin))
                        ->where('prescriptions.user_id','=',Auth::user()->id)
                        ->get();
                    $ven_nums = array();
                    foreach ($lesprescriptions as $prescription){
                        array_push($ven_nums, $prescription->ven_num);
                    }

                    $total_cat = 0;
                    $mont = 0;
                    $pec = 0;
                    $net = 0;
                    $output .='
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">

                            <tr style="border-radius: 15px; background-color: #E5CC75";>
                                <th style="font-size: 15px;" width="50%">'.$categorie->cat_lib.'</th>
                            </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #A2ACC4";>
                                <th style="font-size: 15px;" width="30%">Produit</th>
                                <th style="font-size: 15px;" width="12%">P U</th>
                                <th style="font-size: 15px;" width="10%">Qte</th>
                                <th style="font-size: 15px;" width="12%">Montant</th>
                                <th style="font-size: 15px;" width="12%">Prise en charge</th>
                                <th style="font-size: 15px;" width="13%">Net Payer</th>
                                <th style="font-size: 11px;" width="15%">Stock final</th>
                            </tr>
                        </thead>
                        <tbody>';

                        foreach($prescriptions as $produit){
                            $qte_las=0;
                            $mont += $produit->mont;
                            $pec += $produit->pec;
                            $net += $produit->net;
                            $output .='
                               <tr style="border-collapse: collapse; border: 1px solid">
                                   <td  width="30%" style="font-size:15px; border: 1px solid;">'.$produit->pdt_lib.'</td>
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
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                       <tr>
                          <tr style="border-radius: 5px; background-color: #E5CC75";>
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

        $encaisses = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('prescriptions.userid','=',Auth::user()->id)
            ->sum('ven_paye');

        $categories = DB::table('concerner_prescriptions')
            ->join('categories','categories.cat_num','=','concerner_prescriptions.cat_num')
            ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->where('prescriptions.userid','=',Auth::user()->id)
            ->select('categories.cat_num','categories.cat_lib','categories.cat_type')->distinct()
            ->orderby('categories.cat_lib')
            ->get();

        $recap_mut = DB::table('prescriptions')
            ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
            ->selectRaw('mutuelles.mut_lib,sum(prescriptions.ven_pec) as ven_pec')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->where('prescriptions.userid','=',Auth::user()->id)
            ->groupBy('mutuelles.mut_lib')
            ->get();


        $centre  = Centre::find('1');
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                        <div style="font-size:10px;">'.$centre->service.'</div>
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
            $prescriptions = DB::table('concerner_prescriptions')
                ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
                ->selectRaw('produits.cat_num,produits.pdt_num,produits.pdt_lib,concerner_prescriptions.pu,sum(concerner_prescriptions.qte) as qte, sum(concerner_prescriptions.mont) as mont, sum(concerner_prescriptions.pec) as pec, sum(concerner_prescriptions.net) as net')
                ->whereBetween('prescriptions.ven_date', array($debut, $fin))
                ->where('prescriptions.userid','=',Auth::user()->id)
                ->where('produits.cat_num','=',$categorie->cat_num)
                ->groupBy('produits.cat_num','produits.pdt_num','produits.pdt_lib','concerner_prescriptions.pu')
                ->orderby('produits.pdt_lib')
                ->get();

            $lesprescriptions = DB::table('prescriptions')
                ->whereBetween('ven_date', array($debut, $fin))
                ->where('prescriptions.userid','=',Auth::user()->id)
                ->get();
            $ven_nums = array();
            foreach ($lesprescriptions as $prescription){
                array_push($ven_nums, $prescription->ven_num);
            }

            $total_cat = 0;
            $mont = 0;
            $pec = 0;
            $net = 0;
            $output .='
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">

                            <tr style="border-radius: 15px; background-color: #E5CC75";>
                                <th style="font-size: 15px;" width="50%">'.$categorie->cat_lib.'</th>
                            </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #A2ACC4";>
                                <th style="font-size: 15px;" width="30%">Produit</th>
                                <th style="font-size: 15px;" width="12%">P U</th>
                                <th style="font-size: 15px;" width="10%">Qte</th>
                                <th style="font-size: 15px;" width="12%">Montant</th>
                                <th style="font-size: 15px;" width="12%">Prise en charge</th>
                                <th style="font-size: 15px;" width="13%">Net Payer</th>
                                <th style="font-size: 11px;" width="15%">Stock final</th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach($prescriptions as $produit){
                $qte_las=0;
                $mont += $produit->mont;
                $pec += $produit->pec;
                $net += $produit->net;
                $output .='
                               <tr style="border-collapse: collapse; border: 1px solid">
                                   <td  width="30%" style="font-size:15px; border: 1px solid;">'.$produit->pdt_lib.'</td>
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
                <tr style="border-radius: 15px; background-color: #A2ACC4";>
                        <th style="font-size: 15px;" width="55%">ASSURANCE / MUTUELLE</th>
                        <th style="font-size: 15px;" width="45%">MONTANT</th>
                    </tr>
                <tbody>';

        foreach($recap_mut as $mutuelle){
            $output .='
                       <tr style="border-collapse: collapse; border: 1px solid">
                           <td  width="55%" style="font-size:15px; border: 1px solid;">'.$mutuelle->mut_lib.'</td>
                           <td  width="45%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($mutuelle->ven_pec,'0','.',' ').'</td>

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
            Usercon::create(['id'=>$id]);
            return \response()->json(['success'=>$user_select->name.' ajoutee']);
        }
    }

    public function etatphar(Request $request){
        $users = DB::table('users')
            ->where('etat','=','OK')
            ->where('ut','=',3)
            ->get();

        $usercon = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->where('users.etat','=','OK')
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
            //$lien ="<a href="{{route('appromag.delete',[$produit->am_num,$produit->pdt_num])}}" class="btn btn-danger"><i class="fa fa-trash"></i></a>";
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
        Usercon::find($id)->delete();
        return response()->json($user->name.' a ete retire');
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

        $lesprescriptions = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->get();
        $ven_nums = array();
        foreach ($lesprescriptions as $prescription){
            array_push($ven_nums, $prescription->ven_num);
        }

        $vmomt = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->whereIn('prescriptions.user_id',$uc)
            ->where('produits.cat_num','=',1)
            ->sum('concerner_prescriptions.mont');

        $vpec = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->whereIn('prescriptions.user_id',$uc)
            ->where('produits.cat_num','=',1)
            ->sum('concerner_prescriptions.pec');
        $vnet = DB::table('concerner_prescriptions')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->whereIn('prescriptions.user_id',$uc)
            ->where('produits.cat_num','=',1)
            ->sum('concerner_prescriptions.net');


        $centre  = Centre::find('1');
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="../public/images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                        <div style="font-size:10px;">'.$centre->service.'</div>
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

            $prescriptions = DB::table('concerner_prescriptions')
                ->join('produits', 'produits.pdt_num', '=', 'concerner_prescriptions.pdt_num')
                ->join('prescriptions', 'prescriptions.ven_num', '=', 'concerner_prescriptions.ven_num')
                ->selectRaw('produits.pdt_num,produits.pdt_lib,concerner_prescriptions.pu,sum(concerner_prescriptions.qte) as qte, sum(concerner_prescriptions.mont) as mont, sum(concerner_prescriptions.pec) as pec, sum(concerner_prescriptions.net) as net')
                ->whereBetween('prescriptions.ven_date', array($debut, $fin))
                ->whereIn('prescriptions.user_id',$uc)
                ->where('produits.cat_num', '=', 1)
                ->groupBy('produits.pdt_num','produits.pdt_lib', 'concerner_prescriptions.pu')
                ->orderby('produits.pdt_lib')
                ->get();


            $output .= '
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <tr style="border-radius: 15px; background-color: #E5CC75";>
                        <th style="font-size: 15px;" width="50%">PRODUITS PHARMACEUTIQUES</th>
                    </tr>
                </table>
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                   <thead>
                       <tr style="border-radius: 15px; background-color: #A2ACC4";>
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

            foreach ($prescriptions as $produit) {
                $pdt = DB::table('mouvements')
                    ->whereIn('ad_num', $ven_nums)
                    ->where('pdt_num','=',$produit->pdt_num)
                    ->orderby('mv_date')
                    ->get();
                $las = count($pdt)-1;
                $qte_las = $pdt[$las]->mv_act;

                $output .= '
                      <tr style="border-collapse: collapse; border: 1px solid">
                          <td  width="30%" style="font-size:15px; border: 1px solid;">' . $produit->pdt_lib . '</td>
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

        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        //return $pdf->stream();
        DB::table('usercons')->delete();
        return response()->json(['data' => $output]);

    }

    public function etatcaisse(){
        $users = DB::table('users')
            ->where('etat','=','OK')
            ->whereIn('ut',[1,4])
            ->get();

        $usercon = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->where('users.etat','=','OK')
            ->get();
        if (Auth::user()->ut==1){
            return view('etat.etatcaissea', compact('users','usercon'));
        }elseif(Auth::user()->ut==2){
            return view('etat.etatcaissecompta', compact('users','usercon'));
        }elseif (Auth::user()->ut==3){
            return view('etat.etatcaissemag', compact('users','usercon'));
        }elseif (Auth::user()->ut==5){
            return view('etat.etatcaisse', compact('users','usercon'));
        }else{
            //Rien
        }
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

        $vmomt = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('ven_mont');

        $vpec = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('ven_pec');
        $vnet = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('ven_net');

        $encaisses = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('ven_paye');

        $reglements = DB::table('reglements')
            ->join('prescriptions','prescriptions.ven_num','=','reglements.ven_num')
            ->where('reglements.reg_source', '=','REGLEMENT')
            ->whereBetween('reglements.reg_date', array($debut, $fin))
            ->whereIn('reglements.user_id',$uc)
            ->get();

        $total = DB::table('reglements')
            ->whereBetween('reg_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->where('reg_source', '=','REGLEMENT')
            ->sum('reg_mont');

        $catcon = DB::table('produits')
            ->join('categories','categories.cat_num','=','produits.cat_num')
            ->join('concerner_prescriptions','concerner_prescriptions.pdt_num','=','produits.pdt_num')
            ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
            ->whereIn('prescriptions.user_id',$uc)
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->select('produits.cat_num','categories.cat_lib')->distinct()->get();

        $recap_mut = DB::table('prescriptions')
            ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
            ->selectRaw('mutuelles.mut_lib,sum(prescriptions.ven_pec) as ven_pec')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->whereIn('prescriptions.user_id',$uc)
            ->groupBy('mutuelles.mut_lib')
            ->get();

        $concerner_prescriptions = DB::table('concerner_prescriptions')
            ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->whereIn('prescriptions.user_id',$uc)
            ->get();
        $marge = 0;
        $magasin = DB::table('magasins')
            ->Where('mag_etat','=','OK')
            ->where('mag_type','=','Depot_vente')
            ->get();
        $depot = (object) $magasin[0];
        foreach ($concerner_prescriptions as $con_ven){
            $qp = DB::table('quantite_produits')
                ->where('etat','=','Encours')
                ->where('mag_num','=',$depot->mag_num)
                ->where('pdt_num','=',$con_ven->pdt_num)
                ->get();
            if (count($qp)!=0){
                $pdtcon = (object) $qp[0];
                $marge+=$pdtcon->marge*$con_ven->qte;
            }

        }

        $centre  = Centre::find('1');
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                        <div style="font-size:10px;">'.$centre->service.'</div>
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
            $prescriptions = DB::table('concerner_prescriptions')
                ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
                ->selectRaw('produits.pdt_lib,concerner_prescriptions.pu,sum(concerner_prescriptions.qte) as qte, sum(concerner_prescriptions.mont) as mont, sum(concerner_prescriptions.pec) as pec, sum(concerner_prescriptions.net) as net')
                ->whereBetween('prescriptions.ven_date', array($debut, $fin))
                ->whereIn('prescriptions.user_id',$uc)
                ->where('produits.cat_num','=',$categorie->cat_num)
                ->groupBy('produits.pdt_lib','concerner_prescriptions.pu')
                ->get();

            $total_cat = DB::table('concerner_prescriptions')
                ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
                ->selectRaw('produits.cat_num, sum(concerner_prescriptions.mont) as mont, sum(concerner_prescriptions.pec) as pec, sum(concerner_prescriptions.net) as net')
                ->whereBetween('prescriptions.ven_date', array($debut, $fin))
                ->whereIn('prescriptions.user_id',$uc)
                ->where('produits.cat_num','=',$categorie->cat_num)
                ->groupBy('produits.cat_num')
                ->get();

            $mont = DB::table('concerner_prescriptions')
                ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
                ->whereBetween('prescriptions.ven_date', array($debut, $fin))
                ->whereIn('prescriptions.user_id',$uc)
                ->where('produits.cat_num','=',$categorie->cat_num)
                ->sum('concerner_prescriptions.mont');

            $pec = DB::table('concerner_prescriptions')
                ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
                ->whereBetween('prescriptions.ven_date', array($debut, $fin))
                ->whereIn('prescriptions.user_id',$uc)
                ->where('produits.cat_num','=',$categorie->cat_num)
                ->sum('concerner_prescriptions.pec');

            $net = DB::table('concerner_prescriptions')
                ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
                ->whereBetween('prescriptions.ven_date', array($debut, $fin))
                ->whereIn('prescriptions.user_id',$uc)
                ->where('produits.cat_num','=',$categorie->cat_num)
                ->sum('concerner_prescriptions.net');


            $output .='
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">

                            <tr style="border-radius: 15px; background-color: #E5CC75";>
                                <th style="font-size: 15px;" width="50%">'.$categorie->cat_lib.'</th>
                            </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #A2ACC4";>
                                <th style="font-size: 15px;" width="35%">Produit</th>
                                <th style="font-size: 15px;" width="12%">P U</th>
                                <th style="font-size: 15px;" width="10%">Qte</th>
                                <th style="font-size: 15px;" width="14%">Montant</th>
                                <th style="font-size: 15px;" width="14%">Prise en charge</th>
                                <th style="font-size: 15px;" width="15%">Net Payer</th>
                            </tr>
                        </thead>
                        <tbody>';

                    foreach($prescriptions as $produit){
                        $output .='
                           <tr style="border-collapse: collapse; border: 1px solid">
                               <td  width="35%" style="font-size:15px; border: 1px solid;">'.$produit->pdt_lib.'</td>
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
                <tr style="border-radius: 15px; background-color: #A2ACC4";>
                        <th style="font-size: 15px;" width="55%">ASSURANCE / MUTUELLE</th>
                        <th style="font-size: 15px;" width="45%">MONTANT</th>
                    </tr>
                <tbody>';

            foreach($recap_mut as $mutuelle){
                $output .='
                   <tr style="border-collapse: collapse; border: 1px solid">
                       <td  width="55%" style="font-size:15px; border: 1px solid;">'.$mutuelle->mut_lib.'</td>
                       <td  width="45%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($mutuelle->ven_pec,'0','.',' ').'</td>
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
                ->where('ven_num','=',$reglement->ven_num)
                ->sum('reg_mont');
            $reste = $reglement->ven_net-$total_reg;
            $output.='<tr style="border-radius: 12px";>
              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->reg_date.'</td>
              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->pat_num.'</td>
              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->ven_num.'</td>
              <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->ven_date.'</td>
              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reglement->ven_net,'0','.',' ').'</td>
              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reglement->reg_mont,'0','.',' ').'</td>
              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($total_reg,'0','.',' ').'</td>
              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reste,'0','.',' ').'</td>
            </tr>';
        }
        $output.='
            <tr style="border-radius: 5px; background-color: #E5CC75";>
              <td colspan="3" style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL REGLE</td>
              <td colspan="5" style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total,'0','.',' ').'</td>
            </tr>
            </body>
        </table>
        <br><br>
        <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
           <tr>
              <tr style="border-radius: 5px; background-color: #E5CC75";>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL ENCAISSE</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total+$encaisses,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">BENEFICE SUR LES prescriptions</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($marge,'0','.',' ').' Franc CFA</td>
            </tr>
        </table> ';
        DB::table('usercons')->delete();
        return response()->json(['data' => $output]);

    }

    public function user_selected()
    {
        $usercon = DB::table('usercons')
            ->join('users','users.id','=','usercons.id')
            ->where('users.etat','=','OK')
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
            ->get();
        $uc = array();
        $utilisateurs = '';
        foreach ($users as $user){
            array_push($uc, $user->id);
            $utilisateurs .= $user->name.'-';
        }

        $utilisateurs= rtrim($utilisateurs,'-');

        $vmomt = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('ven_mont');

        $vpec = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('ven_pec');

        $vnet = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            ->sum('ven_net');

        $total = DB::table('reglements')
            ->whereBetween('reg_date', array($debut, $fin))
            ->whereIn('user_id',$uc)
            //->where('reg_source', '=','REGLEMENT')
            ->sum('reg_mont');

        $concerner_prescriptions = DB::table('concerner_prescriptions')
            ->join('prescriptions','prescriptions.ven_num','=','concerner_prescriptions.ven_num')
            ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
            //->selectRaw('produits.pdt_lib')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->whereIn('prescriptions.user_id',$uc)
            ->orderBy('prescriptions.ven_date')
            ->orderBy('produits.pdt_lib')
            ->orderBy('prescriptions.user_id')
            ->get();
        $prescriptions = DB::table('prescriptions')
            ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
            ->whereBetween('prescriptions.ven_date', array($debut, $fin))
            ->whereIn('prescriptions.user_id',$uc)
            ->orderBy('prescriptions.ven_num')
            ->get();

        $centre  = Centre::find('1');
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                        <div style="font-size:10px;">'.$centre->service.'</div>
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
                    <tr style="border-radius: 15px; background-color: #A2ACC4";>
                        <th style="font-size: 15px;" width="10%">Date</th>
                        <th style="font-size: 15px;" width="10%">Vente N°</th>
                        <th style="font-size: 15px;" width="25%">Produit</th>
                        <th style="font-size: 15px;" width="10%">Qte</th>
                        <th style="font-size: 15px;" width="10%">Montant</th>
                        <th style="font-size: 15px;" width="10%">PEC</th>
                        <th style="font-size: 15px;" width="10%">Net Payer</th>
                        <th style="font-size: 15px;" width="15%">User</th>
                    </tr>
                </thead>
                <tbody>';

            foreach($concerner_prescriptions as $prescription){
                $produits = DB::table('concerner_prescriptions')
                    ->join('produits','produits.pdt_num','=','concerner_prescriptions.pdt_num')
                    ->where('concerner_prescriptions.ven_num','=',$prescription->ven_num)
                    ->orderBy('produits.pdt_lib')
                    ->get();
                $output .='
                <table style="width: 3%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <tr style="border-radius: 15px; background-color: #A2ACC4";>
                        <th style="font-size: 20px;font-weight: bold" width="10%">Vente N° : '.$prescription->ven_num.'</th>
                        <th style="font-size: 15px;" width="10%">Date : '.$prescription->ven_date.'</th>
                        <th style="font-size: 15px;" width="10%">Patient : '.$prescription->ven_num.'</th>
                        <th style="font-size: 15px;" width="10%">Montant : '.$prescription->ven_mont.'</th>
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
                            <th style="font-size: 15px;font-weight: bold" width="10%">'.$produit->pdt_lib.'</th>
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
              <tr style="border-radius: 5px; background-color: #E5CC75";>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">VENTE : '.number_format($vmomt,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">ASSURANCE : '.number_format($vpec,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">NET : '.number_format($vnet,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">ENCAISSE : '.number_format($total,'0','.',' ').' Franc CFA</td>
                  <td style="font-weight: bold; color: #0a3650; text-align: center">CREDIT : '.number_format($total-$vnet,'0','.',' ').' Franc CFA</td>
            </tr>
        </table> </div>';
        DB::table('usercons')->delete();
        return response()->json(['data' => $output]);

    }

    public function etatmutuelle(Request $request){
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('prescriptions')
                ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
                ->selectRaw('mutuelles.mut_num,mutuelles.mut_lib,sum(prescriptions.ven_pec) as ven_pec')
                ->whereBetween('prescriptions.ven_date', array($request->from_date, $request->to_date))
                ->groupBy('mutuelles.mut_num','mutuelles.mut_lib')
                ->get();
        }
        else
        {
            $historiques = DB::table('prescriptions')
                ->join('mutuelles','mutuelles.mut_num','=','prescriptions.mut_num')
                ->selectRaw('mutuelles.mut_num,mutuelles.mut_lib,sum(prescriptions.ven_pec) as ven_pec')
                ->whereBetween('prescriptions.ven_date', array(date('Y-m-d'), date('Y-m-d')))
                ->groupBy('mutuelles.mut_num','mutuelles.mut_lib')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        if (Auth::user()->ut==1){
            return view('etat.etatmutuellea', compact('historiques'));
        }else{
            return view('etat.etatmutuellec', compact('historiques'));
        }


    }

    protected function printetatmut($debut,$fin,$mut){
        $mutuelle = Mutuelle::find($mut);

        $prescriptions = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('mut_num','=',$mut)
            ->get();

        $vmomt = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('mut_num','=',$mut)
            ->sum('ven_mont');

        $vpec = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('mut_num','=',$mut)
            ->sum('ven_pec');
        $vnet = DB::table('prescriptions')
            ->whereBetween('ven_date', array($debut, $fin))
            ->where('mut_num','=',$mut)
            ->sum('ven_net');

        $centre  = Centre::find('1');
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="../public/images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                        <div style="font-size:10px;">'.$centre->service.'</div>
                        <div style="font-size:15px;">'.$centre->adresse.'</div>
                        <div style="font-size:15px;">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">DETAILS DES prescriptions DE LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
                </tr>
                <tr>
                    <td width="100%" style="font-size: 15px;">DE LA MUTUELLE : '.$mutuelle->mut_lib.' </td>
                </tr>
            </table>

            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
               <thead>
                   <tr style="border-radius: 15px; background-color: #A2ACC4";>
                       <th style="font-size: 15px;" width="14%">Date</th>
                       <th style="font-size: 15px;" width="16%">Vente num</th>
                       <th style="font-size: 15px;" width="12%">Montant</th>
                       <th style="font-size: 15px;" width="12%">Prise en charge</th>
                       <th style="font-size: 15px;" width="12%">Net Payer</th>
                       <th style="font-size: 15px;" width="34%">Patient</th>
                   </tr>
               </thead>
               <tbody>';
                foreach($prescriptions as $prescription){
                    $output .='
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td  width="14%" style="font-size:15px; border: 1px solid;">'.$prescription->ven_date.'</td>
                        <td  width="16%" style="font-size:15px; border: 1px solid;">'.$prescription->ven_num.'</td>
                        <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.($prescription->ven_mont).'</td>
                        <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.($prescription->ven_pec).'</td>
                        <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.($prescription->ven_net).'</td>
                        <td  width="34%" style="font-size:15px; border: 1px solid; text-align: left">'.$prescription->pat_num.'</td>
                    </tr>';
                }
            $output .='
                </body>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="33%" style="font-size: 17px;">Vente Totale : <b>'.($vmomt).'</b> </td>
                    <td width="33%" style="font-size: 17px;">Prise en  charge : <b>'.($vpec).'</b> </td>
                    <td width="33%" style="font-size: 17px;">Net payer : <b>'.($vnet).'</b> </td>
                </tr>
            </table>
            ';

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($output);
        return $pdf->stream();
        //return response()->json(['data' => $output]);

    }

    public function credit(){
        $credits = DB::table('prescriptions')
            ->where('ven_etat','=','Credit')
            ->get();
        $les_credits = [];

        foreach ($credits as $credit){
            $total_reg = DB::table('reglements')
                ->where('ven_num','=',$credit->ven_num)
                ->sum('reg_mont');
            $reste = $credit->ven_net-$total_reg;
            $element  = new \stdClass();
            $element->ven_date = $credit->ven_date;
            $element->ven_num = $credit->ven_num;
            $element->ven_net = $credit->ven_net;
            $element->total_reg = $total_reg;
            $element->reste = $reste;
            $element->pat_num = $credit->pat_num;

            array_push($les_credits,$element);
        }

        if (Auth::user()->ut==1){
            return view ('asc.credit', compact('les_credits'));
        }elseif (Auth::user()->ut==2){
            return view ('asc.creditcompta', compact('les_credits',));
        }elseif (Auth::user()->ut==4){
            return view ('asc.creditcaisse', compact('les_credits',));
        }else{
            //
        }
    }

    public function credit_liste(){
        $credits = DB::table('prescriptions')
            ->where('ven_etat','=','Credit')
            ->get();
        $centre  = Centre::find('1');
        $total=0;
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$centre->nom.'</div>
                        <div style="font-size:10px;">'.$centre->service.'</div>
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
                        ->where('ven_num','=',$credit->ven_num)
                        ->sum('reg_mont');
                    $reste = $credit->ven_net-$total_reg;

                    $output.='<tr style="border-radius: 12px";>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$credit->pat_num.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$credit->ven_date.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: left">'.$credit->ven_num.'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($credit->ven_net,'0','.',' ').'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($total_reg,'0','.',' ').'</td>
                              <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reste,'0','.',' ').'</td>
                            </tr>';
                    $total+=$reste;
                    }
                    $output.='
                        <tr style="border-radius: 5px; background-color: #E5CC75";>
                          <td colspan="3" style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL DES CREDITS</td>
                          <td colspan="3" style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total,'0','.',' ').' Fr CFA</td>
                        </tr>
                        </body>
                    </table>
                    ';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($output);
        return $pdf->stream();
    }
}
