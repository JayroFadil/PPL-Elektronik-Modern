@if (session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.showAlert === 'function') {
                window.showAlert('success', 'Berhasil!', "{!! addslashes(session('status')) !!}");
            } else {
                console.log("Status: {{ session('status') }}");
            }
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.showAlert === 'function') {
                window.showAlert('error', 'Gagal!', "{!! addslashes(session('error')) !!}");
            } else {
                console.error("Error: {{ session('error') }}");
            }
        });
    </script>
@endif

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const errorMsg = "{!! addslashes(implode('<br>', $errors->all())) !!}";
            if (typeof window.showAlert === 'function') {
                window.showAlert('error', 'Ups!', errorMsg);
            } else {
                console.error("Validation Errors: " + errorMsg.replace('<br>', '\n'));
            }
        });
    </script>
@endif

@if (session('payment_success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.showPaymentSuccessAlert === 'function') 
            {
                window.showPaymentSuccessAlert('Pembayaran Berhasil!',
                "{!!addslashes(session('payment_success')) !!}");
            }
        });
    </script>
@endif
