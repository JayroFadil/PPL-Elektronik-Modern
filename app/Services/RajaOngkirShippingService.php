<?php

namespace App\Services;

use App\Models\AlamatUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirShippingService
{
    public function isConfigured(): bool
    {
        return filled(config('services.rajaongkir.api_key'))
            && filled(config('services.rajaongkir.origin_id'));
    }

    public function resolveDestinationCityId(AlamatUser $alamat): ?int
    {
        $alamat->loadMissing('desa.kecamatan.kota.provinsi');

        $district = $alamat->desa?->kecamatan;
        $village = $alamat->desa;
        $city = $district?->kota;
        $province = $city?->provinsi;

        if (!$village || !$district || !$city || !$province) {
            return null;
        }

        $baseUrl = rtrim((string) config('services.rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
        $response = Http::withHeaders([
            'key' => config('services.rajaongkir.api_key'),
        ])
            ->acceptJson()
            ->get($baseUrl . '/destination/domestic-destination', [
                'search' => $village->nama_desa . ' ' . $district->nama_kecamatan . ' ' . $city->nama_kota . ' ' . $province->nama_provinsi,
                'limit' => 10,
                'offset' => 0,
            ]);

        if (!$response->successful()) {
            Log::warning('RajaOngkir destination lookup failed.', [
                'status' => $response->status(),
                'city' => $city->nama_kota,
                'district' => $district->nama_kecamatan,
                'response' => $response->json(),
            ]);

            return null;
        }

        $destinations = $response->json('data');
        if (!is_array($destinations)) {
            Log::warning('RajaOngkir destination lookup returned an unexpected format.', [
                'city' => $city->nama_kota,
                'district' => $district->nama_kecamatan,
                'response' => $response->json(),
            ]);

            return null;
        }

        foreach ($destinations as $destination) {
            if (!is_array($destination) || !isset($destination['id'])) {
                continue;
            }

            $candidateCity = $destination['city_name'] ?? $destination['city'] ?? null;
            $candidateProvince = $destination['province_name'] ?? $destination['province'] ?? null;
            $candidateDistrict = $destination['district_name'] ?? $destination['district'] ?? null;
            $candidateVillage = $destination['subdistrict_name'] ?? $destination['village_name'] ?? null;

            if (
                $this->matchesLocationName($candidateCity, $city->nama_kota)
                && $this->matchesLocationName($candidateProvince, $province->nama_provinsi)
                && ($candidateDistrict === null || $this->matchesLocationName($candidateDistrict, $district->nama_kecamatan))
                && ($candidateVillage === null || $this->matchesLocationName($candidateVillage, $village->nama_desa))
            ) {
                return (int) $destination['id'];
            }
        }

        Log::warning('RajaOngkir destination was not found for the saved address.', [
            'city' => $city->nama_kota,
            'district' => $district->nama_kecamatan,
            'village' => $village->nama_desa,
            'province' => $province->nama_provinsi,
        ]);

        return null;
    }

    public function getOptionsForAlamat(AlamatUser $alamat, int $totalQty = 1): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $destinationId = $this->resolveDestinationCityId($alamat);
        if ($destinationId === null) {
            return [];
        }

        $couriers = $this->courierCodes();
        if ($couriers === []) {
            return [];
        }

        $weightGram = max(1, (int) $totalQty) * 1000;
        $options = [];

        $results = $this->getDomesticCost($destinationId, $weightGram, implode(':', $couriers));

        foreach ($results as $result) {
            $courier = $result['code'] ?? '';
            $options[] = [
                'id_ekspedisi' => $this->mapCourierToEkspedisiId($courier),
                'kode_kurir' => strtoupper((string) $courier),
                'nama_ekspedisi' => strtoupper((string) $courier),
                'service' => (string) ($result['service'] ?? 'REG'),
                'biaya_pengiriman' => (int) ($result['cost'] ?? 0),
                'etd' => (string) ($result['etd'] ?? '1-3 hari'),
            ];
        }

        $options = array_values(array_filter($options, fn ($option) => $option['id_ekspedisi'] !== null));
        usort($options, fn ($a, $b) => ($a['biaya_pengiriman'] <=> $b['biaya_pengiriman']));

        return $options;
    }

    public function getDomesticCost(int $destinationId, int $weightGram, ?string $courier = null): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $baseUrl = rtrim((string) config('services.rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
        $courierValue = strtolower(trim((string) ($courier ?? config('services.rajaongkir.couriers', 'jne'))));

        if ($courierValue === '') {
            return [];
        }

        $response = Http::withHeaders([
            'key' => config('services.rajaongkir.api_key'),
        ])
            ->acceptJson()
            ->asForm()
            ->post($baseUrl . '/calculate/domestic-cost', [
                'origin' => (string) config('services.rajaongkir.origin_id'),
                'destination' => (string) $destinationId,
                'weight' => max(1, (int) $weightGram),
                'courier' => $courierValue,
                'price' => 'lowest',
            ]);

        if (!$response->successful()) {
            Log::warning('RajaOngkir shipping cost request failed.', [
                'status' => $response->status(),
                'courier' => $courierValue,
                'response' => $response->json(),
            ]);

            return [];
        }

        $payload = $response->json('data');
        if (!is_array($payload)) {
            Log::warning('RajaOngkir shipping cost response has an unexpected format.', [
                'courier' => $courierValue,
                'response' => $response->json(),
            ]);

            return [];
        }

        $results = [];

        foreach ($payload as $costEntry) {
            $courierCode = $costEntry['code'] ?? null;
            if (!is_string($courierCode) || $courierCode === '') {
                continue;
            }

            $cost = filter_var($costEntry['cost'] ?? null, FILTER_VALIDATE_INT);
            if ($cost === false || $cost < 0) {
                continue;
            }

            $results[] = [
                'code' => strtoupper($courierCode),
                'service' => strtoupper((string) ($costEntry['service'] ?? $courierCode)),
                'cost' => $cost,
                'etd' => (string) ($costEntry['etd'] ?? 'Estimasi tidak tersedia'),
            ];
        }

        return $results;
    }

    protected function courierCodes(): array
    {
        $configured = (string) config('services.rajaongkir.couriers', 'jne:pos:tiki:jnt');
        $couriers = preg_split('/[,:;|]/', $configured, -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter(array_map(fn ($courier) => strtolower(trim((string) $courier)), $couriers)));
    }

    protected function matchesLocationName(?string $candidate, string $expected): bool
    {
        if ($candidate === null) {
            return false;
        }

        $normalize = static function (string $name): string {
            $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $name)));

            return preg_replace('/^(kota administrasi|kabupaten|kota|kecamatan|kec\\.?|kelurahan|desa|provinsi|prov\\.)\\s*/u', '', $normalized);
        };

        return $normalize($candidate) === $normalize($expected);
    }

    protected function mapCourierToEkspedisiId(string $courierCode): ?int
    {
        $normalized = strtoupper(trim((string) $courierCode));

        $map = [
            'JNE' => 1,
            'JNT' => 2,
            'SICEPAT' => 3,
            'POS' => 4,
            'TIKI' => 5,
        ];

        foreach ($map as $code => $id) {
            if (str_contains($normalized, $code)) {
                return $id;
            }
        }

        return $map[$normalized] ?? null;
    }
}
