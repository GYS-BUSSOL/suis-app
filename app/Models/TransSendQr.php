<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransSendQr extends Model
{
    use HasFactory;
    protected $table = 'trans_sendQr', $guarded = ['id'];
    public $timestamps = false;
}
