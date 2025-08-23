<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            $table->string('key', 100)->unique();                  // unique, required
            $table->text('value');                                  // required (stored as text)
            $table->enum('data_type', ['string','integer','boolean','json'])
                  ->default('string');                              // type hint for casting
            $table->text('description')->nullable();                // optional
            $table->boolean('is_public')->default(false);           // default: false

            $table->timestamps();

            // helpful index
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
