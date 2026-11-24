<?php
namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\MidtransPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

class MidtransController extends Controller
{
    /**
     * Customer: halaman pembayaran melalui Midtrans Snap.
     */
    public function pay(Pesanan $pesanan, MidtransPaymentService $midtrans)
    {
        if ((int) $pesanan->id_users !== (int) Auth::id()) {
            abort(403);
        }

        $pembayaran = $pesanan->pembayaran;

        if (
            !$pembayaran ||
            !in_array($pembayaran->metode_pembayaran, ['Midtrans', 'Midtrans Snap'], true)
        ) {
            return redirect()->route('customer.orders')
                ->with('error', 'Pesanan ini tidak memakai pembayaran online.');
        }

        if ((int) $pembayaran->status_konfirmasi === 1) {
            return redirect()->route('customer.orders')
                ->with('status', 'Pesanan ini sudah dibayar.');
        }

        if (!$midtrans->isConfigured()) {
            return redirect()->route('customer.orders')
                ->with('error', 'Konfigurasi Midtrans belum lengkap. Hubungi admin.');
        }

        try {
            $snapToken = $midtrans->createSnapToken(
                $pesanan->load('user'),
                $pembayaran,
                (int) $pembayaran->status_konfirmasi === 2
            );
        } catch (ConnectionException | RequestException | RuntimeException $exception) {
            Log::error('Midtrans snap token gagal.', [
                'pesanan_id' => $pesanan->id_pesanan,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('customer.orders')
                ->with('error', 'Gagal menghubungi Midtrans. Silakan coba lagi nanti.');
        }

        return view('customer.pay', [
            'pesanan' => $pesanan,
            'snapToken' => $snapToken,
            'clientKey' => config('services.midtrans.client_key'),
            'isProduction' => (bool) config('services.midtrans.is_production'),
        ]);
    }

    /**
     * Webhook notifikasi dari Midtrans.
     */
    public function notification(
        Request $request,
        MidtransPaymentService $midtrans
    ) {
        $p = $request->all();

        if (!$midtrans->isValidNotificationSignature($p)) {
            Log::warning('Midtrans notifikasi: signature tidak valid', [
                'order_id' => $p['order_id'] ?? null,
            ]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $status = $p['transaction_status'] ?? '';
        $fraud = $p['fraud_status'] ?? null;

        $result = DB::transaction(function () use ($p, $status, $fraud, $midtrans) {
            $pembayaran = Pembayaran::where(
                'midtrans_order_id',
                $p['order_id']
            )->lockForUpdate()->first();

            if (!$pembayaran) {
                return 404;
            }

            $pesanan = $pembayaran->pesanan;

            if (!$pesanan || !$midtrans->isMatchingAmount(
                (string) ($p['gross_amount'] ?? ''),
                (int) $pesanan->total_bayar
            )) {
                Log::warning('Midtrans notifikasi: nominal tidak cocok', [
                    'order_id' => $p['order_id'] ?? null,
                ]);

                return 400;
            }

            if ((int) $pembayaran->status_konfirmasi === 1) {
                return 200;
            }

            $paid = $status === 'settlement'
                || (
                    $status === 'capture'
                    && in_array($fraud, [null, 'accept'], true)
                );

            $failed = in_array(
                $status,
                ['deny', 'cancel', 'expire', 'failure'],
                true
            );

            if ($paid) {
                $pembayaran->update([
                    'status_konfirmasi' => 1,
                    'transaction_status' => $status,
                    'transaction_id' => $p['transaction_id'] ?? null,
                    'payment_type' => $p['payment_type'] ?? null,
                    'fraud_status' => $fraud,
                    'paid_at' => now(),
                ]);

                if ($pesanan->status_pesanan === 'menunggu') {
                    $pesanan->update([
                        'status_pesanan' => 'diproses',
                    ]);
                }

                $this->notify(
                    $pesanan->id_users,
                    'Pembayaran Berhasil',
                    'Pembayaran pesanan ' . $pesanan->no_resi
                        . ' berhasil. Pesanan Anda sedang diproses.'
                );
            } elseif ($failed) {
                $pembayaran->update([
                    'status_konfirmasi' => 2,
                    'transaction_status' => $status,
                    'transaction_id' => $p['transaction_id'] ?? null,
                    'payment_type' => $p['payment_type'] ?? null,
                    'fraud_status' => $fraud,
                ]);

                $this->notify(
                    $pesanan->id_users,
                    'Pembayaran Gagal',
                    'Pembayaran pesanan ' . $pesanan->no_resi
                        . ' gagal atau kedaluwarsa. Silakan coba bayar kembali dari halaman pesanan.'
                );
            } else {
                $pembayaran->update([
                    'transaction_status' => $status,
                    'transaction_id' => $p['transaction_id'] ?? null,
                    'payment_type' => $p['payment_type'] ?? null,
                    'fraud_status' => $fraud,
                ]);
            }

            return 200;
        });

        return response()->json([
            'message' => $result === 200 ? 'OK' : 'Error',
        ], $result);
    }

    private function notify(
        int $userId,
        string $title,
        string $message
    ): void {
        Notifikasi::create([
            'id_users' => $userId,
            'judul' => $title,
            'pesan' => $message,
            'is_read' => 0,
        ]);
    }
}
