<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitCorrectionStock extends Model
{
    use HasFactory;
    protected $table = 'produit_correction_stocks';
    protected $primaryKey = 'produit_correction_stock_id';
    protected $guarded = [];
}
