<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransfertDps extends Model
{
    use HasFactory;
    protected $table = 'transfert_dps';
    protected $primaryKey = 'transfert_dps_id';
    protected $guarded = [];
}
