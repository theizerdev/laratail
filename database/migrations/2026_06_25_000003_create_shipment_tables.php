<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('carrier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->enum('estado', ['preparando', 'enviado', 'en_transito', 'entregado', 'devuelto'])->default('preparando');
            $table->date('fecha_envio')->nullable();
            $table->date('fecha_entrega_esperada')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->decimal('peso', 8, 3)->nullable();
            $table->decimal('costo_envio', 12, 2)->default(0);
            $table->text('direccion_destino')->nullable();
            $table->string('ciudad_destino')->nullable();
            $table->string('estado_destino')->nullable();
            $table->string('codigo_postal_destino')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('order_id');
            $table->index('estado');
            $table->index('numero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
