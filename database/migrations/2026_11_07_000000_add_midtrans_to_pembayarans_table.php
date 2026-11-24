<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('snap_token')->nullable();
        });

        // Pembayaran Midtrans tidak punya bukti upload.
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('bukti_bayar')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['midtrans_order_id', 'snap_token']);
        });
    }
};
