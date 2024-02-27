<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProduit extends Model
{
    use HasFactory;
    protected $table = 'stock_produits';
    protected $primaryKey = 'stock_produit_id';
    protected $guarded = [];
}
