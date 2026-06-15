<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('descripcion_corta')->nullable();
            $table->longText('descripcion')->nullable();
            $table->decimal('precio', 12, 2)->default(0);
            $table->decimal('precio_oferta', 12, 2)->nullable();
            $table->decimal('precio_compra', 12, 2)->nullable(); // costo
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->boolean('rastrear_inventario')->default(true);
            $table->decimal('peso', 8, 3)->nullable(); // kg
            $table->decimal('largo', 8, 2)->nullable(); // cm
            $table->decimal('ancho', 8, 2)->nullable();
            $table->decimal('alto', 8, 2)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->boolean('tiene_variantes')->default(false);
            $table->boolean('destacado')->default(false);
            $table->boolean('nuevo')->default(false);
            $table->date('fecha_publicacion')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('imagen_principal')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('sku');
            $table->index('slug');
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('status');
            $table->index('destacado');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('nombre')->nullable(); // "Rojo / XL"
            $table->decimal('precio', 12, 2)->nullable(); // null = usa precio del producto
            $table->decimal('precio_oferta', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->decimal('peso', 8, 3)->nullable();
            $table->string('imagen')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index('product_id');
            $table->index('sku');
        });

        // Pivot: variant ↔ attribute_value
        Schema::create('product_variant_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();

            $table->unique(['product_variant_id', 'attribute_value_id'], 'pv_av_unique');
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->string('ruta');
            $table->string('alt_text')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index('product_variant_id');
        });

        // Pivot: product ↔ attribute values (for non-variant attributes)
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->nullable()->constrained('attribute_values')->nullOnDelete();
            $table->string('valor_personalizado')->nullable(); // para tipo text

            $table->unique(['product_id', 'attribute_id'], 'prod_attr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variant_attribute_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
