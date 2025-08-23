<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // allow nulls for manual payments (no gateway ref)
            $table->string('konnect_payment_id', 255)->nullable()->change();
            $table->string('konnect_transaction_id', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('konnect_payment_id', 255)->nullable(false)->change();
            $table->string('konnect_transaction_id', 255)->nullable(false)->change();
        });
    }
};
