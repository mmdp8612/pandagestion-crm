<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienesraices_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idBienRaiz')->constrained('bienesraices')->cascadeOnDelete();
            $table->string('Archivo');
            $table->unsignedInteger('Orden')->default(0);
            $table->boolean('Portada')->default(false);
            $table->boolean('Hab')->default(true);
            $table->timestamps();

            $table->index(['idBienRaiz', 'Orden']);
        });

        DB::table('bienesraices')->update(['TieneFoto' => false]);
    }

    public function down(): void
    {
        Schema::dropIfExists('bienesraices_imagenes');
    }
};
