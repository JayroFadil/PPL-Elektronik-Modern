<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    // Menguji koneksi RajaOngkir
    public function test()
    {
        $apiKey = config('services.rajaongkir.api_key');
        $baseUrl = config('services.rajaongkir.base_url');

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'API Key belum diatur di file .env'
            ], 500);
        }

        $response = Http::withHeaders([
            'key' => $apiKey,
        ])->get($baseUrl . '/destination/domestic-destination', [
            'search' => '69162',
            'limit' => 5,
            'offset' => 0,
        ]);

        return response()->json(
            $response->json(),
            $response->status()
        );
    }

    // Mencari lokasi berdasarkan nama atau kode pos
    public function search(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|min:2|max:100',
        ]);

        $apiKey = config('services.rajaongkir.api_key');
        $baseUrl = config('services.rajaongkir.base_url');

        if (!$apiKey) {
            return response()->json([
                'message' => 'API Key RajaOngkir belum diatur.'
            ], 500);
        }

        $response = Http::timeout(20)
            ->withHeaders([
                'key' => $apiKey,
            ])
            ->get($baseUrl . '/destination/domestic-destination', [
                'search' => $validated['search'],
                'limit' => 10,
                'offset' => 0,
            ]);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Gagal mengambil data dari RajaOngkir.',
                'detail' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json());
    }
}
