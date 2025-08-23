<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('phone', 20)->nullable();
                $table->date('date_of_birth')->nullable();
                $table->text('address')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('governorate', 100)->nullable();
                $table->string('profile_picture_url', 500)->nullable();
                $table->string('cv_file_url', 500)->nullable();
                $table->boolean('is_email_verified')->default(false);
                $table->string('email_verification_token', 255)->nullable();
                $table->string('password_reset_token', 255)->nullable();
                $table->dateTime('password_reset_expires')->nullable();
                $table->enum('status', ['active', 'suspended', 'banned'])->default('active');
                $table->dateTime('last_access_time')->nullable();
                $table->boolean('is_admin')->default(false);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
