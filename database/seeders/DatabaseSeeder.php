<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AntiquitiesSeeder::class,
            CommercializationsSeeder::class,
            TypologiesSeeder::class,
            GaragesSeeder::class,
            OrientationsSeeder::class,
            PropertyUsesSeeder::class,
            PropertyViewsSeeder::class,
            CurrencyTypesSeeder::class,
        ]);
    }
}
