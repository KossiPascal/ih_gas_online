<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitVente extends Model
{
    use HasFactory;
    protected $table = 'produit_ventes';
    protected $primaryKey = 'produit_vente_id';
    protected $guarded = [];
}
