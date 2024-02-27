<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitTransfertDps extends Model
{
    use HasFactory;
    protected $table = 'produit_transfert_dps';
    protected $primaryKey = 'produit_transfert_dps_id';
    protected $guarded = [];
}
