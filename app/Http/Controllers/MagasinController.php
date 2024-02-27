<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Magasin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class MagasinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage-action',['magasin','lister']);
        $magasins = DB::table('magasins')
            ->where('statut', '=', 'true')
            ->where('centre_id', '=', Auth::user()->centre_id)
            ->get();
        if (request()->ajax()) {
            return datatables()->of($magasins)
                ->addColumn('action', function ($magasin) {
                    $button = '<button type="button" name="editer" id="' . $magasin->magasin_id . '" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="delete" id="' . $magasin->magasin_id . '" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('donnees.magasin.index');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('manage-action',['magasin','creer']);
        $rules = array(
            'libelle'    =>  'required',
            'type'    =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $nbcat = Magasin::count()+1;
        $magasin_id = $nbcat.Auth::user()->id;

        $form_data = array(
            'libelle' =>  $request->libelle,
            'type' =>  $request->type,
            'centre_id' =>  Auth::user()->centre_id,
        );

        $magasin = DB::table('magasins')
            ->Where('statut','=','true')
            ->Where('libelle','=',$request->libelle)
            ->Where('centre_id','=',Auth::user()->centre_id)
            ->get();

        $depot = DB::table('magasins')
            ->Where('libelle','=',$request->libelle)
            ->where('centre_id', '=', Auth::user()->centre_id)
            ->get();

        if ($request->magasin_id==null){
            if (count($magasin)==0){
                try {
                    DB::beginTransaction();
                    Magasin::create($form_data);
                    //DB::connection('vps')->table('magasins')->insert($form_data);
                    DB::commit();
                    return response()->json(['success' => 'Magasin cree avec success.']);
                } catch (\Throwable $th) {
                    DB::rollBack();
                    Alert::error('Erreur !', 'Une erreur s\'est produite.');
                }

            }else{
                return response()->json(['error' => 'Le magasin '.$request->libelle.' existe deja dans la base de donnee.']);
            }
        }else{
            if (count($magasin)==0){
                if (count($depot)==0){
                    try {
                        DB::beginTransaction();
                        Magasin::find($request->magasin_id)->update(['libelle'=>$request->libelle,'type'=>$request->type]);
                        //DB::connection('vps')->table('magasins')->where('magasin_id',$request->magasin_id)->update(['libelle'=>$request->libelle,'type'=>$request->type]);
                        DB::commit();
                        return response()->json(['success' => 'Magasin modifie avec success.']);
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        Alert::error('Erreur !', 'Une erreur s\'est produite.');
                    }
                }else{
                    if ($request->type=='Depot_vente'){
                        return response()->json(['error' => 'Un depot existe deja en base de donnee.']);
                    }else{
                        try {
                            DB::beginTransaction();
                            Magasin::find($request->magasin_id)->update(['libelle'=>$request->libelle,'type'=>$request->type]);
                            //DB::connection('vps')->table('magasins')->where('magasin_id',$request->magasin_id)->update(['libelle'=>$request->libelle,'type'=>$request->type]);
                            DB::commit();
                            return response()->json(['success' => 'Magasin modifie avec success.']);
                        } catch (\Throwable $th) {
                            DB::rollBack();
                            Alert::error('Erreur !', 'Une erreur s\'est produite.');
                        }
                    }
                }
            }else{
                if (count($depot)==0){
                    try {
                        DB::beginTransaction();
                        Magasin::find($request->magasin_id)->update(['type'=>$request->type]);
                        //DB::connection('vps')->table('magasins')->where('magasin_id',$request->magasin_id)->update(['type'=>$request->type]);
                        DB::commit();
                        return response()->json(['success' => 'Magasin modifie avec success.']);
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        Alert::error('Erreur !', 'Une erreur s\'est produite.');
                    }
                }else{
                    if ($request->type=='Depot_vente'){
                        return response()->json(['error' => 'Un depot existe deja en base de donnee.']);
                    }else{
                        try {
                        DB::beginTransaction();
                        Magasin::find($request->magasin_id)->update(['type'=>$request->type]);
                        //DB::connection('vps')->table('magasins')->where('magasin_id',$request->magasin_id)->update(['type'=>$request->type]);
                        DB::commit();
                        return response()->json(['success' => 'Magasin modifie avec success.']);
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        Alert::error('Erreur !', 'Une erreur s\'est produite.');
                    }
                    }
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
        $this->authorize('manage-action',['magasin','editer']);
        if(request()->ajax())
        {
            return response()->json(Magasin::findOrFail($id));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $this->authorize('manage-action',['magasin','supprimer']);
        if(request()->ajax())
        {
            try {
                DB::beginTransaction();
                Magasin::findOrfail($id)->update(['statut'=>'false']);
                //DB::connection('vps')->table('magasins')->where('magasin_id',$id)->update(['statut'=>'false']);
                DB::commit();
                return redirect()->route('mag.index')->with('success', 'Le magasin a ete supprime');
            } catch (\Throwable $th) {
                DB::rollBack();
                Alert::error('Erreur !', 'Une erreur s\'est produite.');
            }
        }
    }

    public function stock($id){
        $magasin = Magasin::find($id);
        if($magasin){
            $produits = DB::table('stock_produits')
                ->where('magasin_id','=',$id)
                ->where('etat','<>','Delete')
                ->count('produit_id');

            if ($produits==0){
                Alert::info('Infos','Pas de produits dans le magasin '.$magasin->libelle);
                return redirect()->route('mag.index');
            }else{
                $outpout = $this->stockable($magasin);
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($outpout);
                return $pdf->stream();
            }
        }else{
            Alert::info('Infos','Magsin inexistant.');
            return redirect()->route('mag.index');
        }
    }

    protected function stockable($magasin){
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->select(DB::raw('produits.nom_commercial,produits.prix_achat,produits.prix_vente,sum(stock_produits.qte) as qte,sum(produits.prix_achat*stock_produits.qte) as montachat,sum(produits.prix_vente*stock_produits.qte) as montvente'))
            ->where('stock_produits.magasin_id','=',$magasin->magasin_id)
            ->where('stock_produits.etat','<>','Delete')
            ->where('produits.statut','=','true')
            ->groupBy('produits.nom_commercial','produits.prix_achat','produits.prix_vente')
            ->get();
        //dd($produits);

        $date = date('d-m-Y');

        $centre  = Centre::find('1');
        $achat=0;
        $vente=0;
        $marge=0;

        $output ='<table>
                    <tr>
                        <td width="15%">
                            <img src="../public/images/logo.png" width="100" height="50">
                        </td>
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
                        <td style="text-align: center;">ETAT DU STOCK AU '.$date.' du magasin :<b>'.$magasin->libelle.'</b></td>
                    </tr>
                </table>
                <br>
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                        <tr style="border-radius: 10px; background-color: #E5CC75";>
                            <th width="35%">Libelle</th>
                            <th width="8%">Qte</th>
                            <th width="8%">Prix Achat</th>
                            <th width="12%">Cout Achat</th>
                            <th width="8%">Prix Vente</th>
                            <th width="12%">Cout Vente</th>
                            <th width="9%">Marge</th>
                            <th width="8%">Ecart</th>
                        </tr>
                    </thead>
                    <tbody>';
                foreach($produits as $produit){
                    $achat+=$produit->montachat;
                    $vente+=$produit->montvente;
                    $marge+=$produit->montvente-$produit->montachat;
                    $output .='
                        <tr style="border-collapse: collapse; border: 1px solid;">
                            <td  width="35%" style="border: 1px solid;">'.$produit->nom_commercial.'</td>
                            <td  width="8%" style="border: 1px solid; text-align: right"><b>'.number_format($produit->qte,'0',',',' ').'</b></td>
                            <td  width="8%" style="border: 1px solid; text-align: right"><b>'.number_format($produit->prix_achat,'0',',',' ').'</b></td>
                            <td  width="12%" style="border: 1px solid; text-align: right">'.number_format($produit->montachat,'0',',',' ').'</td>
                            <td  width="8%" style="border: 1px solid; text-align: right"><b>'.number_format($produit->prix_vente,'0',',',' ').'</b></td>
                            <td  width="12%" style="border: 1px solid; text-align: right">'.number_format($produit->montvente,'0',',',' ').'</td>
                            <td  width="9%" style="border: 1px solid; text-align: right"><i>'.number_format($produit->montvente-$produit->montachat,'0',',',' ').'</i></td>
                            <td  width="8%" style="border: 1px solid; text-align: right"></td>
                        </tr>';
                }
                $output .='</tbody>
                   </table><br>
                        <table width="100%">
                            <tr style="border-radius: 10px; background-color: #E5CC75";>
                                <th width="100%" colspan="3">MONTANT PREVISIONNEL</th>
                            </tr>
                            <tr style="border-radius: 10px";>
                                <th width="34%">Cout achat : '.number_format($achat,'0',',',' ').'</th>
                                <th width="33%">Cout vente : '.number_format($vente,'0',',',' ').'</th>
                                <th width="33%">Marge  : '.number_format($marge,'0',',',' ').'</th>
                            </tr>
                        </table>
                                ';
        return $output;
    }

    public function cout_stock($id)
    {
        $meg_mag = DB::table('produits')
            ->select(DB::raw('sum(pdt_pv*pdt_mag) as cout_mag'))
            ->where('magasin_id','=',$id)
            ->where('pdt_etat','=','OK')
            ->first();
        $meg_dep = DB::table('produits')
            ->select(DB::raw('sum(pdt_pv*pdt_dep) as cout_dep'))
            ->where('magasin_id','=',$id)
            ->where('pdt_etat','=','OK')
            ->first();
        $meg_total = $meg_mag->cout_mag+$meg_dep->cout_dep;
        $magasin = magasin::find($id);
        if(request()->ajax())
        {
            return response()->json(['cat'=>$magasin->libelle,'mag' => $meg_mag,'dep' => $meg_dep,'total' => $meg_total]);
        }
    }
}
