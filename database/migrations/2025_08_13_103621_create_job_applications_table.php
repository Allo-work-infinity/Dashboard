<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_offer_id')->constrained('job_offers')->cascadeOnDelete();

            // Core fields
            $table->enum('status', [
                'submitted', 'under_review', 'shortlisted', 'rejected', 'accepted'
            ])->default('submitted');

            $table->string('cv_file_url', 500)->nullable();
            $table->json('additional_documents')->nullable();

            // Review info
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('response_message')->nullable();

            // Custom timestamps
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Helpful indexes
            $table->index(['user_id', 'job_offer_id']);
            $table->index('status');
            $table->index('applied_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
