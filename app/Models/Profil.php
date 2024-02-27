<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Profil extends Model
{
    use HasFactory;

    protected $primaryKey = 'profil_id';

    protected $fillable = [
        'nom',
        'statut',
    ];

    protected $casts = [
        'statut' => 'boolean',
    ];

    /** @return BelongsToMany<Droit> */
    public function droits()//: ///BelongsToMany
    {
        return $this->belongsToMany(Droit::class,'droit_profils','droit_id','profil_id');
    }

    /** @return BelongsToMany<User> 
    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Moels\User');
            //->withPivot(['date_ajout'])
            //->using(ProfilUser::class);
    }*/
}
