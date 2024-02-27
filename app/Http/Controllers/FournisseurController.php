<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage-action',['fournisseur','lister']);
        $fournisseurs = DB::table('fournisseurs')
            ->where('statut', '=', 'true')
            //->where('centre_id', '=', Auth::user()->centre_id)
            ->get();
        //dd($fournisseurs);
        if (request()->ajax()) {
            return datatables()->of($fournisseurs)
                ->addColumn('action', function ($fournisseur) {
                    $button = '<button type="button" name="editer" id="' . $fournisseur->fournisseur_id . '" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="delete" id="' . $fournisseur->fournisseur_id . '" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('donnees.fournisseur.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('manage-action',['fournisseur','creer']);
        $rules = array(
            'nom'    =>  'required',
            'adresse'     =>  'required',
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $nbcat = Fournisseur::count()+1;
        $fournisseur_id = $nbcat.Auth::user()->id;

        $form_data = array(
            'fournisseur_id' =>  $fournisseur_id,
            'nom' =>  $request->nom,
            'adresse' =>  $request->adresse,
            'ville' =>  $request->ville,
            'telephone' =>  $request->telephone,
            'email' =>  $request->email,
            'centre_id' =>  Auth::user()->centre_id,
        );

        $fournisseur = DB::table('fournisseurs')
            ->Where('statut','=','true')
            ->Where('nom','=',$request->nom)
            ->where('centre_id', '=', Auth::user()->centre_id)
            ->get();

        if ($request->fournisseur_id==null){
            if (count($fournisseur)==0){
                try {
                    DB::beginTransaction();
                    Fournisseur::create($form_data);
                    //DB::connection('vps')->table('fournisseurs')->insert($form_data);
                    DB::commit();
                    return response()->json(['success' => 'fournisseur cree avec success.']);
                } catch (\Throwable $th) {
                    DB::rollBack();
                    Alert::success('Success !', 'Correction modifiee avec success.');
                }

            }else{
                return response()->json(['error' => 'Le fournisseur '.$request->nom.' existe deja dans la base de donnee.']);
            }
        }else{
            DB::beginTransaction();
            try {
                if (count($fournisseur)==0){
                    Fournisseur::find($request->fournisseur_id)->update([
                        'nom'=>$request->nom,
                        'adresse'=>$request->adresse,
                        'telephone'=>$request->telephone,
                        'ville' =>  $request->ville,
                        'email' =>  $request->email]);
                    // DB::connection('vps')->table('fournisseurs')->where('fournisseur_id',$request->fournisseur_id)->update([
                    //     'nom'=>$request->nom,
                    //     'adresse'=>$request->adresse,
                    //     'telephone'=>$request->telephone,
                    //     'ville' =>  $request->ville,
                    //     'email' =>  $request->email]);
                    DB::commit();
                    return response()->json(['success' => 'fournisseur modifiee avec success.']);
                }else{
                    Fournisseur::find($request->fournisseur_id)->update([
                        'adresse'=>$request->adresse,
                        'telephone'=>$request->telephone,
                        'ville' =>  $request->ville,
                        'email' =>  $request->email]);
                    // DB::connection('vps')->table('fournisseurs')->where('fournisseur_id',$request->fournisseur_id)->update([
                    //     'nom'=>$request->nom,
                    //     'adresse'=>$request->adresse,
                    //     'telephone'=>$request->telephone,
                    //     'ville' =>  $request->ville,
                    //     'email' =>  $request->email]);
                    DB::commit();
                    return response()->json(['success' => 'fournisseur modifiee avec success.']);
                }

            } catch (\Throwable $th) {
                DB::rollBack();
                Alert::success('Success !', 'Correction modifiee avec success.');
            }

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(request()->ajax())
        {
            $data = Fournisseur::findOrFail($id);
            return response()->json($data);
        }
    }


    public function delete($id)
    {
        $this->authorize('manage-action',['fournisseur','supprimer']);
        try {
            DB::beginTransaction();
            Fournisseur::findOrfail($id)->update(['statut'=>'false']);
            //DB::connection('vps')->table('fournisseurs')->where('fournisseur_id', $id)->update(['statut'=>'false']);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Alert::success('Success !', 'Correction modifiee avec success.');
        }
    }
}
