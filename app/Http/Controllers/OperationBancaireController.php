<?php

namespace App\Http\Controllers;

use App\Models\OperationBancaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class OperationBancaireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type_operation = ['Encaissement'=>'Encaissement','Decaissement'=>'Decaissememnt'];
        if(!empty($request->from_date) & !empty($request->to_date))
        {
            $historiques = DB::table('operation_bancaires')
                ->whereBetween('date', array($request->from_date, $request->to_date))
                ->where('centre_id','=',Auth::user()->centre_id)
                ->orderby('date','desc')
                ->get();
        }
        else
        {
            $debut = date('Y').'-'.date('m').'-01';
            $historiques = DB::table('operation_bancaires')
                ->whereBetween('date', array($debut, date('Y-m-d')))
                ->where('centre_id','=',Auth::user()->centre_id)
                ->orderby('date','desc')
                ->get();
        }

        if(request()->ajax())
        {
            return datatables()->of($historiques)
                ->addColumn('action', function($histo){})
                ->make(true);
        }
        return view('operation.index', compact('historiques','type_operation'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = array(
            'date'     =>  'required|date',
            'type_operation'     =>  'required',
            'libelle'     =>  'required',
            'operant'     =>  'required',
            'montant'     =>  'required|numeric|min:0',
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }
        $solde = 0;
        $entree = 0;
        $sortie = 0;

        if($request->type_operation=='Encaissement'){
            $entree = $request->montant;
        }else{
            $sortie = $request->montant;
        }

        if ($request->operation_id==null){
            $operations = DB::table('operation_bancaires')
                ->where('centre_id','=',Auth::user()->centre_id)
                ->orderBy('operation_id','desc')
                ->get();
            if(count($operations)>0){
                $op = (object) $operations[0];
                $solde = $op->solde;
            }

            $form_data = array(
                'type_operation' => $request->type_operation,
                'date' =>  $request->date,
                'libelle' =>  $request->libelle,
                'operant' =>  $request->operant,
                'initiale' =>  $solde,
                'entree' =>  $entree,
                'sortie' =>  $sortie,
                'solde' =>  $solde+$entree-$sortie,
                'centre_id' =>  Auth::user()->centre_id,
                'user_id' =>  Auth::user()->id
            );
            try {
                DB::beginTransaction();
                OperationBancaire::create($form_data);
                DB::connection('vps')->table('operation_bancaires')->insert($form_data);
                DB::commit();
                return response()->json(['success' => 'Operation cree avec success .']);
            } catch (\Throwable $th) {
                DB::rollBack();
                Alert::error('Erreur !', 'Une erreur s\'est produite.');
            }
        }else{
            $operation = OperationBancaire::find($request->operation_id);
            $form_data = array(
                'type_operation' => $request->type_operation,
                'date' =>  $request->date,
                'libelle' =>  $request->libelle,
                'operant' =>  $request->operant,
                'initiale' =>  $operation->initiale,
                'entree' =>  $entree,
                'sortie' =>  $sortie,
                'solde' =>  $operation->initiale+$entree-$sortie,
                'centre_id' =>  Auth::user()->centre_id,
                'user_id' =>  Auth::user()->id);
            try {
                DB::beginTransaction();
                $operation->update($form_data);
                DB::connection('vps')->table('operation_bancaires')->where('operation_bancaire_id',$request->operation_id)->update($form_data);
                DB::commit();
                return response()->json(['success' => 'Operation modifiee avec success.']);
            } catch (\Throwable $th) {
                DB::rollBack();
                Alert::error('Erreur !', 'Une erreur s\'est produite.');
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Operation  $operation
     * @return \Illuminate\Http\Response
     */
    public function edit($op_id)
    {
        if (\request()->ajax()){
            return response()->json(OperationBancaire::find($op_id));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OperationBancaire  $operationBancaire
     * @return \Illuminate\Http\Response
     */
    public function show(OperationBancaire $operationBancaire)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OperationBancaire  $operationBancaire
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OperationBancaire $operationBancaire)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OperationBancaire  $operationBancaire
     * @return \Illuminate\Http\Response
     */
    public function destroy(OperationBancaire $operationBancaire)
    {
        //
    }
}
