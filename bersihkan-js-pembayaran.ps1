
$p = 'resources\views\customer\checkout.blade.php'
$c = Get-Content $p -Raw

$pattern = "(?s)        document\.addEventListener\('change', event => \{.*?\r?\n        \}\);\r?\n\r?\n(?=        document\.addEventListener\('click', async event => \{)"

$count = [regex]::Matches($c, $pattern).Count

if ($count -ne 1) {
    Write-Host "GAGAL: Blok event change ditemukan sebanyak $count kali."
    Write-Host "File tidak diubah."
    exit 1
}

Copy-Item $p 'resources\views\customer\checkout.sebelum-event-cleanup.blade.php' -Force

$new = @'
        document.addEventListener('change', event => {
            if (
                event.target.matches('input[name="id_alamat"]') ||
                event.target.matches('#courierCode')
            ) {
                clearShippingRates();
            }

            if (event.target.matches('input[name="shipping_rate_choice"]')) {
                calculateTotal();
            }
        });
'@

$c = [regex]::Replace(
    $c,
    $pattern,
    [System.Text.RegularExpressions.MatchEvaluator]{ param($m) $new + "`r`n" },
    1
)

Set-Content -Path $p -Value $c -Encoding UTF8

Write-Host 'Berhasil: event pembayaran manual dibersihkan.'
Write-Host 'Backup sebelum perubahan berhasil dibuat.'
