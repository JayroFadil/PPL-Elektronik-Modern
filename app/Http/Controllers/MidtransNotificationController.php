<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Notifikasi;
use App\Services\MidtransPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MidtransNotificationController extends Controller
{
    public function handle(Request $request, MidtransPaymentService $midtrans): JsonResponse
    {
        $notification = $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required', 'string'],
            'signature_key' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
            'transaction_status' => ['required', 'string'],
            'payment_type' => ['nullable', 'string'],
            'fraud_status' => ['nullable', 'string'],
        ]);

        if (!$midtrans->isValidNotificationSignature($notification)) {
            return response()->json(['message' => 'Invalid notification signature.'], 403);
        }

        $pembayaran = Pembayaran::with('pesanan')
            ->where('midtrans_order_id', $notification['order_id'])
            ->first();

        if (!$pembayaran || !$pembayaran->pesanan) {
            return response()->json(['message' => 'Payment record not found.'], 404);
        }

        if (!$midtrans->isMatchingAmount($notification['gross_amount'], (int) $pembayaran->pesanan->total_bayar)) {
            return response()->json(['message' => 'Notification amount does not match the order.'], 422);
        }

        $transactionStatus = $notification['transaction_status'];
        $fraudStatus = $notification['fraud_status'] ?? null;
        $isPaid = $transactionStatus === 'settlement'
            || ($transactionStatus === 'capture' && $fraudStatus === 'accept');
        $isFailed = in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true);

        DB::transaction(function () use ($notification, $pembayaran, $transactionStatus, $fraudStatus, $isPaid, $isFailed) {
            $lockedPayment = Pembayaran::query()
                ->whereKey($pembayaran->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $pesanan = Pesanan::query()
                ->whereKey($lockedPayment->id_pesanan)
                ->lockForUpdate()
                ->firstOrFail();

            $previousPaymentStatus = (int) $lockedPayment->status_konfirmasi;
            if ($previousPaymentStatus !== 1) {
                $lockedPayment->update([
                    'transaction_id' => $notification['transaction_id'] ?? $lockedPayment->transaction_id,
                    'transaction_status' => $transactionStatus,
                    'payment_type' => $notification['payment_type'] ?? $lockedPayment->payment_type,
                    'fraud_status' => $fraudStatus,
                    'status_konfirmasi' => $isPaid ? 1 : ($isFailed ? 2 : 0),
                    'paid_at' => $isPaid ? ($lockedPayment->paid_at ?? now()) : null,
                ]);

                if ($isPaid && $pesanan->status_pesanan === 'menunggu') {
                    $pesanan->update(['status_pesanan' => 'diproses']);
                    Notifikasi::create([
                        'id_users' => $pesanan->id_users,
                        'judul' => 'Pembayaran Terverifikasi',
                        'pesan' => 'Pembayaran untuk pesanan ' . $pesanan->no_resi . ' berhasil. Pesanan Anda sedang diproses.',
                        'is_read' => 0,
                    ]);
                } elseif ($isFailed && $previousPaymentStatus !== 2) {
                    Notifikasi::create([
                        'id_users' => $pesanan->id_users,
                        'judul' => 'Pembayaran Gagal',
                        'pesan' => 'Pembayaran untuk pesanan ' . $pesanan->no_resi . ' belum berhasil. Silakan coba pembayaran kembali.',
                        'is_read' => 0,
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Notification processed.']);
    }
}
