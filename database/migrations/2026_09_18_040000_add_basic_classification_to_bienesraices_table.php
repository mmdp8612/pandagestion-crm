<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->char('IdTipologia', 4)->nullable()->index();
            $table->char('IdUso', 4)->nullable()->index();
            $table->string('Antiguedad', 3)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->dropIndex(['IdTipologia']);
            $table->dropIndex(['IdUso']);
            $table->dropIndex(['Antiguedad']);
            $table->dropColumn(['IdTipologia', 'IdUso', 'Antiguedad']);
        });
    }
};
