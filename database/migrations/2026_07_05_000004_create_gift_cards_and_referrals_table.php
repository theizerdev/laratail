<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 16)->unique();
            $table->decimal('valor_inicial', 10, 2);
            $table->decimal('saldo', 10, 2);
            $table->string('destinatario_email')->nullable();
            $table->string('destinatario_nombre')->nullable();
            $table->string('mensaje')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // buyer
            $table->foreignId('redeemed_by_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_canje')->nullable();
            $table->timestamp('fecha_expiracion')->nullable();
            $table->string('estado')->default('activo'); // activo, canjeado, expirado
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('empresa_id')->nullable()->constrained();
            $table->timestamps();
        });

        // Referrals
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('codigo_referido')->unique();
            $table->foreignId('referred_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('puntos_referrer')->default(0);
            $table->integer('puntos_referred')->default(0);
            $table->string('estado')->default('pendiente'); // pendiente, completado
            $table->timestamps();
        });

        // Add referral code to customers
        Schema::table('customers', function (Blueprint $table) {
            $table->string('codigo_referido')->nullable()->unique()->after('fuente');
            $table->foreignId('referred_by')->nullable()->after('codigo_referido');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['codigo_referido', 'referred_by']);
        });
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('gift_cards');
    }
};
