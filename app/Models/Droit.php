<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Droit extends Model
{
    use HasFactory;

    protected $table = 'droits';

    protected $primaryKey = 'droit_id';

    protected $fillable = [
        'code',
        'groupe',
        'nom',
    ];

    public function profils(): BelongsToMany
    {
        return $this->belongsToMany(Profil::class,'profil_id','droit_id');
    }
}
