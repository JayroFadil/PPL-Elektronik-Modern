@extends('layouts.app')

@section('title', 'Checkout – Elektronik Modern')

@section('head')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <section class="py-8 md:py-[72px] bg-g50 min-h-screen px-4 md:px-8">
        <div class="max-w-[1280px] mx-auto" id="checkoutPageContainer">
            <div class="flex items-center gap-1.5 mb-6 text-[13px]">
                <a href="{{ route('index') }}" class="text-g500 hover:text-primary transition-colors flex items-center gap-1.5"><i class="fi fi-rr-home"></i> Beranda</a> 
                <i class="fi fi-rr-angle-small-right text-g400"></i> 
                <a href="{{ route('cart.index') }}" class="text-g500 hover:text-primary transition-colors">Keranjang</a>
                <i class="fi fi-rr-angle-small-right text-g400"></i>
                <span class="text-g800 font-semibold">Checkout</span>
            </div>
            
            <div class="mb-8">
                <h1 class="font-heading text-[28px] md:text-[32px] font-extrabold text-g900 mb-2 flex items-center gap-3">
                    <i class="fi fi-rr-shopping-bag text-primary"></i> Checkout
                </h1>
                <p class="text-g500 text-[15px]">Periksa pesanan Anda sebelum melanjutkan pembayaran</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-8 items-start">
                <!-- Main Form Section -->
                <div class="flex-1 w-full space-y-5">
                    <form id="checkoutForm" method="POST" action="{{ route('customer.placeOrder') }}">
                        @csrf
                        <!-- Items -->
                        <x-card class="p-6 mb-5">
                            <h3 class="font-heading text-lg font-extrabold text-g900 mb-5 flex items-center gap-2">
                                <i class="fi fi-rr-box text-primary"></i> Produk Dipesan
                            </h3>
                            <div class="flex flex-col divide-y divide-g100">
                            @foreach ($items as $item)
                                <div class="py-3.5 flex items-center gap-4 group">
                                    <div class="w-16 h-16 rounded-xl bg-g50 border border-g100 p-1 flex items-center justify-center shrink-0">
                                        @if($item->produk->gambar)
                                            <img src="{{ asset('storage/products/' . $item->produk->gambar) }}" alt="{{ $item->produk->nama_produk }}" loading="lazy" decoding="async" class="w-full h-full object-contain mix-blend-multiply">
                                        @else
                                            <i class="fi fi-rr-picture text-2xl text-g300"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[13px] font-bold text-g800 mb-1 leading-snug">{{ $item->produk->nama_produk }}</div>
                                        <div class="text-xs text-g500">{{ $item->qty }} × Rp {{ number_format($item->produk->harga, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="font-heading font-extrabold text-primary text-[15px] whitespace-nowrap pl-4">
                                        Rp {{ number_format($item->lineTotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </x-card>

                        <!-- Alamat Pengiriman -->
                        <x-card class="p-6 mb-5">
                            <h3 class="font-heading text-lg font-extrabold text-g900 mb-5 flex items-center gap-2">
                                <i class="fi fi-rr-map-marker text-primary"></i> Alamat Pengiriman
                            </h3>
                            @if ($alamats->count())
                                <div class="space-y-3">
                                @foreach ($alamats as $alamat)
                                    <label class="flex items-start gap-3 p-4 bg-white rounded-xl cursor-pointer border-[1.5px] border-g200 hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-light/50 has-[:checked]:ring-4 has-[:checked]:ring-primary/10">
                                        <input type="radio" name="id_alamat" value="{{ $alamat->id_alamat }}"
                                            {{ $loop->first ? 'checked' : '' }}
                                            class="w-4 h-4 mt-0.5 accent-primary shrink-0 rounded-full text-primary focus:ring-primary">
                                        <div>
                                            <div class="text-[14px] font-bold text-g800 mb-0.5">{{ $alamat->label_alamat }}</div>
                                            <div class="text-[13px] text-g600 leading-relaxed mb-1">{{ $alamat->detail_alamat }}</div>
                                            <div class="text-[12px] font-semibold text-g500 flex items-center gap-1.5"><i class="fi fi-rr-phone-call"></i> {{ $alamat->nomor_telepon }}</div>
                                        </div>
                                    </label>
                                @endforeach
                                </div>
                            @else
                                <x-alert type="warning" class="mb-4">
                                    Anda belum memiliki alamat pengiriman. Tambahkan alamat terlebih dahulu melalui halaman profil.
                                </x-alert>
                                <x-button variant="outline" onclick="window.location='{{ route('customer.profile') }}?tab=alamat'">
                                    <i class="fi fi-rr-plus"></i> Tambah Alamat
                                </x-button>
                            @endif
                        </x-card>

                        <!-- Ekspedisi -->
                        <x-card class="p-6 mb-5">
                            <h3 class="font-heading text-lg font-extrabold text-g900 mb-5 flex items-center gap-2">
                                <i class="fi fi-rr-truck-side text-primary"></i> Pilih Ekspedisi
                            </h3>
                            <div class="space-y-3" id="ekspedisiList">
                            @foreach ($ekspedisis as $ekspedisi)
                                <label class="flex items-center gap-3 p-4 bg-white rounded-xl cursor-pointer border-[1.5px] border-g200 hover:border-primary transition-all has-[:checked]:border-primary has-[:checked]:bg-primary-light/50 has-[:checked]:ring-4 has-[:checked]:ring-primary/10">
                                    <input type="radio" name="id_ekspedisi" value="{{ $ekspedisi->id_ekspedisi }}"
                                        data-biaya="{{ $ekspedisi->biaya_pengiriman }}"
                                        {{ $loop->first ? 'checked' : '' }}
                                        onchange="calculateTotal()"
                                        class="w-4 h-4 accent-primary shrink-0 rounded-full text-primary focus:ring-primary">
                                    <div class="flex-1">
                                        <div class="text-[14px] font-bold text-g800">{{ $ekspedisi->nama_ekspedisi }}</div>
                                    </div>
                                    <div class="font-heading font-extrabold text-primary text-[15px]">
                                        Rp {{ number_format($ekspedisi->biaya_pengiriman, 0, ',', '.') }}
                                    </div>
                                </label>
                            @endforeach
                            </div>
                        </x-card>

                        <!-- Midtrans Payment -->
                        <x-card class="p-6">
                            <h3 class="font-heading text-lg font-extrabold text-g900 mb-5 flex items-center gap-2">
                                <i class="fi fi-rr-credit-card text-primary"></i> Pembayaran Aman dengan Midtrans
                            </h3>
                            <p class="text-sm text-g500 leading-relaxed">
                                Setelah pesanan dibuat, pilih metode pembayaran yang tersedia di jendela Midtrans.
                                Pembayaran akan diverifikasi otomatis; Anda tidak perlu mengunggah bukti pembayaran.
                            </p>
                        </x-card>
                    </form>
                </div>

                <!-- Summary Section (Outside checkout form to enable smooth voucher logic) -->
                <div class="w-full lg:w-[380px] shrink-0 lg:sticky lg:top-[84px]">
                    <div class="bg-white rounded-[2.5rem] shadow-card p-6 md:p-8 border border-g100">
                        <h3 class="font-heading text-xl font-extrabold text-g900 mb-6">Ringkasan Belanja</h3>

                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-g500 font-medium">Subtotal ({{ count($items) }} produk)</span>
                                <span class="font-bold text-g900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            @if ($appliedPromo)
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-green-600 font-bold flex items-center gap-2">
                                        <i class="fi fi-rr-ticket"></i> Voucher ({{ $appliedPromo->kode_voucher }})
                                    </span>
                                    <span class="font-bold text-green-600">- Rp {{ number_format($discount, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between items-center text-sm">
                                <span class="text-g500 font-medium">Ongkos Kirim</span>
                                <span class="font-bold text-g800" id="shippingCost">Rp 0</span>
                            </div>
                        </div>

                        <!-- Voucher Form -->
                        <x-voucher-panel 
                            :appliedPromo="$appliedPromo" 
                            :promos="$promos" 
                            inputId="checkout_voucher_input" 
                            formId="voucherFormCheckout" 
                        />

                        <div class="flex justify-between items-center mt-8 pt-6 border-t-2 border-dashed border-g100">
                            <span class="text-base font-bold text-g900">Total Pembayaran</span>
                            <span class="font-heading text-2xl font-extrabold text-primary" id="totalPayment">Rp {{ number_format(max(0, $subtotal - $discount), 0, ',', '.') }}</span>
                        </div>

                        <!-- Place Order Button linked to #checkoutForm -->
                        <button type="submit" form="checkoutForm" id="btnSubmitCheckout" class="w-full mt-8 py-4 px-6 bg-primary text-white rounded-2xl font-bold text-[15px] shadow-xl shadow-primary/20 opacity-60 cursor-not-allowed pointer-events-none transition-all flex items-center justify-center gap-2">
                            Buat Pesanan <i class="fi fi-rr-arrow-right"></i>
                        </button>

                        <div class="mt-6 flex items-center justify-center gap-2 text-[10px] font-bold text-g400 uppercase tracking-widest">
                            <i class="fi fi-rr-lock text-xs"></i> Transaksi aman & terenkripsi
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        const baseSubtotal = {{ max(0, $subtotal - $discount) }};
        
        function formatRupiah(amount) {
            return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function calculateTotal() {
            const selectedEkspedisi = document.querySelector('input[name="id_ekspedisi"]:checked');
            if (selectedEkspedisi) {
                const ongkir = parseInt(selectedEkspedisi.getAttribute('data-biaya')) || 0;
                const total = baseSubtotal + ongkir;
                
                document.getElementById('shippingCost').textContent = formatRupiah(ongkir);
                const totalEl = document.getElementById('totalPayment');
                if (totalEl) totalEl.textContent = formatRupiah(total);
            }
        }

        function validateCheckoutReady() {
            const btn = document.getElementById('btnSubmitCheckout');
            const hasAlamat = {{ $alamats->isNotEmpty() ? 'true' : 'false' }};
            const hasEkspedisi = document.querySelector('input[name="id_ekspedisi"]:checked') !== null;

            if (hasAlamat && hasEkspedisi) {
                btn.classList.remove('opacity-60', 'cursor-not-allowed', 'pointer-events-none');
            } else {
                btn.classList.add('opacity-60', 'cursor-not-allowed', 'pointer-events-none');
            }
        }

        // Smooth Voucher AJAX Logic
        document.addEventListener('submit', async function(e) {
            const form = e.target;
            if (!form.classList.contains('voucher-form-ajax')) return;
            
            e.preventDefault();
            const container = document.getElementById('checkoutPageContainer');
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newContent = doc.getElementById('checkoutPageContainer');
                if (newContent) {
                    container.innerHTML = newContent.innerHTML;
                    
                    // Trigger SweetAlert based on redirected session data in the new HTML
                    const flash = doc.getElementById('flash-messages');
                    if (flash) {
                        const status = flash.getAttribute('data-status');
                        const error = flash.getAttribute('data-error');
                        const errors = flash.getAttribute('data-errors');
                        
                        if (status) window.showAlert('success', 'Berhasil!', status);
                        if (error) window.showAlert('error', 'Gagal!', error);
                        if (errors) window.showAlert('error', 'Ups!', errors.replace(/\\n/g, '<br>'));
                    }

                    calculateTotal();
                    validateCheckoutReady();
                }
            } catch (err) {
                console.error(err);
                location.reload();
            } finally {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            calculateTotal();
            validateCheckoutReady();
        });
    </script>
    @endpush
@endsection
