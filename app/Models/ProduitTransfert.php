<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitTransfert extends Model
{
    use HasFactory;
    protected $table = 'produit_transferts';
    protected $primaryKey = 'produit_transfert_id';
    protected $guarded = [];
}
