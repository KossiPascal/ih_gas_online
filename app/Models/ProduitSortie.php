<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitSortie extends Model
{
    use HasFactory;
    protected $table = 'produit_sorties';
    protected $primaryKey = 'produit_sortie_id';
    protected $guarded = [];
}
