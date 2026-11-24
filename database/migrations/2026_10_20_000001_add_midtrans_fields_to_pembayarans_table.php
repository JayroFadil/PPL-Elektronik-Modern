
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->unique();
            $table->string('transaction_status', 30)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('fraud_status', 30)->nullable();
            $table->dateTime('paid_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropUnique(['transaction_id']);
            $table->dropColumn([
                'transaction_id',
                'transaction_status',
                'payment_type',
                'fraud_status',
                'paid_at',
            ]);
        });
    }
};
