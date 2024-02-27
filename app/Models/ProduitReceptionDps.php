<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitReceptionDps extends Model
{
    use HasFactory;
    protected $table = 'produit_reception_dps';
    protected $primaryKey = 'produit_reception_dps_id';
    protected $guarded = [];
}
