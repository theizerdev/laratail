<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Color, Talla, Material, etc.
            $table->string('slug')->unique();
            $table->string('tipo')->default('select'); // select, text, color
            $table->boolean('usado_para_variantes')->default(true);
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id']);
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('valor'); // Rojo, XL, Algodón, etc.
            $table->string('codigo_color')->nullable(); // Para atributos tipo color: #FF0000
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->unique(['attribute_id', 'valor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
