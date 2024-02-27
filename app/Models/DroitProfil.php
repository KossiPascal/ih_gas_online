<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DroitProfil extends Model
{
    use HasFactory;
    protected $table = 'droit_profils';

    protected $fillable = [
        'droit_id',
        'profil_id',
    ];
}
