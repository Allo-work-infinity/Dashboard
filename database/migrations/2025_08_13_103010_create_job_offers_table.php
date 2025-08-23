<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();

            // FK to companies (required)
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // Core fields
            $table->string('title', 255);                 // required
            $table->text('description');                  // required
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();

            // Enums
            $table->enum('job_type', ['full_time','part_time','contract','internship','remote']);
            $table->enum('experience_level', ['entry','junior','mid','senior','lead']);

            // Salary / currency
            $table->decimal('salary_min', 10, 3)->nullable();
            $table->decimal('salary_max', 10, 3)->nullable();
            $table->string('currency', 3)->default('TND');

            // Location
            $table->string('location', 200);              // required
            $table->string('city', 100);                  // required
            $table->string('governorate', 100);           // required
            $table->boolean('remote_allowed')->default(false);

            // Extras
            $table->json('skills_required')->nullable();
            $table->json('benefits')->nullable();
            $table->dateTime('application_deadline')->nullable();

            // Flags & counters
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft','active','paused','closed'])->default('draft');
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('applications_count')->default(0);

            $table->timestamps();

            // Helpful indexes
            $table->index(['company_id','status']);
            $table->index(['status','is_featured']);
            $table->index('application_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};
