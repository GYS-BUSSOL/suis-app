<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransSendQrDetail extends Model
{
    use HasFactory;
    protected $table = 'trans_sendQr_detail', $guarded = ['id_sendQr_detail'];
    public $timestamps = false;
    protected $primaryKey = 'id_sendQr_detail';
}
