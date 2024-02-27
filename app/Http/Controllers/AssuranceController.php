<?php

namespace App\Http\Controllers;

use App\Models\Assurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AssuranceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage-action',['assurance','lister']);
        $assurances = DB::table('assurances')
            ->where('statut', '=', 'true')
            //->where('centre_id', '=', Auth::user()->centre_id)
            ->get();
        //dd($assurances);
        if (request()->ajax()) {
            return datatables()->of($assurances)
                ->addColumn('action', function ($mutuelle) {
                    $button = '<button type="button" name="editer" id="' . $mutuelle->assurance_id . '" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="delete" id="' . $mutuelle->assurance_id . '" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('donnees.assurance.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('manage-action',['assurance','creer']);
        $rules = array(
            'nom'    =>  'required',
            'taux'     =>  'required|numeric|min:0|max:100',
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $nbcat = Assurance::count()+1;
        $assurance_id = $nbcat.Auth::user()->id;

        $form_data = array(
            'assurance_id' =>  $assurance_id,
            'nom' =>  $request->nom,
            'taux' =>  $request->taux,
            'centre_id' =>  Auth::user()->centre_id,
        );

        $mutuelle = DB::table('assurances')
            ->Where('statut','=','true')
            ->Where('nom','=',$request->nom)
            ->get();

        if ($request->assurance_id==null){
            if (count($mutuelle)==0){
                try {
                    DB::beginTransaction();
                    Assurance::create($form_data);
                    //DB::connection('vps')->table('assurances')->insert($form_data);
                    DB::commit();
                    return response()->json(['success' => 'Assurance cree avec success.']);
                } catch (\Throwable $th) {
                    DB::rollBack();
                }

            }else{
                return response()->json(['error' => 'Le Assurance '.$request->nom.' existe deja dans la base de donnee.']);
            }
        }else{
            if (count($mutuelle)==0){
                try {
                    DB::beginTransaction();
                    Assurance::find($request->assurance_id)->update(['nom'=>$request->nom,'taux'=>$request->taux]);
                   // DB::connection('vps')->table('assurances')->where('assurance_id',$request->assurance_id)->update(['nom'=>$request->nom,'taux'=>$request->taux]);
                    DB::commit();
                    return response()->json(['success' => 'Assurance modifiee avec success.']);
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
            }else{
                try {
                    DB::beginTransaction();
                    Assurance::find($request->assurance_id)->update(['taux'=>$request->taux]);
                    //DB::connection('vps')->table('assurances')->where('assurance_id',$request->assurance_id)->update(['taux'=>$request->taux]);
                    DB::commit();
                    return response()->json(['success' => 'Assurance modifiee avec success.']);
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
        $this->authorize('manage-action',['assurance','editer']);
        if(request()->ajax())
        {
            $data = Assurance::findOrFail($id);
            return response()->json(['data' => $data]);
        }
    }

    public function delete($id)
    {
        $this->authorize('manage-action',['assurance','supprimer']);
        if (\request()->ajax()){
            try {
                DB::beginTransaction();
                Assurance::findOrfail($id)->update(['statut'=>'false']);
                //DB::connection('vps')->table('assurances')->where('assurance_id',$id)->updateupdate(['statut'=>'false']);
                DB::commit();
                return redirect()->route('ass.index')->with('success', 'L assurance a ete supprime');
            } catch (\Throwable $th) {
                DB::rollBack();
            }
        }
    }
}
