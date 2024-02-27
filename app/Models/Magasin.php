<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Magasin extends Model
{
    use HasFactory;
    protected $table = 'magasins';
    protected $primaryKey = 'magasin_id';
    protected $guarded = [];
}
