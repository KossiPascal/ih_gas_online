<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Commande;
use App\Models\ProduitReceptionDps;
use App\Models\ProduitTransfertDps;
use App\Models\TransfertDps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class TransfertDpsController extends Controller
{
    protected function code(){
        $debut = date('Y').'-'.date('m').'-01';
        $fin = date('Y-m-d');
        $achatp = DB::table('transfert_dps')
            ->whereBetween('date_transfert', array($debut, $fin))
            ->where('centre_id', '=', Auth::user()->centre_id)
            ->get();
        $nb_cmde = $achatp->count()+1;
        $code = '00'.$nb_cmde.'TR'.date('m').date('Y').Auth::user()->id;
        return $code;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transfert = new TransfertDps();
        $code = $this->code();
        $reception_dps = [];
        Session::forget('code');
        return view('transfertdps.index', compact('transfert','code','reception_dps'));
    }

    public function reception(){
        if (\request()->ajax()){
            $reception_dps = DB::table('reception_dps')
                ->join('centres','centres.centre_id','=','reception_dps.centre_id')
                //->join('commandes','commandes.commande_id','=','reception_dps.commande_id')
                ->whereIn('reception_dps.etat_liv', ['LivreeSI','PartielleSI'])
                ->get();
            return $reception_dps;
        }
    }

    public function getcommande($id){
        $pdtcon = DB::table('reception_dps')
            ->where('reception_dps_id', '=', $id)
            ->get();
        $reception = (object) $pdtcon[0];
        return response()->json($reception->commande_id);
    }

    public function pdt_recu($prsi_id)
    {
        $this->pdt_tr($prsi_id);
    }

    public function pdt_tr($prsi_id)
    {
        $pdtcon = DB::table('produit_reception_dps')
            ->where('reception_dps_id','=',$prsi_id)
            ->get();
        $output='<table class="table table-striped table-bordered contour_table" id="pdt_tr">
           <thead>
           <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
               <td class="description">Produit</td>
               <td class="price">Qte recue</td>
               <td class="price">Qte transferee</td>
               <td class="price">Observation</td>
               <td ></td>
           </tr>
           </thead>
           <tbody>';
            foreach($pdtcon as $produit){
                $button_edit = '<button type="button" name="editer" id="'.$produit->produit_reception_dps_id.'" class="select btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';;
                //$button_supp = '<button type="button" name="delete" id="'.$produit->produit_reception_dps_id.'" class="select btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';;

                $output .='<tr>
                 <td class="cart_title">'.$produit->libelle.'</td>
                 <td class="cart_price">'.$produit->qte_recue.'</td>
                 <td class="cart_price">'.$produit->qte_transferee.'</td>
                 <td class="cart_price">'.$produit->remarque.'</td>
                 <td class="cart_delete">'.$button_edit.'</td>
             </tr>';
            }
            $output.='</body>
                    </table>';
        return $output;
    }



    public function select($id){
        ///if(request()->ajax()) {
            $produit = ProduitReceptionDps::find($id);
            $qte_liv = $pdtcon = DB::table('produit_reception_dps')
                ->where('produit_reception_dps_id','=',$id)
                ->sum('qte_recue');
            return response()->json(['produit'=>$produit,'qte_liv'=>$qte_liv]);
        //}
    }

    public function add(Request $request){
        $rules = array(
            'produit_id'     =>  'required',
            'qte_tr'     =>  'required|numeric|min:1',
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        DB::beginTransaction();
        try {
            if ($request->qte_cmde>=($request->qte_tr+$request->qte_liv)){
                ProduitReceptionDps::find($request->produit_reception_dps_id)->update([
                    'qte_transferee' =>$request->qte_tr,
                    'remarque' =>$request->remarque
                ]);
                DB::connection('vps')->table('produit_reception_dps')->where('produit_reception_dps_id',$request->produit_reception_dps_id)->update([
                    'qte_transferee' =>$request->qte_tr,
                    'remarque' =>$request->remarque
                ]);
                DB::commit();
                return response()->json(['success' => 'Produit modifie avec success']);
            }else{
                return response()->json(['error' => 'Quantite saisie depasse la quantite recue']);
            }
        }catch (\PDOException $se) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur survenu lors de l execution. produit non ajouter '.$se]);
        }
    }
    /*public function delete($id){
        if(request()->ajax()) {
            ProduitTransfertDps::find($id)->delete();
        }
    }*/


    public function store(Request $request){
        $rules = array(
            'date_transfert'     =>  'required',
            'reception_dps_id'     =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            Alert::error('Erreur','Merci de definir la date');
            return back();
        }

        $form_data = array(
            'code' =>  $this->code(),
            'date_transfert' =>  $request->date_transfert,
            'reception_dps_id' =>  $request->reception_dps_id,
            'observation' =>  $request->observation,
            'etat' =>  'Encours',
            'user_id'   =>  Auth::user()->id,
            'centre_id'   =>  Auth::user()->centre_id,
            'commande_id' =>  $request->commande_id,
        );
        //dd($form_data);
        DB::beginTransaction();
        try {

            TransfertDps::create($form_data);
            DB::connection('vps')->table('transfert_dps')->insert($form_data);
            DB::table('reception_dps')
                ->where('reception_dps_id','=',$request->reception_dps_id)
                ->update(['etat_liv'=>'Transferee']);
            DB::connection('vps')->table('reception_dps')
                ->where('reception_dps_id','=',$request->reception_dps_id)
                ->update(['etat_liv'=>'Transferee']);

            Alert::success('Success !', 'transfert enregistre avec success.');
            DB::commit();
            return redirect()->route('trdps.index');
        }catch (\PDOException $se){
            DB::rollBack();
            dd($se);
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.'.$se);
            return redirect()->route('trdps.index');
        }
    }

    public function edit($id)
    {
        //Session::put('transfert_dps_id',$id);
        //return redirect()->route('rec.editer');
    }

    public function editer(){
        /*$transfert_dps_id = Session::get('transfert_dps_id');

        if (Session::get('transfert_dps_id')){
            $transfert = TransfertDps::find($transfert_dps_id);
            $code = $transfert->code;
            $reception_dps = DB::table('reception_dps')
                ->where('reception_dps_id','=',$transfert->reception_dps_id)
                ->get();
            if (Auth::user()->ut==1){
                return view('transfert_si.edit', compact('transfert','code','magasins','reception_dps'));
            }elseif (Auth::user()->ut=2){
                return view('transfert_si.editc', compact('transfert','code','magasins','reception_dps'));
            }elseif (Auth::user()->ut==3){
                return view('transfert_si.editp', compact('transfert','code','magasins','reception_dps'));
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
            $historiques = DB::table('transfert_dps')
                ->join('centres','centres.centre_id','=','transfert_dps.centre_id')
                ->join('reception_dps','reception_dps.reception_dps_id','=','transfert_dps.reception_dps_id')
                ->join('commandes','commandes.commande_id','=','reception_dps.commande_id')
                ->whereBetween('transfert_dps.date_transfert', array($request->from_date, $request->to_date))
                ->get();
        }
        else
        {
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('transfert_dps')
                ->join('centres','centres.centre_id','=','transfert_dps.centre_id')
                ->join('reception_dps','reception_dps.reception_dps_id','=','transfert_dps.reception_dps_id')
                ->join('commandes','commandes.commande_id','=','reception_dps.commande_id')
                ->whereBetween('transfert_dps.date_transfert', array($debut, date('Y-m-d')))
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }

        return view('transfertdps.histo', compact('historiques'));
    }


    protected function show($id){
        $transfert = DB::table('transfert_dps')
            ->join('users','users.id','=','transfert_dps.user_id')
            ->join('reception_dps','reception_dps.reception_dps_id','=','transfert_dps.reception_dps_id')
            ->join('commandes','commandes.commande_id','=','reception_dps.commande_id')
            ->where('transfert_dps.transfert_dps_id','=', $id)
            ->get();

        if (count($transfert)==0){
            Alert::error('Erreur:','Transfert inexistante');
            return back();
        }else{
            $transfert = (object) $transfert[0];
            $date = new \DateTime($transfert->date_transfert);
            $date_transfert = $date->format('d-m-Y');

            $categories = DB::table('produit_reception_dps')
                ->join('produits','produits.produit_id','=','produit_reception_dps.produit_id')
                ->join('categories','categories.categorie_id','=','produits.categorie_id')
                ->join('reception_dps','reception_dps.reception_dps_id','=','produit_reception_dps.reception_dps_id')
                ->join('transfert_dps','transfert_dps.reception_dps_id','=','reception_dps.reception_dps_id')
                ->where('transfert_dps.transfert_dps_id','=',$id)
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
                            <td width="50%">Rransfert N° <b>' .$transfert->code.'</b></td>
                            <td width="50%">Date  <b>'.$date_transfert.'</b></td>
                        </tr>
                        <tr>
                            <td width="50%">Utilisateur: <b>' .$transfert->name.'</b></td>
                            <td width="50%">Commande Num: <b>' .$transfert->code.'</b></td>
                        </tr>
                    </table>
                    <br>
                    <table style="width: 100%; border: 0px solid;" cellspacing="0" cellpadding="0">';
            foreach($categories as $categorie){
                $produits = DB::table('produit_reception_dps')
                    ->join('produits','produits.produit_id','=','produit_reception_dps.produit_id')
                    ->join('reception_dps','reception_dps.reception_dps_id','=','produit_reception_dps.reception_dps_id')
                    ->join('transfert_dps','transfert_dps.reception_dps_id','=','reception_dps.reception_dps_id')
                    ->where('transfert_dps.transfert_dps_id','=',$id)
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
                                    <th width="10%">Reference</th>
                                    <th width="40%">Libelle</th>
                                    <th width="10%">Qte Commandee</th>
                                    <th width="10%">Qte recue</th>
                                    <th width="10%">Qte Transferee</th>
                                    <th width="20%">Observation</th>
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
                                <td style="border: 1px solid; text-align: right">'.number_format($produit->qte_transferee,'0','.',' ').'</td>
                                <td style="border: 1px solid; text-align: right">'.($produit->remarque).'</td>
                            </tr>';
                        }
                        $output .='
                    </tbody>
                    </table>
                    </td>
                </tr>';

            }
            $output .='
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

    public function details_tr($id){
        $output = $this->show($id);
        $pdf = '<table class="details_tr" id="details_rec"><tr><td></td>'.$output.'</tr></table>';
        return $output;
    }

    protected function rec_cmde($id){
        $commande = DB::table('commande')
            ->join('fournisseurs','fournisseurs.fournisseur_id','=','commande.fournisseur_id')
            ->where('commande.reception_dps_id','=', $id)
            ->get();
        if (count($commande)==0){
            Alert::error('Erreur:','commande inexistante');
            return back();
        }else{
            $commande = (object) $commande[0];
            $transferts = DB::table('transfert_dps')
                ->join('magasins','magasins.magasin_id','=','transfert_dps.magasin_id')
                ->join('users','users.id','=','transfert_dps.user_id')
                ->where('transfert_dps.reception_dps_id','=', $commande->reception_dps_id)
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
                    <td colspan="2">LES LIVRAISONS DE LA commande N° <b>' .$commande->reception_dps_id.' DU ' .$cmde_date.' ADRESSEE A '.$commande->nom.'</b></td>
                </tr>
            </table>
            <br>

            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">';
            foreach($transferts as $transfert){
                $produits = DB::table('produit_reception_dps')
                    ->join('produits','produits.produit_id','=','produit_reception_dps.produit_id')
                    ->where('produit_reception_dps.code','=',$transfert->code)
                    ->get();
                $cout_total+=$transfert->montant;
                $output .='
                <tr style="border-collapse: collapse; border: 1px solid; background-color: #fffde7; text-align: center; size: 20px">
                    <td style="border: 1px solid;">transfert N '.$transfert->code.' / Date : '.$transfert->date_transfert_si.'</td>
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
                    <td colspan="3" style="border: 1px solid;">Cout transfert</td>
                    <td colspan="5" style="border: 1px solid; text-align: right">Cout Achat => '.number_format($transfert->montant,'0','.',' ').' Fr CFA</td>
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
