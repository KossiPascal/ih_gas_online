<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Centre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class CategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        //$this->authorize('manage-action',['categorie','creer']);
        $categories = DB::table('categories')
            ->where('statut', '=', 'true')
            ->get();
        if (request()->ajax()) {
            return datatables()->of($categories)
                ->addColumn('action', function ($categorie) {
                    $button = '<button type="button" name="editer" id="' . $categorie->categorie_id . '" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="delete" id="' . $categorie->categorie_id . '" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="cout" id="' . $categorie->categorie_id . '" class="cout btn btn-warning btn-sm"><i class="fa fa-info"></i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('donnees.categorie.index');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('manage-action',['categorie','creer']);
        $rules = array(
            'libelle'    =>  'required',
            'type'    =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $form_data = array(
            'libelle' =>  $request->libelle,
            'type' =>  $request->type,
        );

        $categorie = DB::table('categories')
            ->Where('statut','=','true')
            ->Where('libelle','=',$request->libelle)
            ->get();

        if ($request->categorie_id==null){
            if (count($categorie)==0){
                try {
                    DB::beginTransaction();
                        Categorie::create($form_data);
                        //DB::connection('vps')->table('categories')->insert($form_data);
                    DB::commit();
                    return response()->json(['success' => 'Categorie cree avec success.']);
                } catch (\Throwable $th) {
                    DB::rollBack();
                    return response()->json(['error' => $th->getMessage()]);
                }
            }else{
                return response()->json(['error' => 'La categorie '.$request->libelle.' existe deja dans la base de donnee.']);
            }
        }else{
            if (count($categorie)==0){
                try {
                    DB::beginTransaction();
                        Categorie::find($request->categorie_id)->update(['libelle'=>$request->libelle,'type'=>$request->type]);
                       // DB::connection('vps')->table('categories')->where('categorie_id', $request->categorie_id)->update(['libelle'=>$request->libelle,'type'=>$request->type]);
                    DB::commit();
                    return response()->json(['success' => 'Categorie modifiee avec success.']);
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
            }else{
                try {
                    DB::beginTransaction();
                        Categorie::find($request->categorie_id)->update(['type'=>$request->type]);
                        //DB::connection('vps')->table('categories')->where('categorie_id', $request->categorie_id)->update(['type'=>$request->type]);
                    DB::commit();
                    return response()->json(['success' => 'Categorie modifiee avec success.']);
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->authorize('manage-action',['categorie','editer']);
        if(request()->ajax())
        {
            $data = Categorie::findOrFail($id);
            return response()->json(['data' => $data]);
        }
    }

    public function delete($id)
    {
        $this->authorize('manage-action',['categorie','supprimer']);
        if(request()->ajax())
        {
            //$this->authorize('supprimer', 'categorie');
            try {
                DB::beginTransaction();
                    Categorie::findOrfail($id)->update(['statut'=>'false']);
                    //DB::connection('vps')->table('categories')->where('categorie_id', $id)->update(['statut'=>'false']);
                DB::commit();
                return redirect()->route('cat.index')->with('success', 'La categorie a ete supprime');
            } catch (\Throwable $th) {
                DB::rollBack();
            }
        }
    }

    public function imprimer($id){
        $categorie = Categorie::find($id);
        $produits = DB::table('produits')
            ->where('categorie_id','=',$id)
            ->where('pdt_etat','=','OK')
            ->count('pdt_num');
        $outpout='';
        if ($produits==0){
            Alert::info('Infos','Pas de produits ou actes associes a la categorie '.$categorie->libelle);
            return response()->json(['error'=>'Pas de produits ou actes associes a la categorie '.$categorie->libelle]);
        }else{
            if ($categorie->type=='Stockable'){
                $outpout = $this->stockable($categorie);
            }else{
                $outpout = $this->non_stockable($categorie);
            }
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($outpout);
            return $pdf->stream();
            //return response()->json(['success'=>$outpout]);
        }

    }

    protected function stockable($categorie){
        $produits = [];
        $couta = 0;
        $coutv = 0;

        $allProduits = DB::table('quantite_produits')
            ->join('produits','produits.pdt_num','=','quantite_produits.pdt_num')
            ->selectRaw('produits.pdt_ref,produits.pdt_lib,sum(quantite_produits.qter) as qte,avg(quantite_produits.pa) as pa,avg(quantite_produits.pv) as pv')
            ->where('produits.categorie_id','=',$categorie->categorie_id)
            ->where('quantite_produits.etat','<>','Delete')
            ->where('produits.pdt_etat','=','OK')
            ->groupBy('produits.pdt_ref','produits.pdt_lib')
            ->get();

        if (count($allProduits)>0){
            foreach ($allProduits as $produit){
                $pdt = new \stdClass();
                $pdt->pdt_ref = $produit->pdt_ref;
                $pdt->pdt_lib = $produit->pdt_lib;
                $pdt->pdt_pa = $produit->pa;
                $pdt->pdt_pv = $produit->pv;
                $pdt->pdt_qte = $produit->qte;
                $pdt->couta = $produit->qte*$produit->pa;
                $pdt->coutv = $produit->qte*$produit->pv;

                array_push($produits,$pdt);
                $couta+=$produit->qte*$produit->pa;
                $coutv+=$produit->qte*$produit->pv;
            }
        }

        $date = date('d-m-Y');

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
                            <td style="text-align: center;">ETAT DU STOCK AU '.$date.'</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">CATEGORIE :<b>'.$categorie->libelle.'</b></td>
                        </tr>
                    </table>
                    <br>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 10px; background-color: #E5CC75";>
                                <th width="35%">Libelle</th>
                                <th width="8%">Prix d achat</th>
                                <th width="8%">Prix de vente</th>
                                <th width="8%">Qte</th>
                                <th width="13%">Cout achat</th>
                                <th width="13%">Cout Vente</th>
                                <th width="10%">Marge</th>
                            </tr>
                        </thead>
                        <tbody>';
                        foreach($produits as $produit){
                        $output .='
                            <tr style="border-collapse: collapse; border: 1px solid;">
                                <td width="35%" style="border: 1px solid;">'.$produit->pdt_lib.'</td>
                                <td width="8%" style="border: 1px solid; text-align: right">'.number_format($produit->pdt_pa,'0',',',' ').'</td>
                                <td width="8%" style="border: 1px solid; text-align: right">'.number_format($produit->pdt_pv,'0',',',' ').'</td>
                                <td width="8%" style="border: 1px solid; text-align: right">'.number_format($produit->pdt_qte,'0',',',' ').'</td>
                                <td width="13%" style="border: 1px solid; text-align: right">'.number_format($produit->couta,'0',',',' ').'</td>
                                <td width="13%" style="border: 1px solid; text-align: right">'.number_format($produit->coutv,'0',',',' ').'</td>
                                <td width="10%" style="border: 1px solid; text-align: right">'.number_format($produit->coutv-$produit->couta,'0',',',' ').'</td>
                            </tr>';
                        }
                        $output .='</tbody>
                       </table><br>
                        <table width="100%">
                            <tr style="border-radius: 10px";>
                                <th width="34%">Cout achat : '.number_format($couta,'0',',',' ').'</th>
                                <th width="33%">Cout vente : '.number_format($coutv,'0',',',' ').'</th>
                                <th width="33%">Marge previsionnelle : '.number_format($coutv-$couta,'0',',',' ').'</th>
                            </tr>
                        </table>';
        return $output;
    }

    protected function non_stockable($categorie){
        $produits = DB::table('produits')
            ->where('categorie_id','=',$categorie->categorie_id)
            ->where('pdt_etat','=','OK')
            ->orderby('pdt_lib')
            ->get();

        $date = date('d-m-Y');

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
                        <td style="text-align: center;">LISTE DES ELEMENTS DE LA CATEGORIE :<b>'.$categorie->libelle.'</b></td>
                    </tr>
                </table>
                <br>
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                        <tr style="border-radius: 10px; background-color: #E5CC75";>
                            <th width="15%">Reference</th>
                            <th width="65%">Libelle</th>
                            <th width="20%">Prix Unitaire</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach($produits as $produit){
                        $output .='
                            <tr style="border-collapse: collapse; border: 1px solid;">
                                <td  width="15%" style="border: 1px solid;">'.$produit->pdt_ref.'</td>
                                <td  width="65%" style="border: 1px solid;">'.$produit->pdt_lib.'</td>
                                <td  width="20%" style="border: 1px solid;">'.$produit->pdt_pv.'</td>
                            </tr>';
                        }
                    $output .='</tbody>
                               </table><br>';
        return $output;
    }

    public function cout_stock($id)
    {
        //if(request()->ajax()) {
            $categorie = Categorie::find($id);
            if ($categorie->type=='Stockable'){
                $coutmagasins = DB::table('quantite_produits')
                    ->join('produits','produits.pdt_num','=','quantite_produits.pdt_num')
                    ->join('magasins','magasins.mag_num','=','quantite_produits.mag_num')
                    ->selectRaw('magasins.mag_lib,sum(quantite_produits.qter) as qte,avg(quantite_produits.pa) as pa,avg(quantite_produits.pv) as pv')
                    ->where('produits.categorie_id','=',$id)
                    ->where('quantite_produits.etat','<>','Delete')
                    ->where('produits.pdt_etat','=','OK')
                    ->groupBy('magasins.mag_lib')
                    ->get();

                dd($coutmagasins);

                $totala = 0;
                $totalv = 0;

                $output='<table class="table table-striped table-bordered contour_table" id="cout_stock">
                   <thead>
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <td class="description">Magasin</td>
                       <td>Cout Achat</td>
                       <td>Cout Vente</td>
                   </tr>
                   </thead>
                   <tbody>';
                    foreach($coutmagasins as $magasin){
                        $output .='<tr>
                             <td class="cart_title">'.$magasin->mag_lib.'</td>
                             <td class="cart_price" style="text-align: right">'.number_format($magasin->qte*$magasin->pa,'0','.',' ').'</td>
                             <td class="cart_price" style="text-align: right">'.number_format($magasin->qte*$magasin->pv,'0','.',' ').'</td>
                         </tr>';
                        $totala+=$magasin->qte*$magasin->pa;
                        $totalv+=$magasin->qte*$magasin->pv;
                    }
                    $output.='
                        <tr>
                            <td>Cout Total </td>
                            <td style="text-align: right">'.number_format($totala,'0','.',' ').'</td>
                            <td style="text-align: right">'.number_format($totalv,'0','.',' ').'</td>
                        </tr>
                        </body>
                    </table>';
                return $output;
            }else{
                $output='<table class="table table-striped table-bordered" id="coutStock">
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <td class="description">Les produits de cette categorie ne sont pas stockables</td>
                   </tr>
                   </table>';
                return $output;
            }
        //}
    }
}
