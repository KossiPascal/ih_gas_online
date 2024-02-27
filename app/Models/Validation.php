<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    use HasFactory;
    protected $table = 'validations';
    protected $primaryKey = 'validation_id';
    protected $guarded = [];
}
