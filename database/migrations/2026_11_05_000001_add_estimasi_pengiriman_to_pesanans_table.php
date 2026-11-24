<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pesanans', 'estimasi_pengiriman')) {
            Schema::table('pesanans', function (Blueprint $table) {
                $table->string('estimasi_pengiriman')
                    ->nullable()
                    ->after('ongkos_kirim');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pesanans', 'estimasi_pengiriman')) {
            Schema::table('pesanans', function (Blueprint $table) {
                $table->dropColumn('estimasi_pengiriman');
            });
        }
    }
};
