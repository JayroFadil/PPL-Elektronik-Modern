
$p = 'resources\views\customer\checkout.blade.php'

$c = Get-Content $p -Raw

# Cari batas bagian pembayaran
$start = $c.IndexOf('                        <!-- Midtrans Payment -->')
$end = $c.IndexOf('                    </form>', $start)

if ($start -lt 0 -or $end -lt 0) {
    Write-Host 'GAGAL: Bagian pembayaran tidak ditemukan.'
    exit 1
}

# Ganti tampilan pembayaran menjadi Midtrans saja
$new = @'
                        <!-- Midtrans Payment -->
                        <x-card class="p-6">
                            <h3 class="font-heading text-lg font-extrabold text-g900 mb-5 flex items-center gap-2">
                                <i class="fi fi-rr-credit-card text-primary"></i> Pembayaran melalui Midtrans
                            </h3>

                            <input type="hidden" name="metode_pembayaran" value="Midtrans">

                            <div class="flex items-center gap-3.5 p-4 bg-primary-light rounded-xl border border-primary">
                                <div class="text-[28px] text-primary flex items-center justify-center bg-white w-12 h-12 rounded-full shadow-sm">
                                    <i class="fi fi-rr-credit-card"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-[14px] text-g800 mb-0.5">Midtrans Snap</div>
                                    <div class="text-[11px] font-medium text-g500 uppercase tracking-wider">Virtual Account · Kartu · E-Wallet · QRIS</div>
                                </div>
                            </div>

                            <p class="mt-4 text-sm text-g500">
                                Pilih metode pembayaran yang tersedia pada halaman pembayaran Midtrans setelah pesanan dibuat.
                            </p>
                        </x-card>
'@

$c = $c.Substring(0, $start) + $new + $c.Substring($end)

# Ubah validasi checkout agar tidak membutuhkan bukti pembayaran
$vstart = $c.IndexOf('        function validateCheckoutReady() {')
$vend = $c.IndexOf('        function calculateTotal() {', $vstart)

if ($vstart -lt 0 -or $vend -lt 0) {
    Write-Host 'GAGAL: Fungsi validasi checkout tidak ditemukan.'
    exit 1
}

$validation = @'
        function validateCheckoutReady() {
            const button = document.getElementById('btnSubmitCheckout');
            if (!button) return;

            const hasAddress = document.querySelector('input[name="id_alamat"]:checked') !== null;
            const hasRate = document.querySelector('input[name="shipping_rate_choice"]:checked') !== null;
            const ready = hasAddress && hasRate;

            button.disabled = !ready;
            button.classList.toggle('opacity-60', !ready);
            button.classList.toggle('cursor-not-allowed', !ready);
            button.classList.toggle('pointer-events-none', !ready);
        }

'@

$c = $c.Substring(0, $vstart) + $validation + $c.Substring($vend)

Set-Content -Path $p -Value $c -Encoding UTF8

Write-Host 'Berhasil: tampilan pembayaran menjadi Midtrans saja.'
