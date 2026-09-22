<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tip_tipomoneda', function (Blueprint $table) {
            $table->smallInteger('idTipoMoneda')->primary();
            $table->char('Descrip', 10);
            $table->char('Simbolo', 3)->nullable();
            $table->boolean('Hab')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tip_tipomoneda');
    }
};
