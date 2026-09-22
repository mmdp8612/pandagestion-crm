<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tip_uso', function (Blueprint $table) {
            $table->char('IdUso', 4)->primary();
            $table->char('Descrip', 15);
            $table->boolean('Hab')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tip_uso');
    }
};
