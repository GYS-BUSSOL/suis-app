<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstDbCodeReadr extends Model
{
    use HasFactory;
    protected $table = 'mst_db_codereadr', $guarded = ['id'];
    public $timestamps = false;
}
