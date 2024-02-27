<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assurance extends Model
{
    use HasFactory;
    protected $table = 'assurances';
    protected $primaryKey = 'assurance_id';
    protected $guarded = [];
}
