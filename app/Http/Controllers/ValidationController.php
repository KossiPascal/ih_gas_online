<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Commande;
use App\Models\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class ValidationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage-action',['commande','valider']);
        $commandes = DB::table('commandes')
            ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
            ->join('users','users.id','=','commandes.user_id')
            ->join('centres','centres.centre_id','=','commandes.centre_id')
            //->where('commandes.centre_id', '=', Auth::user()->centre_id)
            ->where('commandes.etat', '=','Encours')
            ->orderBy('commandes.date_commande')
            ->get();
        if (request()->ajax()) {
            return datatables()->of($commandes)
                ->addColumn('action', function ($commande) {
                    $button = '<button type="button" name="valider" id="' . $commande->commande_id . '" class="valider btn btn-success btn-sm"><i class="fa fa-edit">Valider</i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="observer" id="' . $commande->commande_id . '" class="observer btn btn-primary btn-sm"><i class="fa fa-note">Observer</i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="details" id="' . $commande->commande_id . '" class="details btn btn-danger btn-sm"><i class="fa fa-infos">Details</i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('validation.index', compact('commandes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function valider($id)
    {
        $this->authorize('manage-action',['validation','valider']);
        if (\request()->ajax()){
            Commande::find($id)->update(['etat'=>'Validee']);
            //DB::connection('vps')->table('commandes')->where('commande_id',$id)->update(['etat'=>'Validee']);

        }
    }

    public function cmde($id)
    {
        if (\request()->ajax()){
            $infos = DB::table('commandes')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->join('centres','centres.centre_id','=','commandes.centre_id')
                ->where('commandes.commande_id', '=',$id)
                ->get();
            $cmde = (object) $infos[0];
            return $cmde;
        }
    }

    public function val($id)
    {
        if (\request()->ajax()){
            $infos = DB::table('validations')
                ->join('commandes','commandes.commande_id','=','validations.commande_id')
                ->join('centres','centres.centre_id','=','commandes.centre_id')
                ->where('validations.validation_id', '=',$id)
                ->get();
            $cmde = (object) $infos[0];
            return $cmde;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('manage-action',['commande','valider']);
        $rules = array(
            'date'     =>  'required|date'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        DB::beginTransaction();
        try {
            Validation::create([
                'date' => $request->date,
                'observation' => $request->observation,
                'commande_id' => $request->commande_id,
                'centre_id' => $request->centre_id,
                'user_id' => Auth::user()->id,
            ]);
            // DB::connection('vps')->table('validations')->insert([
            //     'date' => $request->date,
            //     'observation' => $request->observation,
            //     'commande_id' => $request->commande_id,
            //     'centre_id' => $request->centre_id,
            //     'user_id' => Auth::user()->id,
            // ]);
            if ($request->source_action=="1"){
                Commande::find($request->commande_id)->update(['etat'=>'Validee']);
                //DB::connection('vps')->table('commandes')->where('commande_id',$request->commande_id)->update(['etat'=>'Validee']);

            }
            DB::commit();
            return response()->json(['success' => 'Action notee']);
        }catch (\PDOException $se) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur survenu lors de l execution. '.$se]);
        }
    }

    public function update_val(Request $request)
    {
        $this->authorize('manage-action',['validation','editer']);
        $rules = array(
            'date'     =>  'required|date'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        DB::beginTransaction();
        try {
            Validation::find($request->validation_id)->update([
                'date' => $request->date,
                'observation' => $request->observation]);
            // DB::connection('vps')->table('validations')->where('validation_id',$request->validation_id)->update([
            //     'date' => $request->date,
            //     'observation' => $request->observation]);
            if ($request->source_action=="1"){
                Commande::find($request->commande_id)->update(['etat'=>'Validee']);
                //DB::connection('vps')->table('commandes')->where('commande_id',$request->commande_id)->update(['etat'=>'Validee']);
            }
            DB::commit();
            return response()->json(['success' => 'Action notee']);
        }catch (\PDOException $se) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur survenu lors de l execution. '.$se]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Validation  $validation
     * @return \Illuminate\Http\Response
     */
    protected function details($id){
        $commande = DB::table('commandes')
            ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
            ->join('users','users.id','=','commandes.user_id')
            ->where('commandes.commande_id','=', $id)
            ->get();

        $validations = DB::table('validations')
            ->where('commande_id','=', $id)
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

            $centre  = Centre::find($commande->centre_id);

            $output ='
                    <table width="100%">
                        <tr>
                            <td width="100%" colspan="2">
                                <div>'.$centre->nom_centre.' / '.$centre->adrssse.' / '.$centre->telephone.'</div>
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
                    <table style="width: 100%; border: 0px solid; border-radius: 10px" cellspacing="0" cellpadding="0">';
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
                            <tr style="border-collapse: collapse; border: 0px solid; background-color: #fffde7; text-align: center; size: 20px">
                                <td colspan="2" style="border: 1px solid;">'.$categorie->libelle.'</td>
                            </tr>
                            <tr style="border-collapse: collapse; border: 1px solid; text-align: center; size: 20px">
                                <td colspan="2" style="border: 1px solid;">
                                    <table style="width: 100%; cellspacing="0" cellpadding="0">
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
                                            <tr style="border-collapse: collapse; border: 0px solid;">
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
                        </tbody>';
                    }

                $output .= '
                        <tr style="border-collapse: collapse; border: 1px solid; background-color: #fffde7; text-align: center; size: 20px">
                            <td colspan="2" style="border: 1px solid;"> COUT TOTAL DE LA COMMANDE  '.number_format($cout_achat_total,'0','.',' ').' Fr CFA</td>
                        </tr>
                </table>';
                if(count($validations)>0){
                    $output .= '
                        <p>Les observations</p>
                        <table style="width: 100%; cellspacing="0" cellpadding="0">
                        <thead>
                            <tr>
                                <th width="15%">Date</th>
                                <th width="85%">Observations</th>
                            </tr>
                        </thead>
                        <tbody>';
                        foreach($validations as $validation){
                            $output .='
                            <tr style="border-collapse: collapse; border: 0px solid;">
                                <td style="border: 1px solid;">'.$validation->date.'</td>
                                <td style="border: 1px solid;">'.$validation->observation.'</td>
                            </tr>';
                        }
                    $output.='</tbody></table>';
                }

            return $output;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Validation  $validation
     * @return \Illuminate\Http\Response
     */
    public function edit(Validation $validation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Validation  $validation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Validation $validation)
    {
        //
    }

    public function histo(Request $request){
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('validations')
                ->join('commandes','commandes.commande_id','=','validations.commande_id')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->join('users','users.id','=','commandes.user_id')
                ->join('centres','centres.centre_id','=','commandes.centre_id')
                //->where('commandes.centre_id', '=', Auth::user()->centre_id)
                ->whereBetween('commandes.date_commande', array($request->from_date, $request->to_date))
                //->where('commandes.etat','<>','Annulee')
                ->get();
        }
        else
        {
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('validations')
                ->join('commandes','commandes.commande_id','=','validations.commande_id')
                ->join('fournisseurs','fournisseurs.fournisseur_id','=','commandes.fournisseur_id')
                ->join('users','users.id','=','commandes.user_id')
                ->join('centres','centres.centre_id','=','commandes.centre_id')
                ->whereBetween('commandes.date_commande', array($debut, date('Y-m-d')))
                //->where('commandes.etat','<>','Annulee')
                ->get();
        }

        if (request()->ajax()) {
            return datatables()->of($historiques)
                ->addColumn('action', function ($validation) {
                    $button = '<button type="button" name="editer" id="' . $validation->validation_id . '" class="editer btn btn-success btn-sm"><i class="fa fa-edit">Editer</i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="envoyer" id="' . $validation->validation_id . '" class="envoyer btn btn-primary btn-sm"><i class="fa fa-send">Envoyer</i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="details" id="' . $validation->validation_id . '" class="details btn btn-danger btn-sm"><i class="fa fa-infos">Details</i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('validation.histo', compact('historiques'));

    }

}
