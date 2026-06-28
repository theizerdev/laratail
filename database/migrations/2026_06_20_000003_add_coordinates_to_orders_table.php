<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'latitud')) {
                $table->decimal('latitud', 10, 8)->nullable()->after('codigo_postal_envio');
            }
            if (!Schema::hasColumn('orders', 'longitud')) {
                $table->decimal('longitud', 11, 8)->nullable()->after('latitud');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });
    }
};
