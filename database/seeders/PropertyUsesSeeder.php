<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertyUsesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tip_uso')->insertOrIgnore([
            ['IdUso' => 'APRO', 'Descrip' => 'Apta Profes', 'Hab' => true],
            ['IdUso' => 'COME', 'Descrip' => 'Comercial', 'Hab' => true],
            ['IdUso' => 'INDU', 'Descrip' => 'Industrial', 'Hab' => true],
            ['IdUso' => 'TODO', 'Descrip' => 'Todo Destino', 'Hab' => true],
            ['IdUso' => 'VIVI', 'Descrip' => 'Vivienda', 'Hab' => true],
        ]);
    }
}
