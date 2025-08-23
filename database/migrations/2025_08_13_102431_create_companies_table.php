<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Core info
            $table->string('name', 200);                // required
            $table->text('description')->nullable();    // optional
            $table->string('industry', 100)->nullable();

            // Size enum
            $table->enum('company_size', [
                'startup', 'small', 'medium', 'large', 'enterprise'
            ])->nullable();

            // Contact & profile
            $table->string('website', 255)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('governorate', 100)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 20)->nullable();

            // Status & verification
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['active', 'suspended'])->default('active');

            $table->timestamps();

            // Helpful indexes
            $table->index('name');
            $table->index(['status', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
