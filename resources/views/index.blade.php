@extends('layouts.app')

@section('title', 'Elektronik Modern – Toko Elektronik Terpercaya')

@section('content')
    <!-- HERO -->
    <section
        class="relative min-h-[500px] md:min-h-[580px] bg-gradient-to-br from-[#050d2e] via-[#0f2060] to-[#0a3060] overflow-hidden flex items-center py-12 md:py-0">
        <div id="particles" class="absolute inset-0 overflow-hidden pointer-events-none"></div>
        <div
            class="absolute w-[300px] h-[300px] md:w-[600px] md:h-[600px] rounded-full bg-[radial-gradient(circle,rgba(26,92,255,0.2)_0%,transparent_70%)] -top-[100px] -right-[50px] md:-top-[200px] md:-right-[100px] animate-[pulse_4s_ease-in-out_infinite]">
        </div>

        <div
            class="relative z-10 max-w-[1280px] mx-auto px-4 md:px-8 grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-[60px] items-center w-full">
            <div class="text-center md:text-left">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 text-white backdrop-blur-md text-[11px] md:text-xs font-bold uppercase tracking-wider mb-6">
                    <i class="fi fi-rr-sparkles text-primary"></i> Toko Elektronik Terpercaya
                </div>
                <h1
                    class="font-heading text-[32px] sm:text-[40px] md:text-[50px] lg:text-[60px] font-extrabold text-white leading-[1.1] mb-6">
                    Elektronik <span
                        class="bg-gradient-to-br from-blue-400 to-emerald-400 bg-clip-text text-transparent">Rumah
                        Modern</span>, Harga Terbaik!
                </h1>
                <p class="text-white/70 text-sm md:text-base leading-relaxed mb-8 max-w-lg mx-auto md:mx-0">
                    Temukan ribuan produk elektronik pilihan — LED, Kabel, Speaker, Router & lebih banyak lagi — dengan
                    pengiriman cepat ke seluruh Indonesia.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center md:justify-start">
                    <x-button onclick="window.location='{{ route('products.index') }}'"
                        class="py-3.5 px-8 text-sm font-bold shadow-xl shadow-primary/20">
                        <i class="fi fi-rr-shopping-bag mr-2"></i> Belanja Sekarang
                    </x-button>
                    <x-button variant="outline" onclick="window.location='{{ route('products.index') }}'"
                        class="py-3.5 px-8 text-sm font-bold bg-white/10 text-white border-white/20 hover:bg-white/20 backdrop-blur-sm">
                        Lihat Katalog <i class="fi fi-rr-arrow-right ml-2"></i>
                    </x-button>
                </div>
                <div class="flex justify-center md:justify-start gap-6 md:gap-10 mt-12 pt-8 border-t border-white/10">
                    <div>
                        <div class="font-heading text-xl md:text-2xl font-extrabold text-white">{{ $jumlahProduk }}+</div>
                        <div class="text-[10px] md:text-xs text-white/50 uppercase tracking-wider font-bold mt-1">Produk
                        </div>
                    </div>
                    <div>
                        <div class="font-heading text-xl md:text-2xl font-extrabold text-white">{{ $jumlahUser }}+</div>
                        <div class="text-[10px] md:text-xs text-white/50 uppercase tracking-wider font-bold mt-1">Pelanggan
                        </div>
                    </div>
                    <div>
                        <div class="font-heading text-xl md:text-2xl font-extrabold text-white flex items-center">
                            5.0<i class="fi fi-rr-star text-base md:text-xl text-yellow-400 ml-1.5"></i>
                        </div>
                        <div class="text-[10px] md:text-xs text-white/50 uppercase tracking-wider font-bold mt-1">Rating
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative hidden md:block">
                <div class="relative p-4 bg-white/5 rounded-[2.5rem] border border-white/10 backdrop-blur-sm">
                    <img src="https://images.unsplash.com/photo-1593784991095-a205069470b6?w=700&q=85" alt="Electronics"
                        class="w-full h-[300px] lg:h-[400px] rounded-[2rem] object-cover shadow-2xl" />
                </div>

                <div
                    class="absolute -bottom-6 -left-6 bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-xl flex items-center gap-3 min-w-max">
                    <div
                        class="w-10 h-10 rounded-xl bg-green-100 flex flex-shrink-0 items-center justify-center text-xl text-green-600">
                        <i class="fi fi-rr-shield-check"></i>
                    </div>
                    <div class="flex flex-col gap-1">
                        <div
                            class="text-[10px] text-g500 font-bold uppercase tracking-wider leading-none whitespace-nowrap">
                            Garansi Resmi</div>
                        <div class="text-sm font-extrabold text-g900 font-heading leading-none whitespace-nowrap">100% Aman
                        </div>
                    </div>
                </div>

                <div
                    class="absolute -top-6 -right-6 bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-xl flex items-center gap-3 min-w-max">
                    <div
                        class="w-10 h-10 rounded-xl bg-blue-100 flex flex-shrink-0 items-center justify-center text-xl text-primary">
                        <i class="fi fi-rr-shipping-fast"></i>
                    </div>
                    <div class="flex flex-col gap-1">
                        <div
                            class="text-[10px] text-g500 font-bold uppercase tracking-wider leading-none whitespace-nowrap">
                            Pengiriman</div>
                        <div class="text-sm font-extrabold text-g900 font-heading leading-none whitespace-nowrap">Super
                            Cepat</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BRANDS -->
    <section class="py-10 md:py-14 bg-white border-t border-g100 px-4 md:px-8">
        <div class="max-w-[1280px] mx-auto overflow-hidden">
            <h3 class="text-center text-[11px] md:text-[13px] font-bold text-g400 tracking-widest uppercase mb-8 md:mb-10">
                Merek Terpercaya Kami</h3>
            <div class="flex items-center justify-center gap-6 md:gap-16 flex-wrap opacity-40">
                @foreach (['POLYTRON', 'SONY', 'ASUS', 'LENOVO', 'HP', 'SHARP', 'CANON', 'REDMI', 'REALME', 'IPHONE'] as $brand)
                    <div
                        class="font-heading text-lg md:text-[22px] font-extrabold text-g600 hover:text-primary transition-colors cursor-default uppercase">
                        {{ $brand }}
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CATEGORIES -->
    <section class="py-16 md:py-[80px] bg-g50 px-4 md:px-8 reveal">
        <div class="max-w-[1280px] mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
                <div class="text-center md:text-left">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-light text-primary text-[11px] font-bold uppercase tracking-wider mb-3">
                        <i class="fi fi-rr-apps"></i> Kategori
                    </div>
                    <h2 class="font-heading text-[32px] md:text-[40px] font-extrabold text-g900 leading-tight mb-3">Jelajahi
                        Produk Kami</h2>
                    <p class="text-g500 text-sm md:text-base max-w-md mx-auto md:mx-0">Temukan barang impian berdasarkan
                        kategori favorit Anda.</p>
                </div>
                <x-button variant="outline" onclick="window.location='{{ route('products.index') }}'"
                    class="hidden md:inline-flex py-3 px-6 text-sm font-bold">
                    Lihat Semua Kategori <i class="fi fi-rr-arrow-right ml-2"></i>
                </x-button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-5 reveal-group">
                @foreach ($kategoris as $k)
                    <a href="{{ route('products.index', ['kategori' => $k->nama_kategori]) }}"
                        class="bg-white rounded-[2rem] p-6 shadow-sm hover:shadow-card-lg hover:-translate-y-1.5 border border-g100 hover:border-primary/20 text-center transition-all duration-300 group">
                        <div
                            class="w-16 h-16 mx-auto rounded-2xl bg-g50 text-primary text-3xl mb-4 flex items-center justify-center group-hover:scale-110 group-hover:bg-primary-light transition-all duration-300">
                            <span class="group-hover:rotate-12 transition-transform duration-300">
                                {!! $k->ikonHtml() !!}
                            </span>
                        </div>
                        <div
                            class="text-[14px] font-extrabold text-g900 mb-1 leading-tight group-hover:text-primary transition-colors">
                            {{ $k->nama_kategori }}
                        </div>
                        <div class="text-[10px] font-bold text-g400 uppercase tracking-widest">{{ $k->produks_count }} Produk
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 text-center md:hidden">
                <x-button variant="outline" onclick="window.location='{{ route('products.index') }}'"
                    class="w-full py-3.5 px-6 text-sm font-bold border-2">
                    Lihat Semua Kategori <i class="fi fi-rr-arrow-right ml-2"></i>
                </x-button>
            </div>
        </div>
    </section>

    <!-- BEST SELLING -->
    @if($produkTerlaris->count() > 0)
        <section class="py-16 md:py-[80px] bg-white px-4 md:px-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-primary/5 rounded-full blur-[100px] -mr-48 -mt-48"></div>
            <div class="max-w-[1280px] mx-auto relative reveal">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
                    <div class="max-w-xl text-center md:text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-50 text-red-500 text-[11px] font-bold uppercase tracking-wider mb-4">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                            Hot Item
                        </div>
                        <h2 class="font-heading text-[32px] md:text-[44px] font-extrabold text-g900 leading-[1.1] mb-4">Produk
                            Terlaris</h2>
                        <p class="text-g500 text-sm md:text-lg leading-relaxed">Produk favorit pilihan ribuan pelanggan yang
                            sudah terbukti kualitasnya.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-6 reveal-group">
                    @foreach ($produkTerlaris as $item)
                        @if ($item->produk)
                            <div class="relative group">
                                <div
                                    class="absolute -inset-0.5 bg-gradient-to-r from-primary/20 to-teal/20 rounded-[2rem] blur opacity-0 group-hover:opacity-100 transition duration-500">
                                </div>
                                <div class="relative">
                                    <x-product-card :produk="$item->produk" :wishlistIds="$wishlistIds ?? []" />
                                    <div class="absolute top-4 left-4 z-10">
                                        <span
                                            class="bg-primary/90 backdrop-blur-sm text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-lg border border-white/20">
                                            {{ $item->total_terjual }} Terjual
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        <!-- DEALS / HIGHLIGHT -->
        @if ($produkTerlaris->count() >= 2)
            <section class="pb-16 md:pb-[80px] bg-white px-4 md:px-8">
                <div class="max-w-[1280px] mx-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        @foreach ($produkTerlaris->take(2) as $item)
                            <div class="group relative min-h-[300px] md:min-h-[360px] rounded-[2.5rem] overflow-hidden cursor-pointer border border-white/50 shadow-card transition-all duration-500 hover:shadow-card-lg hover:-translate-y-1.5"
                                onclick="window.location='{{ route('products.show', $item->produk) }}'">
                                <img src="{{ asset('storage/products/' . $item->produk->gambar) }}"
                                    alt="{{ $item->produk->nama_produk }}"
                                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-g900/95 via-g900/40 to-transparent p-6 md:p-10 flex flex-col justify-end">
                                    <div class="flex items-center gap-3 mb-3 md:mb-4">
                                        <div class="flex -space-x-2">
                                            <div
                                                class="w-7 h-7 md:w-8 md:h-8 rounded-full border-2 border-white bg-g200 flex items-center justify-center text-[9px] md:text-[10px] font-bold">
                                                JD</div>
                                            <div
                                                class="w-7 h-7 md:w-8 md:h-8 rounded-full border-2 border-white bg-primary text-white flex items-center justify-center text-[9px] md:text-[10px] font-bold">
                                                AS</div>
                                            <div
                                                class="w-7 h-7 md:w-8 md:h-8 rounded-full border-2 border-white bg-teal text-white flex items-center justify-center text-[9px] md:text-[10px] font-bold">
                                                +50</div>
                                        </div>
                                        <span class="text-white/80 text-[10px] md:text-xs font-medium uppercase tracking-wider">Trusted
                                            by Users</span>
                                    </div>
                                    <h3
                                        class="font-heading text-2xl md:text-3xl font-extrabold text-white mb-3 md:mb-4 leading-tight tracking-tight max-w-sm">
                                        {{ $item->produk->nama_produk }}
                                    </h3>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-white/50 text-[10px] md:text-[11px] uppercase tracking-widest font-bold mb-1">Harga
                                                Terbaik</span>
                                            <div class="text-xl md:text-2xl font-extrabold text-white">
                                                Rp {{ number_format($item->produk->harga, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div
                                            class="bg-white/10 backdrop-blur-xl border border-white/20 p-3 md:p-4 rounded-2xl group-hover:bg-primary transition-colors duration-300">
                                            <i class="fi fi-rr-arrow-right text-white text-lg md:text-xl"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endif

    <!-- NEW ARRIVALS -->
    <section class="py-16 md:py-[80px] bg-g50 px-4 md:px-8 reveal">
        <div class="max-w-[1280px] mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
                <div class="text-center md:text-left">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-bold uppercase tracking-wider mb-3">
                        <i class="fi fi-rr-sparkles"></i> Terbaru
                    </div>
                    <h2 class="font-heading text-[32px] md:text-[40px] font-extrabold text-g900 leading-tight mb-3">Produk
                        Baru Masuk</h2>
                    <p class="text-g500 text-sm md:text-base max-w-md mx-auto md:mx-0">Jangan lewatkan koleksi terbaru kami!
                    </p>
                </div>
                <x-button variant="outline" onclick="window.location='{{ route('products.index') }}'"
                    class="hidden md:inline-flex py-3 px-6 text-sm font-bold border-2">
                    Lihat Semua <i class="fi fi-rr-arrow-right ml-2"></i>
                </x-button>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 reveal-group">
                @foreach ($produkBaru as $produk)
                    <x-product-card :produk="$produk" :wishlistIds="$wishlistIds ?? []" />
                @endforeach
            </div>
            <div class="mt-8 text-center md:hidden">
                <x-button variant="outline" onclick="window.location='{{ route('products.index') }}'"
                    class="w-full py-3.5 px-6 text-sm font-bold border-2">
                    Lihat Semua Produk <i class="fi fi-rr-arrow-right ml-2"></i>
                </x-button>
            </div>
        </div>
    </section>

    <!-- WHY US -->
    <section class="py-20 md:py-[100px] bg-gradient-to-br from-g900 to-[#0f2060] px-4 md:px-8 relative overflow-hidden">
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[radial-gradient(circle,rgba(26,92,255,0.05)_0%,transparent_70%)]">
        </div>
        <div class="max-w-[1280px] mx-auto text-center relative z-10 reveal">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-white/60 text-[11px] font-bold uppercase tracking-wider mb-6">
                <i class="fi fi-rr-bolt text-primary"></i> Keunggulan Kami
            </div>
            <h2 class="font-heading text-[32px] md:text-[44px] font-extrabold text-white leading-tight mb-4">Kenapa Belanja
                di Sini?</h2>
            <p class="text-white/50 text-sm md:text-base max-w-xl mx-auto leading-relaxed">Kami berkomitmen memberikan
                pelayanan terbaik dan produk berkualitas untuk kebutuhan elektronik Anda.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-6 mt-16 reveal-group">
                <div
                    class="group p-8 rounded-[2rem] bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-300">
                    <div
                        class="w-16 h-16 rounded-2xl bg-primary/20 flex items-center justify-center text-[32px] mx-auto mb-6 text-primary transition-transform group-hover:scale-110">
                        <i class="fi fi-rr-shield-check"></i>
                    </div>
                    <h3 class="font-heading text-lg font-extrabold text-white mb-3">100% Original</h3>
                    <p class="text-sm text-white/40 leading-relaxed">Semua produk bersumber langsung dari distributor resmi
                        dengan garansi pabrik.</p>
                </div>
                <div
                    class="group p-8 rounded-[2rem] bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-300">
                    <div
                        class="w-16 h-16 rounded-2xl bg-teal/20 flex items-center justify-center text-[32px] mx-auto mb-6 text-teal transition-transform group-hover:scale-110">
                        <i class="fi fi-rr-truck-side"></i>
                    </div>
                    <h3 class="font-heading text-lg font-extrabold text-white mb-3">Pengiriman Cepat</h3>
                    <p class="text-sm text-white/40 leading-relaxed">Pengiriman ke seluruh Indonesia dengan estimasi 1–5
                        hari kerja saja.</p>
                </div>
                <div
                    class="group p-8 rounded-[2rem] bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-300">
                    <div
                        class="w-16 h-16 rounded-2xl bg-purple-500/20 flex items-center justify-center text-[32px] mx-auto mb-6 text-purple-400 transition-transform group-hover:scale-110">
                        <i class="fi fi-rr-credit-card"></i>
                    </div>
                    <h3 class="font-heading text-lg font-extrabold text-white mb-3">Pembayaran Aman</h3>
                    <p class="text-sm text-white/40 leading-relaxed">Transfer bank & e-wallet dengan konfirmasi manual yang
                        sangat cepat.</p>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function buildHeroParticles() {
            const c = document.getElementById("particles");
            if (!c) return;
            for (let i = 0; i < 20; i++) {
                const d = document.createElement("div");
                d.className = "absolute rounded-full bg-white/5 animate-[floatSlow_linear_infinite]";
                const s = Math.random() * 80 + 20;
                d.style.cssText = `width:${s}px;height:${s}px;left:${Math.random() * 100}%;bottom:${Math.random() * -20}%;animation-duration:${Math.random() * 15 + 8}s;animation-delay:${Math.random() * 10}s`;
                c.appendChild(d);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if ('requestIdleCallback' in window) {
                requestIdleCallback(buildHeroParticles);
            } else {
                window.addEventListener('load', buildHeroParticles);
            }
        });
    </script>
@endpush