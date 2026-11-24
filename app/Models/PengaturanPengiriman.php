<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanPengiriman extends Model
{
    protected $table = 'pengaturan_pengiriman';

    protected $fillable = [
        'origin_id',
        'alamat_asal',
    ];
}
