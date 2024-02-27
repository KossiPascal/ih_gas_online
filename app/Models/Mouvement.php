<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mouvement extends Model
{
    use HasFactory;
    protected $table = 'mouvements';
    protected $primaryKey = 'mouvement_id';
    protected $guarded = [];
}
