<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tip_tipologia', function (Blueprint $table) {
            $table->char('IdTipologia', 4)->primary();
            $table->char('Descrip', 20);
            $table->char('TipoGral', 15)->nullable();
            $table->smallInteger('OrdTipoGral')->default(0);
            $table->boolean('Hab')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tip_tipologia');
    }
};
