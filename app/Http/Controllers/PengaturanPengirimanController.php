<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengaturanPengiriman;

class PengaturanPengirimanController extends Controller
{
    // Menampilkan halaman pengaturan pengiriman
    public function index()
    {
        $pengaturan = PengaturanPengiriman::firstOrCreate(
            ['id' => 1]
        );

        return view('pengaturan-pengiriman.index', compact('pengaturan'));
    }

    // Menyimpan perubahan pengaturan pengiriman
    public function update(Request $request)
    {
        $request->validate([
            'origin_id' => 'nullable|string|max:255',
            'alamat_asal' => 'nullable|string|max:1000',
        ]);

        $pengaturan = PengaturanPengiriman::firstOrCreate(
            ['id' => 1]
        );

        $pengaturan->update([
            'origin_id' => $request->origin_id,
            'alamat_asal' => $request->alamat_asal,
        ]);

        return redirect()->back()->with(
            'success',
            'Pengaturan pengiriman berhasil diperbarui.'
        );
    }
}
