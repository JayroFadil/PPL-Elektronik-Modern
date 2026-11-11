<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlamatUser extends Model
{
    protected $table = 'alamat_users';
    protected $primaryKey = 'id_alamat';
    public $timestamps = false;
    protected $fillable = [
        'id_users',
        'id_desa',
        'label_alamat',
        'nama_penerima',
        'nomor_telepon',
        'detail_alamat',
        'is_utama',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_users', 'id_users');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'id_alamat', 'id_alamat');
    }
}
