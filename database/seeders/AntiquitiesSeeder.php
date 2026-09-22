<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AntiquitiesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tip_antiguedad')->insertOrIgnore([
            ['IdAntiguedad' => 'A10', 'Descrip' => 'Menor a 10', 'Orden' => 30, 'Hab' => true],
            ['IdAntiguedad' => 'A15', 'Descrip' => 'Menor a 15', 'Orden' => 40, 'Hab' => true],
            ['IdAntiguedad' => 'A20', 'Descrip' => 'Menor a 20', 'Orden' => 50, 'Hab' => true],
            ['IdAntiguedad' => 'A25', 'Descrip' => 'Menor a 25', 'Orden' => 55, 'Hab' => true],
            ['IdAntiguedad' => 'A30', 'Descrip' => 'Menor a 30', 'Orden' => 56, 'Hab' => true],
            ['IdAntiguedad' => 'A35', 'Descrip' => 'Menor a 35', 'Orden' => 57, 'Hab' => true],
            ['IdAntiguedad' => 'A40', 'Descrip' => 'Menor a 40', 'Orden' => 58, 'Hab' => true],
            ['IdAntiguedad' => 'A45', 'Descrip' => 'Menos a 45', 'Orden' => 59, 'Hab' => true],
            ['IdAntiguedad' => 'A50', 'Descrip' => 'Menor a 50', 'Orden' => 60, 'Hab' => true],
            ['IdAntiguedad' => 'A99', 'Descrip' => 'Mas de 50', 'Orden' => 80, 'Hab' => true],
            ['IdAntiguedad' => 'AES', 'Descrip' => 'A Estrenar', 'Orden' => 10, 'Hab' => true],
            ['IdAntiguedad' => 'AN5', 'Descrip' => 'Menor a 5', 'Orden' => 20, 'Hab' => true],
            ['IdAntiguedad' => 'ENC', 'Descrip' => 'En Construcción', 'Orden' => 5, 'Hab' => true],
            ['IdAntiguedad' => 'SIN', 'Descrip' => 'Sin Determinar', 'Orden' => 0, 'Hab' => true],
        ]);
    }
}
