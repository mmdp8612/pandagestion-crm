<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->decimal('SupCubiertaPropia', 12, 2)->nullable();
            $table->decimal('SupTerreno', 12, 2)->nullable();
            $table->unsignedSmallInteger('Plantas')->nullable();
            $table->unsignedSmallInteger('Ambientes')->nullable();
            $table->unsignedSmallInteger('Sanitarios')->nullable();
            $table->unsignedSmallInteger('Suite')->nullable();
            $table->unsignedSmallInteger('Dormitorios')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->dropColumn([
                'SupCubiertaPropia',
                'SupTerreno',
                'Plantas',
                'Ambientes',
                'Sanitarios',
                'Suite',
                'Dormitorios',
            ]);
        });
    }
};
