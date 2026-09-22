<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tip_antiguedad', function (Blueprint $table) {
            $table->string('IdAntiguedad', 3)->primary();
            $table->char('Descrip', 15)->nullable();
            $table->integer('Orden')->nullable();
            $table->boolean('Hab')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tip_antiguedad');
    }
};
