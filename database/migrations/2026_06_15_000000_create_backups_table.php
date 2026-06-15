<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('disk')->default('local');
            $table->string('path');
            $table->boolean('compressed')->default(false);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
