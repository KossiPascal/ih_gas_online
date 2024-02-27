<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    /*protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }*/

    protected function create(array $data)
    {
        try {
            DB::beginTransaction();

            // Insérer dans la première base de données
            $userLocal = DB::connection('db_gas')->table('users')->insertGetId([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Insérer dans la deuxième base de données si la première insertion réussit
            if ($userLocal) {
                DB::connection('db_gas_local')->table('users')->insert([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                ]);

                // Si tout s'est bien passé, on commit les transactions
                DB::commit();

                // Récupérer les détails de l'utilisateur créé
                $createdUser = DB::connection('db_gas')->table('users')->find($userLocal);

                // Retourner les détails de l'utilisateur créé avec succès
                return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $createdUser]);
            } else {
                // Si la première insertion échoue, on rollBack la transaction
                DB::rollBack();

                return response()->json(['error' => 'Erreur lors de la création de l\'utilisateur'], 500);
            }
        } catch (\Exception $e) {
            // En cas d'exception, on rollBack la transaction
            DB::rollBack();

            return response()->json(['error' => 'Erreur lors de la création de l\'utilisateur'], 500);
        }
    }
}