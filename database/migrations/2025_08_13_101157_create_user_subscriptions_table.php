<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();

            // FKs
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();

            // Statuses
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');

            // Payment info
            $table->string('payment_id', 255)->nullable();
            $table->string('transaction_id', 255)->nullable();
            $table->string('payment_method', 50)->nullable();

            // Core fields
            $table->decimal('amount_paid', 10, 3);   // TND
            $table->dateTime('start_date')->nullable(); // app-level validation when active
            $table->dateTime('end_date')->nullable();   // app-level validation when active
            $table->boolean('auto_renewal')->default(false);

            $table->timestamps();

            // Helpful indexes
            $table->index(['user_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
