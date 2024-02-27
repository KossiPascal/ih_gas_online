<?php

namespace App\Http\Controllers;
use App\Models\Centre;
use App\Models\Correctionstock;
use App\Models\Mouvement;
use App\Models\Produit;
use App\Models\ProduitCorrectionStock;
use App\Models\QuantiteProduit;
use App\Models\StockProduit;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;


class CSController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function code_cs(){
        $debut = date('Y').'-'.date('m').'-01';
        $fin = date('Y-m-d');
        $achatp = DB::table('correction_stocks')->whereBetween('date_cs', array($debut, $fin))->get();
        $nb_cmde = $achatp->count()+1;
        $correction_stock_id = '00'.$nb_cmde.'COR'.date('m').date('Y').Auth::user()->id.Auth::user()->centre_id;
        return $correction_stock_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage-action',['correction','lister']);
        $cs = new Correctionstock();
        $magasins = [];

        $cs_type = ['Avarie'=>'Avarie','Casse'=>'Casse','Manque'=>'Manque','Perime'=>'Perime','Surplus'=>'Surplus','Autres'=>'Autres'];

        $code_cs = $this->code_cs();

        return view('correction.index', compact('magasins','code_cs','cs','cs_type'));
    }

    public function magasins(){
        if (\request()->ajax()){
            $magasins = DB::table('magasins')
                ->where('statut','=','true')
                ->get();
            return $magasins;
        }
    }

    public function rech_mont($code){
        if (\request()->ajax()){
            $cs_mont = ProduitCorrectionStock::where('code_cs','=',$code)->sum('cout');
            return $cs_mont;
        }
    }

    public function pdt_mag($magasin_id)
    {
        $pdt_mags = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('categories.type','=','Stockable')
            ->where('stock_produits.etat','<>','Delete')
            ->where('stock_produits.magasin_id','=',$magasin_id)
            ->get();
        /*$output='';
        $output='<table class="table table-striped table-bordered contour_table" id="pdt_selected">
                   <thead>
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <td class="description">Produit</td>
                       <td class="description">Lot</td>
                       <td class="price">Quantite</td>
                   </tr>
                   </thead>
                   <tbody>';
        foreach($pdt_mags as $produit){
            $output .='<tr>
                 <td class="cart_title">
                    <a href="#" class="select" id="'.$produit->produit_correction_stock_id.'">'.$produit->pdt_lib.'</a>
                 </td>
                 <td class="cart_title">'.$produit->lot.'</td>
                 <td class="cart_price">'.$produit->qter.'</td>
             </tr>';
        }
        return $output;*/
        return datatables()->of($pdt_mags)
            ->addColumn('action', function ($produit) {
                $button = '<button type="button" name="select" id="' . $produit->stock_produit_id . '" class="select btn btn-success btn-sm"><i class="fa fa-check"></i></button>';
                return $button;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function pdt_con($code_cs,$magasin_id)
    {
        $pdtcon = DB::table('produit_correction_stocks')
            //->join('produits','produits.produit_id','=','produit_correction_stocks.produit_id')
            ->where('code_cs','=',$code_cs)
            ->where('magasin_id','=',$magasin_id)
            ->get();
        $output='';
        $output='<table class="table table-striped table-bordered contour_table" id="pdt_selected">
                   <thead>
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <td class="description">Produit</td>
                       <td class="description">Lot</td>
                       <td class="quantity">Operation</td>
                       <td class="quantity">Qte</td>
                       <td></td>
                       <td></td>
                   </tr>
                   </thead>
                   <tbody>';
        foreach($pdtcon as $produit){
            $button_edit = '<button type="button" name="edit" id="'.$produit->produit_correction_stock_id.'" class="edit btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
            $button_delete = '<button type="button" name="delete" id="'.$produit->produit_correction_stock_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
            $output .='<tr>
                         <td class="cart_title">'.$produit->libelle.'</td>
                         <td class="cart_title">'.$produit->lot.'</td>
                         <td class="cart_title">'.$produit->motif.'</td>
                         <td class="cart_price">'.$produit->qte.'</td>
                         <td class="cart_delete">'.$button_edit.'</td>
                         <td class="cart_delete">'.$button_delete.'</td>
                     </tr>';
        }
        return $output;
    }


    public function add(Request $request)
    {
        $this->authorize('manage-action',['correction','creer']);
        $rules = array(
            'qte'     =>  'required|numeric'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }
        $qp = StockProduit::find($request->idqp);
        $form_data = array(
            'stock_produit_id' =>  $request->stock_produit_id,
            'code_cs' =>  $request->hidden_code,
            'produit_id'  =>  $request->produit_id,
            'motif'   =>  $request->type,
            'lot'   =>  $request->lot,
            'libelle'   =>  $request->libelle,
            'qte'   =>  $request->qte,
            'pu'   =>  $request->pu,
            'cout'   =>  $request->qte*$request->pu,
            'magasin_id'   =>  $request->mag_id,
        );

        if ($request->qtes+$request->qte<0){
            return response()->json(['error' => 'Quantite en stock insuffusant pour effectuer cette correction']);
        }else{
            if ($request->idcon==null){
                $con_bon = DB::table('produit_correction_stocks')
                    ->where('code_cs','=',$request->hidden_code_csace)
                    ->where('produit_id','=',$request->produit_id)
                    ->where('lot','=',$request->lot)
                    ->get();
                if (count($con_bon)==0){
                    DB::beginTransaction();
                    try {
                        ProduitCorrectionStock::create($form_data);
                        //->table('produit_correction_stocks')->insert($form_data);
                        return response()->json(['success' => 'Produit ajoute']);
                        DB::commit();
                    }catch (\PDOException $se){
                        return response()->json(['error' => 'Produit non ajoute. ']);
                        DB::rollBack();
                    }
                }else{
                    return response()->json(['error' => 'Vous aviez deja selectionner ce produit']);
                }
            }else{
                DB::beginTransaction();
                try {
                    ProduitCorrectionStock::find($request->idcon)->update($form_data);
                    //->table('produit_correction_stocks')->where('produit_correction_stock_id',$request->idcon)->update($form_data);
                    return response()->json(['success' => 'Produit modifie']);
                    DB::commit();
                }catch (\PDOException $se){
                    return response()->json(['error' => 'Produit non modifie.']);
                    DB::rollBack();
                }
            }
        }
    }

    public function store(Request $request)
    {
        $this->authorize('manage-action',['correction','creer']);
        $code_cs = $this->code_cs();
        DB::table('produit_correction_stocks')
            ->where('code_cs','=',$code_cs)
            ->where('magasin_id','<>',$request->magnum)
            ->delete();
        $cout = ProduitCorrectionStock::where('code_cs','=',$code_cs)->sum('cout');
        $concernes = DB::table('produit_correction_stocks')
            ->where('code_cs','=',$code_cs)
            ->get();

        $form_data = array(
            'code_cs' =>  $code_cs,
            'date_cs' =>  $request->date_cs,
            'motif'  =>  $request->motif_cs,
            'cout'  =>  $cout,
            'magasin_id'  =>  $request->magnum,
            'centre_id'   =>  Auth::user()->centre_id,
            'user_id'   =>  Auth::user()->id
        );
        //dd($form_data,$concernes);

        DB::beginTransaction();
        try {
            foreach ($concernes as $concerne) {
                $qteIni = DB::table('stock_produits')
                    ->where('magasin_id','=',$request->magnum)
                    ->where('produit_id','=',$concerne->produit_id)
                    ->where('etat','<>','Delete')
                    ->sum('qte');

                $qp = StockProduit::find($concerne->stock_produit_id);
                if ($concerne->qte > 0) {
                    $ent = $concerne->qte;
                    $sor = 0;
                } else {
                    $ent = 0;
                    $sor = $concerne->qte * (-1);
                }

                $qp->update(['qte' => $qp->qte+($concerne->qte)]);
                //->table('stock_produits')->where('stock_produit_id',$concerne->stock_produit_id)->update(['qte' => $qp->qte+($concerne->qte)]);


                Mouvement::create([
                    'date' => $request->date_cs,
                    'magasin_id' => $request->magnum,
                    'centre_id' => Auth::user()->centre_id,
                    'user_id' => Auth::user()->id,
                    'produit_id' => $concerne->produit_id,
                    'motif' => 'Correction du stock de '.$concerne->libelle.'/lot '.$concerne->lot,
                    'libelle' => $concerne->libelle,
                    'qte_initiale' => $qteIni,
                    'qte_entree' => $ent,
                    'qte_sortie' => $sor,
                    'qte_reelle' => $qteIni+($concerne->qte),
                    'idop' => $concerne->code_cs,
                    'idcon' => $concerne->produit_correction_stock_id,
                ]);
                // //->table('mouvements')->insert(['
                //     date' => $request->date_cs,
                //     'magasin_id' => $request->magnum,
                //     'centre_id' => Auth::user()->centre_id,
                //     'user_id' => Auth::user()->id,
                //     'produit_id' => $concerne->produit_id,
                //     'motif' => 'Correction du stock de '.$concerne->libelle.'/lot '.$concerne->lot,
                //     'libelle' => $concerne->libelle,
                //     'qte_initiale' => $qteIni,
                //     'qte_entree' => $ent,
                //     'qte_sortie' => $sor,
                //     'qte_reelle' => $qteIni+($concerne->qte),
                //     'idop' => $concerne->code_cs,
                //     'idcon' => $concerne->produit_correction_stock_id,
                // ]);
            }
            Correctionstock::create($form_data);
            //->table('correction_stocks')->insert($form_data);
            $lastId = DB::getPdo()->lastInsertId();
            DB::table('produit_correction_stocks')
                ->where('code_cs','=',$code_cs)
                ->update(['correction_stock_id'=>$lastId]);
            // //->table('produit_correction_stocks')
            //     ->where('code_cs','=',$code_cs)
            //     ->update(['correction_stock_id'=>$lastId]);

            DB::commit();
            Alert::success('Success !', 'Correction enregistre avec success.');
            return redirect()->route('cs.index');
        }catch (\PDOException $se){
            DB::rollBack();
            dd($se);
            Alert::error('Erreur !', 'Correction non enregistree.');
            return redirect()->route('cs.index');
        }
    }

    public function select($id)
    {
        if(request()->ajax())
        {
            $pdt_mags = DB::table('stock_produits')
                ->join('produits','produits.produit_id','=','stock_produits.produit_id')
                ->where('stock_produits.stock_produit_id','=',$id)
                ->get();
            $data = (object) $pdt_mags[0];
            $produit = Produit::find($data->produit_id);
            return response()->json(['stock'=>$data,'produit'=>$produit]);
        }
    }

    public function select_edit($id)
    {
        if(request()->ajax())
        {
            $pdt_cor = ProduitCorrectionStock::find($id);
            $stock = StockProduit::find($pdt_cor->stock_produit_id);
            $pdt_mags = DB::table('produit_correction_stocks')
                ->join('produits','produits.produit_id','=','produit_correction_stocks.produit_id')
                ->join('stock_produits','stock_produits.stock_produit_id','=','produit_correction_stocks.stock_produit_id')
                ->where('produit_correction_stocks.produit_correction_stock_id','=',$id)
                ->get();
            $data = (object) $pdt_mags[0];
            return response()->json(['produit'=>$pdt_cor,'stock'=>$stock]);
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $this->authorize('manage-action',['correction','editer']);
        $cs = Correctionstock::find($request->correction_stock_id);
        $cs_mont = ProduitCorrectionStock::where('correction_stock_id','=',$request->correction_stock_id)->sum('mont');
        $form_data = array(
            'date_cs' =>  $request->date_cs,
            'cs_motif'  =>  $request->cs_motif,
            'cs_mont'  =>  $cs_mont,
            'user_id'   =>  Auth::user()->id
        );
        try {
            DB::beginTransaction();
            Correctionstock::find($request->correction_stock_id)->update($form_data);
            //->table('correction_stocks')->where('correction_stock_id',$request->correction_stock_id)->update($form_data);
            DB::commit();
            Alert::success('Success !', 'Correction modifiee avec success.');
            return redirect()->route('correction.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            Alert::error('Erreur !', 'Erreur survenu lors de l execution.');
        }
    }

    public function delete($id)
    {
        if(request()->ajax())
        {
            try {
                DB::beginTransaction();
                ProduitCorrectionStock::find($id)->delete();
                //->table('produit_correction_stocks')->where('produit_correction_stock_id',$id)->delete();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Alert::error('Erreur !', 'Erreur survenu lors de l execution.');
            }

        }
    }

    public function histo(Request $request){
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('correction_stocks')
                ->join('users','users.id','=','correction_stocks.user_id')
                ->join('magasins','magasins.magasin_id','=','correction_stocks.magasin_id')
                ->where('users.centre_id','=',Auth::user()->centre_id)
                ->whereBetween('correction_stocks.date_cs', array($request->from_date, $request->to_date))
                ->get();
        }
        else
        {
            $historiques = DB::table('correction_stocks')
                ->join('users','users.id','=','correction_stocks.user_id')
                ->join('magasins','magasins.magasin_id','=','correction_stocks.magasin_id')
                ->where('users.centre_id','=',Auth::user()->centre_id)
                ->whereBetween('correction_stocks.date_cs', array(date('Y-m-d'), date('Y-m-d')))
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }

        return view('correction.histo', compact('historiques'));

    }

    protected function show($id){
        $cs = DB::table('correction_stocks')
            ->join('magasins','magasins.magasin_id','=','correction_stocks.magasin_id')
            ->join('users','users.id','=','correction_stocks.user_id')
            ->where('correction_stocks.correction_stock_id','=', $id)
            ->get();

        $cs = (object) $cs[0];
        $date = new \DateTime($cs->date_cs);
        $date_cs = $date->format('d-m-Y');

        $produits = DB::table('produit_correction_stocks')
            ->join('produits','produits.produit_id','=','produit_correction_stocks.produit_id')
            ->where('produit_correction_stocks.correction_stock_id','=',$id)
            ->get();

        $centre  = Centre::find(Auth::user()->centre_id);
        //dd($cs,$produits,$centre);
        $output ='<table>
                <tr>
                    <td width="15%">
                        <img src="../public/images/logo.png" width="100" height="50">
                    </td>
                    <td width="85%">
                        <div style="size: 25px">'.$centre->nom_centre.'</div>
                        <div style="size: 10">'.$centre->services.'</div>
                        <div style="font-style: italic">'.$centre->adresse.'</div>
                        <div style="font-style: italic">'.$centre->telephone.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 10px">
                <tr>
                    <td width="30%">CORRECTION N° </td>
                    <td width="30%"><b>' .$cs->code_cs.'</b></td>
                    <td width="15%"><b>Date</b></td>
                    <td width="25%">'.$date_cs.'</td>
                </tr>
                <tr>
                    <td width="30%">Magasin: </td>
                    <td width="30%"><b>' .$cs->libelle.'</b></td>
                    <td width="30%">Utilisateur: </td>
                    <td width="30%" colspan="3"><b>' .$cs->name.'</b></td>
                </tr>
                <tr>
                    <td width="30%">Motif: </td>
                    <td width="30%" colspan="3"><b>' .$cs->motif.'</b></td>
                </tr>
            </table>
            <br>
            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                <thead>
                    <tr style="border-radius: 10px; background-color: #E5CC75";>
                        <th width="45%">Produit</th>
                        <th width="10%">Lot</th>
                        <th width="15%">Motif</th>
                        <th width="10%">Quantite</th>
                        <th width="10%">Prix</th>
                        <th width="10%">Cout</th>
                    </tr>
                </thead>
                <tbody>';
                foreach($produits as $produit){
                    $output .='
                    <tr style="border-collapse: collapse; border: 1px solid;">
                        <td  width="45%" style="border: 1px solid;">'.$produit->libelle.'</td>
                        <td  width="10%" style="border: 1px solid;">'.$produit->lot.'</td>
                        <td  width="15%" style="border: 1px solid; text-align: right">'.($produit->motif).'</td>
                        <td  width="10%" style="border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                        <td  width="10%" style="border: 1px solid; text-align: right">'.number_format($produit->prix_vente,'0','.',' ').'</td>
                        <td  width="10%" style="border: 1px solid; text-align: right">'.number_format($produit->cout,'0','.',' ').'</td>
                    </tr>';
                }
                $output .='
                    <tr style="border-collapse: collapse; border: 1px solid;">
                        <td style="border: 1px solid; text-align: center" colspan="5"> COUT TOTAL</td>
                        <td style="border: 1px solid; text-align: right">'.number_format($cs->cout,'0','.',' ').'</td>
                    </tr>
            </tbody>
          </table><br>';

        //$pdf = App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        //return $pdf->stream();
        return $output;
    }
}
