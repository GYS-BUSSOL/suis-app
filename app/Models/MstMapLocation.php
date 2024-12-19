<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstMapLocation extends Model
{
    use HasFactory;
    protected $table = 'mst_map_location', $guarded = ['id'];
    public $timestamps = false;
}
