<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Commande;
use App\Models\ProduitCommande;
use App\Models\ProduitReceptionDps;
use App\Models\ReceptionDps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class ReceptionDpsController extends Controller
{
    protected function code(){
        $debut = date('Y').'-'.date('m').'-01';
        $fin = date('Y-m-d');
        $achatp = DB::table('reception_dps')
            ->whereBetween('date_reception', array($debut, $fin))
            ->where('dps_id', '=', Auth::user()->dps_id)
            ->get();
        $nb_cmde = $achatp->count()+1;
        $code = '00'.$nb_cmde.'REC'.date('m').date('Y').Auth::user()->id;
        return $code;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$this->authorize('manage-action',['reception','lister']);
        $reception = new ReceptionDps();
        $code = $this->code();
        $commandes = [];
        Session::forget('code');
        return view('receptiondps.index', compact('reception','code','commandes'));
    }

    public function commandes(){
        //if (\request()->ajax()){
            $commandes = DB::table('commandes')
                ->join('centres','centres.centre_id','=','commandes.centre_id')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->where('commandes.dps_id', '=',Auth::user()->dps_id)
                ->where('commandes.etat', ['Validee','PartielleSI'])
                ->get();
            return $commandes;
        //}
    }

    public function getcommande($cmde_id){
        $pdtcon = DB::table('reception_dps')
            ->where('commande_id', '=', $cmde_id)
            ->get();
        $reception = (object) $pdtcon[0];
        return response()->json($reception);
    }


    public function rech_montant($id){
        if (\request()->ajax()){
            $rec_momt = DB::table('produit_reception_dps')
                ->where('reception_dps_id','=',$id)
                ->sum('montant');
            return $rec_momt;
        }
    }

    public function pdt_cmde($commande_id)
    {
        $pdtcon = DB::table('produit_commandes')
            ->where('produit_commandes.commande_id','=',$commande_id)
            ->get();
        $output='<table class="table table-striped table-bordered contour_table" id="pdt_cmde">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">Produit</td>
               <td class="price">Qte Cmde</td>
               <td class="price">Qte livree</td>
           </tr>
           </thead>
           <tbody>';
        foreach($pdtcon as $produit){
            $output .='<tr>
                 <td><a href="#" id="'.$produit->produit_commande_id.'" class="select">'.$produit->libelle.'</a></td>
                 <td><a href="#" id="'.$produit->produit_commande_id.'">'.$produit->qte.'</a></td>
                 <td><a href="#" id="'.$produit->produit_commande_id.'">'.$produit->qte_liv.'</a></td>
             </tr>';
        }
        $output.='</body>
                    </table><br><br>';
        return $output;
    }

    public function pdt_rec($code,$commande_id)
    {
        DB::table('produit_reception_dps')
            ->where('produit_reception_dps.code','=',$code)
            ->delete();
        $pdt_cmdes = DB::table('produit_commandes')
            ->where('commande_id','=',$commande_id)
            ->get();

        foreach($pdt_cmdes as $produit){
            $qte_liv = DB::table('produit_reception_dps')
                ->where('commande_id','=',$commande_id)
                ->where('produit_id','=',$produit->produit_id)
                ->sum('qte_recue');
            if($produit->qte>$qte_liv){
                ProduitReceptionDps::create([
                    'code' => $this->code(),
                    'produit_id' => $produit->produit_id,
                    'libelle' => $produit->libelle,
                    'qte_commandee' => $produit->qte,
                    'qte_recue' => $produit->qte-$qte_liv,
                    'qte_transferee' => $produit->qte-$qte_liv,
                    'commande_id' => $commande_id
                ]);
            }
        }

        $pdtcon = DB::table('produit_reception_dps')
            ->where('produit_reception_dps.code','=',$code)
            ->get();
        $output='<table class="table table-striped table-bordered contour_table" id="pdt_rec">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">Produit</td>
               <td class="price">Qte Cmde</td>
               <td class="price">Qte recue</td>
               <td colspan="2"></td>
           </tr>
           </thead>
           <tbody>';
            foreach($pdtcon as $produit){
                $button_edit = '<button type="button" name="editer" id="'.$produit->produit_reception_dps_id.'" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';;
                $button_supp = '<button type="button" name="delete" id="'.$produit->produit_reception_dps_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';;

                $output .='<tr>
                 <td class="cart_title">'.$produit->libelle.'</td>
                 <td class="cart_price">'.$produit->qte_commandee.'</td>
                 <td class="cart_price">'.$produit->qte_recue.'</td>
                 <td class="cart_delete">'.$button_edit.'</td>
                 <td class="cart_delete">'.$button_supp.'</td>
             </tr>';
            }
            $output.='</body>
                    </table>';
        return $output;
    }

    public function pdtrec($code,$commande_id)
    {

        $pdtcon = DB::table('produit_reception_dps')
            ->where('produit_reception_dps.code','=',$code)
            ->get();
        $output='<table class="table table-striped table-bordered contour_table" id="pdt_rec">
           <thead>
           <tr class="cart_menu"">
               <td class="description">Produit</td>
               <td class="price">Qte Cmde</td>
               <td class="price">Qte recue</td>
               <td colspan="2"></td>
           </tr>
           </thead>
           <tbody>';
            foreach($pdtcon as $produit){
                $button_edit = '<button type="button" name="editer" id="'.$produit->produit_reception_dps_id.'" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';;
                $button_supp = '<button type="button" name="delete" id="'.$produit->produit_reception_dps_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';;

                $output .='<tr>
                 <td class="cart_title">'.$produit->libelle.'</td>
                 <td class="cart_price">'.$produit->qte_commandee.'</td>
                 <td class="cart_price">'.$produit->qte_recue.'</td>
                 <td class="cart_delete">'.$button_edit.'</td>
                 <td class="cart_delete">'.$button_supp.'</td>
             </tr>';
            }
            $output.='</body>
                    </table>';
        return $output;
    }

    public function select($id,$cmde){
        if(request()->ajax()) {
            $concerne = ProduitCommande::find($id);
            $pdt_cons = DB::table('produit_commandes')
                ->join('produits','produits.produit_id','=','produit_commandes.produit_id')
                ->where('produit_commandes.produit_id','=',$concerne->produit_id)
                ->where('produit_commandes.produit_commande_id','=',$id)
                ->get();

            $pdt_con = (object) $pdt_cons[0];

            return response()->json($pdt_con);
        }
    }

    public function select_edit($id,$cmde){
        if(request()->ajax()){
            $pdt_con = ProduitReceptionDps::find($id);

            $qte_liv = DB::table('produit_reception_dps')
                ->where('commande_id','=',$cmde)
                ->where('produit_id','=',$pdt_con->produit_id)
                ->sum('qte_recue');

            return response()->json(['produit'=>$pdt_con,'qte_liv'=>$qte_liv]);
        }
    }

    public function add(Request $request)
    {
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
            'qte_commandee' => $request->qte_cmde,
            'qte_recue' => $request->qte,
            'qte_transferee' => $request->qte,
            'remarque' => $request->remarque,
            'commande_id' => $request->hidden_commande_id
        );

        $con_ini = ProduitReceptionDps::where('code','=',$request->hidden_code)
            ->where('produit_id','=',$request->produit_id)
            ->get();

        if ($request->produit_reception_dps_id==null){
            if (count($con_ini)==0){
                DB::beginTransaction();
                try {
                    if ($request->qte_cmde>=($request->qte+$request->qte_liv)){
                        ProduitReceptionDps::create($form_data);
                        //DB::connection('vps')->table('produit_reception_dps')->insert($form_data);
                        DB::commit();
                        return response()->json(['success' => 'Produit ajoute']);
                    }else{
                        return response()->json(['error' => 'Quantite saisie depasse la quantite commandee']);
                    }

                }catch (\PDOException $se) {
                    DB::rollBack();
                    return response()->json(['error' => 'Erreur survenu lors de l execution. produit non ajouter '.$se->getMessage()]);
                }
            }else{
                return response()->json(['error' => 'Vous aviez deja selectionner ce produit']);
            }
        }else{
            DB::beginTransaction();
            try {
                if ($request->qte_cmde>=($request->qte+$request->qte_liv)){
                    ProduitReceptionDps::find($request->produit_reception_dps_id)->update($form_data);
                    //DB::connection('vps')->table('produit_reception_dps')->where('produit_reception_dps_id',$request->produit_reception_dps_id)->update($form_data);
                    DB::commit();
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
                ProduitReceptionDps::find($id)->delete();
                DB::commit();
                //DB::connection('vps')->table('produit_reception_dps')->where('produit_reception_dps_id',$id)->delete();
            } catch (\Throwable $th) {
                DB::rollBack();

            }
        }
    }

    public function store(Request $request){
        $rules = array(
            'date_reception'     =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            Alert::error('Erreur','Merci de definir la date');
            return back();
        }

        $qte_cmde = DB::table('produit_commandes')
            ->where('commande_id','=',$request->cmdenum)
            ->sum('qte');
        $qte_liv = DB::table('produit_reception_dps')
            ->where('commande_id','=',$request->cmdenum)
            ->sum('qte_recue');
            if ($qte_liv!=$qte_cmde){
                $etat='PartielleSI';
            }else{
                $etat='Livree';
            }

        $taux_liv = ($qte_liv/$qte_cmde)*100;
        $taux = (int)round($taux_liv);

        $form_data = array(
            'code' =>  $this->code(),
            'date_reception' =>  $request->date_reception,
            'etat_liv' =>  $etat,
            //'taux_liv' =>  $taux,
            'commande_id' =>  $request->cmdenum,
            'user_id'   =>  Auth::user()->id,
            'centre_id'   =>  Auth::user()->centre_id,
            'dps_id'   =>  Auth::user()->dps_id
        );
        //dd($form_data);
        DB::beginTransaction();
        try {

            ReceptionDps::create($form_data);
            //DB::connection('vps')->table('reception_dps')->insert($form_data);
            $reception_dps_id = DB::getPdo()->lastInsertId();
            DB::table('produit_reception_dps')
                ->where('code','=',$request->code)
                ->update(['reception_dps_id'=>$reception_dps_id]);
            // DB::connection('vps')->table('produit_reception_dps')
            //     ->where('code','=',$request->code)
            //     ->update(['reception_dps_id'=>$reception_dps_id]);

            DB::table('commandes')
                ->where('commande_id','=',$request->cmdenum)
                ->update(['etat'=>$etat,'taux'=>$taux]);
            // DB::connection('vps')->table('commandes')
            //     ->where('commande_id','=',$request->cmdenum)
            //     ->update(['etat'=>$etat,'taux'=>$taux]);

            Alert::success('Success !', 'reception enregistre avec success.');
            DB::commit();
            return redirect()->route('recdps.index');
        }catch (\PDOException $se){
            DB::rollBack();
            dd($se);
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.'.$se->getMessage());
            return redirect()->route('recdps.index');
        }
    }

    public function edit($id)
    {
        //Session::put('reception_dps_id',$id);
        //return redirect()->route('rec.editer');
    }

    public function editer(){
        /*$reception_dps_id = Session::get('reception_dps_id');

        if (Session::get('reception_dps_id')){
            $reception = ReceptionDps::find($reception_dps_id);
            $code = $reception->code;
            $commandes = DB::table('commandes')
                ->where('commande_id','=',$reception->commande_id)
                ->get();
            if (Auth::user()->ut==1){
                return view('reception_si.edit', compact('reception','code','magasins','commandes'));
            }elseif (Auth::user()->ut=2){
                return view('reception_si.editc', compact('reception','code','magasins','commandes'));
            }elseif (Auth::user()->ut==3){
                return view('reception_si.editp', compact('reception','code','magasins','commandes'));
            }else{
                //Rien a faire
            }
        }else{
            return redirect()->route('rec.histo');
        }*/

    }

    public function update(Request $request, $id)
    {

    }

    public function histo(Request $request){
        Session::forget('code');
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('reception_dps')
                ->join('centres','centres.centre_id','=','reception_dps.centre_id')
                ->join('commandes','commandes.commande_id','=','reception_dps.commande_id')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->whereBetween('reception_dps.date_reception', array($request->from_date, $request->to_date))
                ->get();
        }
        else
        {
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('reception_dps')
                ->join('centres','centres.centre_id','=','reception_dps.centre_id')
                //->join('users','users.id','=','reception_dps.user_id')
                ->join('commandes','commandes.commande_id','=','reception_dps.commande_id')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->whereBetween('reception_dps.date_reception', array($debut, date('Y-m-d')))
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }

        return view('receptiondps.histo', compact('historiques'));
    }


    protected function show($id){
        $reception = DB::table('reception_dps')
            ->join('users','users.id','=','reception_dps.user_id')
            ->join('commandes','commandes.commande_id','=','reception_dps.commande_id')
            ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
            ->where('reception_dps.reception_dps_id','=', $id)
            ->get();

        if (count($reception)==0){
            Alert::error('Erreur:','Livraison inexistante');
            return back();
        }else{
            $reception = (object) $reception[0];
            $date = new \DateTime($reception->date_reception);
            $date_reception = $date->format('d-m-Y');

            $categories = DB::table('produit_reception_dps')
                ->join('produits','produits.produit_id','=','produit_reception_dps.produit_id')
                ->join('categories','categories.categorie_id','=','produits.categorie_id')
                ->where('produit_reception_dps.reception_dps_id','=',$id)
                ->select('produits.categorie_id','categories.libelle')->distinct()
                ->get();
            $cout_achat=0;
            $cout_achat_total=0;
            $centre  = Centre::find('1');

            $output ='<table>
                        <tr>
                            <td width="15%"></td>
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
                            <td width="50%">Reception N° <b>' .$reception->reception_dps_id.'</b></td>
                            <td width="50%">Date  <b>'.$date_reception.'</b></td>
                        </tr>
                        <tr>
                            <td width="50%">Utilisateur: <b>' .$reception->name.'</b></td>
                            <td width="50%">Commande Num: <b>' .$reception->code.'</b></td>
                        </tr>
                        <tr>
                            <td width="50%">commande N: <b>' .$reception->commande_id.'</b></td>
                            <td width="50%">Fournisseur: <b>' .$reception->nom.'</b></td>
                        </tr>
                    </table>
                    <br>
                    <table style="width: 100%; border: 0px solid;" cellspacing="0" cellpadding="0">';
            foreach($categories as $categorie){
                $produits = DB::table('produit_reception_dps')
                    ->join('produits','produits.produit_id','=','produit_reception_dps.produit_id')
                    ->where('produit_reception_dps.reception_dps_id','=',$id)
                    ->where('produits.categorie_id','=',$categorie->categorie_id)
                    ->get();

                $output .='
                <tr style="border-collapse: collapse; border: 0px solid; text-align: center; size: 20px">
                    <td colspan="2" style="border: 0px solid;">'.$categorie->libelle.'</td>
                </tr>
                <tr style="border-collapse: collapse; border: 0px solid; text-align: center; size: 20px">
                    <td colspan="2" style="border: 0px solid;">
                        <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                            <thead>
                                <tr style="border-radius: 10px; background-color: #F7F4F3";>
                                    <th width="15%">Reference</th>
                                    <th width="45%">Libelle</th>
                                    <th width="10%">Qte Commandee</th>
                                    <th width="10%">Qte recue</th>
                                    <th width="10%">Prix Achat</th>
                                    <th width="10%">Unite</th>
                                </tr>
                            </thead>
                            <tbody>';
                        foreach($produits as $produit){
                            $output .='
                            <tr style="border-collapse: collapse; border: 1px solid;">
                                <td style="border: 1px solid;">'.$produit->reference.'</td>
                                <td style="border: 1px solid;">'.$produit->libelle.'</td>
                                <td style="border: 1px solid; text-align: right">'.number_format($produit->qte_commandee,'0','.',' ').'</td>
                                <td style="border: 1px solid; text-align: right">'.number_format($produit->qte_recue,'0','.',' ').'</td>
                                <td style="border: 1px solid; text-align: right">'.number_format($produit->prix_achat,'0','.',' ').'</td>
                                <td style="border: 1px solid; text-align: right">'.($produit->unite_achat).'</td>
                            </tr>';
                        }
                        $output .='
                    </tbody>
                    </table>
                    </td>
                </tr>';

            }
            $output .='
            <tr style="border-collapse: collapse; border: 1px solid;">
                <td colspan="2" style="text-align: center;font-weight: bold; font-size: 16px">Cout Achat => '.number_format($reception->montant,'0','.',' ').' Fr CFA</td>
            </tr>
        </tbody>
       </table><br>';

        }
        return $output;
    }

    public function imprimer($id){
        $output = $this->show($id);
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($output);

        return $pdf->stream();
    }

    public function details_rec($id){
        $output = $this->show($id);
        $pdf = '<table class="details_rec" id="details_rec"><tr><td></td>'.$output.'</tr></table>';
        return $output;
    }

    protected function rec_cmde($id){
        $commande = DB::table('commande')
            ->join('fournisseurs','fournisseurs.fournisseur_id','=','commande.fournisseur_id')
            ->where('commande.commande_id','=', $id)
            ->get();
        if (count($commande)==0){
            Alert::error('Erreur:','commande inexistante');
            return back();
        }else{
            $commande = (object) $commande[0];
            $receptions = DB::table('reception_dps')
                ->join('magasins','magasins.magasin_id','=','reception_dps.magasin_id')
                ->join('users','users.id','=','reception_dps.user_id')
                ->where('reception_dps.commande_id','=', $commande->commande_id)
                ->get();

            $date = new \DateTime($commande->cmde_date);
            $cmde_date = $date->format('d-m-Y');
            $cout_total = 0;

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
                    <td colspan="2">LES LIVRAISONS DE LA commande N° <b>' .$commande->commande_id.' DU ' .$cmde_date.' ADRESSEE A '.$commande->nom.'</b></td>
                </tr>
            </table>
            <br>

            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">';
            foreach($receptions as $reception){
                $produits = DB::table('produit_reception_dps')
                    ->join('produits','produits.produit_id','=','produit_reception_dps.produit_id')
                    ->where('produit_reception_dps.code','=',$reception->code)
                    ->get();
                $cout_total+=$reception->montant;
                $output .='
                <tr style="border-collapse: collapse; border: 1px solid; background-color: #fffde7; text-align: center; size: 20px">
                    <td style="border: 1px solid;">Reception N '.$reception->code.' / Date : '.$reception->date_reception_si.'</td>
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
                            <th width="11%">Produit le</th>
                            <th width="11%">Expire le</th>
                        </tr>
                    </thead>
                    <tbody>';
                foreach($produits as $produit){
                    $output .='
                    <tr style="border-collapse: collapse; border: 1px solid;">
                        <td style="border: 1px solid;">'.$produit->pdt_lib.'</td>
                        <td style="border: 1px solid; text-align: left">'.($produit->pdt_type).'</td>
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
            $cmde = Commande::find($id);
            return $cmde;
        }
    }
}
