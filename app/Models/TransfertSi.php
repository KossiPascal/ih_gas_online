<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransfertSi extends Model
{
    use HasFactory;
    protected $table = 'transfert_sis';
    protected $primaryKey = 'transfert_si_id';
    protected $guarded = [];
}
