@extends('layouts.app')

@section('title', 'Pesanan Saya – Elektronik Modern')

@section('head')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <section class="py-8 md:py-[72px] bg-g50 min-h-screen px-4 md:px-8">
        <div class="max-w-[1024px] mx-auto">
            <div class="flex items-center gap-1.5 mb-6 text-[13px]">
                <a href="{{ route('index') }}" class="text-g500 hover:text-primary transition-colors flex items-center gap-1.5"><i class="fi fi-rr-home"></i> Beranda</a> 
                <i class="fi fi-rr-angle-small-right text-g400"></i> 
                <span class="text-g800 font-semibold">Pesanan Saya</span>
            </div>
            
            <div class="mb-8">
                <h1 class="font-heading text-[28px] md:text-[32px] font-extrabold text-g900 mb-2 flex items-center gap-3">
                    <i class="fi fi-rr-box text-primary"></i> Pesanan Saya
                </h1>
                <p class="text-g500 text-[15px]">Riwayat dan status pesanan Anda</p>
            </div>

            <div class="space-y-4">
            @forelse ($pesanans as $pesanan)
                @php
                    $statusClass = match ($pesanan->status_pesanan) {
                        'menunggu' => 'bg-orange-50 text-orange-600 border-orange-200',
                        'diproses' => 'bg-blue-50 text-blue-600 border-blue-200',
                        'dikirim'  => 'bg-purple-50 text-purple-600 border-purple-200',
                        'selesai'  => 'bg-green-50 text-green-600 border-green-200',
                        default    => 'bg-g100 text-g600 border-g200',
                    };
                    $statusIcon = match ($pesanan->status_pesanan) {
                        'menunggu' => 'fi-rr-time-clock',
                        'diproses' => 'fi-rr-settings',
                        'dikirim'  => 'fi-rr-truck-side',
                        'selesai'  => 'fi-rr-check-circle',
                        default    => 'fi-rr-info',
                    };
                    $statusLabel = match ($pesanan->status_pesanan) {
                        'menunggu' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'dikirim'  => 'Dikirim',
                        'selesai'  => 'Selesai',
                        default    => ucfirst($pesanan->status_pesanan),
                    };
                    $paymentStatus = (int) ($pesanan->pembayaran?->status_konfirmasi ?? 0);
                    $transactionStatus = $pesanan->pembayaran?->transaction_status;
                    $paymentLabel = match (true) {
                        $paymentStatus === 1 => 'Lunas',
                        $paymentStatus === 2 => 'Pembayaran gagal',
                        $transactionStatus === 'pending' => 'Menunggu pembayaran',
                        default => 'Belum dibayar',
                    };
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-g100 overflow-hidden transition-all hover:shadow-card group">
                    <div class="flex items-center justify-between py-4 px-6 border-b border-g100 flex-wrap gap-3 bg-white">
                        <div>
                            <span class="font-extrabold text-[15px] text-g900">{{ $pesanan->no_resi }}</span>
                            <span class="text-xs font-semibold text-g500 ml-3">{{ \Illuminate\Support\Carbon::parse($pesanan->tanggal_pesan)->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex items-center gap-1.5 {{ $statusClass }} border py-1.5 px-3 rounded-full text-xs font-bold tracking-wide uppercase">
                                <i class="fi {{ $statusIcon }}"></i> {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="py-2 px-6">
                        @foreach ($pesanan->detailPesanans as $detail)
                            <div class="flex items-center gap-4 py-3 border-b border-g100 last:border-none">
                                @if ($detail->produk)
                                    <div class="w-14 h-14 rounded-xl bg-g50 border border-g100 p-1 flex items-center justify-center shrink-0">
                                        @if($detail->produk->gambar)
                                            <img src="{{ asset('storage/products/' . $detail->produk->gambar) }}" alt="{{ $detail->produk->nama_produk }}" loading="lazy" decoding="async" class="w-full h-full object-contain mix-blend-multiply">
                                        @else
                                            <i class="fi fi-rr-picture text-xl text-g300"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[13px] font-bold text-g800 line-clamp-1 mb-0.5">{{ $detail->produk->nama_produk }}</div>
                                        <div class="text-xs font-semibold text-g500">{{ $detail->qty }} × Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="font-heading font-extrabold text-primary text-[14px] whitespace-nowrap pl-4">
                                        Rp {{ number_format($detail->harga_beli * $detail->qty, 0, ',', '.') }}
                                    </div>
                                @else
                                    <div class="w-14 h-14 rounded-xl bg-g50 border border-g100 p-1 flex items-center justify-center shrink-0 text-g300 text-xl"><i class="fi fi-rr-ban"></i></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[13px] font-bold text-g500 italic mb-0.5">Produk tidak tersedia</div>
                                        <div class="text-xs font-semibold text-g400">{{ $detail->qty }} item</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between py-4 px-6 bg-g50 border-t border-g100 flex-wrap gap-4">
                        <div class="flex flex-col gap-4">
                            <div>
                                <span class="text-xs font-bold text-g500 uppercase tracking-widest mb-1 block">Pengiriman</span>
                                <span class="text-[13px] font-semibold text-g800 flex items-center gap-1.5"><i class="fi fi-rr-truck-side text-g400"></i> {{ $pesanan->ekspedisi->nama_ekspedisi ?? '-' }} <span class="text-g400 mx-1">•</span> Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="space-y-1">
                                <span class="text-xs font-bold text-g500 uppercase tracking-widest mb-1 block">Rincian Pembayaran</span>
                                <div class="flex flex-col gap-0.5 text-[13px]">
                                    <div class="flex justify-between w-48 text-g600">
                                        <span>Subtotal</span>
                                        <span class="font-semibold">Rp {{ number_format($pesanan->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    @if($pesanan->diskon > 0)
                                    <div class="flex flex-col gap-0.5 w-48 text-red-500">
                                        <div class="flex justify-between items-center">
                                            <span>Diskon 
                                                @if($pesanan->promo && $pesanan->promo->kode_voucher !== 'NO-VOUCHER')
                                                    <span class="text-[10px] font-bold bg-red-100 px-1.5 py-0.5 rounded ml-1">{{ $pesanan->promo->kode_voucher }}</span>
                                                    <span class="text-[11px]">
                                                        ({{ $pesanan->promo->tipe_diskon === 'persen' ? $pesanan->promo->nilai_diskon . '%' : 'Rp ' . number_format($pesanan->promo->nilai_diskon, 0, ',', '.') }})
                                                    </span>
                                                @endif
                                            </span>
                                            <span class="font-semibold">- Rp {{ number_format($pesanan->diskon, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="flex justify-between w-48 text-g600">
                                        <span>Ongkos Kirim</span>
                                        <span class="font-semibold">Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-g500 uppercase tracking-widest mb-1">Total Bayar</div>
                            <div class="font-heading text-xl font-extrabold text-primary mb-2">Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}</div>

                            <div class="text-xs font-semibold text-g500 mb-3">
                                Pembayaran: <span class="{{ $paymentStatus === 1 ? 'text-green-600' : ($paymentStatus === 2 ? 'text-red-600' : 'text-orange-600') }}">{{ $paymentLabel }}</span>
                                @if ($pesanan->pembayaran?->payment_type)
                                    · {{ ucfirst(str_replace('_', ' ', $pesanan->pembayaran->payment_type)) }}
                                @endif
                            </div>

                            @if ($paymentStatus !== 1 && $pesanan->status_pesanan === 'menunggu')
                                <form action="{{ route('customer.orders.payment', $pesanan) }}" method="POST" class="mb-3">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 py-2 px-4 rounded-xl font-bold text-[13px] bg-primary text-white shadow-sm hover:bg-primary-dark transition-all">
                                        <i class="fi fi-rr-credit-card"></i> Pilih Metode Pembayaran
                                    </button>
                                </form>
                            @endif
                            
                            @if ($pesanan->status_pesanan === 'dikirim')
                                <form action="{{ route('customer.orders.complete', $pesanan) }}" method="POST" onsubmit="event.preventDefault(); window.confirmCompleteOrder(this);">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 py-2 px-4 rounded-xl font-bold text-[13px] bg-green-500 text-white shadow-sm hover:bg-green-600 transition-all">
                                        <i class="fi fi-rr-check-circle"></i> Pesanan Selesai
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-g100 p-12 text-center mt-5">
                    <div class="text-[64px] text-g300 mb-4 inline-flex justify-center"><i class="fi fi-rr-box"></i></div>
                    <h3 class="font-heading text-xl font-extrabold text-g800 mb-2">Belum Ada Pesanan</h3>
                    <p class="text-g500 text-sm mb-6 max-w-sm mx-auto">Yuk mulai belanja produk elektronik berkualitas di Elektronik Modern!</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 py-3 px-6 rounded-full font-bold text-[15px] bg-primary text-white shadow-[0_4px_12px_rgba(26,92,255,0.3)] hover:bg-primary-dark hover:-translate-y-px transition-all">
                        <i class="fi fi-rr-shopping-bag"></i> Belanja Sekarang
                    </a>
                </div>
            @endforelse
            </div>
            
            @if($pesanans->hasPages())
            <div class="mt-8 flex justify-center w-full overflow-hidden">
                <div class="inline-flex max-w-full bg-white rounded-xl shadow-sm border border-g200 p-1">
                    {{ $pesanans->links('pagination::tailwind') }}
                </div>
            </div>
            @endif
        </div>
    </section>

    @if (config('services.midtrans.client_key'))
        @php
            $snapScriptHost = config('services.midtrans.is_production')
                ? 'https://app.midtrans.com'
                : 'https://app.sandbox.midtrans.com';
        @endphp
        @push('scripts')
            <script src="{{ $snapScriptHost }}/snap/snap.js?client-key={{ urlencode(config('services.midtrans.client_key')) }}"></script>
            @if (session('snap_token'))
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        window.snap.pay(@json(session('snap_token')), {
                            onSuccess: () => window.location.reload(),
                            onPending: () => window.location.reload(),
                            onError: () => window.location.reload()
                        });
                    });
                </script>
            @endif
        @endpush
    @endif
@endsection
