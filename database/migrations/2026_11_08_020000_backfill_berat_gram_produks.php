<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('produks')
            ->whereNull('berat_gram')
            ->whereIn('id_kategori', [1])
            ->update(['berat_gram' => 1500]);

        DB::table('produks')
            ->whereNull('berat_gram')
            ->whereIn('id_kategori', [2])
            ->update(['berat_gram' => 500]);

        DB::table('produks')
            ->whereNull('berat_gram')
            ->whereIn('id_kategori', [3])
            ->update(['berat_gram' => 1000]);

        DB::table('produks')
            ->whereNull('berat_gram')
            ->whereIn('id_kategori', [4])
            ->update(['berat_gram' => 40000]);

        DB::table('produks')
            ->whereNull('berat_gram')
            ->whereIn('id_kategori', [5])
            ->update(['berat_gram' => 2500]);

        DB::table('produks')
            ->whereNull('berat_gram')
            ->whereIn('id_kategori', [6])
            ->update(['berat_gram' => 8000]);
    }

    public function down(): void
    {
        DB::table('produks')->update(['berat_gram' => null]);
    }
};
