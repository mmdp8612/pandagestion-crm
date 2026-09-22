<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tip_cochera', function (Blueprint $table) {
            $table->char('IdCochera', 3)->primary();
            $table->char('Descrip', 20)->nullable();
            $table->boolean('Hab')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tip_cochera');
    }
};
