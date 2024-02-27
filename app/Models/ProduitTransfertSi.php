<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitTransfertSi extends Model
{
    use HasFactory;
    protected $table = 'produit_transfert_sis';
    protected $primaryKey = 'produit_transfert_si_id';
    protected $guarded = [];
}
