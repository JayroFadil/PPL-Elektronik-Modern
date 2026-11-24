<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransController extends Controller
{
    private function configure(): void
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Customer: halaman bayar (Snap popup).
     */
    public function pay(Pesanan $pesanan)
    {
        if ((int) $pesanan->id_users !== (int) Auth::id()) {
            abort(403);
        }

        $pembayaran = $pesanan->pembayaran;

        if (!$pembayaran || $pembayaran->metode_pembayaran !== 'Midtrans') {
            return redirect()->route('customer.orders')->with('error', 'Pesanan ini tidak memakai pembayaran online.');
        }

        if ((int) $pembayaran->status_konfirmasi === 1) {
            return redirect()->route('customer.orders')->with('status', 'Pesanan ini sudah dibayar.');
        }

        if (!config('midtrans.server_key') || !config('midtrans.client_key')) {
            return redirect()->route('customer.orders')->with('error', 'Konfigurasi Midtrans belum lengkap. Hubungi admin.');
        }

        $this->configure();

        // Buat token baru jika belum ada atau pembayaran sebelumnya gagal/kedaluwarsa.
        if (!$pembayaran->snap_token || (int) $pembayaran->status_konfirmasi === 2) {
            $orderId = 'ORDER-' . $pesanan->id_pesanan . '-' . time();
            $user = $pesanan->user;

            try {
                $token = Snap::getSnapToken([
                    'transaction_details' => [
                        'order_id'     => $orderId,
                        'gross_amount' => (int) $pesanan->total_bayar,
                    ],
                    'customer_details' => [
                        'first_name' => $user->nama ?? 'Customer',
                        'email'      => $user->email ?? null,
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::error('Midtrans snap token gagal: ' . $e->getMessage());
                return redirect()->route('customer.orders')->with('error', 'Gagal menghubungi Midtrans. Coba lagi nanti.');
            }

            $pembayaran->update([
                'midtrans_order_id' => $orderId,
                'snap_token'        => $token,
                'status_konfirmasi' => 0,
            ]);
        }

        return view('customer.pay', [
            'pesanan'      => $pesanan,
            'snapToken'    => $pembayaran->snap_token,
            'clientKey'    => config('midtrans.client_key'),
            'isProduction' => (bool) config('midtrans.is_production'),
        ]);
    }

    /**
     * Webhook dari Midtrans (tanpa auth & CSRF).
     */
    public function notification(Request $request)
    {
        $p = $request->all();

        $expected = hash('sha512',
            ($p['order_id'] ?? '') . ($p['status_code'] ?? '') . ($p['gross_amount'] ?? '') . config('midtrans.server_key')
        );

        if (!hash_equals($expected, (string) ($p['signature_key'] ?? ''))) {
            Log::warning('Midtrans notifikasi: signature tidak valid', ['order_id' => $p['order_id'] ?? null]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $status = $p['transaction_status'] ?? '';
        $fraud  = $p['fraud_status'] ?? null;

        $result = DB::transaction(function () use ($p, $status, $fraud) {
            $pembayaran = Pembayaran::where('midtrans_order_id', $p['order_id'])->lockForUpdate()->first();
            if (!$pembayaran) {
                return 404;
            }

            $pesanan = $pembayaran->pesanan;

            if ((int) floor((float) $p['gross_amount']) !== (int) $pesanan->total_bayar) {
                Log::warning('Midtrans notifikasi: nominal tidak cocok', ['order_id' => $p['order_id']]);
                return 400;
            }

            // Sudah lunas: abaikan notifikasi berikutnya.
            if ((int) $pembayaran->status_konfirmasi === 1) {
                return 200;
            }

            $paid = $status === 'settlement' || ($status === 'capture' && in_array($fraud, [null, 'accept'], true));
            $failed = in_array($status, ['deny', 'cancel', 'expire', 'failure'], true);

            if ($paid) {
                $pembayaran->update(['status_konfirmasi' => 1]);
                if ($pesanan->status_pesanan === 'menunggu') {
                    $pesanan->update(['status_pesanan' => 'diproses']);
                }
                $this->notify($pesanan->id_users, 'Pembayaran Berhasil',
                    'Pembayaran pesanan ' . $pesanan->no_resi . ' berhasil. Pesanan Anda sedang diproses.');
            } elseif ($failed) {
                $pembayaran->update(['status_konfirmasi' => 2]);
                $this->notify($pesanan->id_users, 'Pembayaran Gagal',
                    'Pembayaran pesanan ' . $pesanan->no_resi . ' gagal atau kedaluwarsa. Silakan coba bayar kembali dari halaman pesanan.');
            }

            return 200;
        });

        return response()->json(['message' => $result === 200 ? 'OK' : 'Error'], $result);
    }

    private function notify(int $userId, string $title, string $message): void
    {
        Notifikasi::create([
            'id_users' => $userId,
            'judul'    => $title,
            'pesan'    => $message,
            'is_read'  => 0,
        ]);
    }
}
