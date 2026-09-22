<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienesraices', function (Blueprint $table) {
            $table->id();
            $table->string('Codigo', 30)->unique();
            $table->text('Descrip')->nullable();
            $table->string('Calle', 120);
            $table->string('Numero', 20)->nullable();
            $table->string('Piso', 20)->nullable();
            $table->string('Torre', 50)->nullable();
            $table->string('Provincia', 100)->nullable();
            $table->string('Partido', 100)->nullable();
            $table->string('Localidad', 100)->nullable()->index();
            $table->string('Barrio', 100)->nullable();
            $table->string('CodigoPostal', 20)->nullable();
            $table->decimal('Latitud', 10, 7)->nullable();
            $table->decimal('Longitud', 11, 7)->nullable();
            $table->boolean('Hab')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienesraices');
    }
};
