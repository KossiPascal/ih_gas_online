<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceptionSi extends Model
{
    use HasFactory;
    protected $table = 'reception_sis';
    protected $primaryKey = 'reception_si_id';
    protected $guarded = [];
}
