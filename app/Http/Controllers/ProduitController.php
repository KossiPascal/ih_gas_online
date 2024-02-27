<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //dd($this->authorize('lister', 'produit'));
        $this->authorize('manage-action',['produit','creer']);
        $produits = DB::table('produits')
            //->distinct('produits.produit_id')
            ->join('categories','categories.categorie_id','=','produits.categorie_id')
            ->where('produits.statut','=','true')
            ->orderBy('produits.nom_commercial')
            ->get();


        $categories = Categorie::where('statut','=','true')->pluck('libelle','categorie_id');
        if(request()->ajax())
        {
            return datatables()->of($produits)
                ->addColumn('action', function($produit){
                    $button = '<button type="button" name="editer" id="'.$produit->produit_id.'" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="delete" id="'.$produit->produit_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="infos" id="'.$produit->produit_id.'" class="infos btn btn-primary btn-sm"><i class="fa fa-info-circle"></i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('donnees.produit.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    private function getProduitId()
    {
        return DB::table('produits')->count()+1;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('manage-action',['produit','creer']);
        
        $rules = array(
            'nom_commercial'    =>  'required',
            'type'     =>  'required',
            'prix_achat'     =>  'required|numeric|min:0',
            'prix_vente'     =>  'required|numeric|min:0',
            'stock_minimal'     =>  'required|numeric|min:1',
            'stock_maximal'     =>  'required|numeric|min:1',
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }
        $nbpdt = Produit::count()+1;
        $produit_id = $nbpdt.Auth::user()->id;

        $form_data = array(
            'produit_id' =>  $produit_id,
            'reference' =>  $request->reference,
            'nom_commercial'  =>  $request->nom_commercial,
            'dci'  =>  $request->dci,
            'unite_achat'  =>  $request->unite_achat,
            'unite_vente'  =>  $request->unite_vente,
            'type'  =>  $request->type,
            'famille_therapeutique'  =>  $request->famille_therapeutique,
            'prix_achat'  =>  $request->prix_achat,
            'prix_vente'  =>  $request->prix_vente,
            'stock_minimal'  =>  $request->stock_minimal,
            'stock_maximal'  =>  $request->stock_maximal,
            'categorie_id'  =>  $request->categorie_id,
            'user_id'  =>  Auth::user()->id
        );

        $produit= DB::table('produits')
            ->Where('statut','=','yes')
            ->Where('nom_commercial','=',$request->nom_commercial)
            ->get();
        if ($request->stock_minimal>=$request->stock_maximal){
            return response()->json(['error' => 'Le stock maximum doit etre superieur au stock minumum.']);
        }else{
            if ($request->produit_id==null){
                if (count($produit)==0){
                    DB::beginTransaction();
                    try {

                        Produit::create($form_data);
                        //DB::connection('vps')->table('produits')->insert($form_data);
                        DB::commit();
                        return response()->json(['success' => 'produit cree avec success.']);
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        return response()->json(['error' => 'Erreur survenu lors de l\'enregistrement.']);
                    }
                }else{
                    return response()->json(['error' => 'Le produit'.$request->nom_commercial.' existe deja dans la base de donnee.']);
                }
            }else{
                DB::beginTransaction();
                try {
                    if (count($produit)==0){
                        Produit::find($request->produit_id)->update([
                            'reference'=>$request->reference,
                            'nom_commercial'  =>  $request->nom_commercial,
                            'dci'  =>  $request->dci,
                            'unite_achat'  =>  $request->unite_achat,
                            'unite_vente'  =>  $request->unite_vente,
                            'type'  =>  $request->type,
                            'famille_therapeutique'  =>  $request->famille_therapeutique,
                            'prix_achat'  =>  $request->prix_achat,
                            'prix_vente'  =>  $request->prix_vente,
                            'stock_minimal'  =>  $request->stock_minimal,
                            'stock_maximal'  =>  $request->stock_maximal,
                            'categorie_id'  =>  $request->categorie_id]);
                        // DB::connection('vps')->table('produits')->where('produit_id',$request->produit_id)->update([
                        //     'reference'=>$request->reference,
                        //     'nom_commercial'  =>  $request->nom_commercial,
                        //     'dci'  =>  $request->dci,
                        //     'unite_achat'  =>  $request->unite_achat,
                        //     'unite_vente'  =>  $request->unite_vente,
                        //     'type'  =>  $request->type,
                        //     'famille_therapeutique'  =>  $request->famille_therapeutique,
                        //     'prix_achat'  =>  $request->prix_achat,
                        //     'prix_vente'  =>  $request->prix_vente,
                        //     'stock_minimal'  =>  $request->stock_minimal,
                        //     'stock_maximal'  =>  $request->stock_maximal,
                        //     'categorie_id'  =>  $request->categorie_id]);
                    }else{
                        Produit::find($request->produit_id)->update([
                            'reference'=>$request->reference,
                            'dci'  =>  $request->dci,
                            'unite_achat'  =>  $request->unite_achat,
                            'unite_vente'  =>  $request->unite_vente,
                            'type'  =>  $request->type,
                            'famille_therapeutique'  =>  $request->famille_therapeutique,
                            'prix_achat'  =>  $request->prix_achat,
                            'prix_vente'  =>  $request->prix_vente,
                            'stock_minimal'  =>  $request->stock_minimal,
                            'stock_maximal'  =>  $request->stock_maximal,
                            'categorie_id'  =>  $request->categorie_id]);
                        // DB::connection('vps')->table('produits')->where('produit_id',$request->produit_id)->update([
                        //     'reference'=>$request->reference,
                        //     'dci'  =>  $request->dci,
                        //     'unite_achat'  =>  $request->unite_achat,
                        //     'unite_vente'  =>  $request->unite_vente,
                        //     'type'  =>  $request->type,
                        //     'famille_therapeutique'  =>  $request->famille_therapeutique,
                        //     'prix_achat'  =>  $request->prix_achat,
                        //     'prix_vente'  =>  $request->prix_vente,
                        //     'stock_minimal'  =>  $request->stock_minimal,
                        //     'stock_maximal'  =>  $request->stock_maximal,
                        //     'categorie_id'  =>  $request->categorie_id]);
                    }
                    DB::commit();
                    return response()->json(['success' => 'produit modifiee avec success.']);
                }catch (\PDOException $se){
                    DB::rollBack();
                    return response()->json(['error' => 'Erreur survenu lors de l enregistrement.']);
                }
            }
        }
    }

    public function storenp(Request $request)
    {
        $rules = array(
            'pdtnom_commercial'    =>  'required',
            'pdttype'     =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }
        $nbpdt = Produit::count()+1;
        $produit_id = $nbpdt.Auth::user()->id;

        $form_data = array(
            'produit_id' =>  $produit_id,
            'reference' =>  $request->pdtreference,
            'nom_commercial'  =>  $request->pdtnom_commercial,
            'dci'  =>  $request->pdtdci,
            'unite_achat'  =>  $request->pdtunite_achat,
            'unite_vente'  =>  $request->pdtunite_vente,
            'type'  =>  $request->tpdtype,
            'famille_therapeutique'  =>  $request->pdtfamille_therapeutique,
            'prix_achat'  =>  $request->pdtprix_achat,
            'prix_vente'  =>  $request->pdtprix_vente,
            'stock_minimal'  =>  $request->pdtstock_minimal,
            'stock_maximal'  =>  $request->pdtstock_maximal,
            'categorie_id'  =>  $request->categorieid,
            'user_id'  =>  Auth::user()->id
        );

        $produit= DB::table('produits')
            ->Where('statut','=','yes')
            ->Where('nom_commercial','=',$request->pdtnom_commercial)
            ->get();
        if ($request->pdtstock_minimal>=$request->pdtstock_maximal){
            return response()->json(['error' => 'Le stock maximum doit etre superieur au stock minumum.']);
        }else{
            if ($request->produit_id==null){
                if (count($produit)==0){
                    try {
                        DB::beginTransaction();
                        Produit::create($form_data);
                        //DB::connection('vps')->table('produits')->insert($form_data);
                        DB::commit();
                        return response()->json(['success' => 'produit cree avec success.']);
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        return response()->json(['error' => 'Erreur survenu lors de l enregistrement.']);
                    }
                }else{
                    return response()->json(['error' => 'Le produit'.$request->pdtnom_commercial.' existe deja dans la base de donnee.']);
                }
            }else{
                DB::beginTransaction();
                try {
                    if (count($produit)==0){
                        Produit::find($request->produit_id)->update([
                            'reference'=>$request->reference,
                            'nom_commercial'  =>  $request->pdtnom_commercial,
                            'dci'  =>  $request->dci,
                            'unite_achat'  =>  $request->pdtunite_achat,
                            'unite_vente'  =>  $request->pdtunite_vente,
                            'type'  =>  $request->pdttype,
                            'famille_therapeutique'  =>  $request->pdtfamille_therapeutique,
                            'prix_achat'  =>  $request->pdtprix_achat,
                            'prix_vente'  =>  $request->pdtprix_vente,
                            'stock_minimal'  =>  $request->pdtstock_minimal,
                            'stock_maximal'  =>  $request->pdtstock_maximal,
                            'categorie_id'  =>  $request->categorieid]);
                        // DB::connection('vps')->table('produits')->where('produit_id',$request->produit_id)->update([
                        //     'reference'=>$request->reference,
                        //     'nom_commercial'  =>  $request->pdtnom_commercial,
                        //     'dci'  =>  $request->dci,
                        //     'unite_achat'  =>  $request->pdtunite_achat,
                        //     'unite_vente'  =>  $request->pdtunite_vente,
                        //     'type'  =>  $request->pdttype,
                        //     'famille_therapeutique'  =>  $request->pdtfamille_therapeutique,
                        //     'prix_achat'  =>  $request->pdtprix_achat,
                        //     'prix_vente'  =>  $request->pdtprix_vente,
                        //     'stock_minimal'  =>  $request->pdtstock_minimal,
                        //     'stock_maximal'  =>  $request->pdtstock_maximal,
                        //     'categorie_id'  =>  $request->categorieid]);
                    }else{
                        Produit::find($request->produit_id)->update([
                            'reference'=>$request->pdtreference,
                            'dci'  =>  $request->pdtdci,
                            'unite_achat'  =>  $request->pdtunite_achat,
                            'unite_vente'  =>  $request->pdtunite_vente,
                            'type'  =>  $request->pdttype,
                            'famille_therapeutique'  =>  $request->pdtfamille_therapeutique,
                            'prix_achat'  =>  $request->pdtprix_achat,
                            'prix_vente'  =>  $request->pdtprix_vente,
                            'stock_minimal'  =>  $request->pdtstock_minimal,
                            'stock_maximal'  =>  $request->pdtstock_maximal,
                            'categorie_id'  =>  $request->categorieid]);
                        // DB::connection('vps')->table('produits')->where('produit_id',$request->produit_id)->update([
                        //     'reference'=>$request->pdtreference,
                        //     'dci'  =>  $request->pdtdci,
                        //     'unite_achat'  =>  $request->pdtunite_achat,
                        //     'unite_vente'  =>  $request->pdtunite_vente,
                        //     'type'  =>  $request->pdttype,
                        //     'famille_therapeutique'  =>  $request->pdtfamille_therapeutique,
                        //     'prix_achat'  =>  $request->pdtprix_achat,
                        //     'prix_vente'  =>  $request->pdtprix_vente,
                        //     'stock_minimal'  =>  $request->pdtstock_minimal,
                        //     'stock_maximal'  =>  $request->pdtstock_maximal,
                        //     'categorie_id'  =>  $request->categorieid]);
                    }
                    DB::commit();
                    return response()->json(['success' => 'produit modifiee avec success.']);
                }catch (\PDOException $se){
                    DB::rollBack();
                    return response()->json(['error' => 'Erreur survenu lors de l enregistrement.']);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Produit  $produit
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (\request()->ajax()){
            return response()->json(Produit::find($id));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Produit  $produit
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (\request()->ajax()){
            return response()->json(Produit::find($id));
        }
    }

    public function delete($id)
    {
        if (\request()->ajax()){
            try {
                DB::beginTransaction();
                Produit::find($id)->update(['statut'=>'false']);
                //DB::connection('vps')->table('produits')->where('produit_id',$id)->update(['statut'=>'false']);
                DB::commit();
                return back()->with('success', 'Le Produit a ete supprime');
            } catch (\Throwable $th) {
                DB::rollBack();
                Alert::error('Erreur !', 'Une erreur s\'est produite.');
            }
        }

    }
}
