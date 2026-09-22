<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypologiesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tip_tipologia')->insertOrIgnore([
            ['IdTipologia' => 'BAUL', 'Descrip' => 'Baulera', 'TipoGral' => 'Cocheras', 'OrdTipoGral' => 60, 'Hab' => true],
            ['IdTipologia' => 'CAMP', 'Descrip' => 'Campo', 'TipoGral' => 'Campos', 'OrdTipoGral' => 80, 'Hab' => true],
            ['IdTipologia' => 'CAPH', 'Descrip' => 'PH', 'TipoGral' => 'Casas', 'OrdTipoGral' => 10, 'Hab' => true],
            ['IdTipologia' => 'CASA', 'Descrip' => 'Casa', 'TipoGral' => 'Casas', 'OrdTipoGral' => 10, 'Hab' => true],
            ['IdTipologia' => 'CHAC', 'Descrip' => 'Chacra', 'TipoGral' => 'Campos', 'OrdTipoGral' => 80, 'Hab' => true],
            ['IdTipologia' => 'CHAL', 'Descrip' => 'Chalet', 'TipoGral' => 'Casas', 'OrdTipoGral' => 10, 'Hab' => true],
            ['IdTipologia' => 'COCH', 'Descrip' => 'Cochera', 'TipoGral' => 'Cocheras', 'OrdTipoGral' => 60, 'Hab' => true],
            ['IdTipologia' => 'CQUI', 'Descrip' => 'Casa Quinta', 'TipoGral' => 'Casas', 'OrdTipoGral' => 10, 'Hab' => true],
            ['IdTipologia' => 'CTRY', 'Descrip' => 'Country', 'TipoGral' => 'Casas', 'OrdTipoGral' => 10, 'Hab' => true],
            ['IdTipologia' => 'DEPO', 'Descrip' => 'Depósito', 'TipoGral' => 'Industriales', 'OrdTipoGral' => 70, 'Hab' => true],
            ['IdTipologia' => 'DPTO', 'Descrip' => 'Departamento', 'TipoGral' => 'Departamentos', 'OrdTipoGral' => 20, 'Hab' => true],
            ['IdTipologia' => 'DUPL', 'Descrip' => 'Duplex', 'TipoGral' => 'Casas', 'OrdTipoGral' => 10, 'Hab' => true],
            ['IdTipologia' => 'FDOC', 'Descrip' => 'Fondo de Comercio', 'TipoGral' => 'Comerciales', 'OrdTipoGral' => 40, 'Hab' => true],
            ['IdTipologia' => 'FRAC', 'Descrip' => 'Fracción', 'TipoGral' => 'Lotes', 'OrdTipoGral' => 30, 'Hab' => true],
            ['IdTipologia' => 'GALP', 'Descrip' => 'Galpón', 'TipoGral' => 'Industriales', 'OrdTipoGral' => 70, 'Hab' => true],
            ['IdTipologia' => 'INDU', 'Descrip' => 'Industria', 'TipoGral' => 'Industriales', 'OrdTipoGral' => 70, 'Hab' => true],
            ['IdTipologia' => 'LOCA', 'Descrip' => 'Local', 'TipoGral' => 'Comerciales', 'OrdTipoGral' => 40, 'Hab' => true],
            ['IdTipologia' => 'LOFT', 'Descrip' => 'Loft', 'TipoGral' => 'Departamentos', 'OrdTipoGral' => 20, 'Hab' => true],
            ['IdTipologia' => 'LOSH', 'Descrip' => 'Local Shopping', 'TipoGral' => 'Comerciales', 'OrdTipoGral' => 40, 'Hab' => true],
            ['IdTipologia' => 'LOTE', 'Descrip' => 'Lote', 'TipoGral' => 'Lotes', 'OrdTipoGral' => 30, 'Hab' => true],
            ['IdTipologia' => 'NEGE', 'Descrip' => 'Negocios Especiales', 'TipoGral' => 'Negocios Especi', 'OrdTipoGral' => 90, 'Hab' => true],
            ['IdTipologia' => 'OFIC', 'Descrip' => 'Oficina', 'TipoGral' => 'Oficinas', 'OrdTipoGral' => 50, 'Hab' => true],
            ['IdTipologia' => 'PFAB', 'Descrip' => 'Planta Fabril', 'TipoGral' => 'Industriales', 'OrdTipoGral' => 70, 'Hab' => true],
            ['IdTipologia' => 'PISO', 'Descrip' => 'Piso', 'TipoGral' => 'Departamentos', 'OrdTipoGral' => 20, 'Hab' => true],
            ['IdTipologia' => 'SEMI', 'Descrip' => 'Semipiso', 'TipoGral' => 'Departamentos', 'OrdTipoGral' => 20, 'Hab' => true],
            ['IdTipologia' => 'TALL', 'Descrip' => 'Taller', 'TipoGral' => 'Industriales', 'OrdTipoGral' => 70, 'Hab' => true],
            ['IdTipologia' => 'TRIP', 'Descrip' => 'Triplex', 'TipoGral' => 'Casas', 'OrdTipoGral' => 10, 'Hab' => true],
        ]);
    }
}
