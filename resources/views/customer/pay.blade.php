@extends('layouts.app')

@section('title', 'Pembayaran – Elektronik Modern')

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <section class="py-8 md:py-[72px] bg-g50 min-h-screen px-4 md:px-8">
        <div class="max-w-[520px] mx-auto bg-white rounded-[2rem] shadow-card p-8 border border-g100 text-center">
            <h1 class="font-heading text-2xl font-extrabold text-g900 mb-2">Pembayaran Pesanan</h1>
            <p class="text-g500 text-sm mb-6">{{ $pesanan->no_resi }}</p>
            <div class="font-heading text-3xl font-extrabold text-primary mb-8">
                Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}
            </div>
            <button type="button" id="payButton"
                class="w-full py-4 px-6 bg-primary text-white rounded-2xl font-bold text-[15px]">
                Bayar Sekarang
            </button>
            <a href="{{ route('customer.orders') }}" class="block mt-4 text-sm text-g500 hover:text-primary">Bayar nanti</a>
        </div>
    </section>

    @push('scripts')
    <script src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ $clientKey }}"></script>
    <script>
        const ordersUrl = @json(route('customer.orders'));
        function openSnap() {
            window.snap.pay(@json($snapToken), {
                onSuccess: () => { window.location = ordersUrl; },
                onPending: () => { window.location = ordersUrl; },
                onError:   () => { alert('Pembayaran gagal. Silakan coba lagi.'); },
                onClose:   () => {}
            });
        }
        document.getElementById('payButton').addEventListener('click', openSnap);
        window.addEventListener('load', openSnap);
    </script>
    @endpush
@endsection
