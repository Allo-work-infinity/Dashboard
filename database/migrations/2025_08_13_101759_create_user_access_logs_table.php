<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_access_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // FK to users
            $table->dateTime('access_time');                                 // required

            $table->string('ip_address', 45)->nullable();                    // IPv4/IPv6
            $table->text('user_agent')->nullable();                          // optional
            $table->integer('session_duration')->default(0);                 // minutes, default 0

            $table->json('pages_visited')->nullable();                       // optional JSON
            $table->json('actions_performed')->nullable();                   // optional JSON

            // Helpful indexes for 6-hour lookups
            $table->index(['user_id', 'access_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_access_logs');
    }
};
