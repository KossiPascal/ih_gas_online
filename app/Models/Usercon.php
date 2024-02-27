<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usercon extends Model
{
    use HasFactory;
    protected $table = 'usercons';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
