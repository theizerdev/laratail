<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('fecha_apertura');
            $table->timestamp('fecha_cierre')->nullable();
            $table->decimal('monto_inicial', 12, 2)->default(0);
            $table->decimal('monto_final', 12, 2)->nullable();
            $table->decimal('total_ingresos', 12, 2)->default(0);
            $table->decimal('total_egresos', 12, 2)->default(0);
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->text('notas')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();

            $table->index(['empresa_id', 'sucursal_id', 'estado']);
        });

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 12, 2);
            $table->string('descripcion')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('cash_registers');
    }
};
