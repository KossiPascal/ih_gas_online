<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $primaryKey = 'categorie_id';
    protected $guarded = [];

    public function produit () {
        return $this->hasMany(Produit::class);
    }
}
