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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('usd_rate', 10, 4);
            $table->decimal('eur_rate', 10, 4)->nullable();
            $table->enum('source', ['bcv', 'manual'])->default('bcv');
            $table->time('fetch_time');
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            $table->unique(['date', 'fetch_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
