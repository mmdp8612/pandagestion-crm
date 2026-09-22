<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->char('IdComercializacion', 3)->nullable()->index();
            $table->decimal('ImporteVta', 15, 2)->nullable();
            $table->decimal('ImporteAlq', 15, 2)->nullable();
            $table->smallInteger('idTipoMonedaVta')->nullable()->index();
            $table->smallInteger('idTipoMonedaAlq')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->dropIndex(['IdComercializacion']);
            $table->dropIndex(['idTipoMonedaVta']);
            $table->dropIndex(['idTipoMonedaAlq']);
            $table->dropColumn([
                'IdComercializacion',
                'ImporteVta',
                'ImporteAlq',
                'idTipoMonedaVta',
                'idTipoMonedaAlq',
            ]);
        });
    }
};
