<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->char('IdOrientacion', 2)->nullable()->index();
            $table->char('IdCochera', 3)->nullable()->index();
            $table->char('IdVista', 7)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('bienesraices', function (Blueprint $table) {
            $table->dropIndex(['IdOrientacion']);
            $table->dropIndex(['IdCochera']);
            $table->dropIndex(['IdVista']);
            $table->dropColumn(['IdOrientacion', 'IdCochera', 'IdVista']);
        });
    }
};
