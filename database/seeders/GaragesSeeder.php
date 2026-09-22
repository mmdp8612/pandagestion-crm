<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GaragesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tip_cochera')->insertOrIgnore([
            ['IdCochera' => 'CCU', 'Descrip' => 'Cochera Cubierta', 'Hab' => true],
            ['IdCochera' => 'COC', 'Descrip' => 'Cochera', 'Hab' => true],
            ['IdCochera' => 'DES', 'Descrip' => 'Cochera Descubierta', 'Hab' => true],
            ['IdCochera' => 'ENT', 'Descrip' => 'Entrada de Auto', 'Hab' => true],
            ['IdCochera' => 'EPA', 'Descrip' => 'Entrada Pasante', 'Hab' => true],
            ['IdCochera' => 'GAR', 'Descrip' => 'Garage', 'Hab' => true],
            ['IdCochera' => 'GPA', 'Descrip' => 'Garage Pasante', 'Hab' => true],
            ['IdCochera' => 'OPT', 'Descrip' => 'Optativa', 'Hab' => true],
            ['IdCochera' => 'PAS', 'Descrip' => 'Pasante', 'Hab' => true],
            ['IdCochera' => 'PLA', 'Descrip' => 'Playa Estacionam', 'Hab' => true],
            ['IdCochera' => 'SCO', 'Descrip' => 'Sin Cochera', 'Hab' => true],
            ['IdCochera' => 'SEM', 'Descrip' => 'Semicubierta', 'Hab' => true],
            ['IdCochera' => 'SUM', 'Descrip' => 'Sumergida', 'Hab' => true],
        ]);
    }
}
