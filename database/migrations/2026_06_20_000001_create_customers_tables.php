<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre');
            $table->string('apellido')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('tipo_documento')->nullable(); // RIF, CI, NIT, etc.
            $table->string('documento')->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('estado_region')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->foreignId('pais_id')->nullable()->constrained('pais')->nullOnDelete();
            $table->string('empresa_nombre')->nullable(); // Nombre comercial / razón social
            $table->text('notas')->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('fuente')->nullable(); // web, referido, tienda, whatsapp
            $table->date('fecha_nacimiento')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'sucursal_id']);
            $table->index('email');
            $table->index('telefono');
            $table->index('documento');
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('tipo')->default('envio'); // envio, facturacion
            $table->string('alias')->nullable(); // Casa, Oficina, etc.
            $table->string('nombre_destinatario')->nullable();
            $table->string('telefono_destinatario', 20)->nullable();
            $table->text('direccion');
            $table->string('ciudad')->nullable();
            $table->string('estado_region')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->foreignId('pais_id')->nullable()->constrained('pais')->nullOnDelete();
            $table->boolean('predeterminada')->default(false);
            $table->timestamps();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
