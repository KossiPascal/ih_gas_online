<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationBancaire extends Model
{
    use HasFactory;
    protected $table = 'operation_bancaires';
    protected $primaryKey = 'operation_id';
    protected $guarded = [];
}
