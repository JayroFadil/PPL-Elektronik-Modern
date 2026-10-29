<?php

namespace App\Models;

use App\Models\AlamatUser;
use App\Models\DetailPesanan;
use App\Models\Ekspedisi;
use App\Models\Pembayaran;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;


class Pesanan extends Model
{
    protected $table = 'pesanans';
    protected $primaryKey = 'id_pesanan';
    public $timestamps = false;
    protected $fillable = [
        'id_users',
        'id_alamat',
        'id_promo',
        'id_ekspedisi',
        'tanggal_pesan',
        'subtotal',
        'diskon',
        'no_resi',
        'ongkos_kirim',
        'total_bayar',
        'status_pesanan',
        'layanan_ekspedisi',
    ];

    protected $casts = [
        'tanggal_pesan' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_users', 'id_users');
    }

    public function alamat(): BelongsTo
    {
        return $this->belongsTo(AlamatUser::class, 'id_alamat', 'id_alamat');
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class, 'id_promo', 'id_promo');
    }

    public function ekspedisi(): BelongsTo
    {
        return $this->belongsTo(Ekspedisi::class, 'id_ekspedisi', 'id_ekspedisi');
    }

    public function detailPesanans(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_pesanan', 'id_pesanan');
    }
}
