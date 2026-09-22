<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencyTypesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tip_tipomoneda')->insertOrIgnore([
            ['idTipoMoneda' => 0, 'Descrip' => 'Dolares', 'Simbolo' => 'u$s', 'Hab' => true],
            ['idTipoMoneda' => 1, 'Descrip' => 'Pesos', 'Simbolo' => '$', 'Hab' => true],
        ]);
    }
}
