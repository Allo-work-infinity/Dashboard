<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // FKs
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('user_subscriptions')->cascadeOnDelete();

            // Konnect IDs
            $table->string('konnect_payment_id', 255);      // required
            $table->string('konnect_transaction_id', 255)->nullable();

            // Amount & payment info
            $table->decimal('amount', 10, 3);               // required
            $table->string('currency', 3)->default('TND');  // default TND
            $table->string('payment_method', 50);           // required

            // Status
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])
                  ->default('pending');

            // Raw gateway response & diagnostics
            $table->json('konnect_response')->nullable();
            $table->string('failure_reason', 500)->nullable();

            // Processing timestamp
            $table->dateTime('processed_at')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->unique('konnect_payment_id');
            $table->index(['user_id', 'subscription_id']);
            $table->index(['status', 'processed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
