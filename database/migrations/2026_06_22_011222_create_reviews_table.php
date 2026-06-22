<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('titulo', 120)->nullable();
            $table->text('comentario')->nullable();
            $table->boolean('verificado')->default(false); // compra verificada
            $table->boolean('aprobado')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'customer_id']); // una reseña por cliente por producto
            $table->index('product_id');
            $table->index('aprobado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
