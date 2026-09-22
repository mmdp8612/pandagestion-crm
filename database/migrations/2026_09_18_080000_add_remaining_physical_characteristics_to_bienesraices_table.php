<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->decimal('Frente', 12, 2)->nullable();
            $table->decimal('Fondo', 12, 2)->nullable();
            $table->decimal('MtsFondo', 12, 2)->nullable();
            $table->string('Luminosidad', 30)->nullable();
            $table->unsignedSmallInteger('LineasTel')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->dropColumn([
                'Frente',
                'Fondo',
                'MtsFondo',
                'Luminosidad',
                'LineasTel',
            ]);
        });
    }
};
