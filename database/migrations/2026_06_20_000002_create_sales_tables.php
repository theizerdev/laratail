<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Coupons (first because orders references it) ──
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['porcentaje', 'fijo'])->default('porcentaje');
            $table->decimal('valor', 10, 2)->default(0);
            $table->decimal('compra_minima', 12, 2)->default(0);
            $table->decimal('descuento_maximo', 12, 2)->nullable();
            $table->integer('usos_maximos')->nullable();
            $table->integer('usos_actuales')->default(0);
            $table->integer('usos_por_cliente')->default(1);
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('acumular')->default(false);
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('codigo');
            $table->index('activo');
        });

        // ─── Orders ───────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // ORD-20260620-XXXX
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // vendedor
            $table->enum('tipo', ['venta', 'cotizacion'])->default('venta');
            $table->enum('estado', ['borrador', 'pendiente', 'confirmado', 'procesando', 'enviado', 'entregado', 'cancelado', 'devuelto'])->default('borrador');
            $table->enum('estado_pago', ['pendiente', 'parcial', 'pagado', 'reembolsado'])->default('pendiente');

            // Totals
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('impuesto', 14, 2)->default(0);
            $table->decimal('envio', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            // Discount
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('codigo_cupon')->nullable();

            // Shipping
            $table->text('direccion_envio')->nullable();
            $table->string('ciudad_envio')->nullable();
            $table->string('estado_envio')->nullable();
            $table->string('codigo_postal_envio')->nullable();
            $table->foreignId('pais_envio_id')->nullable()->constrained('pais')->nullOnDelete();
            $table->string('metodo_envio')->nullable();
            $table->string('numero_seguimiento')->nullable();

            // Payment
            $table->string('metodo_pago')->nullable(); // efectivo, transferencia, tarjeta, etc.
            $table->text('referencia_pago')->nullable();
            $table->timestamp('fecha_pago')->nullable();

            // Dates
            $table->timestamp('fecha_confirmacion')->nullable();
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_entrega')->nullable();
            $table->timestamp('fecha_cancelacion')->nullable();

            // Notes
            $table->text('notas_internas')->nullable();
            $table->text('notas_cliente')->nullable();

            // Multi-tenant
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('numero');
            $table->index('customer_id');
            $table->index('estado');
            $table->index('estado_pago');
            $table->index('tipo');
        });

        // ─── Order Items ──────────────────────────────────
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('nombre_producto'); // Snapshot at order time
            $table->string('sku')->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });

        // ─── Carts ────────────────────────────────────────
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // vendedor
            $table->enum('estado', ['activo', 'abandonado', 'convertido'])->default('activo');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->timestamp('expira_en')->nullable();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('customer_id');
            $table->index('estado');
        });

        // ─── Cart Items ───────────────────────────────────
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'cart_item_unique');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
    }
};
