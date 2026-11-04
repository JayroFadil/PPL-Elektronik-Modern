<?php

namespace Tests\Unit;

use App\Models\AlamatUser;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kota;
use App\Models\Provinsi;
use App\Services\RajaOngkirShippingService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RajaOngkirShippingServiceTest extends TestCase
{
    public function test_it_resolves_local_address_to_rajaongkir_destination_and_calculates_shipping(): void
    {
        config([
            'services.rajaongkir.api_key' => 'shipping-cost-test-key',
            'services.rajaongkir.origin_id' => 'origin-123',
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
            'services.rajaongkir.couriers' => 'jne',
        ]);

        Http::fake([
            'rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'data' => [[
                    'id' => 456,
                    'subdistrict_name' => 'Kertajaya',
                    'district_name' => 'Gubeng',
                    'city_name' => 'Surabaya',
                    'province_name' => 'Jawa Timur',
                ]],
            ]),
            'rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'meta' => ['code' => 200, 'status' => 'success'],
                'data' => [[
                    'name' => 'JNE',
                    'code' => 'jne',
                    'service' => 'CTC',
                    'cost' => 35000,
                    'etd' => '9 day',
                ]],
            ]),
        ]);

        $province = new Provinsi(['nama_provinsi' => 'Jawa Timur']);
        $city = new Kota(['nama_kota' => 'Surabaya']);
        $city->setRelation('provinsi', $province);
        $district = new Kecamatan(['nama_kecamatan' => 'Gubeng']);
        $district->setRelation('kota', $city);
        $village = new Desa(['nama_desa' => 'Kertajaya']);
        $village->setRelation('kecamatan', $district);
        $address = new AlamatUser();
        $address->setRelation('desa', $village);

        $options = (new RajaOngkirShippingService())->getOptionsForAlamat($address, 2);

        $this->assertSame(1, $options[0]['id_ekspedisi']);
        $this->assertSame('JNE', $options[0]['kode_kurir']);
        $this->assertSame(35000, $options[0]['biaya_pengiriman']);
        $this->assertSame('9 day', $options[0]['etd']);

        Http::assertSent(function (Request $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return str_contains($request->url(), '/destination/domestic-destination')
                && $request->method() === 'GET'
                && ($query['search'] ?? null) === 'Kertajaya Gubeng Surabaya Jawa Timur';
        });
        Http::assertSent(function (Request $request): bool {
            return str_ends_with($request->url(), '/calculate/domestic-cost')
                && (string) $request['destination'] === '456'
                && (string) $request['weight'] === '2000';
        });
    }

    public function test_it_requests_and_parses_komerce_domestic_cost_response(): void
    {
        config([
            'services.rajaongkir.api_key' => 'shipping-cost-test-key',
            'services.rajaongkir.origin_id' => 'origin-123',
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
        ]);

        Http::fake([
            'rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'meta' => [
                    'message' => 'Success Calculate Domestic Shipping cost',
                    'code' => 200,
                    'status' => 'success',
                ],
                'data' => [
                    [
                        'name' => 'Jalur Nugraha Ekakurir (JNE)',
                        'code' => 'jne',
                        'service' => 'CTC',
                        'description' => 'JNE City Courier',
                        'cost' => 35000,
                        'etd' => '9 day',
                    ],
                ],
            ]),
        ]);

        $service = new RajaOngkirShippingService();
        $results = $service->getDomesticCost(456, 2000, 'jne');

        $this->assertSame([
            [
                'code' => 'JNE',
                'service' => 'CTC',
                'cost' => 35000,
                'etd' => '9 day',
            ],
        ], $results);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost'
                && $request->method() === 'POST'
                && $request->hasHeader('key', 'shipping-cost-test-key')
                && (string) $request['origin'] === 'origin-123'
                && (string) $request['destination'] === '456'
                && (string) $request['weight'] === '2000'
                && (string) $request['courier'] === 'jne';
        });
    }

    public function test_it_requests_only_selected_couriers_and_keeps_all_returned_services(): void
    {
        config([
            'services.rajaongkir.api_key' => 'shipping-cost-test-key',
            'services.rajaongkir.origin_id' => 'origin-123',
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
            'services.rajaongkir.couriers' => 'jne:pos:tiki:jnt',
        ]);

        Http::fake([
            'rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'data' => [[
                    'id' => 456,
                    'subdistrict_name' => 'Kertajaya',
                    'district_name' => 'Gubeng',
                    'city_name' => 'Surabaya',
                    'province_name' => 'Jawa Timur',
                ]],
            ]),
            'rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => function (Request $request) {
                $couriers = explode(':', strtoupper((string) $request['courier']));
                $services = [];

                foreach ($couriers as $courier) {
                    $services[] = [
                        [
                            'code' => $courier,
                            'service' => 'REG',
                            'cost' => 10000,
                            'etd' => '2 day',
                        ],
                        [
                            'code' => $courier,
                            'service' => 'EXP',
                            'cost' => 20000,
                            'etd' => '1 day',
                        ],
                    ];
                }

                return Http::response(['data' => array_merge(...$services)]);
            },
        ]);

        $province = new Provinsi(['nama_provinsi' => 'Jawa Timur']);
        $city = new Kota(['nama_kota' => 'Surabaya']);
        $city->setRelation('provinsi', $province);
        $district = new Kecamatan(['nama_kecamatan' => 'Gubeng']);
        $district->setRelation('kota', $city);
        $village = new Desa(['nama_desa' => 'Kertajaya']);
        $village->setRelation('kecamatan', $district);
        $address = new AlamatUser();
        $address->setRelation('desa', $village);

        $options = (new RajaOngkirShippingService())->getOptionsForAlamat($address);

        $this->assertCount(8, $options);
        $this->assertSame(
            ['JNE', 'JNT', 'POS', 'TIKI'],
            collect($options)->pluck('kode_kurir')->unique()->sort()->values()->all()
        );
        $this->assertSame(
            ['EXP', 'REG'],
            collect($options)->pluck('service')->unique()->sort()->values()->all()
        );

        Http::assertSent(function (Request $request): bool {
            return str_ends_with($request->url(), '/calculate/domestic-cost')
                && (string) $request['courier'] === 'jne:pos:tiki:jnt';
        });
    }
}
