@extends('layouts.app')

@section('title', 'Kelola Pesanan – Admin Elektronik Modern')

@section('head')
@endsection

@section('header')
@endsection

@section('content')
    <div class="flex flex-col md:flex-row min-h-screen bg-g50">
        @include('partials.admin-sidebar')

        <div class="flex-1 w-full min-w-0 flex flex-col p-6 md:p-8 overflow-y-auto h-screen relative">
            <div class="flex justify-between items-center mb-8 pt-12 md:pt-0">
                <h1 class="font-heading text-[24px] font-extrabold text-g900">Kelola Pesanan</h1>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-g100 flex items-center gap-4 hover:shadow-card transition-shadow">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0 bg-blue-50 text-blue-600"><i class="fi fi-rr-time-fast"></i></div>
                    <div>
                        <div class="text-[12px] font-bold text-g500 uppercase tracking-wider mb-1">Menunggu</div>
                        <div class="font-heading text-2xl font-extrabold text-g900">{{ $jumlahMenunggu }}</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-g100 flex items-center gap-4 hover:shadow-card transition-shadow">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0 bg-blue-50 text-blue-600"><i class="fi fi-rr-settings"></i></div>
                    <div>
                        <div class="text-[12px] font-bold text-g500 uppercase tracking-wider mb-1">Diproses</div>
                        <div class="font-heading text-2xl font-extrabold text-g900">{{ $jumlahDiproses }}</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-g100 flex items-center gap-4 hover:shadow-card transition-shadow">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0 bg-teal-50 text-teal-600"><i class="fi fi-rr-truck-side"></i></div>
                    <div>
                        <div class="text-[12px] font-bold text-g500 uppercase tracking-wider mb-1">Dikirim</div>
                        <div class="font-heading text-2xl font-extrabold text-g900">{{ $jumlahDikirim }}</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-g100 flex items-center gap-4 hover:shadow-card transition-shadow">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0 bg-green-50 text-green-600"><i class="fi fi-rr-check-circle"></i></div>
                    <div>
                        <div class="text-[12px] font-bold text-g500 uppercase tracking-wider mb-1">Selesai</div>
                        <div class="font-heading text-2xl font-extrabold text-g900">{{ $jumlahSelesai }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-g100">
                <div class="p-6 border-b border-g100 flex justify-between items-center">
                    <h3 class="font-heading text-[16px] font-extrabold text-g900 flex items-center gap-2">
                        <i class="fi fi-rr-document text-primary"></i> Daftar Pesanan
                    </h3>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full min-w-[1000px] text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">No. Resi</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Pelanggan</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Total</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Pembayaran</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Status</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pesanans as $pesanan)
                                @php
                                    $statusLabel = match ($pesanan->status_pesanan) {
                                        'diproses' => 'Diproses',
                                        'dikirim'  => 'Dikirim',
                                        'selesai'  => 'Selesai',
                                        default    => 'Menunggu',
                                    };
                                    $statusClass = match ($pesanan->status_pesanan) {
                                        'diproses' => 'bg-blue-50 text-blue-600 border-blue-200',
                                        'dikirim'  => 'bg-purple-50 text-purple-600 border-purple-200',
                                        'selesai'  => 'bg-green-50 text-green-600 border-green-200',
                                        default    => 'bg-orange-50 text-orange-600 border-orange-200',
                                    };
                                    $paymentStatus = (int) ($pesanan->pembayaran?->status_konfirmasi ?? 0);
                                    $paymentLabel = match ($paymentStatus) {
                                        1 => 'Lunas',
                                        2 => 'Gagal',
                                        default => 'Menunggu',
                                    };
                                    $paymentClass = match ($paymentStatus) {
                                        1 => 'bg-green-50 text-green-600 border-green-200',
                                        2 => 'bg-red-50 text-red-600 border-red-200',
                                        default => 'bg-orange-50 text-orange-600 border-orange-200',
                                    };
                                @endphp
                                <tr class="group hover:bg-g50/50 transition-colors">
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <div class="font-extrabold text-g900 text-[14px]">{{ $pesanan->no_resi }}</div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <div class="font-bold text-g800 text-[13px] mb-1">{{ $pesanan->user->nama ?? '-' }}</div>
                                        <div class="font-semibold text-g400 text-[11px] mb-2">
                                            {{ \Illuminate\Support\Carbon::parse($pesanan->tanggal_pesan)->format('d M Y, H:i') }}
                                        </div>
                                        <div class="text-[11px] font-medium text-g500 space-y-0.5">
                                            @foreach($pesanan->detailPesanans as $detail)
                                                <div class="flex items-center gap-1.5"><i class="fi fi-rr-minus-small"></i> {{ $detail->produk->nama_produk ?? 'Produk Dihapus' }} (x{{ $detail->qty }})</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <div class="font-heading font-extrabold text-primary text-[15px] whitespace-nowrap">
                                            Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <div class="flex flex-col gap-1.5 items-start">
                                            <span class="inline-flex items-center {{ $paymentClass }} border py-1 px-2 rounded text-[10px] font-bold tracking-wider">{{ $paymentLabel }}</span>
                                            @if ($pesanan->pembayaran?->midtrans_order_id)
                                                <span class="text-[11px] font-semibold text-g500">{{ $pesanan->pembayaran->payment_type ?? 'Midtrans Snap' }}</span>
                                                @if ($pesanan->pembayaran->transaction_status)
                                                    <span class="text-[10px] text-g400">{{ ucfirst($pesanan->pembayaran->transaction_status) }}</span>
                                                @endif
                                            @elseif ($pesanan->pembayaran?->bukti_bayar)
                                                <a href="{{ asset('storage/payments/' . $pesanan->pembayaran->bukti_bayar) }}" target="_blank" rel="noopener" class="text-[11px] text-primary underline">Lihat bukti pembayaran lama</a>
                                                @if ($paymentStatus === 1)
                                                    <span class="text-[10px] font-bold text-g500">Sudah diverifikasi</span>
                                                @else
                                                    <div class="flex gap-2 mt-1">
                                                        <form method="POST" action="{{ route('admin.orders.updatePayment', $pesanan) }}" class="m-0">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status_konfirmasi" value="1">
                                                            <button type="submit" class="inline-flex py-1 px-2 bg-green-50 text-green-600 border border-green-200 rounded text-[11px] font-bold hover:bg-green-600 hover:text-white transition-colors">Verifikasi</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.orders.updatePayment', $pesanan) }}" class="m-0">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status_konfirmasi" value="2">
                                                            <button type="submit" class="inline-flex py-1 px-2 bg-red-50 text-red-600 border border-red-200 rounded text-[11px] font-bold hover:bg-red-600 hover:text-white transition-colors">Tolak</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-[11px] text-g400">Menunggu pembayaran</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <span class="inline-flex items-center {{ $statusClass }} border py-1 px-2.5 rounded text-[11px] font-extrabold tracking-widest uppercase">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        @if ($pesanan->status_pesanan !== 'selesai')
                                            <form method="POST" action="{{ route('admin.orders.updateStatus', $pesanan) }}" class="m-0">
                                                @csrf @method('PATCH')
                                                <div class="flex gap-2 items-center">
                                                    @php
                                                        $nextStatus = match ($pesanan->status_pesanan) {
                                                            'menunggu' => 'diproses',
                                                            'diproses' => 'dikirim',
                                                            'dikirim'  => 'selesai',
                                                            default    => null,
                                                        };
                                                        $nextLabel = match ($nextStatus) {
                                                            'diproses' => '<i class="fi fi-rr-settings mt-0.5"></i> Proses',
                                                            'dikirim'  => '<i class="fi fi-rr-truck-side mt-0.5"></i> Kirim',
                                                            'selesai'  => '<i class="fi fi-rr-check-circle mt-0.5"></i> Selesai',
                                                            default    => '',
                                                        };
                                                    @endphp
                                                    @if ($nextStatus)
                                                        @if ($nextStatus === 'selesai')
                                                            <button type="button" disabled class="inline-flex py-1.5 px-3 bg-g100 text-g400 border border-g200 rounded-lg font-bold text-[12px] cursor-not-allowed items-center gap-1.5" title="Hanya customer yang dapat menyelesaikan pesanan">
                                                                {!! $nextLabel !!}
                                                            </button>
                                                        @else
                                                            <input type="hidden" name="status_pesanan" value="{{ $nextStatus }}">
                                                            <button type="submit" class="inline-flex py-1.5 px-3 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg font-bold text-[12px] hover:bg-blue-600 hover:text-white transition-colors items-center gap-1.5">
                                                                {!! $nextLabel !!}
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-green-600 bg-green-50 py-1.5 px-3 rounded-lg"><i class="fi fi-rr-check"></i> Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 px-6 text-center text-g500 font-semibold text-[13px]">
                                        Belum ada pesanan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pesanans->hasPages())
                <div class="p-6 border-t border-g100 flex justify-center w-full overflow-hidden">
                    <div class="inline-flex max-w-full bg-white rounded-xl shadow-sm border border-g200 p-1">
                        {{ $pesanans->links('pagination::tailwind') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
