<?php

namespace App\Http\Controllers;

use App\Models\AlamatUser;
use App\Models\Ekspedisi;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class OngkirController extends Controller
{
    public function calculate(
        Request $request,
        RajaOngkirService $rajaOngkir
    ) {
        $data = $request->validate([
            'id_alamat' => ['required', 'integer'],
            'courier' => [
                'required',
                Rule::in(['jne', 'jnt', 'pos', 'tiki']),
            ],
        ]);

        $alamat = AlamatUser::where(
            'id_users',
            Auth::id()
        )
            ->where('id_alamat', $data['id_alamat'])
            ->with('desa.kecamatan.kota.provinsi')
            ->first();

        if (!$alamat) {
            return response()->json([
                'message' => 'Alamat pengiriman tidak valid.',
            ], 422);
        }

        $origin = (int) config(
            'services.rajaongkir.origin_id'
        );

        if (
            !$origin ||
            !config('services.rajaongkir.key')
        ) {
            return response()->json([
                'message' =>
                    'Konfigurasi RajaOngkir belum lengkap.',
            ], 422);
        }

        $carrierNames = [
            'jne' => 'JNE',
            'jnt' => 'JNT',
            'pos' => 'POS',
            'tiki' => 'TIKI',
        ];

        $ekspedisi = Ekspedisi::whereRaw(
            'LOWER(nama_ekspedisi) = ?',
            [mb_strtolower($carrierNames[$data['courier']])]
        )->first();

        if (!$ekspedisi) {
            return response()->json([
                'message' =>
                    'Ekspedisi belum tersedia pada data aplikasi.',
            ], 422);
        }

        try {
            // RajaOngkir menerima berat dalam gram. Hitung total dari
            // berat setiap produk dikalikan kuantitas yang dipesan.
            $weight = $this->currentCartWeight();

            $destination = $rajaOngkir->destinationIdFor(
                $alamat
            );

            $rates = $rajaOngkir->calculateDomestic(
                $origin,
                $destination,
                $weight,
                $data['courier']
            );

            $options = [];

            foreach ($rates as $rate) {
                if (!isset($rate['cost'])) {
                    continue;
                }

                $token = Str::random(48);

                $service = trim(
                    ($rate['service'] ?? '')
                    . ' '
                    . ($rate['description'] ?? '')
                );

                $cost = (int) $rate['cost'];

                // Tarif disimpan di session server.
                // Checkout tidak boleh mempercayai harga
                // yang dikirim dari browser.
                session()->put(
                    'shipping_quotes.' . $token,
                    [
                        'id_alamat' => (int) $alamat->id_alamat,
                        'id_ekspedisi' =>
                            (int) $ekspedisi->id_ekspedisi,
                        'service' => $service,
                        'cost' => $cost,
                        'weight_gram' => $weight,
                        'created_at' => now()->timestamp,
                    ]
                );

                $options[] = [
                    'token' => $token,
                    'name' => $rate['name']
                        ?? $carrierNames[$data['courier']],
                    'service' => $rate['service'] ?? '',
                    'description' => $rate['description'] ?? '',
                    'cost' => $cost,
                    'etd' => $rate['etd'] ?? '-',
                ];
            }

            if (empty($options)) {
                return response()->json([
                    'message' =>
                        'Tidak ada tarif pengiriman untuk alamat dan kurir tersebut.',
                ], 422);
            }

            return response()->json([
                'data' => $options,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('RajaOngkir calculation failed.', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' =>
                    'Gagal menghitung ongkir. Periksa konfigurasi API dan coba kembali.',
            ], 502);
        }
    }

    private function currentCartWeight(): int
    {
        $weight = 0;

        $addWeight = function ($produk, int $qty) use (&$weight) {
            if (
                !$produk ||
                !$produk->berat_gram ||
                (int) $produk->berat_gram < 1
            ) {
                throw new RuntimeException(
                    'Isi berat dalam gram untuk semua produk di pesanan.'
                );
            }

            $weight += (int) $produk->berat_gram * $qty;
        };

        $buyNow = session('buy_now_item');

        if ($buyNow) {
            $produk = Produk::find(
                $buyNow['id_produk']
            );

            $addWeight(
                $produk,
                (int) $buyNow['qty']
            );

            return max(1, $weight);
        }

        $keranjang = Keranjang::where(
            'id_users',
            Auth::id()
        )
            ->with('detailKeranjangs.produk')
            ->first();

        if (
            !$keranjang ||
            $keranjang->detailKeranjangs->isEmpty()
        ) {
            throw new RuntimeException(
                'Keranjang belanja kosong.'
            );
        }

        foreach ($keranjang->detailKeranjangs as $detail) {
            $addWeight(
                $detail->produk,
                (int) $detail->qty
            );
        }

        return max(1, $weight);
    }
}