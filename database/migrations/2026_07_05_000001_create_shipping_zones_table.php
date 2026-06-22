<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo_postal_inicio')->nullable();
            $table->string('codigo_postal_fin')->nullable();
            $table->string('pais_codigo')->nullable(); // ISO2 country code or 'ALL'
            $table->decimal('costo_base', 10, 2)->default(0);
            $table->decimal('costo_por_kg', 10, 2)->default(0);
            $table->integer('dias_entrega_min')->default(3);
            $table->integer('dias_entrega_max')->default(7);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
