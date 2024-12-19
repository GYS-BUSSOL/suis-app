<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblAccessLevel extends Model
{
    use HasFactory;
    protected $table = 'tbl_acesslevel', $guarded = ['id'];
    public $timestamps = false;
}
