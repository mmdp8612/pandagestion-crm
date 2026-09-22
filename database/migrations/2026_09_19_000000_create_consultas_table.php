<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idBienRaiz')->constrained('bienesraices')->cascadeOnDelete();
            $table->string('CodigoPropiedad', 30);
            $table->string('Nombre', 120);
            $table->string('Email', 190)->nullable();
            $table->string('Telefono', 50)->nullable();
            $table->text('Mensaje');
            $table->string('Estado', 20)->default('nueva');
            $table->timestamps();

            $table->index(['Estado', 'created_at']);
            $table->index(['idBienRaiz', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
