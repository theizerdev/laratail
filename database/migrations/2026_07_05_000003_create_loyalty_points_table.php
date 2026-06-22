<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Loyalty points ledger
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->integer('puntos'); // positive = earned, negative = redeemed
            $table->string('tipo'); // 'compra', 'canje', 'referido', 'bonus', 'expiracion'
            $table->string('descripcion')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Add points balance column to customers
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('puntos_acumulados')->default(0)->after('notas');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('puntos_acumulados');
        });
        Schema::dropIfExists('loyalty_points');
    }
};
