<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Chart\Chart;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //return view('layouts/adminlayout');
        $ventes = $this->vente();
        $perimes = $this->datePer();
        $stock = $this->stock();
        $recette = $this->recette_encours();
        $commande = $this->commande_encours();
        $produit_perime = $this->produit_perime();
        $stock_alerte = $this->stock_alerte();
        //dd($ventes,$perimes,$stock,$recette,$commande,$produit_perime,$stock_alerte);
        return view('layouts.admin',compact('ventes','perimes','stock','recette','commande','produit_perime','stock_alerte'));
    }

    public function admin()
    {
        $ventes = $this->vente();
        $perimes = $this->datePer();
        $stock = $this->stock();
        $recette = $this->recette_encours();
        return view('layouts.admin',compact('ventes','perimes','stock','recette'));
    }

    private function rechpdtPQ($produit_id){
        $pdtqp = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->where('stock_produits.centre_id','=',Auth::user()->centre_id)
            ->where('stock_produits.etat','<>','Delete')
            ->where('stock_produits.produit_id','=',$produit_id)
            ->where('produits.statut','=','true')
            //->where('stock_produits.qte','>','0')
            ->get();
        $produit = new \stdClass();    
        if(count($pdtqp)>0){
            $pdt = (object) $pdtqp[0];
            $qte = DB::table('stock_produits')
                ->where('etat','<>','Delete')
                ->where('produit_id','=',$produit_id)
                ->sum('qte');
            $pu = Produit::find($produit_id)->prix_vente;    
            $produit->categorie_id = $pdt->categorie_id;
            $produit->produit_id = $pdt->produit_id;
            $produit->libelle = $pdt->libelle;
            $produit->qte = $qte;
        }else{
            $produit->categorie_id =0;
            $produit->produit_id = 0;
            $produit->libelle ='null';
            $produit->qte = 0;
        }
        return $produit;
    }

    private function datePer(){
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

            $months = floor($dateDifference / (30 * 60 * 60 * 24));
            $pdt = new \stdClass();
            $pdt->produit_id = $produit->produit_id;
            $pdt->libelle = $produit->libelle;
            $pdt->qte = $produit->qte;
            $pdt->date_peremption = $produit->date_peremption;
            $pdt->mois = $months;
            array_push($lesProduits,$pdt);
        }
        return $lesProduits;
    }

    private function vente(){
        $debut = date('Y').'-'.date('m').'-01';
        $ventes = [];
        $lesventes = DB::table('ventes')
            ->selectRaw('date_vente,sum(montant_total) as montant_total')
            ->whereBetween('date_vente', array($debut, date('Y-m-d')))
            ->where('ventes.centre_id','=',Auth::user()->centre_id)
            ->groupBy('date_vente')
            ->get();
        foreach ($lesventes as $vente){
            array_push($ventes,$vente);
        }
        return $ventes;
    }

    private function stock(){
        $produits = [];
        $allProduits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('stock_produits.centre_id','=',Auth::user()->centre_id)
            ->where('categories.type','=','Stockable')
            ->where('produits.statut','=','true')
            ->select('produits.produit_id')->distinct()
            ->get();

        foreach ($allProduits as $pdt){
            array_push($produits,$this->rechpdtPQ($pdt->produit_id));
        }
        return $produits;
    }

    private function recette_encours(){
        $debut = date('Y').'-'.date('m').'-01';
        $ventes = [];
        $recette = DB::table('ventes')
            ->whereBetween('date_vente', array($debut, date('Y-m-d')))
            ->where('centre_id','=',Auth::user()->centre_id)
            ->sum('montant_total');
        return $recette;
    }

    private function commande_encours(){
        $commande = DB::table('commandes')
            ->where('centre_id','=',Auth::user()->centre_id)
            ->where('etat','=','Encours')
            ->count('commande_id');
        $output ='';    
        if ($commande>0){
            $output = '
                <div class="alert alert-danger mt-3">
                    <h5 class="mb-0">'.$commande.' commande(s)</h5>
                </div>
                ';
        }else{
            $output = '
                <div class="alert alert-success mt-3">
                    <h5 class="mb-0"> 0commande</h5>
                </div>
                ';
        } 
        return $commande;
    }

    private function produit_perime(){
        $debut = date('Y').'-'.date('m').'-01';
        $date = new DateTime($debut); 
        $date->add(new DateInterval('P3M')); 

        $produits = DB::table('stock_produits')
            ->where('centre_id','=',Auth::user()->centre_id)
            ->where('date_peremption','<=',$date)
            ->count('produit_id');
        return $produits;
    }

    private function stock_alerte(){
        $nombre =0;
        $produits = DB::table('stock_produits')
            ->join('produits','produits.produit_id','=','stock_produits.produit_id')
            ->where('stock_produits.centre_id','=',Auth::user()->centre_id)
            //->where('stock_produits.qte','<=','produits.stock_minimal')
            ->get();
        foreach($produits as $produit){
            $pdt = Produit::find($produit->produit_id);
            if ($pdt->stock_minimal>=$produit->qte){
                $nombre++;
            }
        }   
        return $nombre;
    }
}
