<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alamat_users', function (Blueprint $table) {
            $table->string('nama_penerima', 100)->nullable()->after('label_alamat');
        });

        DB::table('alamat_users')
            ->join('users', 'alamat_users.id_users', '=', 'users.id_users')
            ->whereNull('alamat_users.nama_penerima')
            ->update(['alamat_users.nama_penerima' => DB::raw('users.nama')]);
    }

    public function down(): void
    {
        Schema::table('alamat_users', function (Blueprint $table) {
            $table->dropColumn('nama_penerima');
        });
    }
};
