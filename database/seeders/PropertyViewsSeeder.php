<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertyViewsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tip_vista')->insertOrIgnore([
            ['IdVista' => 'C/FTE', 'Descrip' => 'Contrafrente', 'Hab' => true],
            ['IdVista' => 'FRENTE', 'Descrip' => 'Al Frente', 'Hab' => true],
            ['IdVista' => 'INTERNO', 'Descrip' => 'Interno', 'Hab' => true],
            ['IdVista' => 'LATERAL', 'Descrip' => 'Lateral', 'Hab' => true],
        ]);
    }
}
