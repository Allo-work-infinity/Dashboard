<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();                                      // id (PK)
            $table->string('name', 100);                       // required
            $table->text('description')->nullable();           // optional
            $table->decimal('price', 10, 3);                   // required, TND
            $table->unsignedInteger('duration_days');          // required
            $table->json('features')->nullable();              // optional JSON
            $table->boolean('is_active')->default(true);       // default: true
            $table->timestamps();                              // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
