<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommercializationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tip_comercializacion')->insertOrIgnore([
            ['IdComercializacion' => 'A-V', 'Descrip' => 'Ambos V/A', 'Hab' => true],
            ['IdComercializacion' => 'ALQ', 'Descrip' => 'Alquiler', 'Hab' => true],
            ['IdComercializacion' => 'ATE', 'Descrip' => 'Alq Temporario', 'Hab' => true],
            ['IdComercializacion' => 'FON', 'Descrip' => 'Fondo de Comerc', 'Hab' => false],
            ['IdComercializacion' => 'VTA', 'Descrip' => 'Venta', 'Hab' => true],
        ]);
    }
}
