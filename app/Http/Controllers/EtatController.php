<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Direction;
use App\Models\Magasin;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EtatController extends Controller
{
    public function stockGlobal(){
        $this->authorize('manage-action',['menu','si']);
        $produits = [];
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->selectRaw('produits.produit_id,produits.reference,produits.nom_commercial,sum(stock_produits.qte) as qte')
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->groupBy('produits.produit_id','produits.reference','produits.nom_commercial')
            ->get();   

        if (request()->ajax()) {
            return datatables()->of($produits)
                ->addColumn('action', function ($produit) {
                    $button = '<button type="button" name="details" id="' . $produit->produit_id . '" class="details btn btn-primary btn-sm"><i class="fa fa-info"></i> Repartition par FS</button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view ('etatglobal.stockglobal', compact('produits'));
    }

    public function details_pdt($produit_id){
        if (\request()->ajax()){
            $qteatot=0;
            $qtetot=0;
            $produits = DB::table('stock_produits')
                ->join('produits','produits.produit_id','=','stock_produits.produit_id')
                ->where('stock_produits.produit_id','=',$produit_id)
                ->where('stock_produits.qte','>',0)
                ->get();
            $pdt = Produit::find($produit_id);
            $output='<table class="table table-striped table-bordered contour_table" id="pdt_selected">
               <thead>
                    <tr>
                        <td colspan="8">DETASILS PRODUIT : '.$pdt->nom_commercial.'</td>
                    </tr>
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <th>Formation Sanitaires</th>
                        <th>Qte en stock</th>
                   </tr>
               </thead>
                <tbody>';
            foreach($produits as $produit){
                $centre = Centre::find($produit->centre_id); 
                $qte = DB::table('stock_produits')
                    ->where('centre_id','=',$produit->centre_id)
                    ->where('produit_id','=',$produit->produit_id)
                    ->sum('qte');
                $qtetot+=$qte;
                $output .='<tr>
                     <td class="cart_title">'.$centre->nom_centre.'</td>
                     <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$qte.'</td>
                 </tr>';
            }
            $output.='<tr>
                    <td colspan="1" class="cart_total" style="color: #95124e;font-weight: bold">TOTAL STOCK</td>
                    <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$qtetot.'</td>
                </tr></body>
                </table>';
            return $output;
        }
    }

    private function rechpdtPQ($produit_id){
        $pdt_con = Produit::find($produit_id);
        $qte = DB::table('stock_produits')
            ->where('stock_produits.etat','<>','Delete')
            ->where('stock_produits.produit_id','=',$produit_id)
            ->where('stock_produits.centre_id','=',Auth::user()->centre_id)
            ->sum('qte');
        $produit = new \stdClass();
       
        $produit->reference = $pdt_con->reference;
        $produit->produit_id = $produit_id;
        $produit->libelle = $pdt_con->nom_commercial;
        $produit->pa = $pdt_con->prix_achat;
        $produit->pv = $pdt_con->prix_vente;
        $produit->qte = $qte;
        $produit->min = $pdt_con->stock_minimal;
        $produit->max = $pdt_con->stock_maximal;
        $produit->cout_pa = $qte*$pdt_con->prix_achat;
        $produit->cout_pv = $qte*$pdt_con->prix_vente;

        return $produit;
    }

    public function print_egstock(){
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->selectRaw('produits.categorie_id,produits.produit_id,produits.reference,produits.nom_commercial,sum(stock_produits.qte) as qte,produits.prix_achat,produits.prix_vente')
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->groupBy('produits.categorie_id','produits.produit_id','produits.reference','produits.nom_commercial')
            ->orderBy('produits.nom_commercial')
            ->get();    

        $categories = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('stock_produits.etat','<>','Delete')
            ->where('produits.statut','=','true')
            ->where('categories.statut','=','true')
            ->where('categories.type','=','Stockable')
            ->select('categories.categorie_id','categories.libelle','categories.type')->distinct()
            ->orderby('categories.libelle')
            ->get();

        $centre  = Centre::find('1');
        $cout_achat=0;
        $cout_totalachat=0;
        $cout_vente=0;
        $cout_totalvente=0;

        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="/images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">ETAT DU TOCK GLOBAL </td>
                </tr>
            </table>';
            foreach($categories as $category){
                $output .=' <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td  width=20%" style="font-size:15px; border: 1px solid; text-align: center">'.$category->libelle.'</td>
                    </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #A2ACC4";>
                                <th style="font-size: 15px;" width="10%">Reference</th>
                                <th style="font-size: 15px;" width="38%">Produit</th>
                                <th style="font-size: 15px;" width="10%">PU Achat</th>
                                <th style="font-size: 15px;" width="10%">PU Vente</th>
                                <th style="font-size: 15px;" width="8%">Qte</th>
                                <th style="font-size: 15px;" width="12%">Cout Achat</th>
                                <th style="font-size: 15px;" width="12%">Cout Vente</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $pdt_cats = [];
                        foreach ($produits as $pdt){
                            if($category->categorie_id == $pdt->categorie_id){
                                array_push($pdt_cats,$pdt);
                            }
                        }
                        foreach($pdt_cats as $produit){
                            $cout_achat += $produit->qte*$produit->prix_achat;
                            $cout_vente += $produit->qte*$produit->prix_vente;

                            $output .='
                               <tr style="border-collapse: collapse; border: 1px solid">
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->nom_commercial.'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_achat,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_vente,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_achat,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_vente,'0','.',' ').'</td>
                               </tr>';
                        }
                        $cout_totalachat+=$cout_achat;
                        $cout_totalvente+=$cout_vente;
                        $output .='<tr>
                            <td colspan="3">Cout d achat de la categorie => '.number_format($cout_achat,'0','.',' ').'</td>
                            <td colspan="4">Cout de vente de la categorie => '.number_format($cout_vente,'0','.',' ').'</td>
                        </tr>
                    </tbody>
                  </table>';
                }
                $output .='<br><table>
                <tr>
                        <td>Cout total d achat => '.number_format($cout_totalachat,'0','.',' ').' / Cout total de vente => '.number_format($cout_totalvente,'0','.',' ').' / Marge  => '.number_format($cout_totalvente-$cout_totalachat,'0','.',' ').'</td>
                    </tr>
              </table>';

        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        //return $pdf->stream();
        return $output;
    }

    public function etatdps(){
        $this->authorize('manage-action',['menu','si']);
        $produits =[];
        $directions = [];
        return view('etatglobal.etatdps',compact('produits','directions'));
    }

    public function getEtatdps($dps_id){
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->selectRaw('produits.produit_id,produits.reference,produits.nom_commercial,sum(stock_produits.qte) as qte')
            ->where('stock_produits.dps_id','=',$dps_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->groupBy('produits.produit_id','produits.reference','produits.nom_commercial')
            ->get();

            if (request()->ajax()) {
                return datatables()->of($produits)
                    ->addColumn('action', function ($produit) {
                        $button = '<button type="button" name="details" id="' . $produit->produit_id . '" class="details btn btn-primary btn-sm"><i class="fa fa-info"></i> Repartition par FS</button>';
                        return $button;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }    

        return datatables()->of($produits)
            ->addColumn('action', function($produit){})
            ->make(true);
    }

    public function print_etatdps($dps_id){
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->selectRaw('produits.categorie_id,produits.produit_id,produits.reference,produits.nom_commercial,sum(stock_produits.qte) as qte,produits.prix_achat,produits.prix_vente')
             ->where('stock_produits.dps_id','=',$dps_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->groupBy('produits.categorie_id','produits.produit_id','produits.reference','produits.nom_commercial')
            ->orderBy('produits.nom_commercial')
            ->get();    

        $categories = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
             ->where('stock_produits.dps_id','=',$dps_id)
            ->where('stock_produits.etat','<>','Delete')
            ->where('produits.statut','=','true')
            ->where('categories.statut','=','true')
            ->where('categories.type','=','Stockable')
            ->select('categories.categorie_id','categories.libelle','categories.type')->distinct()
            ->orderby('categories.libelle')
            ->get();

        $direction  = Direction::find($dps_id);
        $cout_achat=0;
        $cout_totalachat=0;
        $cout_vente=0;
        $cout_totalvente=0;

        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="/images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$direction->dps_nom.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">ETAT DU TOCK GLOBAL </td>
                </tr>
            </table>';
            foreach($categories as $category){
                $output .=' <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td  width=20%" style="font-size:15px; border: 1px solid; text-align: center">'.$category->libelle.'</td>
                    </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #A2ACC4";>
                                <th style="font-size: 15px;" width="10%">Reference</th>
                                <th style="font-size: 15px;" width="38%">Produit</th>
                                <th style="font-size: 15px;" width="10%">PU Achat</th>
                                <th style="font-size: 15px;" width="10%">PU Vente</th>
                                <th style="font-size: 15px;" width="8%">Qte</th>
                                <th style="font-size: 15px;" width="12%">Cout Achat</th>
                                <th style="font-size: 15px;" width="12%">Cout Vente</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $pdt_cats = [];
                        foreach ($produits as $pdt){
                            if($category->categorie_id == $pdt->categorie_id){
                                array_push($pdt_cats,$pdt);
                            }
                        }
                        foreach($pdt_cats as $produit){
                            $cout_achat += $produit->qte*$produit->prix_achat;
                            $cout_vente += $produit->qte*$produit->prix_vente;

                            $output .='
                               <tr style="border-collapse: collapse; border: 1px solid">
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->nom_commercial.'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_achat,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_vente,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_achat,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_vente,'0','.',' ').'</td>
                               </tr>';
                        }
                        $cout_totalachat+=$cout_achat;
                        $cout_totalvente+=$cout_vente;
                        $output .='<tr>
                            <td colspan="3">Cout d achat de la categorie => '.number_format($cout_achat,'0','.',' ').'</td>
                            <td colspan="4">Cout de vente de la categorie => '.number_format($cout_vente,'0','.',' ').'</td>
                        </tr>
                    </tbody>
                  </table>';
                }
                $output .='<br><table>
                <tr>
                        <td>Cout total d achat => '.number_format($cout_totalachat,'0','.',' ').' / Cout total de vente => '.number_format($cout_totalvente,'0','.',' ').' / Marge  => '.number_format($cout_totalvente-$cout_totalachat,'0','.',' ').'</td>
                    </tr>
              </table>';

        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        //return $pdf->stream();
        return $output;
    }

    public function details_pdtdps($dps_id,$produit_id){
        if (\request()->ajax()){
            $qteatot=0;
            $qtetot=0;
            $produits = DB::table('stock_produits')
                ->join('centres','centres.centre_id','=','stock_produits.centre_id')
                ->selectRaw('stock_produits.centre_id,centres.nom_centre,stock_produits.lot,sum(stock_produits.qte) as qte')
                ->where('stock_produits.produit_id','=',$produit_id)
                ->where('stock_produits.dps_id','=',$dps_id)
                ->where('stock_produits.qte','>',0)
                ->groupBy('stock_produits.centre_id','centres.nom_centre','stock_produits.lot')
                ->get();
            $pdt = Produit::find($produit_id);
            $output='<h4>DETAILS PRODUIT : '.$pdt->nom_commercial.'</h4>
                <table class="table table-striped table-bordered" width="100%">
                    <thead>
                        <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                            <th width="50%">Formation Sanitaires</th>
                                <th width="50%">Qte en stock</th>
                        </tr>
                    </thead>
                        <tbody>';
                            foreach($produits as $produit){
                                
                                $qtetot+=$produit->qte;
                                $output .='<tr>
                                    <td class="cart_title">'.$produit->nom_centre.'</td>
                                    <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$produit->qte.'</td>
                                </tr>';
                            }
                        $output.='<tr>
                            <td class="cart_total" style="color: #95124e;font-weight: bold">TOTAL STOCK</td>
                            <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$qtetot.'</td>
                        </tr></body>
                </table>';
            return $output;
        }
    }

    public function etatcentre(){
        $this->authorize('manage-action',['menu','si']);
        $produits =[];
        $centres = [];
        return view('etatglobal.etatcentre',compact('produits','centres'));
    }

    public function directions(){
        if (\request()->ajax()){
            return DB::table('directions')->get();
        }
    }

    public function centres(){
        if (\request()->ajax()){
            $centres = DB::table('centres')
                ->get();
            return $centres;
        }
    }

    public function getEtatcentre($centre_id) {
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->selectRaw('produits.produit_id,produits.reference,produits.nom_commercial,sum(stock_produits.qte) as qte')
            ->where('stock_produits.centre_id','=',$centre_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->groupBy('produits.produit_id','produits.reference','produits.nom_commercial')
            ->get();

            if (request()->ajax()) {
                return datatables()->of($produits)
                    ->addColumn('action', function ($produit) {
                        $button = '<button type="button" name="details" id="' . $produit->produit_id . '" class="details btn btn-primary btn-sm"><i class="fa fa-info"></i> Repartition par FS</button>';
                        return $button;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }    

        return datatables()->of($produits)
            ->addColumn('action', function($produit){})
            ->make(true);
    }

    public function print_etatcentre($centre_id){
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->selectRaw('produits.categorie_id,produits.produit_id,produits.reference,produits.nom_commercial,sum(stock_produits.qte) as qte,produits.prix_achat,produits.prix_vente')
             ->where('stock_produits.centre_id','=',$centre_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->groupBy('produits.categorie_id','produits.produit_id','produits.reference','produits.nom_commercial')
            ->orderBy('produits.nom_commercial')
            ->get();    

        $categories = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
             ->where('stock_produits.centre_id','=',$centre_id)
            ->where('stock_produits.etat','<>','Delete')
            ->where('produits.statut','=','true')
            ->where('categories.statut','=','true')
            ->where('categories.type','=','Stockable')
            ->select('categories.categorie_id','categories.libelle','categories.type')->distinct()
            ->orderby('categories.libelle')
            ->get();

        $centre  = Centre::find($centre_id);
        $cout_achat=0;
        $cout_totalachat=0;
        $cout_vente=0;
        $cout_totalvente=0;

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
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">ETAT DU TOCK GLOBAL </td>
                </tr>
            </table>';
            foreach($categories as $category){
                $output .=' <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td  width=20%" style="font-size:15px; border: 1px solid; text-align: center">'.$category->libelle.'</td>
                    </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #A2ACC4";>
                                <th style="font-size: 15px;" width="10%">Reference</th>
                                <th style="font-size: 15px;" width="38%">Produit</th>
                                <th style="font-size: 15px;" width="10%">PU Achat</th>
                                <th style="font-size: 15px;" width="10%">PU Vente</th>
                                <th style="font-size: 15px;" width="8%">Qte</th>
                                <th style="font-size: 15px;" width="12%">Cout Achat</th>
                                <th style="font-size: 15px;" width="12%">Cout Vente</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $pdt_cats = [];
                        foreach ($produits as $pdt){
                            if($category->categorie_id == $pdt->categorie_id){
                                array_push($pdt_cats,$pdt);
                            }
                        }
                        foreach($pdt_cats as $produit){
                            $cout_achat += $produit->qte*$produit->prix_achat;
                            $cout_vente += $produit->qte*$produit->prix_vente;

                            $output .='
                               <tr style="border-collapse: collapse; border: 1px solid">
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->nom_commercial.'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_achat,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_vente,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_achat,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_vente,'0','.',' ').'</td>
                               </tr>';
                        }
                        $cout_totalachat+=$cout_achat;
                        $cout_totalvente+=$cout_vente;
                        $output .='<tr>
                            <td colspan="3">Cout d achat de la categorie => '.number_format($cout_achat,'0','.',' ').'</td>
                            <td colspan="4">Cout de vente de la categorie => '.number_format($cout_vente,'0','.',' ').'</td>
                        </tr>
                    </tbody>
                  </table>';
                }
                $output .='<br><table>
                <tr>
                        <td>Cout total d achat => '.number_format($cout_totalachat,'0','.',' ').' / Cout total de vente => '.number_format($cout_totalvente,'0','.',' ').' / Marge  => '.number_format($cout_totalvente-$cout_totalachat,'0','.',' ').'</td>
                    </tr>
              </table>';

        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        //return $pdf->stream();
        return $output;
    }

    public function date_per(){
        $this->authorize('manage-action',['menu','si']);
        $lesProduits = [];
        $produits = DB::table('stock_produits')
            ->join('centres','centres.centre_id','=','stock_produits.centre_id')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            //->where('stock_produits.centre_id','=',Auth::user()->centre_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->where('stock_produits.qte','>',0)
            ->orderBy('stock_produits.date_peremption')
            ->get();
        foreach ($produits as $produit){
            $today = date('Y-m-d');
            if ($produit->date_peremption==null){
                $dateExp = $today;
            }else{
                $dateExp = $produit->date_peremption;
            }
            $dateDifference = abs(strtotime($dateExp) - strtotime($today));
            $magasin = Magasin::find($produit->magasin_id);

            $months = floor($dateDifference / (30 * 60 * 60 * 24));
            $pdt = new \stdClass();
            $pdt->id = $produit->stock_produit_id;
            $pdt->produit_id = $produit->produit_id;
            $pdt->lot = $produit->lot;
            $pdt->libelle = $produit->nom_commercial;
            $pdt->pv = $produit->prix_vente;
            $pdt->qte = $produit->qte;
            $pdt->date_peremption = $produit->date_peremption;
            $pdt->mag_lib = $magasin->libelle;
            $pdt->nom_centre = $produit->nom_centre;
            $pdt->mois = $months;
            array_push($lesProduits,$pdt);
        }

        //dd($lesProduits);

        if (request()->ajax()) {
            return datatables()->of($lesProduits)
                ->addColumn('action', function ($produit) {
                    $button = '<button type="button" name="details" id="' . $produit->id . '" class="details btn btn-primary btn-sm"><i class="fa fa-info"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('etatglobal.date_per');
    }

    public function print_date_per(){
        $lesProduits = [];
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('centres','centres.centre_id','=','stock_produits.centre_id')
            //->where('stock_produits.centre_id','=',Auth::user()->centre_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->where('stock_produits.qte','>',0)
            ->orderBy('stock_produits.date_peremption')
            ->get();
        foreach ($produits as $produit){
            $today = date('Y-m-d');
            if ($produit->date_peremption==null){
                $dateExp = $today;
            }else{
                $dateExp = $produit->date_peremption;
            }
            $dateDifference = abs(strtotime($dateExp) - strtotime($today));

            $months = floor($dateDifference / (30 * 60 * 60 * 24));
            $pdt = new \stdClass();
            $pdt->id = $produit->stock_produit_id;
            $pdt->produit_id = $produit->produit_id;
            $pdt->lot = $produit->lot;
            $pdt->libelle = $produit->nom_commercial;
            $pdt->pv = $produit->prix_vente;
            $pdt->qte = $produit->qte;
            $pdt->date_peremption = $produit->date_peremption;
            $pdt->centre = $produit->nom_centre;
            $pdt->mois = $months;
            array_push($lesProduits,$pdt);
        }
        $cout_achat=0;
        $cout_totalachat=0;
        $cout_vente=0;
        $cout_totalvente=0;

        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="/images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">ETAT DU TOCK GLOBAL </td>
                </tr>
            </table>';
            foreach($lesProduits as $produit){
                $cout_achat += $produit->qte*$produit->prix_achat;
                $cout_vente += $produit->qte*$produit->prix_vente;

                $output .='
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                        <td style="font-size:15px; border: 1px solid;">'.$produit->nom_commercial.'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_achat,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_vente,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_achat,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_vente,'0','.',' ').'</td>
                    </tr>';
            }
            $output .='<tr>
                        <td colspan="3">Cout d achat de la categorie => '.number_format($cout_achat,'0','.',' ').'</td>
                        <td colspan="4">Cout de vente de la categorie => '.number_format($cout_vente,'0','.',' ').'</td>
                    </tr>
                <tr>
                    <td>Cout total d achat => '.number_format($cout_totalachat,'0','.',' ').' / Cout total de vente => '.number_format($cout_totalvente,'0','.',' ').' / Marge  => '.number_format($cout_totalvente-$cout_totalachat,'0','.',' ').'</td>
                </tr>
                </tbody>
            </table>';

        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        //return $pdf->stream();
        return $output;
    }

    public function etatcaissesi(Request $request){
        $this->authorize('manage-action',['menu','si']);
        if(!empty($request->from_date) & !empty($request->to_date)){
            $historiques = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($request->from_date, $request->to_date))
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();
        }
        else{
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($debut, date('Y-m-d')))
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        //$centre = Centre::find(Auth::user()->centre_id);
        return view('egsi.etatcaissesi', compact('historiques'));
    }

    protected function print_efsi($debut,$fin){
        $historiques = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->groupBy('produit_ventes.libelle','produit_ventes.pu')
            ->get();

        $vmomt = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            //->where('centre_id','=',Auth::user()->centre_id)
            ->sum('montant_total');

        $vpec = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            //->where('centre_id','=',Auth::user()->centre_id)
            ->sum('prise_en_charge');
        $vnet = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            //->where('centre_id','=',Auth::user()->centre_id)
            ->sum('net_apayer');

        $encaisses = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            //->where('centre_id','=',Auth::user()->centre_id)
            ->sum('montant_paye');

        $reglements = DB::table('reglements')
            ->join('ventes','ventes.vente_id','=','reglements.vente_id')
            //->where('reglements.centre_id','=',Auth::user()->centre_id)
            ->where('reglements.reglement_source', '=','REGLEMENT')
            ->whereBetween('reglements.date_reglement', array($debut, $fin))
            ->get();

        $total = DB::table('reglements')
            //->where('centre_id','=',Auth::user()->centre_id)
            ->whereBetween('date_reglement', array($debut, $fin))
            ->where('reglement_source', '=','REGLEMENT')
            ->sum('montant_reglement');

        $catcon = DB::table('produits')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->join('produit_ventes','produit_ventes.produit_id','=','produits.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            //->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->select('produits.categorie_id','categories.libelle')->distinct()->get();

        $recap_mut = DB::table('ventes')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->selectRaw('assurances.nom,sum(ventes.prise_en_charge) as prise_en_charge')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            //->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->groupBy('assurances.nom')
            ->get();

        $produit_ventes = DB::table('produit_ventes')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            //->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->get();
        $marge = 0;
        $magasin = DB::table('magasins')
            //->where('centre_id','=',Auth::user()->centre_id)
            ->Where('statut','=','true')
            ->where('type','=','Depot_vente')
            ->get();
        //$depot = (object) $magasin[0];
        foreach ($produit_ventes as $con_ven){
            $qp = DB::table('stock_produits')
                //->where('centre_id','=',Auth::user()->centre_id)
                ->where('etat','=','Encours')
                ->where('produit_id','=',$con_ven->produit_id)
                ->get();
            $produit = Produit::find($con_ven->produit_id);    
            if (count($qp)!=0){
                $pdtcon = (object) $qp[0];
                $marge+=($produit->prix_vente-$produit->prix_achat)*$con_ven->qte;
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
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">ETAT FINANCIER DE LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
                </tr>
            </table>';

        foreach($catcon as $categorie){
            $ventes = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();

            $total_cat = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produits.categorie_id, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->groupBy('produits.categorie_id')
                ->get();

            $mont = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->sum('produit_ventes.mont');

            $pec = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->sum('produit_ventes.pec');

            $net = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
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
       // DB::table('usercons')->delete();
        //return response()->json(['data' => $output]);
        return $output;

    }
    
    public function etatcaissedps(Request $request){
        $this->authorize('manage-action',['menu','si']);
        $directions =[];
        if(!empty($request->from_date) & !empty($request->to_date)){
            $historiques = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->join('centres','centres.centre_id','=','ventes.centre_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->where('centres.dps_id','=',$request->dps_id)
                ->whereBetween('ventes.date_vente', array($request->from_date, $request->to_date))
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();
        }
        else{
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->join('centres','centres.centre_id','=','ventes.centre_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->where('centres.dps_id','=',$request->dps_id)
                ->whereBetween('ventes.date_vente', array($debut, date('Y-m-d')))
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        
        return view('egsi.etatcaissedps', compact('historiques','directions'));
    }

    protected function print_efdps($debut,$fin,$dps_id){
        $historiques = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
            ->where('centres.dps_id','=',$dps_id)
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->groupBy('produit_ventes.libelle','produit_ventes.pu')
            ->get();

        $vmomt = DB::table('ventes')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('centres.dps_id','=',$dps_id)
            ->sum('ventes.montant_total');

        $vpec = DB::table('ventes')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('centres.dps_id','=',$dps_id)
            ->sum('ventes.prise_en_charge');
        $vnet = DB::table('ventes')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('centres.dps_id','=',$dps_id)
            ->sum('ventes.net_apayer');

        $encaisses = DB::table('ventes')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('centres.dps_id','=',$dps_id)
            ->sum('ventes.montant_paye');

        $reglements = DB::table('reglements')
            ->join('ventes','ventes.vente_id','=','reglements.vente_id')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->where('centres.dps_id','=',$dps_id)
            ->where('reglements.reglement_source', '=','REGLEMENT')
            ->whereBetween('reglements.date_reglement', array($debut, $fin))
            ->get();

        $total = DB::table('reglements')
            ->join('ventes','ventes.vente_id','=','reglements.vente_id')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->where('centres.dps_id','=',$dps_id)
            ->whereBetween('date_reglement', array($debut, $fin))
            ->where('reglement_source', '=','REGLEMENT')
            ->sum('montant_reglement');

        $catcon = DB::table('produits')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->join('produit_ventes','produit_ventes.produit_id','=','produits.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->where('centres.dps_id','=',$dps_id)
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            //->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->select('produits.categorie_id','categories.libelle')->distinct()->get();

        $recap_mut = DB::table('ventes')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->selectRaw('assurances.nom,sum(ventes.prise_en_charge) as prise_en_charge')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->where('centres.dps_id','=',$dps_id)
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            //->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->groupBy('assurances.nom')
            ->get();

        $produit_ventes = DB::table('produit_ventes')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->where('centres.dps_id','=',$dps_id)
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            //->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->get();
        $marge = 0;
        $magasin = DB::table('magasins')
            //->where('centre_id','=',Auth::user()->centre_id)
            ->Where('statut','=','true')
            ->where('type','=','Depot_vente')
            ->get();
        //$depot = (object) $magasin[0];
        foreach ($produit_ventes as $con_ven){
            $qp = DB::table('stock_produits')
                //->where('centre_id','=',Auth::user()->centre_id)
                ->where('etat','=','Encours')
                ->where('produit_id','=',$con_ven->produit_id)
                ->get();
            $produit = Produit::find($con_ven->produit_id);    
            if (count($qp)!=0){
                $pdtcon = (object) $qp[0];
                $marge+=($produit->prix_vente-$produit->prix_achat)*$con_ven->qte;
            }

        }

        //$centre  = Centre::find(Auth::user()->centre_id);
        $dps = Direction::find($dps_id);
        //dd($centre);
        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="/images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <div style="font-size: 15px;">'.$dps->dps_nom.'</div>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 15px; text-align: center">ETAT FINANCIER DE LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
                </tr>
            </table>';

        foreach($catcon as $categorie){
            $ventes = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->join('centres','centres.centre_id','=','ventes.centre_id')
                ->where('centres.dps_id','=',$dps_id)
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();

            $total_cat = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->join('centres','centres.centre_id','=','ventes.centre_id')
                ->where('centres.dps_id','=',$dps_id)
                ->selectRaw('produits.categorie_id, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->groupBy('produits.categorie_id')
                ->get();

            $mont = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->join('centres','centres.centre_id','=','ventes.centre_id')
                ->where('centres.dps_id','=',$dps_id)
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->sum('produit_ventes.mont');

            $pec = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->join('centres','centres.centre_id','=','ventes.centre_id')
                ->where('centres.dps_id','=',$dps_id)
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->sum('produit_ventes.pec');

            $net = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->join('centres','centres.centre_id','=','ventes.centre_id')
                ->where('centres.dps_id','=',$dps_id)
                ->whereBetween('ventes.date_vente', array($debut, $fin))
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
       // DB::table('usercons')->delete();
        //return response()->json(['data' => $output]);
        return $output;

    }

    public function etatcaissecentre(Request $request){
        $this->authorize('manage-action',['menu','si']);
        $centres =[];
        if(!empty($request->from_date) & !empty($request->to_date)){
            $historiques = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->where('ventes.centre_id','=',$request->centre_id)
                ->whereBetween('ventes.date_vente', array($request->from_date, $request->to_date))
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();
        }
        else{
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->where('ventes.centre_id','=',$request->centre_id)
                ->whereBetween('ventes.date_vente', array($debut, date('Y-m-d')))
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        
        return view('egsi.etatcaissecentre', compact('historiques','centres'));
    }

    protected function print_efcentre($debut,$fin,$centre_id){
        $historiques = DB::table('produit_ventes')
            ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->join('centres','centres.centre_id','=','ventes.centre_id')
            ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
            ->where('ventes.centre_id','=',$centre_id)
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->groupBy('produit_ventes.libelle','produit_ventes.pu')
            ->get();

        $vmomt = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('centre_id','=',$centre_id)
            ->sum('montant_total');

        $vpec = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('centre_id','=',$centre_id)
            ->sum('prise_en_charge');
        $vnet = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('centre_id','=',$centre_id)
            ->sum('net_apayer');

        $encaisses = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, $fin))
            ->where('centre_id','=',$centre_id)
            ->sum('montant_paye');

        $reglements = DB::table('reglements')
            ->join('ventes','ventes.vente_id','=','reglements.vente_id')
            ->where('reglements.centre_id','=',$centre_id)
            ->where('reglements.reglement_source', '=','REGLEMENT')
            ->whereBetween('reglements.date_reglement', array($debut, $fin))
            ->get();

        $total = DB::table('reglements')
            ->where('centre_id','=',$centre_id)
            ->whereBetween('date_reglement', array($debut, $fin))
            ->where('reglement_source', '=','REGLEMENT')
            ->sum('montant_reglement');

        $catcon = DB::table('produits')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->join('produit_ventes','produit_ventes.produit_id','=','produits.produit_id')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',$centre_id)
            ->select('produits.categorie_id','categories.libelle')->distinct()->get();

        $recap_mut = DB::table('ventes')
            ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
            ->selectRaw('assurances.nom,sum(ventes.prise_en_charge) as prise_en_charge')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',$centre_id)
            ->groupBy('assurances.nom')
            ->get();

        $produit_ventes = DB::table('produit_ventes')
            ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
            ->whereBetween('ventes.date_vente', array($debut, $fin))
            ->where('ventes.centre_id','=',$centre_id)
            ->get();
        $marge = 0;
        $magasin = DB::table('magasins')
            ->where('centre_id','=',$centre_id)
            ->Where('statut','=','true')
            ->where('type','=','Depot_vente')
            ->get();
        //$depot = (object) $magasin[0];
        foreach ($produit_ventes as $con_ven){
            $qp = DB::table('stock_produits')
                ->where('centre_id','=',$centre_id)
                ->where('etat','=','Encours')
                ->where('produit_id','=',$con_ven->produit_id)
                ->get();
            $produit = Produit::find($con_ven->produit_id);    
            if (count($qp)!=0){
                $pdtcon = (object) $qp[0];
                $marge+=($produit->prix_vente-$produit->prix_achat)*$con_ven->qte;
            }

        }

        $centre  = Centre::find($centre_id);
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
            </table>';

        foreach($catcon as $categorie){
            $ventes = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->where('ventes.centre_id','=',$centre_id)
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();

            $total_cat = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produits.categorie_id, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->where('ventes.centre_id','=',$centre_id)
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->groupBy('produits.categorie_id')
                ->get();

            $mont = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->where('ventes.centre_id','=',$centre_id)
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->sum('produit_ventes.mont');

            $pec = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->where('ventes.centre_id','=',$centre_id)
                ->whereBetween('ventes.date_vente', array($debut, $fin))
                ->where('produits.categorie_id','=',$categorie->categorie_id)
                ->sum('produit_ventes.pec');

            $net = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->where('ventes.centre_id','=',$centre_id)
                ->whereBetween('ventes.date_vente', array($debut, $fin))
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
       // DB::table('usercons')->delete();
        //return response()->json(['data' => $output]);
        return $output;

    }

    //Etat Stocke et Recette DPS

    public function stockGlobaldps(){
        $this->authorize('manage-action',['menu','dps']);
        //$this->authorize('manage-action',['global','etatstock']);
        $produits = [];
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->selectRaw('produits.produit_id,produits.reference,produits.nom_commercial,sum(stock_produits.qte) as qte')
            ->where('stock_produits.dps_id','=',Auth::user()->dps_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->groupBy('produits.produit_id','produits.reference','produits.nom_commercial')
            ->get();   

        if (request()->ajax()) {
            return datatables()->of($produits)
                ->addColumn('action', function ($produit) {
                    $button = '<button type="button" name="details" id="' . $produit->produit_id . '" class="details btn btn-primary btn-sm"><i class="fa fa-info"></i> Repartition par FS</button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view ('etatglobal.stockglobaldps', compact('produits'));
    }

    public function details_pdt_dps($produit_id){
        if (\request()->ajax()){
            $qteatot=0;
            $qtetot=0;
            $produits = DB::table('stock_produits')
                ->join('produits','produits.produit_id','=','stock_produits.produit_id')
                ->where('stock_produits.dps_id','=',Auth::user()->dps_id)
                ->where('stock_produits.produit_id','=',$produit_id)
                ->where('stock_produits.qte','>',0)
                ->get();
            $pdt = Produit::find($produit_id);
            $output='<table class="table table-striped table-bordered contour_table" id="pdt_selected">
               <thead>
                    <tr>
                        <td colspan="8">DETASILS PRODUIT : '.$pdt->nom_commercial.'</td>
                    </tr>
                   <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                       <th>Formation Sanitaires</th>
                        <th>Qte en stock</th>
                   </tr>
               </thead>
                <tbody>';
            foreach($produits as $produit){
                $centre = Centre::find($produit->centre_id); 
                $qte = DB::table('stock_produits')
                    ->where('centre_id','=',$produit->centre_id)
                    ->where('produit_id','=',$produit->produit_id)
                    ->sum('qte');
                $qtetot+=$qte;
                $output .='<tr>
                     <td class="cart_title">'.$centre->nom_centre.'</td>
                     <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$qte.'</td>
                 </tr>';
            }
            $output.='<tr>
                    <td colspan="1" class="cart_total" style="color: #95124e;font-weight: bold">TOTAL STOCK</td>
                    <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$qtetot.'</td>
                </tr></body>
                </table>';
            return $output;
        }
    }

    public function print_egstockdps(){
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->selectRaw('produits.categorie_id,produits.produit_id,produits.reference,produits.nom_commercial,sum(stock_produits.qte) as qte,produits.prix_achat,produits.prix_vente')
            ->where('stock_produits.dps_id','=',Auth::user()->dps_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->groupBy('produits.categorie_id','produits.produit_id','produits.reference','produits.nom_commercial')
            ->orderBy('produits.nom_commercial')
            ->get();    

        $categories = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('stock_produits.dps_id','=',Auth::user()->dps_id)
            ->where('stock_produits.etat','<>','Delete')
            ->where('produits.statut','=','true')
            ->where('categories.statut','=','true')
            ->where('categories.type','=','Stockable')
            ->select('categories.categorie_id','categories.libelle','categories.type')->distinct()
            ->orderby('categories.libelle')
            ->get();

        $centre  = Direction::find(Auth::user()->dps_id);
        $cout_achat=0;
        $cout_totalachat=0;
        $cout_vente=0;
        $cout_totalvente=0;

        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="/images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        <p>'.$centre->dps_nom.'</p>
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">ETAT DU TOCK GLOBAL </td>
                </tr>
            </table>';
            foreach($categories as $category){
                $output .=' <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td  width=20%" style="font-size:15px; border: 1px solid; text-align: center">'.$category->libelle.'</td>
                    </tr>
                    </table>
                    <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="border-radius: 15px; background-color: #A2ACC4";>
                                <th style="font-size: 15px;" width="10%">Reference</th>
                                <th style="font-size: 15px;" width="38%">Produit</th>
                                <th style="font-size: 15px;" width="10%">PU Achat</th>
                                <th style="font-size: 15px;" width="10%">PU Vente</th>
                                <th style="font-size: 15px;" width="8%">Qte</th>
                                <th style="font-size: 15px;" width="12%">Cout Achat</th>
                                <th style="font-size: 15px;" width="12%">Cout Vente</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $pdt_cats = [];
                        foreach ($produits as $pdt){
                            if($category->categorie_id == $pdt->categorie_id){
                                array_push($pdt_cats,$pdt);
                            }
                        }
                        foreach($pdt_cats as $produit){
                            $cout_achat += $produit->qte*$produit->prix_achat;
                            $cout_vente += $produit->qte*$produit->prix_vente;

                            $output .='
                               <tr style="border-collapse: collapse; border: 1px solid">
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->nom_commercial.'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_achat,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_vente,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_achat,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_vente,'0','.',' ').'</td>
                               </tr>';
                        }
                        $cout_totalachat+=$cout_achat;
                        $cout_totalvente+=$cout_vente;
                        $output .='<tr>
                            <td colspan="3">Cout d achat de la categorie => '.number_format($cout_achat,'0','.',' ').'</td>
                            <td colspan="4">Cout de vente de la categorie => '.number_format($cout_vente,'0','.',' ').'</td>
                        </tr>
                    </tbody>
                  </table>';
                }
                $output .='<br><table>
                <tr>
                        <td>Cout total d achat => '.number_format($cout_totalachat,'0','.',' ').' / Cout total de vente => '.number_format($cout_totalvente,'0','.',' ').' / Marge  => '.number_format($cout_totalvente-$cout_totalachat,'0','.',' ').'</td>
                    </tr>
              </table>';

        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        //return $pdf->stream();
        return $output;
    }

    public function etatcentredps(){
        $this->authorize('manage-action',['menu','dps']);
        $produits =[];
        $centres = [];
        return view('etatglobal.etatcentredps',compact('produits','centres'));
    }

    public function centresdps(){
        if (\request()->ajax()){
            $centres = DB::table('centres')
                ->where('dps_id','=',Auth::user()->dps_id)
                ->get();
            return $centres;
        }
    }

    public function date_perdps(){
        $this->authorize('manage-action',['menu','dps']);
        $lesProduits = [];
        $produits = DB::table('stock_produits')
            ->join('centres','centres.centre_id','=','stock_produits.centre_id')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('stock_produits.dps_id','=',Auth::user()->dps_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->where('stock_produits.qte','>',0)
            ->orderBy('stock_produits.date_peremption')
            ->get();
        foreach ($produits as $produit){
            $today = date('Y-m-d');
            if ($produit->date_peremption==null){
                $dateExp = $today;
            }else{
                $dateExp = $produit->date_peremption;
            }
            $dateDifference = abs(strtotime($dateExp) - strtotime($today));
            $magasin = Magasin::find($produit->magasin_id);

            $months = floor($dateDifference / (30 * 60 * 60 * 24));
            $pdt = new \stdClass();
            $pdt->id = $produit->stock_produit_id;
            $pdt->produit_id = $produit->produit_id;
            $pdt->lot = $produit->lot;
            $pdt->libelle = $produit->nom_commercial;
            $pdt->pv = $produit->prix_vente;
            $pdt->qte = $produit->qte;
            $pdt->date_peremption = $produit->date_peremption;
            $pdt->mag_lib = $magasin->libelle;
            $pdt->nom_centre = $produit->nom_centre;
            $pdt->mois = $months;
            array_push($lesProduits,$pdt);
        }

        //dd($lesProduits);

        if (request()->ajax()) {
            return datatables()->of($lesProduits)
                ->addColumn('action', function ($produit) {
                    $button = '<button type="button" name="details" id="' . $produit->id . '" class="details btn btn-primary btn-sm"><i class="fa fa-info"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('etatglobal.date_perdps');
    }

    public function print_date_perdps(){
        $lesProduits = [];
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('centres','centres.centre_id','=','stock_produits.centre_id')
            ->where('stock_produits.dps_id','=',Auth::user()->dps_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->where('stock_produits.qte','>',0)
            ->orderBy('stock_produits.date_peremption')
            ->get();
        foreach ($produits as $produit){
            $today = date('Y-m-d');
            if ($produit->date_peremption==null){
                $dateExp = $today;
            }else{
                $dateExp = $produit->date_peremption;
            }
            $dateDifference = abs(strtotime($dateExp) - strtotime($today));

            $months = floor($dateDifference / (30 * 60 * 60 * 24));
            $pdt = new \stdClass();
            $pdt->id = $produit->stock_produit_id;
            $pdt->produit_id = $produit->produit_id;
            $pdt->lot = $produit->lot;
            $pdt->libelle = $produit->nom_commercial;
            $pdt->pv = $produit->prix_vente;
            $pdt->qte = $produit->qte;
            $pdt->date_peremption = $produit->date_peremption;
            $pdt->centre = $produit->nom_centre;
            $pdt->mois = $months;
            array_push($lesProduits,$pdt);
        }
        $cout_achat=0;
        $cout_totalachat=0;
        $cout_vente=0;
        $cout_totalvente=0;
        $directio = Direction::find(Auth::user()->dps_id);

        $output ='
            <table>
                <tr>
                    <td width="15%">
                        <img src="/images/logo.png" width="80" height="60">
                    </td>
                    <td width="85%">
                        '.$directon->dps_nom.'
                    </td>
                </tr>
            </table>
            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">ETAT DU TOCK GLOBAL </td>
                </tr>
            </table>';
            foreach($lesProduits as $produit){
                $cout_achat += $produit->qte*$produit->prix_achat;
                $cout_vente += $produit->qte*$produit->prix_vente;

                $output .='
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                        <td style="font-size:15px; border: 1px solid;">'.$produit->nom_commercial.'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_achat,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->prix_vente,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_achat,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte*$produit->prix_vente,'0','.',' ').'</td>
                    </tr>';
            }
            $output .='<tr>
                        <td colspan="3">Cout d achat de la categorie => '.number_format($cout_achat,'0','.',' ').'</td>
                        <td colspan="4">Cout de vente de la categorie => '.number_format($cout_vente,'0','.',' ').'</td>
                    </tr>
                <tr>
                    <td>Cout total d achat => '.number_format($cout_totalachat,'0','.',' ').' / Cout total de vente => '.number_format($cout_totalvente,'0','.',' ').' / Marge  => '.number_format($cout_totalvente-$cout_totalachat,'0','.',' ').'</td>
                </tr>
                </tbody>
            </table>';

        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        //return $pdf->stream();
        return $output;
    }

    // protected function print_efdps($debut,$fin){
    //     $historiques = DB::table('produit_ventes')
    //         ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
    //         ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
    //         ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
    //         ->whereBetween('ventes.date_vente', array($debut, $fin))
    //         ->where('ventes.dps_id','=',Auth::user()->dps_id)
    //         ->groupBy('produit_ventes.libelle','produit_ventes.pu')
    //         ->get();

    //     $vmomt = DB::table('ventes')
    //         ->whereBetween('date_vente', array($debut, $fin))
    //         ->where('dps_id','=',Auth::user()->dps_id)
    //         ->sum('montant_total');

    //     $vpec = DB::table('ventes')
    //         ->whereBetween('date_vente', array($debut, $fin))
    //         ->where('dps_id','=',Auth::user()->dps_id)
    //         ->sum('prise_en_charge');
    //     $vnet = DB::table('ventes')
    //         ->whereBetween('date_vente', array($debut, $fin))
    //         ->where('dps_id','=',Auth::user()->dps_id)
    //         ->sum('net_apayer');

    //     $encaisses = DB::table('ventes')
    //         ->whereBetween('date_vente', array($debut, $fin))
    //         ->where('dps_id','=',Auth::user()->dps_id)
    //         ->sum('montant_paye');

    //     $reglements = DB::table('reglements')
    //         ->join('ventes','ventes.vente_id','=','reglements.vente_id')
    //         ->where('dps_id','=',Auth::user()->dps_id)
    //         ->where('reglements.reglement_source', '=','REGLEMENT')
    //         ->whereBetween('reglements.date_reglement', array($debut, $fin))
    //         ->get();

    //     $total = DB::table('reglements')
    //     ->where('dps_id','=',Auth::user()->dps_id)
    //         ->whereBetween('date_reglement', array($debut, $fin))
    //         ->where('reglement_source', '=','REGLEMENT')
    //         ->sum('montant_reglement');

    //     $catcon = DB::table('produits')
    //         ->join('categories','categories.categorie_id','=','produits.categorie_id')
    //         ->join('produit_ventes','produit_ventes.produit_id','=','produits.produit_id')
    //         ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
    //         ->whereBetween('ventes.date_vente', array($debut, $fin))
    //         ->where('ventes.dps_id','=',Auth::user()->dps_id)
    //         ->select('produits.categorie_id','categories.libelle')->distinct()->get();

    //     $recap_mut = DB::table('ventes')
    //         ->join('assurances','assurances.assurance_id','=','ventes.assurance_id')
    //         ->selectRaw('assurances.nom,sum(ventes.prise_en_charge) as prise_en_charge')
    //         ->whereBetween('ventes.date_vente', array($debut, $fin))
    //         ->where('ventes.dps_id','=',Auth::user()->dps_id)
    //         ->groupBy('assurances.nom')
    //         ->get();

    //     $produit_ventes = DB::table('produit_ventes')
    //         ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
    //         ->whereBetween('ventes.date_vente', array($debut, $fin))
    //         ->where('ventes.dps_id','=',Auth::user()->dps_id)
    //         ->get();
    //     $marge = 0;
    //     $magasin = DB::table('magasins')
    //         //->where('centre_id','=',Auth::user()->centre_id)
    //         ->Where('statut','=','true')
    //         ->where('type','=','Depot_vente')
    //         ->get();
    //     //$depot = (object) $magasin[0];
    //     foreach ($produit_ventes as $con_ven){
    //         $qp = DB::table('stock_produits')
    //             ->where('dps_id','=',Auth::user()->dps_id)
    //             ->where('etat','=','Encours')
    //             ->where('produit_id','=',$con_ven->produit_id)
    //             ->get();
    //         $produit = Produit::find($con_ven->produit_id);    
    //         if (count($qp)!=0){
    //             $pdtcon = (object) $qp[0];
    //             $marge+=($produit->prix_vente-$produit->prix_achat)*$con_ven->qte;
    //         }

    //     }

    //     $direction  = Direction::find(Auth::user()->dps_id);
    //     //dd($centre);
    //     $output ='
    //         <table>
    //             <tr>
    //                 <td width="15%">
    //                     <img src="/images/logo.png" width="80" height="60">
    //                 </td>
    //                 <td width="85%">
    //                     <div style="font-size: 15px;">'.$direction->dps_nom.'</div>
    //                 </td>
    //             </tr>
    //         </table>
    //         <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
    //             <tr>
    //                 <td width="100%" style="font-size: 15px; text-align: center">ETAT FINANCIER DE LA PERIODE DU : <b>'.$debut.'</b>  AU <b>'.$fin.'</b> </td>
    //             </tr>
    //         </table>';

    //     foreach($catcon as $categorie){
    //         $ventes = DB::table('produit_ventes')
    //             ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
    //             ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
    //             ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
    //             ->whereBetween('ventes.date_vente', array($debut, $fin))
    //             ->where('produits.categorie_id','=',$categorie->categorie_id)
    //             ->where('ventes.dps_id','=',Auth::user()->dps_id)
    //             ->groupBy('produit_ventes.libelle','produit_ventes.pu')
    //             ->get();

    //         $total_cat = DB::table('produit_ventes')
    //             ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
    //             ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
    //             ->selectRaw('produits.categorie_id, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
    //             ->whereBetween('ventes.date_vente', array($debut, $fin))
    //             ->where('ventes.dps_id','=',Auth::user()->dps_id)
    //             ->where('produits.categorie_id','=',$categorie->categorie_id)
    //             ->groupBy('produits.categorie_id')
    //             ->get();

    //         $mont = DB::table('produit_ventes')
    //             ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
    //             ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
    //             ->whereBetween('ventes.date_vente', array($debut, $fin))
    //             ->where('produits.categorie_id','=',$categorie->categorie_id)
    //             ->sum('produit_ventes.mont');

    //         $pec = DB::table('produit_ventes')
    //             ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
    //             ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
    //             ->whereBetween('ventes.date_vente', array($debut, $fin))
    //             ->where('produits.categorie_id','=',$categorie->categorie_id)
    //             ->sum('produit_ventes.pec');

    //         $net = DB::table('produit_ventes')
    //             ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
    //             ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
    //             ->whereBetween('ventes.date_vente', array($debut, $fin))
    //             ->where('produits.categorie_id','=',$categorie->categorie_id)
    //             ->sum('produit_ventes.net');


    //         $output .='
    //                 <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">

    //                         <tr style="border-radius: 15px; background-color: #27a5de";>
    //                             <th style="font-size: 15px;" width="50%">'.$categorie->libelle.'</th>
    //                         </tr>
    //                 </table>
    //                 <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
    //                     <thead>
    //                         <tr style="border-radius: 15px; background-color: #d1d73f";>
    //                             <th style="font-size: 15px;" width="35%">Produit</th>
    //                             <th style="font-size: 15px;" width="12%">P U</th>
    //                             <th style="font-size: 15px;" width="10%">Qte</th>
    //                             <th style="font-size: 15px;" width="14%">Montant</th>
    //                             <th style="font-size: 15px;" width="14%">Prise en charge</th>
    //                             <th style="font-size: 15px;" width="15%">Part du patient</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>';

    //                 foreach($ventes as $produit){
    //                     $output .='
    //                        <tr style="border-collapse: collapse; border: 1px solid">
    //                            <td  width="35%" style="font-size:15px; border: 1px solid;">'.$produit->libelle.'</td>
    //                            <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pu,'0','.',' ').'</td>
    //                            <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
    //                            <td  width="14%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->mont,'0','.',' ').'</td>
    //                            <td  width="14%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pec,'0','.',' ').'</td>
    //                            <td  width="15%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->net,'0','.',' ').'</td>
    //                        </tr>';
    //                 }
    //                 $output .='<tr style="border-collapse: collapse; border: 1px solid; background-color: #C5C8CE">
    //                        <td colspan="3"  width="35%" style="font-size:15px; border: 1px solid"><b>Total Categorie</b></td>
    //                        <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($mont,'0','.',' ').'</b></td>
    //                        <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($pec,'0','.',' ').'</b></td>
    //                        <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>'.number_format($net,'0','.',' ').'</b></td>
    //                    </tr>
    //                    </tbody>
    //                 </table>';
    //     }
    //     $output .='

    //         <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
    //             <tr>
    //                 <td width="33%" style="font-size: 17px;">Recette Totale : <b>'.number_format($vmomt,'0','.',' ').'</b> </td>
    //                 <td width="33%" style="font-size: 17px;">Prise en  charge : <b>'.number_format($vpec,'0','.',' ').'</b> </td>
    //                 <td width="33%" style="font-size: 17px;">Recette net : <b>'.number_format($vnet,'0','.',' ').'</b> </td>
    //             </tr>
    //             <tr>
    //                 <td width="33%" style="font-size: 17px; color: #0b304e">Recette net vendue : <b>'.number_format($vnet,'0','.',' ').'</b> </td>
    //                 <td width="33%" style="font-size: 17px; color: rgba(21,168,10,0.92)">Encaisse : <b>'.number_format($encaisses,'0','.',' ').'</b> </td>
    //                 <td width="33%" style="font-size: 17px; color: #95124e">Total Credit : <b>'.number_format($vnet-$encaisses,'0','.',' ').'</b> </td>
    //             </tr>
    //         </table>
    //         <p></p>
    //         <p></p>
    //         <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
    //             <thead>
    //                 <tr style="border-radius: 15px;";>
    //                     <th colspan="2" style="font-size: 15px;" width="100%">ETAT DES ASSURANCES</th>
    //                 </tr>
    //             </thead>
    //             <tr style="border-radius: 15px; background-color: #d1d73f";>
    //                     <th style="font-size: 15px;" width="55%">ASSURANCE / assurance</th>
    //                     <th style="font-size: 15px;" width="45%">MONTANT</th>
    //                 </tr>
    //             <tbody>';

    //         foreach($recap_mut as $assurance){
    //             $output .='
    //                <tr style="border-collapse: collapse; border: 1px solid">
    //                    <td  width="55%" style="font-size:15px; border: 1px solid;">'.$assurance->nom.'</td>
    //                    <td  width="45%" style="font-size:15px; border: 1px solid; text-align: right">'.number_format($assurance->prise_en_charge,'0','.',' ').'</td>
    //                </tr>';
    //         }
    //         $output.='<tr style="border-radius: 15px";>
    //                 <th style="font-size: 15px;" width="55%">TOTAL</th>
    //                 <th style="font-size: 15px;" width="45%; text-align: right">'.number_format($vpec,'0','.',' ').'</th>
    //             </tr></body>
    //         </table>';

    //         $output.='
    //             <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
    //               <tr>
    //                   <td width="100%" style="font-size: 15px; text-align: center"> REGLEMENTS CREDITS DE LA PERIODE</td>
    //               </tr>
    //             </table>
    //             <br>
    //             <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
    //                 <thead>
    //                   <tr style="border-radius: 12px; border: 1px solid";>
    //                       <th style="font-size:15px; border: 1px solid; text-align: left" width="11%">Date Reg</th>
    //                       <th style="font-size:15px; border: 1px solid; text-align: left" width="21%">Patient</th>
    //                       <th style="font-size:15px; border: 1px solid; text-align: left" width="12%">Vente Num</th>
    //                       <th style="font-size:15px; border: 1px solid; text-align: left" width="11%">Date Vente</th>
    //                       <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Montant Vente</th>
    //                       <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Montant Reg</th>
    //                       <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Total Reg</th>
    //                       <th style="font-size:15px; border: 1px solid; text-align: left" width="10%">Reste a payer</th>
    //                   </tr>
    //                 </thead>
    //                 <tbody></tbody>';
    //     foreach ($reglements as $reglement){
    //         $total_reg = DB::table('reglements')
    //             ->where('code','=',$reglement->vente_id)
    //             ->sum('montant_reglement');
    //         $reste = $reglement->net_apayer-$total_reg;
    //         $output.='<tr style="border-radius: 12px";>
    //           <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->date_reglement.'</td>
    //           <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->patient_id.'</td>
    //           <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->vente_id.'</td>
    //           <td style="font-size:15px; border: 1px solid; text-align: left">'.$reglement->date_vente.'</td>
    //           <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reglement->net_apayer,'0','.',' ').'</td>
    //           <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reglement->montant_reglement,'0','.',' ').'</td>
    //           <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($total_reg,'0','.',' ').'</td>
    //           <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($reste,'0','.',' ').'</td>
    //         </tr>';
    //     }
    //     $output.='
    //         <tr style="border-radius: 5px; background-color: #27a5de";>
    //           <td colspan="3" style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL REGLE</td>
    //           <td colspan="5" style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total,'0','.',' ').'</td>
    //         </tr>
    //         </body>
    //     </table>
    //     <br><br>
    //     <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
    //        <tr>
    //           <tr style="border-radius: 5px; background-color: #27a5de";>
    //               <td style="font-weight: bold; color: #0a3650; text-align: center">MONTANT TOTAL ENCAISSE</td>
    //               <td style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($total+$encaisses,'0','.',' ').' Franc CFA</td>
    //               <td style="font-weight: bold; color: #0a3650; text-align: center">BENEFICE SUR LES VENTES</td>
    //               <td style="font-weight: bold; color: #0a3650; text-align: center">'.number_format($marge,'0','.',' ').' Franc CFA</td>
    //         </tr>
    //     </table> ';
    //    // DB::table('usercons')->delete();
    //     //return response()->json(['data' => $output]);
    //     return $output;

    // }

    public function etatcaissecentredps(Request $request){
        $this->authorize('manage-action',['menu','si']);
        $centres =[];
        if(!empty($request->from_date) & !empty($request->to_date)){
            $historiques = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->where('ventes.centre_id','=',$request->centre_id)
                ->whereBetween('ventes.date_vente', array($request->from_date, $request->to_date))
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();
        }
        else{
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('produit_ventes')
                ->join('produits','produits.produit_id','=','produit_ventes.produit_id')
                ->join('ventes','ventes.vente_id','=','produit_ventes.vente_id')
                ->selectRaw('produit_ventes.libelle,produit_ventes.pu,sum(produit_ventes.qte) as qte, sum(produit_ventes.mont) as mont, sum(produit_ventes.pec) as pec, sum(produit_ventes.net) as net')
                ->where('ventes.centre_id','=',$request->centre_id)
                ->whereBetween('ventes.date_vente', array($debut, date('Y-m-d')))
                ->groupBy('produit_ventes.libelle','produit_ventes.pu')
                ->get();
        }
        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        
        return view('egsi.etatcaissecentre', compact('historiques','centres'));
    }

    protected function print_efcentredps($debut,$fin,$centre_id){
}
}
