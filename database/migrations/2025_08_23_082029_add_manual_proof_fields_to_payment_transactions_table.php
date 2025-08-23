<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Manual payment reference (e.g., bank transfer/D17 reference the user typed)
            $table->string('manual_reference')->nullable()->after('payment_method');

            // Proof image & metadata
            $table->string('proof_path')->nullable()->after('manual_reference');
            $table->string('proof_original_name')->nullable()->after('proof_path');
            $table->string('proof_mime', 100)->nullable()->after('proof_original_name');
            $table->unsignedBigInteger('proof_size')->nullable()->after('proof_mime');
            $table->timestamp('proof_uploaded_at')->nullable()->after('proof_size');

            // Optional review metadata (admin verification)
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('processed_at');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_note')->nullable()->after('reviewed_at');

            // FK to users for reviewed_by
            $table->foreign('reviewed_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Drop foreign key first (name may vary by DB; this matches Laravel's default)
            $table->dropForeign(['reviewed_by']);

            $table->dropColumn([
                'manual_reference',
                'proof_path',
                'proof_original_name',
                'proof_mime',
                'proof_size',
                'proof_uploaded_at',
                'reviewed_by',
                'reviewed_at',
                'review_note',
            ]);
        });
    }
};
