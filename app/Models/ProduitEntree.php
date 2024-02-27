<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitEntree extends Model
{
    use HasFactory;
    protected $table = 'produit_entrees';
    protected $primaryKey = 'produit_entree_id';
    protected $guarded = [];
}
