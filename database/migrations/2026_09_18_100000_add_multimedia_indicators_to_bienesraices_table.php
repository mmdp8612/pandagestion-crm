<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->boolean('TieneFoto')->default(false);
            $table->boolean('TieneVideo')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->dropColumn(['TieneFoto', 'TieneVideo']);
        });
    }
};
