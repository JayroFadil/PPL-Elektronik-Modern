<?php

namespace App\Models;

use App\Models\DetailKeranjang;
use App\Models\DetailPesanan;
use App\Models\Kategori;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'produks';
    protected $primaryKey = 'id_produk';
    public $timestamps = false;
    protected $fillable = [
        'id_kategori',
        'gambar',
        'nama_produk',
        'deskripsi',
        'harga',
        'stok',
        'berat_gram',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function detailKeranjangs(): HasMany
    {
        return $this->hasMany(DetailKeranjang::class, 'id_produk', 'id_produk');
    }

    public function detailPesanans(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'id_produk', 'id_produk');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'id_produk', 'id_produk');
    }

    /**
     * Use nama_produk (lowercase with underscores) instead of id_produk for routing.
     */
    public function getRouteKey()
    {
        // "Kabel LAN" -> "kabel_lan"
        return str_replace(' ', '_', strtolower($this->nama_produk));
    }

    /**
     * Resolve model binding using id_produk or nama_produk.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // "kabel_lan" -> "kabel lan"
        $nameForQuery = str_replace('_', ' ', $value);

        return $this->where('id_produk', $value)
            ->orWhere('nama_produk', 'like', $nameForQuery)
            ->firstOrFail();
    }
}
