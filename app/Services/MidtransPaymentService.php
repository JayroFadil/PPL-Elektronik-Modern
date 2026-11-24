<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransPaymentService
{
    private const ENABLED_PAYMENTS = [
        'bank_transfer',
        'echannel',
        'gopay',
        'shopeepay',
        'alfamart',
        'Indomaret',
    ];

    public function isConfigured(): bool
    {
        return filled(config('services.midtrans.server_key'))
            && filled(config('services.midtrans.client_key'));
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function createSnapToken(Pesanan $pesanan, Pembayaran $pembayaran, bool $newAttempt = false): string
    {
        if (!$newAttempt && filled($pembayaran->snap_token)) {
            return $pembayaran->snap_token;
        }

        $orderId = $pembayaran->midtrans_order_id ?: $pesanan->no_resi;
        if ($newAttempt && $pembayaran->midtrans_order_id) {
            $orderId = $pesanan->no_resi . '-' . Str::upper(Str::random(8));
        }

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $response = Http::timeout(15)
            ->withBasicAuth(config('services.midtrans.server_key'), '')
            ->acceptJson()
            ->post($baseUrl . '/snap/v1/transactions', [
                'enabled_payments' => self::ENABLED_PAYMENTS,
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $pesanan->total_bayar,
                ],
                'customer_details' => [
                    'first_name' => $pesanan->user->nama,
                    'email' => $pesanan->user->email,
                ],
            ])
            ->throw();

        $token = $response->json('token');
        if (!is_string($token) || $token === '') {
            throw new RuntimeException('Midtrans did not return a Snap token.');
        }

        $pembayaran->update([
            'midtrans_order_id' => $orderId,
            'snap_token' => $token,
            'transaction_status' => null,
            'transaction_id' => null,
            'payment_type' => null,
            'fraud_status' => null,
            'paid_at' => null,
            'status_konfirmasi' => 0,
        ]);

        return $token;
    }
	
    /**
     * Mengambil detail transaksi dari Midtrans Status API.
     */
    public function getTransactionStatus(string $orderId): array
    {
        $baseUrl = config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        $response = Http::timeout(15)
            ->withBasicAuth(config('services.midtrans.server_key'), '')
            ->acceptJson()
            ->get($baseUrl . '/v2/' . rawurlencode($orderId) . '/status')
            ->throw();

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Invalid Midtrans transaction response.');
        }

        return $data;
    }

     public function isValidNotificationSignature(array $notification): bool
{
    $signatureKey = config('services.midtrans.signature_key');

    if (!is_string($signatureKey) || $signatureKey === '') {
        return false;
    }

    $expected = hash(
        'sha512',
        $notification['order_id']
            . $notification['status_code']
            . $notification['gross_amount']
            . $signatureKey
    );

    return hash_equals($expected, $notification['signature_key']);
}

    public function isMatchingAmount(string $grossAmount, int $expectedAmount): bool
    {
        return $grossAmount === number_format($expectedAmount, 2, '.', '');
    }
}
