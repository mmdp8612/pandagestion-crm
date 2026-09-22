<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrientationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tip_orientacion')->insertOrIgnore([
            ['IdOrientacion' => 'E', 'Descrip' => 'Este', 'Hab' => true],
            ['IdOrientacion' => 'N', 'Descrip' => 'Norte', 'Hab' => true],
            ['IdOrientacion' => 'NE', 'Descrip' => 'Noreste', 'Hab' => true],
            ['IdOrientacion' => 'NO', 'Descrip' => 'Noroeste', 'Hab' => true],
            ['IdOrientacion' => 'O', 'Descrip' => 'Oeste', 'Hab' => true],
            ['IdOrientacion' => 'S', 'Descrip' => 'Sur', 'Hab' => true],
            ['IdOrientacion' => 'SE', 'Descrip' => 'Sudeste', 'Hab' => true],
            ['IdOrientacion' => 'SN', 'Descrip' => 'Indefinido', 'Hab' => true],
            ['IdOrientacion' => 'SO', 'Descrip' => 'Sudoeste', 'Hab' => true],
        ]);
    }
}
