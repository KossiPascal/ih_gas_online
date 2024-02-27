<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionStock extends Model
{
    use HasFactory;
    protected $table = 'correction_stocks';
    protected $primaryKey = 'correction_stock_id';
    protected $guarded = [];
}
