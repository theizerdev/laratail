<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'asignado' to the enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN estado ENUM('borrador', 'pendiente', 'confirmado', 'asignado', 'procesando', 'enviado', 'entregado', 'cancelado', 'devuelto') DEFAULT 'borrador'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back
        DB::statement("ALTER TABLE orders MODIFY COLUMN estado ENUM('borrador', 'pendiente', 'confirmado', 'procesando', 'enviado', 'entregado', 'cancelado', 'devuelto') DEFAULT 'borrador'");
    }
};
