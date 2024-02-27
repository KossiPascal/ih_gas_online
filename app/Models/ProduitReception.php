<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitReception extends Model
{
    use HasFactory;
    protected $table = 'produit_receptions';
    protected $primaryKey = 'produit_reception_id';
    protected $guarded = [];
}
