<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bienesraices', function (Blueprint $table): void {
            $table->string('VideoUrl', 500)->nullable()->after('TieneVideo');
        });

        DB::table('bienesraices')->update(['TieneVideo' => false]);
    }

    public function down(): void
    {
        Schema::table('bienesraices', function (Blueprint $table): void {
            $table->dropColumn('VideoUrl');
        });
    }
};
