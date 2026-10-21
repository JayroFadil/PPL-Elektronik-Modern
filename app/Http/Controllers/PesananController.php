<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Produk;
use App\Models\Ekspedisi;
use App\Models\AlamatUser;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\Promo;
use App\Models\Keranjang;
use App\Services\MidtransPaymentService;
use App\Traits\OrderStats;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PesananController extends Controller
{
    use OrderStats;

    /**
     * Status flow yang berurutan.
     */
    private const STATUS_FLOW = ['menunggu', 'diproses', 'dikirim', 'selesai'];

    /**
     * Admin: daftar pesanan.
     */
    public function index()
    {
        $pesanans = Pesanan::with([
                'user:id_users,nama,email',
                'pembayaran:id_pembayaran,id_pesanan,metode_pembayaran,bukti_bayar,status_konfirmasi,transaction_status,payment_type,midtrans_order_id',
                'ekspedisi:id_ekspedisi,nama_ekspedisi,biaya_pengiriman',
                'detailPesanans.produk:id_produk,nama_produk,gambar,harga',
                'promo',
            ])
            ->select('id_pesanan','id_users','id_ekspedisi','id_promo','id_alamat','no_resi','tanggal_pesan',
                     'status_pesanan','total_bayar','subtotal','diskon','ongkos_kirim')
            ->latest('tanggal_pesan')
            ->paginate(20);

        $statusCounts = $this->getOrderStats();
        $stats = $this->mapStatusCounts($statusCounts);

        $jumlahMenunggu  = $stats['menunggu'];
        $jumlahDiproses  = $stats['diproses'];
        $jumlahDikirim   = $stats['dikirim'];
        $jumlahSelesai   = $stats['selesai'];

        return view('admin.orders', compact('pesanans', 'jumlahDiproses', 'jumlahDikirim', 'jumlahSelesai', 'jumlahMenunggu'));
    }

    /**
     * Update status pesanan secara berurutan (admin).
     * menunggu → diproses → dikirim → selesai
     */
    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        $data = $request->validate([
            'status_pesanan' => ['required', 'in:menunggu,diproses,dikirim,selesai'],
        ]);

        $currentIndex = array_search($pesanan->status_pesanan, self::STATUS_FLOW);
        $newIndex = array_search($data['status_pesanan'], self::STATUS_FLOW);

        // Validasi: Status hanya boleh maju satu langkah ke depan
        if ($newIndex !== $currentIndex + 1) {
            return redirect()
                ->route('admin.orders.index')
                ->with('error', 'Status hanya bisa diubah ke tahap berikutnya secara berurutan.');
        }

        // Admin tidak boleh mengubah status ke 'selesai', itu hak customer
        if ($data['status_pesanan'] === 'selesai') {
            return redirect()
                ->route('admin.orders.index')
                ->with('error', 'Hanya pelanggan yang dapat menandai pesanan sebagai selesai.');
        }

        // Validasi Ekstra: Wajib verifikasi pembayaran jika mau ke "diproses"
        if ($data['status_pesanan'] === 'diproses' && (int) ($pesanan->pembayaran?->status_konfirmasi ?? 0) !== 1) {
            return redirect()
                ->route('admin.orders.index')
                ->with('error', 'Pembayaran belum dilakukan oleh pembeli, jadi pesanan belum dapat diproses.');
        }

        DB::transaction(function () use ($pesanan, $data) {
            $pesanan->update($data);
            $this->notifyCustomer(
                $pesanan->id_users,
                $this->statusNotificationTitle($pesanan->status_pesanan),
                $this->statusNotificationMessage($pesanan)
            );
        });

        return redirect()
            ->route('admin.orders.index')
            ->with('status', 'Status pesanan ' . $pesanan->no_resi . ' berhasil diperbarui.');
    }

    /**
     * Customer: tandai pesanan sebagai selesai.
     */
    public function completeOrder(Pesanan $pesanan)
    {
        // Pastikan pesanan milik user yang sedang login
        if ($pesanan->id_users !== Auth::id()) {
            return back()->with('error', 'Akses ditolak.');
        }

        // Hanya bisa diselesaikan jika statusnya 'dikirim'
        if ($pesanan->status_pesanan !== 'dikirim') {
            return back()->with('error', 'Pesanan hanya dapat diselesaikan jika sudah dalam pengiriman.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->update(['status_pesanan' => 'selesai']);
            
            $this->notifyCustomer(
                $pesanan->id_users,
                'Pesanan Selesai',
                'Terima kasih! Pesanan ' . $pesanan->no_resi . ' telah Anda selesaikan.'
            );
        });

        return redirect()
            ->route('customer.orders')
            ->with('status', 'Pesanan ' . $pesanan->no_resi . ' telah diselesaikan. Terima kasih telah berbelanja!');
    }

    /**
     * Customer: riwayat pesanan.
     */
    public function customerOrders()
    {
        $pesanans = Pesanan::with([
                'detailPesanans.produk:id_produk,nama_produk,gambar,harga',
                'ekspedisi:id_ekspedisi,nama_ekspedisi,biaya_pengiriman',
                'pembayaran:id_pembayaran,id_pesanan,metode_pembayaran,status_konfirmasi,transaction_status,payment_type',
                'promo',
            ])
            ->select('id_pesanan','id_users','id_ekspedisi','id_promo','id_alamat','no_resi','tanggal_pesan',
                     'status_pesanan','total_bayar','subtotal','diskon','ongkos_kirim')
            ->where('id_users', Auth::id())
            ->latest('tanggal_pesan')
            ->paginate(10);

        return view('customer.orders', compact('pesanans'));
    }

    /**
     * Customer: proses beli sekarang bypass keranjang.
     */
    public function buyNow(Request $request)
    {
        $request->validate([
            'id_produk' => ['required', 'integer', 'exists:produks,id_produk'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $produk = Produk::findOrFail($request->id_produk);
        if ($produk->stok < $request->qty) {
            return back()->with('error', 'Stok tidak mencukupi.');
        }

        session(['buy_now_item' => [
            'id_produk' => $produk->id_produk,
            'qty' => $request->qty,
        ]]);

        return redirect()->route('customer.checkout');
    }

    /**
     * Customer: checkout dari keranjang.
     */
    public function checkout()
    {
        $buyNowItem = session('buy_now_item');
        $items    = [];
        $subtotal = 0;

        if ($buyNowItem) {
            $produk = Produk::find($buyNowItem['id_produk']);
            if (!$produk) {
                session()->forget('buy_now_item');
                return redirect()->route('index')->with('error', 'Produk tidak ditemukan.');
            }
            $lineTotal = $produk->harga * $buyNowItem['qty'];
            $subtotal += $lineTotal;
            $items[] = (object) [
                'produk'    => $produk,
                'qty'       => $buyNowItem['qty'],
                'lineTotal' => $lineTotal,
            ];
        } else {
            $keranjang = Keranjang::where('id_users', Auth::id())
                ->with('detailKeranjangs.produk')
                ->first();

            if (!$keranjang || $keranjang->detailKeranjangs->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
            }

            foreach ($keranjang->detailKeranjangs as $detail) {
                if ($detail->produk) {
                    $lineTotal = $detail->produk->harga * $detail->qty;
                    $subtotal += $lineTotal;
                    $items[] = (object) [
                        'produk'    => $detail->produk,
                        'qty'       => $detail->qty,
                        'lineTotal' => $lineTotal,
                    ];
                }
            }
        }

        $appliedPromo = $this->appliedPromo();
        $discount     = $appliedPromo ? $this->calculateDiscount($appliedPromo, $subtotal) : 0;
        $alamats    = AlamatUser::where('id_users', Auth::id())
            ->select('id_alamat','id_users','id_desa','label_alamat','nomor_telepon','detail_alamat','is_utama')
            ->with(['desa.kecamatan.kota.provinsi'])
            ->get();
        $ekspedisis = Ekspedisi::select('id_ekspedisi','nama_ekspedisi','biaya_pengiriman')->get();
        
        $promos = Promo::where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->where('kuota', '>', 0)
            ->get();
        
        return view('customer.checkout', compact('items', 'subtotal', 'discount', 'appliedPromo', 'alamats', 'ekspedisis', 'promos'));
    }

    /**
     * Customer: proses pesanan.
     */
    public function placeOrder(Request $request, MidtransPaymentService $midtrans)
    {
        $request->validate([
            'id_alamat' => ['required', 'exists:alamat_users,id_alamat'],
            'id_ekspedisi' => ['required', 'exists:ekspedisis,id_ekspedisi'],
        ], [
            'id_alamat.required' => 'Silakan pilih alamat pengiriman.',
            'id_ekspedisi.required' => 'Silakan pilih ekspedisi pengiriman.',
        ]);

        if (!$midtrans->isConfigured()) {
            return back()->with('error', 'Konfigurasi Midtrans belum lengkap. Silakan hubungi administrator.');
        }

        $alamatMilikUser = AlamatUser::where('id_users', Auth::id())
            ->where('id_alamat', $request->id_alamat)
            ->exists();

        if (!$alamatMilikUser) {
            return back()->with('error', 'Alamat pengiriman tidak valid.')->withInput();
        }

        $buyNowItem = session('buy_now_item');
        $keranjang = null;
        $ekspedisi  = Ekspedisi::findOrFail($request->id_ekspedisi);
        $subtotal   = 0;
        $orderItems = [];

        if ($buyNowItem) {
            $produk = Produk::find($buyNowItem['id_produk']);
            if (!$produk) {
                return redirect()->route('index')->with('error', 'Produk tidak ditemukan.');
            }
            $qty = $buyNowItem['qty'];
            if ($produk->stok < $qty) {
                return back()->with('error', 'Stok ' . $produk->nama_produk . ' tidak mencukupi.')->withInput();
            }
            $lineTotal  = $produk->harga * $qty;
            $subtotal  += $lineTotal;
            $orderItems[] = [
                'id_produk'  => $produk->id_produk,
                'qty'        => $qty,
                'harga_beli' => $produk->harga,
            ];
        } else {
            $keranjang = Keranjang::where('id_users', Auth::id())
                ->with('detailKeranjangs.produk')
                ->first();

            if (!$keranjang || $keranjang->detailKeranjangs->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
            }

            foreach ($keranjang->detailKeranjangs as $detail) {
                if ($detail->produk) {
                    $produk = $detail->produk;
                    $qty    = $detail->qty;
                    if ($produk->stok < $qty) {
                        return back()
                            ->with('error', 'Stok ' . $produk->nama_produk . ' tidak mencukupi.')
                            ->withInput();
                    }
                    $lineTotal  = $produk->harga * $qty;
                    $subtotal  += $lineTotal;
                    $orderItems[] = [
                        'id_produk'  => $produk->id_produk,
                        'qty'        => $qty,
                        'harga_beli' => $produk->harga,
                    ];
                }
            }
        }

        $ongkir = $ekspedisi->biaya_pengiriman ?? 0;
        $appliedPromo = $this->appliedPromo();
        $discount = $appliedPromo ? $this->calculateDiscount($appliedPromo, $subtotal) : 0;
        $promo = $appliedPromo ?: $this->defaultPromo();
        $totalBayar = max(0, $subtotal - $discount) + $ongkir;
        if ($totalBayar <= 0) {
            return back()->with('error', 'Total pembayaran harus lebih dari Rp0 untuk diproses oleh Midtrans.');
        }

        $createdPesanan = DB::transaction(function () use ($request, $subtotal, $discount, $ongkir, $totalBayar, $orderItems, $promo, $appliedPromo) {
            $pesanan = Pesanan::create([
                'id_users' => Auth::id(),
                'id_alamat' => $request->id_alamat,
                'id_promo' => $promo->id_promo,
                'id_ekspedisi' => $request->id_ekspedisi,
                'tanggal_pesan' => now(),
                'subtotal' => $subtotal,
                'diskon' => $discount,
                'no_resi' => '', // diupdate setelah id didapat
                'ongkos_kirim' => $ongkir,
                'total_bayar' => $totalBayar,
                'status_pesanan' => 'menunggu',
            ]);

            $pesanan->update(['no_resi' => 'RESI-' . $pesanan->id_pesanan]);

            Pembayaran::create([
                'id_pesanan' => $pesanan->id_pesanan,
                'metode_pembayaran' => 'Midtrans Snap',
                'bukti_bayar' => '',
                'status_konfirmasi' => 0,
            ]);

            if ($appliedPromo) {
                $appliedPromo->decrement('kuota');
            }

            foreach ($orderItems as $item) {
                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_produk' => $item['id_produk'],
                    'qty' => $item['qty'],
                    'harga_beli' => $item['harga_beli'],
                ]);

                // Kurangi stok
                Produk::where('id_produk', $item['id_produk'])
                    ->decrement('stok', $item['qty']);
            }

            return $pesanan;
        });

        $this->notifyCustomer(
            Auth::id(),
            'Pesanan Berhasil Dibuat',
            'Pesanan ' . $createdPesanan->no_resi . ' berhasil dibuat dan menunggu pembayaran.'
        );

        // Kosongkan keranjang di database (jika bukan buy now)
        if ($buyNowItem) {
            session()->forget('buy_now_item');
        } elseif ($keranjang) {
            $keranjang->detailKeranjangs()->delete();
        }
        session()->forget('applied_promo_id');

        try {
            $snapToken = $midtrans->createSnapToken(
                $createdPesanan->load(['user', 'pembayaran']),
                $createdPesanan->pembayaran
            );
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            Log::error('Midtrans Snap token request failed.', [
                'pesanan_id' => $createdPesanan->id_pesanan,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('customer.orders')->with(
                'error',
                'Pesanan berhasil dibuat, tetapi pembayaran belum dapat dibuka. Silakan coba tombol Bayar pada pesanan Anda.'
            );
        }

        return redirect()
            ->route('customer.orders')
            ->with('status', 'Pesanan berhasil dibuat. Selesaikan pembayaran melalui Midtrans.')
            ->with('snap_token', $snapToken);
    }

    public function startPayment(Pesanan $pesanan, MidtransPaymentService $midtrans)
    {
        if ((int) $pesanan->id_users !== (int) Auth::id()) {
            abort(403);
        }

        $pembayaran = $pesanan->pembayaran;
        if (!$pembayaran) {
            return redirect()->route('customer.orders')->with('error', 'Data pembayaran pesanan tidak ditemukan.');
        }

        if ((int) $pembayaran->status_konfirmasi === 1) {
            return redirect()->route('customer.orders')->with('error', 'Pesanan ini sudah dibayar.');
        }

        if (!$midtrans->isConfigured()) {
            return redirect()->route('customer.orders')->with('error', 'Konfigurasi Midtrans belum lengkap.');
        }

        try {
            $snapToken = $midtrans->createSnapToken(
                $pesanan->load('user'),
                $pembayaran,
                in_array($pembayaran->transaction_status, ['deny', 'cancel', 'expire', 'failure'], true)
            );
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            Log::error('Midtrans Snap retry request failed.', [
                'pesanan_id' => $pesanan->id_pesanan,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('customer.orders')->with('error', 'Pembayaran belum dapat dibuka. Silakan coba kembali.');
        }

        return redirect()->route('customer.orders')->with('snap_token', $snapToken);
    }

    public function updatePayment(Request $request, Pesanan $pesanan)
    {
        $data = $request->validate([
            'status_konfirmasi' => ['required', 'integer', 'in:0,1,2'],
        ]);

        $pembayaran = $pesanan->pembayaran;
        if (!$pembayaran || filled($pembayaran->midtrans_order_id) || blank($pembayaran->bukti_bayar)) {
            return redirect()->route('admin.orders.index')->with(
                'error',
                'Pembayaran Midtrans tidak dapat diverifikasi manual.'
            );
        }

        if ((int) $pembayaran->status_konfirmasi === 1) {
            return redirect()->route('admin.orders.index')->with(
                'error',
                'Pembayaran yang sudah diverifikasi tidak dapat diubah kembali.'
            );
        }

        DB::transaction(function () use ($pesanan, $pembayaran, $data) {
            $pembayaran->update($data);

            if ((int) $data['status_konfirmasi'] === 1 && $pesanan->status_pesanan === 'menunggu') {
                $pesanan->update(['status_pesanan' => 'diproses']);
                $this->notifyCustomer(
                    $pesanan->id_users,
                    'Pembayaran Terverifikasi',
                    'Pembayaran untuk pesanan ' . $pesanan->no_resi . ' sudah diverifikasi. Pesanan Anda sedang diproses.'
                );
            } elseif ((int) $data['status_konfirmasi'] === 2) {
                $this->notifyCustomer(
                    $pesanan->id_users,
                    'Pembayaran Ditolak',
                    'Bukti pembayaran untuk pesanan ' . $pesanan->no_resi . ' ditolak.'
                );
            }
        });

        return redirect()->route('admin.orders.index')->with(
            'status',
            'Status pembayaran pesanan ' . $pesanan->no_resi . ' berhasil diperbarui.'
        );
    }

    private function appliedPromo(): ?Promo
    {
        $promoId = session('applied_promo_id');
        if (!$promoId) {
            return null;
        }

        return Promo::where('id_promo', $promoId)
            ->where('kuota', '>', 0)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->first();
    }

    private function calculateDiscount(Promo $promo, int $subtotal): int
    {
        if ($subtotal <= 0) {
            return 0;
        }

        if ($promo->tipe_diskon === 'persen') {
            return min($subtotal, (int) floor($subtotal * $promo->nilai_diskon / 100));
        }

        return min($subtotal, (int) $promo->nilai_diskon);
    }

    private function defaultPromo(): Promo
    {
        return Promo::firstOrCreate(
            ['kode_voucher' => 'NO-VOUCHER'],
            [
                'tipe_diskon' => 'nominal',
                'nilai_diskon' => 0,
                'kuota' => 999999,
                'tanggal_mulai' => now()->subYear(),
                'tanggal_berakhir' => now()->addYears(10),
            ]
        );
    }

    private function notifyCustomer(int $userId, string $title, string $message): void
    {
        Notifikasi::create([
            'id_users' => $userId,
            'judul' => $title,
            'pesan' => $message,
            'is_read' => 0,
        ]);
    }

    private function statusNotificationTitle(string $status): string
    {
        return match ($status) {
            'dikirim' => 'Pesanan Dikirim',
            'selesai' => 'Pesanan Selesai',
            'diproses' => 'Pesanan Diproses',
            default => 'Status Pesanan Diperbarui',
        };
    }

    private function statusNotificationMessage(Pesanan $pesanan): string
    {
        return match ($pesanan->status_pesanan) {
            'dikirim' => 'Pesanan ' . $pesanan->no_resi . ' sedang dalam proses pengiriman.',
            'selesai' => 'Pesanan ' . $pesanan->no_resi . ' sudah selesai. Terima kasih sudah berbelanja.',
            'diproses' => 'Pesanan ' . $pesanan->no_resi . ' sedang diproses oleh admin.',
            default => 'Status pesanan ' . $pesanan->no_resi . ' telah diperbarui.',
        };
    }
}
