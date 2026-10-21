@extends('layouts.app')

@section('title', 'Admin Dashboard - Elektronik Modern')

@section('head')
@endsection

@section('header')
@endsection

@section('content')
    <div class="flex flex-col md:flex-row min-h-screen bg-g50">
        @include('partials.admin-sidebar')

        <div class="flex-1 w-full min-w-0 flex flex-col p-6 md:p-8 relative">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8 pt-12 md:pt-0">
                <div class="flex flex-col md:flex-row md:items-center gap-4 w-full justify-between">
                    <div>
                        <div class="text-[13px] text-g500 mb-0.5">Selamat datang kembali,</div>
                        <h1 class="font-heading text-[24px] font-extrabold text-g900">Dashboard Admin</h1>
                    </div>
                    <div class="flex flex-wrap gap-3 items-center">
                        <form action="{{ route('admin.report.download') }}" method="GET" class="flex items-center gap-2 bg-white p-1.5 rounded-xl border border-g200 shadow-sm">
                            <select name="bulan" class="bg-transparent border-none text-[12px] font-bold text-g700 outline-none pr-6">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected(now()->month == $m)>{{ date('F', mktime(0,0,0,$m,10)) }}</option>
                                @endforeach
                            </select>
                            <select name="tahun" class="bg-transparent border-none text-[12px] font-bold text-g700 outline-none pr-6">
                                @foreach(range(now()->year-2, now()->year) as $y)
                                    <option value="{{ $y }}" @selected(now()->year == $y)>{{ $y }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-primary text-white p-2 rounded-lg hover:bg-primary-dark transition-colors flex items-center gap-1.5 text-[12px] font-bold">
                                <i class="fi fi-rr-download"></i> Laporan PDF
                            </button>
                        </form>
                        <span class="inline-flex items-center gap-2 py-2 px-4 bg-white border border-g200 rounded-xl text-[13px] font-bold text-g700 shadow-sm">
                            <i class="fi fi-rr-calendar text-primary"></i> {{ now()->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <x-card class="p-6 flex items-center gap-4 hover:-translate-y-1 transition-all cursor-default">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0 bg-blue-50 text-blue-600"><i class="fi fi-rr-box"></i></div>
                    <div>
                        <div class="text-[12px] font-bold text-g500 uppercase tracking-wider mb-1">Total Produk</div>
                        <div class="font-heading text-2xl font-extrabold text-g900">{{ $jumlahProduk }}</div>
                    </div>
                </x-card>
                <x-card class="p-6 flex items-center gap-4 hover:-translate-y-1 transition-all cursor-default">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0 bg-green-50 text-green-600"><i class="fi fi-rr-users"></i></div>
                    <div>
                        <div class="text-[12px] font-bold text-g500 uppercase tracking-wider mb-1">Total Customer</div>
                        <div class="font-heading text-2xl font-extrabold text-g900">{{ $jumlahUser }}</div>
                    </div>
                </x-card>
                <x-card class="p-6 flex items-center gap-4 hover:-translate-y-1 transition-all cursor-default">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0 bg-orange-50 text-orange-600"><i class="fi fi-rr-time-fast"></i></div>
                    <div>
                        <div class="text-[12px] font-bold text-g500 uppercase tracking-wider mb-1">Perlu Konfirmasi</div>
                        <div class="font-heading text-2xl font-extrabold text-g900">{{ $jumlahMenungguKonfirmasi }}</div>
                        <div class="text-[11px] font-semibold text-orange-600 mt-1">Pesanan tertunda</div>
                    </div>
                </x-card>
                <x-card class="p-6 flex items-center gap-4 hover:-translate-y-1 transition-all cursor-default">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0 bg-teal-50 text-teal-600"><i class="fi fi-rr-check-circle"></i></div>
                    <div>
                        <div class="text-[12px] font-bold text-g500 uppercase tracking-wider mb-1">Pesanan Selesai</div>
                        <div class="font-heading text-2xl font-extrabold text-g900">{{ $pesananSelesai }}</div>
                    </div>
                </x-card>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <x-card class="lg:col-span-2 flex flex-col p-0 overflow-hidden">
                    <div class="p-6 border-b border-g100 flex justify-between items-center bg-white">
                        <h3 class="font-heading text-[16px] font-extrabold text-g900 flex items-center gap-2">
                            <i class="fi fi-rr-chart-line-up text-primary"></i> Pesanan 7 Hari Terakhir
                        </h3>
                        <x-badge variant="primary" class="uppercase tracking-widest text-[11px] py-1 px-2.5 bg-blue-50 border border-blue-100 text-blue-600">{{ $pesanan_tujuh_hari_terakhir }} pesanan</x-badge>
                    </div>
                    <div class="p-6 bg-white">
                        <div class="w-full h-[220px]">
                            <canvas id="pesananChart"></canvas>
                        </div>
                    </div>
                </x-card>
                
                <x-card class="flex flex-col p-0 overflow-hidden">
                    <div class="p-6 border-b border-g100 bg-white">
                        <h3 class="font-heading text-[16px] font-extrabold text-g900 flex items-center gap-2">
                            <i class="fi fi-rr-chart-histogram text-primary"></i> Status Pesanan
                        </h3>
                    </div>
                    <div class="p-6 flex flex-col gap-4 bg-white">
                        @php
                            $total = max(1, $statusPesanan->sum());
                        @endphp
                        @foreach([
                            ['Menunggu', 'menunggu', 'bg-orange-500'],
                            ['Diproses', 'diproses', 'bg-blue-500'],
                            ['Dikirim', 'dikirim', 'bg-purple-500'],
                            ['Selesai', 'selesai', 'bg-green-500'],
                        ] as [$label, $key, $colorClass])
                            @php $val = $statusPesanan->get($key, 0); @endphp
                            <div>
                                <div class="flex justify-between text-[13px] mb-1.5">
                                    <span class="text-g600 font-semibold">{{ $label }}</span>
                                    <strong class="text-g900">{{ $val }}</strong>
                                </div>
                                <div class="h-1.5 bg-g100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $colorClass }} rounded-full" style="width:{{ round($val/$total*100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>

            <!-- Recent Orders -->
            <x-card class="p-0 overflow-hidden">
                <div class="p-6 border-b border-g100 flex justify-between items-center flex-wrap gap-4 bg-white">
                    <h3 class="font-heading text-[16px] font-extrabold text-g900 flex items-center gap-2">
                        <i class="fi fi-rr-document text-primary"></i> Pesanan Terbaru
                    </h3>
                    <x-button variant="outline" onclick="window.location='{{ route('admin.orders.index') }}'" class="py-1.5 px-4 text-[12px]">
                        Lihat Semua <i class="fi fi-rr-arrow-right"></i>
                    </x-button>
                </div>
                <div class="overflow-x-auto w-full bg-white">
                    <table class="w-full min-w-[800px] text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">No. Resi</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Pelanggan</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Total</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Pembayaran</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Status</th>
                                <th class="bg-g50 py-3.5 px-6 text-xs font-extrabold text-g500 uppercase tracking-widest border-b border-g200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pesananTerbaru as $pesanan)
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
                                        <div class="font-bold text-g800 text-[13px] mb-0.5">{{ $pesanan->user->nama ?? '-' }}</div>
                                        <div class="font-semibold text-g400 text-[10px] mb-1.5">
                                            {{ $pesanan->tanggal_pesan->format('d M Y, H:i') }}
                                        </div>
                                        <div class="text-[10px] font-medium text-g500 space-y-0.5">
                                            @foreach($pesanan->detailPesanans as $detail)
                                                <div class="flex items-center gap-1 line-clamp-1"><i class="fi fi-rr-minus-small"></i> {{ $detail->produk->nama_produk ?? 'Produk Dihapus' }} (x{{ $detail->qty }})</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <div class="font-heading font-extrabold text-primary text-[15px] whitespace-nowrap">
                                            Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="inline-flex items-center {{ $paymentClass }} border py-0.5 px-2 rounded text-[9px] font-bold tracking-wider whitespace-nowrap">{{ $paymentLabel }}</span>
                                            @if ($pesanan->pembayaran?->transaction_status)
                                                <span class="text-[9px] text-g400">{{ ucfirst($pesanan->pembayaran->transaction_status) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 border-b border-g100 group-last:border-none">
                                        <span class="inline-flex items-center {{ $statusClass }} border py-1 px-2.5 rounded text-[11px] font-extrabold tracking-widest uppercase whitespace-nowrap">
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
                                        Belum ada pesanan terbaru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@section('footer')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('pesananChart').getContext('2d');
            
            const labels = {!! json_encode(array_column($grafikPesanan, 'hari')) !!};
            const data = {!! json_encode(array_column($grafikPesanan, 'jumlah')) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Pesanan',
                        data: data,
                        borderColor: '#2563eb', // blue-600
                        backgroundColor: 'rgba(37, 99, 235, 0.8)', // solid blue for bar
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13, family: "'Inter', sans-serif" },
                            bodyFont: { size: 13, family: "'Inter', sans-serif" },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' pesanan';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: { family: "'Inter', sans-serif", size: 11 },
                                color: '#64748b'
                            },
                            grid: { color: '#f1f5f9', drawBorder: false },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                font: { family: "'Inter', sans-serif", size: 11, weight: 'bold' },
                                color: '#64748b'
                            },
                            border: { display: false }
                        }
                    },
                    interaction: { intersect: false, mode: 'index' },
                }
            });
        });
    </script>
@endsection