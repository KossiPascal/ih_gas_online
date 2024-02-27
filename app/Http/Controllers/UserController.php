<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Direction;
use App\Models\Droit;
use App\Models\DroitProfil;
use App\Models\Profil;
use App\Models\ProfilUser;
use App\Models\User;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PDOException;
use stdClass;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$this->authorize('manage-action',['profil','lister']);
        $profil = new Profil();
        $droits = Droit::orderBy('groupe')->get();
        $droitprofils = [];
        $profils = Profil::query()
            ->where('statut', true)
            ->get();
        if(request()->ajax()){
            return datatables()->of($profils)
                ->addColumn('action', function($profil){
                    $button = '<button type="button" name="delete" id="'.$profil->profil_id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('profil.index', compact('droits', 'profils','profil'));
    }

    public function editProfil($id){
        $this->authorize('manage-action',['profil','creer']);
        $profil = Profil::find($id);
        $droits = Droit::orderBy('groupe')->get();;
        $droit_profil = DroitProfil::where('profil_id','=',$id)->get();
        return view('profil.edit', compact('droits', 'profil','droit_profil'));
    }

    public function createProfil(Request $request){
        $this->authorize('manage-action',['profil','creer']);
        $rules = array(
            'nom'    =>  'required'
        );

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $profil = Profil::where('nom','=',$request->nom)
            ->where('statut','=','true')
            ->get();
        if($request->profil_id==null){
            if(count($profil)>0){
                    return response()->json(['error'=>'Ce profil existe deja en base de donnees']);
            }else{
                try{
                    DB::beginTransaction();
                    $profil = Profil::create([
                        'nom'=>$request->nom,
                        'statut'=>'true'
                    ]);
                    // DB::connection('vps')->table('profils')->insert([
                    //         'nom'=>$request->nom,
                    //         'statut'=>'true'
                    // ]);
                    foreach($request->droits as $droit){
                        DroitProfil::create([
                            'profil_id'=>$profil->profil_id,
                            'droit_id'=>$droit
                        ]);
                        // DB::connection('vps')->table('droit_profils')->insert([
                        //     'profil_id'=>$profil->profil_id,
                        //     'droit_id'=>$droit
                        // ]);
                    }
                    DB::commit();
                    return response()->json(['success'=>'Profil cree avec success']);
                }catch(\Throwable $th){
                    DB::rollBack();
                }
            }
        }else{
            $profil = Profil::find($request->profil_id);
            try {
                DB::beginTransaction();
                $profil->update(['nom'=>$request->nom]);
                //DB::connection('vps')->table('profils')->where('profil_id',$request->profil_id)->update(['nom'=>$request->nom]);
                foreach($request->droits as $droit){
                    DroitProfil::create([
                        'profil_id'=>$profil->profil_id,
                        'droit_id'=>$droit
                    ]);
                    // DB::connection('vps')->table('droit_profils')->insert([
                    //     'profil_id'=>$profil->profil_id,
                    //     'droit_id'=>$droit
                    // ]);
                }
                DB::commit();
                return redirect()->route('user.index')->with(['success'=>'Profil modifie avec success']);
            } catch (\Throwable $th) {
                DB::rollBack();
            }
        }

    }

    public function updateProfil(Request $request){
        $this->authorize('manage-action',['profil','creer']);
        //dd('UPDATE PROFIL');
        $rules = array(
            'nom'    =>  'required'
        );
        DB::table('droit_profils')
            ->where('profil_id','=',$request->id)
            ->delete();

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $profil = Profil::where('nom','=',$request->nom)
            ->where('statut','=','true')
            ->get();

        if(count($profil)>0){
            try{
                DB::beginTransaction();
                foreach($request->droits as $droit){
                    DroitProfil::create([
                        'profil_id'=>$request->id,
                        'droit_id'=>$droit
                    ]);
                    // DB::connection('vps')->table('droit_profils')->insert([
                    //     'profil_id'=>$request->id,
                    //     'droit_id'=>$droit
                    // ]);
                }
                DB::commit();
                return redirect()->route('user.index')->with(['warning'=>'Profil existe deja. Seuls les droits ont ete modifie']);
            }catch(\Throwable $th){
                DB::rollBack();
            }
        }else{
            $profil = Profil::find($request->profil_id);
            try {
                DB::beginTransaction();
                $profil->update(['nom'=>$request->nom]);
                //DB::connection('vps')->table('profils')->where('profil_id',$request->profil_id)->update(['nom'=>$request->nom]);
                foreach($request->droits as $droit){
                    DroitProfil::create([
                        'profil_id'=>$request->id,
                        'droit_id'=>$droit
                    ]);
                    // DB::connection('vps')->table('droit_profils')->insert([
                    //     'profil_id'=>$request->id,
                    //     'droit_id'=>$droit
                    // ]);
                }
                DB::commit();
                return redirect()->route('user.index')->with(['success'=>'Profil modifie avec success']);
            } catch (\Throwable $th) {
                DB::rollBack();
            }
        }
    }

    public function deleteProfil($id){
        $this->authorize('manage-action',['profil','supprimer']);
        if(request()->ajax()){
            try{
                DB::beginTransaction();
                Profil::find($id)->update(['statut'=>'false']);
                //DB::connection('vps')->table('profils')->where('profil_id',$id)->update(['statut'=>'false']);
                DB::commit();
            }catch(\Throwable $th){
                DB::rollBack();
            }
        }
    }

    public function user(){
        $this->authorize('manage-action',['utilisateur','lister']);
        $users = DB::table('users')
            //->join('profils','profils.profil_id','=','users.profil_id')
            ->leftjoin('centres','centres.centre_id','=','users.centre_id')
            ->leftjoin('directions','directions.dps_id','=','users.dps_id')
            ->where('users.statut','=','true')
            ->get();
        $data = array();
        foreach($users as $user){
                $objet = new stdClass;
                $objet->id = $user->id;
                $objet->name = $user->name;
                $objet->email = $user->email;
                $objet->nom = implode(', ',User::find($user->id)->profils()->get()->pluck('nom')->toArray());
                $objet->nom_centre = $user->nom_centre;
                $objet->dps_nom = $user->dps_nom;
                array_push($data,$objet);
        }
        $profils = Profil::where('statut','=','true')->pluck('nom','profil_id');
        $centres = Centre::all()->pluck('nom_centre','centre_id');
        $directions = Direction::all()->pluck('dps_nom','dps_id');
        if(request()->ajax())
        {
            return datatables()->of($data)
                ->addColumn('action', function($user){
                    $button = '<button type="button" name="editer" id="'.$user->id.'" class="edituser btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="delete" id="'.$user->id.'" class="deleteuser btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('user.index',compact('profils','centres','directions'));
    }

    public function usersave(Request $request){
        $this->authorize('manage-action',['menu','usersi']);
        $rules = array(
            'name'    =>  'required',
            'email'    =>  'required',
            'password'    =>  'required'
        );
        //dd($request->profil_id);

        $error = Validator::make($request->all(), $rules);
        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $users = User::where('email','=',$request->email)
            ->where('statut','=','true')
            ->get();
        $form_data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'dps_id' => $request->dps_id,
            'centre_id' => $request->centre_id,
            //'profil_id' => $request->profil_id
        ];
        
        if($request->id==null){
            if(count($users)>0){
                return response()->json(['error'=>'Ce Compte existe deja']);
            }else{
                DB::beginTransaction();
                try {
                    
                    $user = User::create($form_data);
                    //DB::connection('vps')->table('users')->insert($form_data);
                    foreach($request->profil_id as $profil){
                        ProfilUser::create([
                            'user_id'=>$user->id,
                            'profil_id'=>$profil
                        ]);
                        // DB::connection('vps')->table('profil_users')->insert([
                        //     'user_id'=>$user->id,
                        //     'profil_id'=>$profil
                        // ]);
                    }
                    return response()->json(['success'=>'Compte cree avec success']);
                    DB::commit();
                }catch(PDOException $se){
                    DB::rollBack();
                    return response()->json(['error'=>'Erreur survenu. Compte non cree'.$se->getMessage()]);
                }
            }
        }else{
            $user = User::find($request->id);
            DB::beginTransaction();
            try {
                //dd($user);
                DB::table('profil_users')
                    ->where('user_id','=',$user->id)
                    ->delete();
                // DB::connection('vps')->table('profil_users')
                //     ->where('user_id','=',$user->id)
                //     ->delete();
                
                $email ='';
                $message='';
                $statut = '';
                if(count($users)>0){
                    $email = $user->email;
                    $message='Informations mise a jour sauf le compte car existe ne base de donnees';
                    $statut='warning';
                }else{
                    $email = $request->email;
                    $message='Compte modifie avec success';
                    $statut='success';
                }
                $user->update([
                    'name' => $request->name,
                    'email' => $email,
                    'password' => Hash::make($request->password),
                    'type' => $request->type,
                    'dps_id' => $request->dps_id,
                    'centre_id' => $request->centre_id
                ]);
                //dd($request->dps_id,$user);
                
                // DB::connection('vps')->table('users')
                //     ->where('user_id',$request->id)
                //     ->update([
                //     'name' => $request->name,
                //     'email' => $email,
                //     'password' => Hash::make($request->password),
                //     'type' => $request->type,
                //     'dps_id' => $request->dps_id,
                //     'centre_id' => $request->centre_id
                // ]);

                foreach($request->profil_id as $profil){
                    ProfilUser::create([
                        'user_id'=>$user->id,
                        'profil_id'=>$profil
                    ]);
                    // DB::connection('vps')->table('profil_users')->insert([
                    //     'user_id'=>$user->id,
                    //     'profil_id'=>$profil
                    // ]);
                }
                DB::commit();
                return response()->json([$statut=>$message]);
                
            }catch(PDOException $se){
                    DB::rollBack();
            }
        }

    }

    public function edituser($id){
        //$this->authorize('manage-action',['utilisateur','editer']);
        if(request()->ajax()){
            return response()->json(User::find($id));
        }
    }

    public function deleteuser($id){
        $this->authorize('manage-action',['utilisateur','supprimer']);
        if(request()->ajax()){
            User::find($id)->update(['statut'=>'false']);
            //DB::connection('vps')->table('users')->where('user_id',$id)->update(['statut'=>'false']);

        }
    }

    public function userdps(){
        $this->authorize('manage-action',['menu','userdps']);
        $users = DB::table('users')
            ->join('profils','profils.profil_id','=','users.profil_id')
            ->join('directions','directions.dps_id','=','users.dps_id')
            ->where('users.dps_id','=',Auth::user()->dps_id)
            ->where('users.statut','=','true')
            ->get();

        $data = array();
        foreach($users as $user){
                $objet = new stdClass;
                $objet->id = $user->id;
                $objet->name = $user->name;
                $objet->email = $user->email;
                $objet->nom = implode(', ',User::find($user->id)->profils()->get()->pluck('nom')->toArray());
                //$objet->nom_centre = $user->nom_centre;
                $objet->dps_nom = $user->dps_nom;
                array_push($data,$objet);
        }

        $profils = Profil::where('statut','=','true')->pluck('nom','profil_id');
        $centres = Centre::all()->pluck('nom_centre','centre_id');
        $directions = Direction::all()->pluck('dps_nom','dps_id');
        if(request()->ajax())
        {
            return datatables()->of($data)
                ->addColumn('action', function($user){
                    $button = '<button type="button" name="editer" id="'.$user->id.'" class="edituser btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="delete" id="'.$user->id.'" class="deleteuser btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('user.indexdps',compact('profils','centres','directions'));
    }

    public function usersi(){
        $this->authorize('manage-action',['menu','usersi']);
        $users = DB::table('users')
            ->join('profils','profils.profil_id','=','users.profil_id')
            ->join('directions','directions.dps_id','=','users.dps_id')
            ->where('users.dps_id','=',6)
            ->where('users.statut','=','true')
            ->get();

        $data = array();
        foreach($users as $user){
                $objet = new stdClass;
                $objet->id = $user->id;
                $objet->name = $user->name;
                $objet->email = $user->email;
                $objet->nom = implode(', ',User::find($user->id)->profils()->get()->pluck('nom')->toArray());
                //$objet->nom_centre = $user->nom_centre;
                $objet->dps_nom = $user->dps_nom;
                array_push($data,$objet);
        }
        //dd(Auth::user()->dps_id,$users);
        $profils = Profil::where('statut','=','true')->pluck('nom','profil_id');
        $centres = Centre::all()->pluck('nom_centre','centre_id');
        $directions = Direction::all()->pluck('dps_nom','dps_id');
        if(request()->ajax())
        {
            return datatables()->of($data)
                ->addColumn('action', function($user){
                    $button = '<button type="button" name="editer" id="'.$user->id.'" class="edituser btn btn-success btn-sm"><i class="fa fa-edit"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" name="delete" id="'.$user->id.'" class="deleteuser btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('user.indexsi',compact('profils','centres','directions'));
    }

}
