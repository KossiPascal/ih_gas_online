<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Direction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CentreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $centres = DB::table('centres')
            ->where('centre_id','=',Auth::user()->centre_id)
            ->get();
        $directions = Direction::all()->pluck('dps_nom','dps_id');
        //$centres = Centre::find(Auth::user()->centre_id);
        if (request()->ajax()) {
            return datatables()->of($centres)
                ->addColumn('action', function ($centre) {
                    $button = '<button type="button" name="editer" id="' . $centre->centre_id . '" class="editer btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('centre.index', compact('directions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

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
            'nom'    =>  'required',
            'adresse'    =>  'required',
            'telephone'    =>  'required',
            'service'    =>  'required',
            'impression'    =>  'required',
            'dps_id'    =>  'required'
        );
        $centre = Centre::findOrfail(Auth::user()->centre_id);

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }
        $form_data=array(
            'nom_centre'=> $request->nom,
            'adresse'=> $request->adresse,
            'telephone'=> $request->telephone,
            'services'=> $request->service,
            'impression'=> $request->impression,
            'dps_id'=> $request->dps_id
        );

        try {
            DB::beginTransaction();
            $centre->update($form_data);
            DB::connection('vps')->table('centres')
                ->where('id', Auth::user()->centre_id)
                ->update($form_data);
            DB::commit();
            return response()->json(['success' => 'Centre modifiee avec success.']);
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Centre  $centre
     * @return \Illuminate\Http\Response
     */
    public function show(Centre $centre)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Centre  $centre
     * @return \Illuminate\Http\Response
     */
    public function edit(Centre $centre)
    {
        if(request()->ajax()){
            return response()->json($centre);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Centre  $centre
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Centre $centre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Centre  $centre
     * @return \Illuminate\Http\Response
     */
    public function destroy(Centre $centre)
    {
        //
    }
}
