<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitFacture extends Model
{
    use HasFactory;
    protected $table = 'produit_factures';
    protected $primaryKey = 'produit_facture_id';
    protected $guarded = [];
}
