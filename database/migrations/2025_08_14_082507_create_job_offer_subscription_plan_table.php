<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_offer_subscription_plan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_offer_id')
                  ->constrained('job_offers')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('subscription_plan_id')
                  ->constrained('subscription_plans')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            // ✅ give the unique index a short name
            $table->unique(
                ['job_offer_id', 'subscription_plan_id'],
                'job_offer_plan_uniq'
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offer_subscription_plan');
    }
};
