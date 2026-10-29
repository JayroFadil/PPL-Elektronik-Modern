<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;

use App\Models\Wishlist;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    private function buildImageFilename(string $namaProduk, string $extension): string
    {
        $suffix = '-' . Str::random(8) . '.' . $extension;
        $base = Str::slug($namaProduk);
        $base = $base !== '' ? $base : 'produk';
        $maxBase = 50 - strlen($suffix);
        $base = $maxBase > 0 ? Str::limit($base, $maxBase, '') : 'p';

        return $base . $suffix;
    }

    /**
     * Halaman utama / landing page.
     */
    public function index()
    {
        // Ambil hanya kolom yang dibutuhkan untuk landing page
        $produkBaru = Produk::with('kategori')
            ->select('id_produk', 'id_kategori', 'gambar', 'nama_produk', 'harga', 'stok')
            ->latest('id_produk')
            ->take(8)
            ->get();

        $kategoris = Kategori::withCount('produks')
            ->select('id_kategori', 'nama_kategori', 'ikon_kategori')
            ->get();

        // Hitung statistik dalam 2 query ringkas
        $jumlahProduk = Produk::count();
        $jumlahUser   = User::count();

        // Produk terlaris: minimal terjual 10 dan status dikirim/selesai
        $produkTerlaris = DetailPesanan::query()
            ->select('id_produk')
            ->selectRaw('SUM(qty) as total_terjual')
            ->whereHas('pesanan', fn($q) => $q->whereIn('status_pesanan', ['dikirim', 'selesai']))
            ->groupBy('id_produk')
            ->having('total_terjual', '>=', 10)
            ->orderByDesc('total_terjual')
            ->with(['produk' => fn($q) => $q->select('id_produk', 'id_kategori', 'gambar', 'nama_produk', 'harga', 'stok')
                ->with(['kategori' => fn($q) => $q->select('id_kategori', 'nama_kategori')])])
            ->take(10)
            ->get();

        $wishlistIds = Auth::check()
            ? Wishlist::where('id_users', Auth::id())->pluck('id_produk')->toArray()
            : [];

        return view('index', compact('produkBaru', 'kategoris', 'jumlahProduk', 'jumlahUser', 'produkTerlaris', 'wishlistIds'));
    }

    /**
     * Halaman katalog produk dengan filter.
     */
    public function catalog(Request $request)
    {
        $kategoris = Kategori::withCount('produks')
            ->select('id_kategori', 'nama_kategori', 'ikon_kategori')
            ->orderBy('nama_kategori')
            ->get();

        $query = Produk::with(['kategori' => fn($q) => $q->select('id_kategori', 'nama_kategori')])
            ->select('id_produk', 'id_kategori', 'gambar', 'nama_produk', 'harga', 'stok');

        // Filter by kategori (bisa nama_kategori atau id_kategori)
        if ($request->filled('kategori')) {
            $katVal = $request->kategori;
            if (is_numeric($katVal)) {
                $query->where('id_kategori', $katVal);
            } else {
                $query->whereHas('kategori', fn($q) => $q->where('nama_kategori', $katVal));
            }
        }

        // Search by name – filter di DB bukan di Collection
        if ($request->filled('q')) {
            $query->where('nama_produk', 'like', '%' . $request->q . '%');
        }

        // Sort – di DB
        $sort  = $request->get('sort', 'terbaru');
        $query = match ($sort) {
            'termurah' => $query->orderBy('harga', 'asc'),
            'termahal' => $query->orderBy('harga', 'desc'),
            'nama'     => $query->orderBy('nama_produk', 'asc'),
            default    => $query->latest('id_produk'),
        };

        $produks = $query->paginate(12)->appends($request->query());

        $wishlistIds = Auth::check()
            ? Wishlist::where('id_users', Auth::id())->pluck('id_produk')->toArray()
            : [];

        return view('products.index', compact('produks', 'kategoris', 'wishlistIds'));
    }

    /**
     * Halaman detail produk.
     */
    public function show(Produk $produk)
    {
        $produk->load(['kategori' => fn($q) => $q->select('id_kategori', 'nama_kategori')]);

        $produkTerkait = Produk::with(['kategori' => fn($q) => $q->select('id_kategori', 'nama_kategori')])
            ->select('id_produk', 'id_kategori', 'gambar', 'nama_produk', 'harga', 'stok')
            ->where('id_kategori', $produk->id_kategori)
            ->where('id_produk', '!=', $produk->id_produk)
            ->take(4)
            ->get();

        $wishlistIds = Auth::check()
            ? Wishlist::where('id_users', Auth::id())->pluck('id_produk')->toArray()
            : [];

        return view('products.detail', compact('produk', 'produkTerkait', 'wishlistIds'));
    }

    /**
     * Admin: daftar produk – dengan pagination agar tidak load semua sekaligus.
     */
    public function adminIndex()
    {
        $produks   = Produk::with(['kategori' => fn($q) => $q->select('id_kategori', 'nama_kategori')])
            ->select('id_produk', 'id_kategori', 'gambar', 'nama_produk', 'harga', 'stok')
            ->latest('id_produk')
            ->paginate(20);

        $kategoris = Kategori::orderBy('nama_kategori')
            ->select('id_kategori', 'nama_kategori')
            ->get();

        return view('admin.products', compact('produks', 'kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_kategori' => ['required', 'integer', 'exists:kategoris,id_kategori'],
            'nama_produk' => ['required', 'string', 'max:50'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'berat_gram' => ['required', 'integer', 'min:1'],
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $extension = $request->file('gambar')->getClientOriginalExtension();
        $filename = $this->buildImageFilename($data['nama_produk'], $extension);
        $request->file('gambar')->storeAs('products', $filename, 'public');
        $data['gambar'] = $filename;

        Produk::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produk $produk)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('admin.products-edit', compact('produk', 'kategoris'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produk $produk)
    {
        $data = $request->validate([
            'id_kategori' => ['required', 'integer', 'exists:kategoris,id_kategori'],
            'nama_produk' => ['required', 'string', 'max:50'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'berat_gram' => ['required', 'integer', 'min:1'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('gambar')) {
            if ($produk->gambar) {
                Storage::disk('public')->delete('products/' . $produk->gambar);
            }

            $extension = $request->file('gambar')->getClientOriginalExtension();
            $filename = $this->buildImageFilename($data['nama_produk'], $extension);
            $request->file('gambar')->storeAs('products', $filename, 'public');
            $data['gambar'] = $filename;
        } else {
            unset($data['gambar']);
        }

        $produk->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
        if ($produk->gambar) {
            Storage::disk('public')->delete('products/' . $produk->gambar);
        }

        $produk->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil dihapus.');
    }
}
