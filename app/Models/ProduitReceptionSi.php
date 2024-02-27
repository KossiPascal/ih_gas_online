<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitReceptionSi extends Model
{
    use HasFactory;
    protected $table = 'produit_reception_sis';
    protected $primaryKey = 'produit_reception_si_id';
    protected $guarded = [];
}
