<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'statut',
        'type',
        'centre_id',
        'profil_id',
        'dps_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function allows($group, $ability): bool
    {
        info('user allows', compact('group', 'ability'));

        $exists = Droit::query()
            ->leftJoin('droit_profils', 'droits.droit_id', 'droit_profils.droit_id')
            ->leftJoin('profils', 'profils.profil_id', 'droit_profils.profil_id')
            ->leftJoin('profil_users', 'profil_users.profil_id', 'profils.profil_id')
            ->where('profil_users.user_id', $this->id)
            ->where('droits.groupe', $group)
            ->where('droits.code', $ability)
            ->exists();
        
        //info('allows', compact('exists'));

        return $exists;
    }

    protected function user(){
        return User::find(Auth::user()->id);
    }

    public function hasAnyprofil(array $profils){
        return $this->profils()->whereIn('nom',$profils)->first();
    }

    public function hasPermission($permission){
        return $this->permissions()->where('code', $permission)->exists();
    }

    public function permissions(){
        $droit_user = DB::table('droits')
            ->join('droit_profils','droit_profils.droit_id','=','droits.droit_id')
            ->join('profil_users','profil_users.profil_id','=','droit_profils.profil_id')
            ->where('profil_users.user_id','=',$this->user()->id)
            ->get() ;    
        return $droit_user;
    }

    public function profils()
    {
        return $this->belongsToMany(Profil::class,'profil_users','user_id','profil_id');
    }

    public function isUser(){
        return $this->user()->type=='USER';
    }

    public function isDPS(){
        return $this->user()->type=='DPS';
    }

    public function isSI(){
        return $this->user()->type=='SI';
    }
}
