<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitCommande extends Model
{
    use HasFactory;
    protected $table = 'produit_commandes';
    protected $primaryKey = 'produit_commande_id';
    protected $guarded = [];
}
