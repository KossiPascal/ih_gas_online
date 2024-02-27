<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilUser extends Model
{
    use HasFactory;
    protected $table = 'profil_users';

    protected $fillable = [
        'profil_id',
        'user_id',
    ];
}
