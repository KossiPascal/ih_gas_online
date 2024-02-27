<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceptionDps extends Model
{
    use HasFactory;
    protected $table = 'reception_dps';
    protected $primaryKey = 'reception_dps_id';
    protected $guarded = [];
}
