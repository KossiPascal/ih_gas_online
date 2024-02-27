<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sortie extends Model
{
    use HasFactory;
    protected $table = 'sorties';
    protected $primaryKey = 'sortie_id';
    protected $guarded = [];
}
