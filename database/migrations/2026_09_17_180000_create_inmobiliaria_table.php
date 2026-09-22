<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inmobiliaria', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('RazonSocial', 150);
            $table->string('Telefonos')->nullable();
            $table->string('Whatsapp', 50)->nullable();
            $table->string('Email')->nullable();
            $table->string('Domicilio')->nullable();
            $table->string('CodigoPostal', 20)->nullable();
            $table->string('Provincia', 100)->nullable();
            $table->string('Partido', 100)->nullable();
            $table->string('Localidad', 100)->nullable();
            $table->string('Barrio', 100)->nullable();
            $table->decimal('Latitud', 10, 7)->nullable();
            $table->decimal('Longitud', 10, 7)->nullable();
            $table->string('Logo')->nullable();
            $table->string('Web')->nullable();
            $table->string('Matricula', 150)->nullable();
            $table->boolean('Hab')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inmobiliaria');
    }
};
