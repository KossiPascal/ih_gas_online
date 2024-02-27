<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private function code_patient()
    {
        $nb_pat = DB::table('patients')->count()+1;
        $centre = Centre::find(Auth::user()->centre_id);
        $code = '0'.$nb_pat.substr($centre->nom_centre,0,4).date('y').Auth::user()->centre_id;
        return $code;
    }

    public function index()
    {
        //
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
        try {
            DB::beginTransaction();
            $patient = Patient::create([
                'code_patient' => $request->code_patient,
                'nom_prenom' => $request->nom_prenom,
                'sexe' => $request->sexe,
                'age' => $request->age,
                'assurance_id' => $request->assurance_patient,
            ]);
            DB::connection('vps')->table('patients')->insert([
                'code_patient' => $request->code_patient,
                'nom_prenom' => $request->nom_prenom,
                'sexe' => $request->sexe,
                'age' => $request->age,
                'assurance_id' => $request->assurance_patient,
            ]);
            DB::commit();
            return response()->json($patient);
        } catch (\Throwable $th) {
            DB::rollBack();
            Alert::error('Erreur !', 'Une erreur s\'est produite.');
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Http\Response
     */
    public function show(Patient $patient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Http\Response
     */
    public function edit(Patient $patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Patient $patient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Patient $patient)
    {
        //
    }
}
