<?php

namespace App\Services;

use App\Models\AlamatUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RajaOngkirService
{
    private function baseUrl(): string
    {
        return rtrim(config('services.rajaongkir.base_url'), '/');
    }

    private function apiKey(): string
    {
        $key = config('services.rajaongkir.key');

        if (!$key) {
            throw new RuntimeException(
                'API Key RajaOngkir belum dikonfigurasi.'
            );
        }

        return $key;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));

        $value = preg_replace(
            '/^(kota administrasi|kabupaten|kab\.?|kota)\s*/u',
            '',
            $value
        );

        return preg_replace('/[^a-z0-9]/u', '', $value);
    }

    public function destinationIdFor(
        AlamatUser $alamat
    ): int {
        $alamat->loadMissing(
            'desa.kecamatan.kota.provinsi'
        );

        $desa = $alamat->desa;
        $kecamatan = $desa?->kecamatan;
        $kota = $kecamatan?->kota;
        $provinsi = $kota?->provinsi;

        if (!$desa || !$kecamatan || !$kota || !$provinsi) {
            throw new RuntimeException(
                'Data wilayah alamat tujuan belum lengkap.'
            );
        }

        // Data pencarian disimpan sementara agar tidak
        // berulang kali memanggil API untuk lokasi yang sama.
        $search = $desa->nama_desa;

        $results = Cache::remember(
            'rajaongkir.destination.' . sha1($search),
            now()->addDay(),
            function () use ($search) {
                $response = Http::timeout(20)
                    ->withHeaders([
                        'key' => $this->apiKey(),
                    ])
                    ->get(
                        $this->baseUrl()
                        . '/destination/domestic-destination',
                        [
                            'search' => $search,
                            'limit' => 100,
                            'offset' => 0,
                        ]
                    );

                $response->throw();

                return $response->json('data') ?? [];
            }
        );

        $match = collect($results)->first(
            function ($item) use (
                $desa,
                $kecamatan,
                $kota,
                $provinsi
            ) {
                return
                    $this->normalize(
                        $item['subdistrict_name'] ?? ''
                    ) === $this->normalize($desa->nama_desa)
                    &&
                    $this->normalize(
                        $item['district_name'] ?? ''
                    ) === $this->normalize(
                        $kecamatan->nama_kecamatan
                    )
                    &&
                    $this->normalize(
                        $item['city_name'] ?? ''
                    ) === $this->normalize($kota->nama_kota)
                    &&
                    $this->normalize(
                        $item['province_name'] ?? ''
                    ) === $this->normalize(
                        $provinsi->nama_provinsi
                    );
            }
        );

        if (!$match || empty($match['id'])) {
            throw new RuntimeException(
                'Alamat tidak cocok dengan data tujuan RajaOngkir. '
                . 'Periksa nama kelurahan, kecamatan, kota, dan provinsi.'
            );
        }

        return (int) $match['id'];
    }

    public function calculateDomestic(
        int $origin,
        int $destination,
        int $weight,
        string $courier
    ): array {
        $response = Http::timeout(20)
            ->withHeaders([
                'key' => $this->apiKey(),
            ])
            ->asForm()
            ->post(
                $this->baseUrl()
                . '/calculate/domestic-cost',
                [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier,
                    'price' => 'lowest',
                ]
            );

        $response->throw();

        $results = $response->json('data');

        if (!is_array($results)) {
            throw new RuntimeException(
                'RajaOngkir tidak mengembalikan tarif pengiriman.'
            );
        }

        return $results;
    }
}