<?php

namespace App\Http\Controllers;

use App\Models\ProduitQG;
use App\Models\Centre;
use App\Models\Categorie;
use App\Models\Magasin;
use App\Models\Produit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;

class InventaireSIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function etatglobal(Request $request){
        $this->authorize('manage-action',['global','stockglobal']);
        $produits = [];
        $allProduits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('categories.type','=','Stockable')
            //->where('stock_produits.centre_id','=',Auth::user()->centre_id)
            ->where('produits.statut','=','true')
            ->select('produits.produit_id')->distinct()
            ->orderby('produits.nom_commercial')
            ->get();   

        foreach ($allProduits as $pdt){
            array_push($produits,$this->rechpdt($pdt->produit_id));
        }
        //dd($produits);

        if (request()->ajax()) {
            return datatables()->of($produits)
                ->addColumn('action', function ($produit) {
                    $button = '<button type="button" name="details" id="' . $produit->produit_id . '" class="details btn btn-primary btn-sm"><i class="fa fa-info"></i> Repartition</button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view ('inventairesi.etatglobal', compact('produits'));
    }

    private function rechpdt($produit_id){
        $pdt_con = Produit::find($produit_id);
        $qte = DB::table('stock_produits')
            ->where('stock_produits.etat','<>','Delete')
            ->where('stock_produits.produit_id','=',$produit_id)
            //->where('stock_produits.centre_id','=',Auth::user()->centre_id)
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
        $produit->categorie_id = $pdt_con->categorie_id;

        return $produit;
    }

    public function centres(){
        if (\request()->ajax()){
            $centres = DB::table('centres')
                ->get();
            return $centres;
        }
    }

    public function etatproduitcentre($centre_id)
    {
        $produits = [];
        $allProduits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('stock_produits.etat','<>','Delete')
            ->where('categories.type','=','Stockable')
            ->where('stock_produits.centre_id','=',$centre_id)
            ->where('stock_produits.qte','>=','0')
            ->where('produits.statut','=','true')
            ->select('produits.produit_id')->distinct()
            //->orderby('produits.nom_commercial')
            ->get();

        //dd($allProduits);
        if (count($allProduits)>0) {
            foreach ($allProduits as $pdt) {
                $produit = $this->rechpdtPdtCentre($pdt->produit_id, $centre_id);
                if ($produit != null) {
                    array_push($produits, $produit);
                }
            }
        }
        return datatables()->of($produits)
            ->addColumn('action', function($produit){})
            ->make(true);
    }

    public function details_pdt($produit_id){
        if (\request()->ajax()){
            $qteatot=0;
            $qtetot=0;
            $produits = DB::table('stock_produits')
                ->join('produits','produits.produit_id','=','stock_produits.produit_id')
                //->where('stock_produits.centre_id','=',Auth::user()->centre_id)
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
                       <th>Magasin</th>
                       <th>Lot</th>
                        <th>PU Achat</th>
                        <th>PU vente</th>
                        <th>Qte achetee</th>
                        <th>Qte en stock</th>
                        <th>Expire le</th>
                   </tr>
               </thead>
                <tbody>';
            foreach($produits as $produit){
                $magasin = Magasin::find($produit->magasin_id);
                $qteatot+=$produit->qtea;
                $qtetot+=$produit->qte;
                $output .='<tr>
                     <td class="cart_title">'.$magasin->libelle.'</td>
                     <td class="cart_title">'.$produit->lot.'</td>
                     <td class="cart_price">'.$produit->prix_achat.'</td>
                     <td class="cart_total">'.$produit->prix_vente.'</td>
                     <td class="cart_total" style="color: #95124e;font-weight: bold">'.$produit->qtea.'</td>
                     <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$produit->qte.'</td>
                     <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$produit->date_peremption.'</td>
                 </tr>';
            }
            $output.='<tr>
                    <td colspan="4" class="cart_total" style="color: #95124e;font-weight: bold">TOTAL STOCK</td>
                    <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$qteatot.'</td>
                    <td class="cart_total" style="color: #0b2e13;font-weight: bold">'.$qtetot.'</td>
                </tr></body>
                </table>';
            return $output;
        }
    }

    public function print_etatglobal(){
        $produits = [];
        $allProduits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->where('stock_produits.etat','<>','Delete')
            ->where('produits.statut','=','true')
            ->select('produits.produit_id')->distinct()
            ->orderby('produits.nom_commercial')
            ->orderby('produits.nom_commercial')
            ->get();

        foreach ($allProduits as $pdt){
            array_push($produits,$this->rechpdt($pdt->produit_id));
        }
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

        $centre  = Centre::find(Auth::user()->centre_id);
        $cout_achat=0;
        $cout_totalachat=0;
        $cout_vente=0;
        $cout_totalvente=0;
        //dd($produits,$categories);

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
                            $cout_achat += $produit->cout_pa;
                            $cout_vente += $produit->cout_pv;

                            $output .='
                               <tr style="border-collapse: collapse; border: 1px solid">
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                                   <td style="font-size:15px; border: 1px solid;">'.$produit->libelle.'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pa,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pv,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->cout_pa,'0','.',' ').'</td>
                                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->cout_pv,'0','.',' ').'</td>
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

    public function print_etatmagasin($magasin_id){
        $produits = [];
        $magasin  = Magasin::find($magasin_id);
        $allProduits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('categories.type','=','Stockable')
            ->where('stock_produits.magasin_id','=',$magasin_id)
            ->where('stock_produits.qte','>=','0')
            ->where('produits.statut','=','true')
            ->select('produits.produit_id')->distinct()
            ->get();

        foreach ($allProduits as $pdt){
            $produit = $this->rechpdtPQMagasin($pdt->produit_id,$magasin_id);
            if ($produit!=null){
                array_push($produits,$produit);
            }
        }

        $categories = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
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
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">ETAT DU TOCK DU MAGASIN : '.$magasin->mag_lib.' </td>
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
                                <th style="font-size: 15px;" width="30%">Produit</th>
                                <th style="font-size: 15px;" width="10%">PU Achat</th>
                                <th style="font-size: 15px;" width="10%">PU Vente</th>
                                <th style="font-size: 15px;" width="8%">Qte</th>
                                <th style="font-size: 15px;" width="16%">Cout Achat</th>
                                <th style="font-size: 15px;" width="16%">Cout Vente</th>
                            </tr>
                        </thead>
                        <tbody>';
            foreach($produits as $produit){
                $cout_achat += $produit->cout_pa;
                $cout_vente += $produit->cout_pv;

                $output .='
                                   <tr style="border-collapse: collapse; border: 1px solid">
                                       <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                                       <td style="font-size:15px; border: 1px solid;">'.$produit->libelle.'</td>
                                       <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pa,'0','.',' ').'</td>
                                       <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pv,'0','.',' ').'</td>
                                       <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte,'0','.',' ').'</td>
                                       <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->cout_pa,'0','.',' ').'</td>
                                       <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->cout_pv,'0','.',' ').'</td>
                                   </tr>';
            }
            $cout_totalachat+=$cout_achat;
            $cout_totalvente+=$cout_vente;
            $output .='<tr>
                                <td colspan="3">Cout d achat de la categorie => '.number_format($cout_achat,'0','.',' ').'</td>
                                <td colspan="4">Cout de vente de la categorie => '.number_format($cout_vente,'0','.',' ').'</td>
                            </tr>
                        </tbody>
                      </table>
                      <div style="page-break-after: always"></div>';
        }
        $output .='<table>
                <tr>
                        <td>Cout total d achat => '.number_format($cout_achat,'0','.',' ').' / Cout total de vente => '.number_format($cout_vente,'0','.',' ').'</td>
                    </tr>
              </table>';

        //$pdf = \App::make('dompdf.wrapper');
        //$pdf->loadHTML($output);
        return $output;
    }

    public function magasin()
    {
        $produits = [];
        $magasins = [];
        return view ('inventaire.etatmagasin', compact('produits','magasins'));
    }

    public function magasins(){
        if (\request()->ajax()){
            $magasins = DB::table('magasins')
                ->where('centre_id','=',Auth::user()->centre_id)
                ->where('statut','=','true')
                ->get();
            return $magasins;
        }
    }
    private function rechpdtPQ($centre_id,$produit_id){
        $pdtqp = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->where('stock_produits.etat','<>','Delete')
            ->where('stock_produits.centre_id','=',$centre_id)
            ->where('stock_produits.produit_id','=',$produit_id)
            ->where('produits.statut','=','true')
            //->where('stock_produits.qte','>','0')
            ->get();
        $produit = new \stdClass();
        $pdt_con = Produit::find($produit_id);
        if(count($pdtqp)>0){
            $pdt = (object) $pdtqp[0];
            $produit->categorie_id = $pdt->categorie_id;
            $produit->reference = $pdt_con->reference;
            $produit->produit_id = $produit_id;
            $produit->libelle = $pdt->libelle;
            $produit->pa = $pdt_con->prix_achat;
            $produit->pv = $pdt_con->prix_vente;
            $produit->qte = $pdt->qte;
            $produit->min = $pdt_con->stock_minimal;
            $produit->max = $pdt_con->stock_maximal;
            $produit->cout_pa = $pdt->qte*$pdt_con->prix_achat;
            $produit->cout_pv = $pdt->qte*$pdt_con->prix_vente;
           
        }
        return $produit;
    }

    public function rechpdtPQMagasin($produit_id,$magasin_id){
        $pdtqp = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->where('stock_produits.etat','<>','Delete')
            ->where('stock_produits.produit_id','=',$produit_id)
            ->where('stock_produits.magasin_id','=',$magasin_id)
            ->get();
        $produit = new \stdClass();
        if (count($pdtqp)!=0){
            $pdt = (object) $pdtqp[0];
            //dd($pdt);
            $qte = DB::table('stock_produits')
                ->where('etat','<>','Delete')
                ->where('produit_id','=',$produit_id)
                ->where('magasin_id','=',$magasin_id)
                //->where('qte','>','0')
                ->sum('qte');
            $rech_pv = DB::table('stock_produits')
                ->where('produit_id','=',$produit_id)
                ->where('magasin_id','=',$magasin_id)
                ->where('etat','=','Encours')
                ->get();
            $pv=Produit::find($produit_id)->prix_vente;
            $produit->produit_id = $pdt->produit_id;
            $produit->reference = $pdt->reference;
            $produit->libelle = $pdt->libelle;
            $produit->pa = $pdt->prix_achat;
            $produit->pv = $pv;
            $produit->qte = $qte;
            $produit->min = $pdt->stock_minimal;
            $produit->max = $pdt->stock_maximal;
            $produit->cout_pa = $qte*$pdt->prix_achat;
            $produit->cout_pv = $qte*$pv;
        }else{
            $produit = null;
        }
        return $produit;
    }

    public function date_per(){
        $lesProduits = [];
        $produits = DB::table('stock_produits')
            ->join('magasins','magasins.magasin_id','=','stock_produits.magasin_id')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('stock_produits.centre_id','=',Auth::user()->centre_id)
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

        return view('inventaire.date_per');
    }

    public function invglobal(Request $request){
        $inventaires = [];
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $produits = DB::table('mouvements')
                ->selectRaw('produit_id,sum(qte_entree)as ent, sum(qte_sortie) as sor')
                ->whereBetween('date', array($request->from_date, $request->to_date))
                ->groupBy('produit_id')
                ->get();
            foreach ($produits as $produit){
                $pdts = DB::table('mouvements')
                    ->whereBetween('date', array($request->from_date, $request->to_date))
                    ->where('produit_id','=',$produit->produit_id)
                    ->orderby('mouvement_id')
                    ->get();
                $first= $pdts[0];
                $last = $first->qte_initiale+$produit->ent-$produit->sor;
                $pdtcon = Produit::find($produit->produit_id);
                $data = new \stdClass();

                $data->produit_id = $produit->produit_id;
                $data->pdt_lib = $pdtcon->nom_commercial;
                $data->pdt_ini = $first->qte_initiale;
                $data->pdt_ent = $produit->ent;
                $data->pdt_sor = $produit->sor;
                $data->pdt_act = $last;

                array_push($inventaires,$data);
            }
        }else{
            $inventaires = [];
        }

        if(request()->ajax())
        {
            return datatables()->of($inventaires)
                ->addColumn('action', function($inventaire){})
                ->make(true);
        }
        return view('inventairesi.invglobal', compact('inventaires'));
    }

    public function details($debut,$fin,$produit_id){
        $produits = [];
        if(!empty($debut) & !empty($fin))
        {
            $produits = DB::table('mouvements')
                ->whereBetween('date', array($debut, $fin))
                ->where('produit_id','=',$produit_id)
                ->orderby('mouvement_id')
                ->get();
            $produit = Produit::find($produit_id);
        }

        //$centre  = Centre::find('1');

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
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">MOUVEMENTS DU PRODUIT '.$produit->libelle.' DE LA PERIODE DU  : '.$debut.' AU '.$fin.' / '.$produit->nom_commercial.' </td>
                </tr>
            </table>';
        $output .='
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                        <tr style="border-radius: 15px; background-color: #A2ACC4";>
                            <th style="font-size: 15px;" width="10%">Date</th>
                            <th style="font-size: 15px;" width="42%">Libelle</th>
                            <th style="font-size: 15px;" width="12%">Qte Initiale</th>
                            <th style="font-size: 15px;" width="12%">Qte achat</th>
                            <th style="font-size: 15px;" width="12%">Qte sortie/vendue</th>
                            <th style="font-size: 15px;" width="12%">Solde</th>
                        </tr>
                    </thead>
                    <tbody>';
        foreach($produits as $produit){
            $output .='
               <tr style="border-collapse: collapse; border: 1px solid">
                   <td style="font-size:15px; border: 1px solid;">'.$produit->date.'</td>
                   <td style="font-size:15px; border: 1px solid;">'.$produit->motif.'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte_initiale,'0','.',' ').'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte_entree,'0','.',' ').'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte_sortie,'0','.',' ').'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte_reelle,'0','.',' ').'</td>
               </tr>';
        }
        $output .='<tr>
                    <td colspan="6"></td>
                </tr>
            </tbody>
          </table>';

        return $output;

    }

    public function print_invglobal($debut,$fin){
        $lesProduits = [];
        if(!empty($debut) & !empty($fin))
        {
            $produits = DB::table('mouvements')
                ->selectRaw('produit_id,sum(qte_entree)as ent, sum(qte_sortie) as sor')
                ->whereBetween('date', array($debut, $fin))
                ->groupBy('produit_id')
                ->get();
            foreach ($produits as $produit){
                $pdts = DB::table('mouvements')
                    ->whereBetween('date', array($debut, $fin))
                    ->where('produit_id','=',$produit->produit_id)
                    ->orderby('mouvement_id')
                    ->get();
                $first= $pdts[0];
                $last = $first->qte_initiale+$produit->ent-$produit->sor;
                $pdtcon = Produit::find($produit->produit_id);
                $data = new \stdClass();

                $data->produit_id = $produit->produit_id;
                $data->reference = $produit->produit_id;
                $data->pdt_lib = $pdtcon->nom_commercial;
                $data->pdt_ini = $first->qte_initiale;
                $data->pdt_ent = $produit->ent;
                $data->pdt_sor = $produit->sor;
                $data->pdt_act = $last;

                array_push($lesProduits,$data);
            }
        }

        //$centre  = Centre::find('1');

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
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">INVENTAIRE GLOBAL DE LA PERIODE DU  : '.$debut.' AU '.$fin.' </td>
                </tr>
            </table>';
            $output .='
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                        <tr style="border-radius: 15px; background-color: #A2ACC4";>
                            <th style="font-size: 15px;" width="10%">Reference</th>
                            <th style="font-size: 15px;" width="42%">Produit</th>
                            <th style="font-size: 15px;" width="12%">Qte Initiale</th>
                            <th style="font-size: 15px;" width="12%">Qte achat</th>
                            <th style="font-size: 15px;" width="12%">Qte sortie/vendue</th>
                            <th style="font-size: 15px;" width="12%">Solde</th>
                        </tr>
                    </thead>
                    <tbody>';
            foreach($lesProduits as $produit){
            $output .='
               <tr style="border-collapse: collapse; border: 1px solid">
                   <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                   <td style="font-size:15px; border: 1px solid;">'.$produit->pdt_lib.'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pdt_ini,'0','.',' ').'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pdt_ent,'0','.',' ').'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pdt_sor,'0','.',' ').'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pdt_act,'0','.',' ').'</td>
               </tr>';
            }
            $output .='<tr>
                    <td colspan="6"></td>
                </tr>
            </tbody>
          </table>';

        return $output;
    }

    public function invcentre(Request  $request){
        $inventaires = [];
        $centres = [];
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $produits = DB::table('mouvements')
                ->selectRaw('produit_id,sum(qte_entree)as ent, sum(qte_sortie) as sor')
                ->whereBetween('date', array($request->from_date, $request->to_date))
                ->where('centre_id','=',$request->centre_id)
                ->groupBy('produit_id')
                ->get();
            foreach ($produits as $produit){
                $pdts = DB::table('mouvements')
                    ->whereBetween('date', array($request->from_date, $request->to_date))
                    ->where('centre_id','=',$request->centre_id)
                    ->where('produit_id','=',$produit->produit_id)
                    ->orderby('mouvement_id')
                    ->get();
                $first= $pdts[0];
                $last = $first->qte_initiale+$produit->ent-$produit->sor;
                $pdtcon = Produit::find($produit->produit_id);
                $data = new \stdClass();

                $data->produit_id = $produit->produit_id;
                $data->pdt_lib = $pdtcon->nom_commercial;
                $data->pdt_ini = $first->qte_initiale;
                $data->pdt_ent = $produit->ent;
                $data->pdt_sor = $produit->sor;
                $data->pdt_act = $last;

                array_push($inventaires,$data);
            }
        }else{
            $inventaires = [];
        }

        if(request()->ajax())
        {
            return datatables()->of($inventaires)
                ->addColumn('action', function($inventaire){})
                ->make(true);
        }
    
        return view('inventairesi.invcentre',compact('centres','inventaires'));
    }

    public function print_invcentre($debut,$fin,$centre_id){
        $lesProduits = [];
        if(!empty($debut) & !empty($fin))
        {
            $produits = DB::table('mouvements')
                ->join('produits','produits.produit_id','=','mouvements.produit_id')
                ->selectRaw('mouvements.produit_id,sum(mouvements.qte_entree)as ent, sum(mouvements.qte_sortie) as sor')
                ->where('mouvements.centre_id','=',$centre_id)
                ->whereBetween('mouvements.date', array($debut, $fin))
                ->groupBy('mouvements.produit_id')
                ->orderBy('mouvements.produit_id')
                ->get();
            foreach ($produits as $produit){
                $pdts = DB::table('mouvements')
                    ->where('centre_id','=',$centre_id)
                    ->whereBetween('date', array($debut, $fin))
                    ->where('produit_id','=',$produit->produit_id)
                    ->orderby('mouvement_id')
                    ->get();
                $first= $pdts[0];
                $last = $first->qte_initiale+$produit->ent-$produit->sor;
                $pdtcon = Produit::find($produit->produit_id);
                $data = new \stdClass();

                $data->produit_id = $produit->produit_id;
                $data->reference = $produit->produit_id;
                $data->pdt_lib = $pdtcon->nom_commercial;
                $data->pdt_ini = $first->qte_initiale;
                $data->pdt_ent = $produit->ent;
                $data->pdt_sor = $produit->sor;
                $data->pdt_act = $last;

                array_push($lesProduits,$data);
            }
        }

        $centre  = Centre::find($centre_id);

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
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">INVENTAIRE DU CENTRE '.$centre->nom_centre.' DE LA PERIODE DU  : '.$debut.' AU '.$fin.' </td>
                </tr>
            </table>';
        $output .='
                <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                    <thead>
                        <tr style="border-radius: 15px; background-color: #A2ACC4";>
                            <th style="font-size: 15px;" width="10%">Reference</th>
                            <th style="font-size: 15px;" width="42%">Produit</th>
                            <th style="font-size: 15px;" width="12%">Qte Initiale</th>
                            <th style="font-size: 15px;" width="12%">Qte achat</th>
                            <th style="font-size: 15px;" width="12%">Qte sortie/vendue</th>
                            <th style="font-size: 15px;" width="12%">Solde</th>
                        </tr>
                    </thead>
                    <tbody>';
        foreach($lesProduits as $produit){
            $output .='
               <tr style="border-collapse: collapse; border: 1px solid">
                   <td style="font-size:15px; border: 1px solid;">'.$produit->reference.'</td>
                   <td style="font-size:15px; border: 1px solid;">'.$produit->pdt_lib.'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pdt_ini,'0','.',' ').'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pdt_ent,'0','.',' ').'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pdt_sor,'0','.',' ').'</td>
                   <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->pdt_act,'0','.',' ').'</td>
               </tr>';
        }
        $output .='<tr>
                    <td colspan="6"></td>
                </tr>
            </tbody>
          </table>';

        return $output;
    }

    public function invproduit(Request $request){
        $inventaires = [];
        $produits = [];
        if(!empty($request->from_date) & !empty($request->to_date)) {
            $inventaires = DB::table('mouvements')
                ->whereBetween('date', array($request->from_date, $request->to_date))
                //->where('centre_id', '=', Auth::user()->centre_id)
                ->where('produit_id', '=', $request->produit_id)
                ->orderBy('mouvement_id')
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($inventaires)
                ->addColumn('action', function($inventaire){})
                ->make(true);
        }
        return view('inventairesi.invproduit',compact('produits','inventaires'));
    }

    public function produits(){
        if (\request()->ajax()){
            $produits = DB::table('produits')
                //->join('stock_produits.produit_id','=','produits.produit_id')
                ->join('categories','categories.categorie_id','=','produits.categorie_id')
                ->where('categories.type','=','Stockable')
                ->where('produits.statut','=','true')
                ->orderBy('nom_commercial')
                ->get();
            return $produits;
        }
    }

    public function print_invproduit($debut,$fin,$produit_id){
        $produits = DB::table('mouvements')
            ->whereBetween('date', array($debut, $fin))
            //->where('centre_id', '=', Auth::user()->centre_id)
            ->where('produit_id', '=', $produit_id)
            ->orderBy('mouvement_id')
            ->get();;
        $magasins = DB::table('magasins')
            ->where('statut','=','true')
            ->where('centre_id','=',Auth::user()->centre_id)
            ->orderBy('libelle')
            ->get();

        $centre  = Centre::find('1');
        $produit = Produit::find($produit_id);

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
                    <td width="100%" style="font-size: 20px; text-align: center; color: #95124e">FICHE DE STOCK DE  '.$produit->nom_commercial.' DE LA PERIODE DU  : '.$debut.' AU '.$fin.' </td>
                </tr>
            </table>

            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
            <thead>
                <tr style="border-radius: 15px; background-color: #A2ACC4";>
                    <th style="font-size: 15px;" width="40%">Produit</th>
                    <th style="font-size: 15px;" width="15%">Qte Initiale</th>
                    <th style="font-size: 15px;" width="15%">Qte achat</th>
                    <th style="font-size: 15px;" width="15%">Qte sortie/vendue</th>
                    <th style="font-size: 15px;" width="15%">Solde</th>
                </tr>
            </thead>
            <tbody>';
            foreach($produits as $produit){
                $output .='
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td style="font-size:15px; border: 1px solid;">'.$produit->motif.'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte_initiale,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte_entree,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte_sortie,'0','.',' ').'</td>
                        <td style="font-size:15px; border: 1px solid; text-align: right">'.number_format($produit->qte_reelle,'0','.',' ').'</td>
                    </tr>';
            }
            $output .='
                </tbody>
            </table><br>';

        return $output;
    }

}
