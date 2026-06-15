<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->foreignId('empresa_id')
                ->nullable()
                ->after('user_id')
                ->constrained('empresas')
                ->nullOnDelete();

            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('sucursales')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['empresa_id', 'sucursal_id']);
        });
    }
};
