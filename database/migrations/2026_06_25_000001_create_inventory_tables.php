<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Suppliers (Proveedores)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('contacto')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('rif')->nullable(); // RIF/NIT/Tax ID
            $table->text('direccion')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('status');
        });

        // Inventory Movements (Kardex)
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->enum('tipo', ['entrada', 'salida', 'ajuste', 'transferencia']);
            $table->integer('cantidad');
            $table->integer('stock_anterior')->default(0);
            $table->integer('stock_nuevo')->default(0);
            $table->decimal('costo_unitario', 12, 2)->nullable();
            $table->foreignId('sucursal_origen_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('sucursal_destino_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->string('referencia')->nullable(); // PO number, order number, etc.
            $table->text('motivo')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();

            $table->index('product_id');
            $table->index('tipo');
            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('suppliers');
    }
};
