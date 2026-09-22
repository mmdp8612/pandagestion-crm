<?php

namespace Tests\Feature\Database;

use App\Support\PropertyVideo;
use Database\Seeders\DemoPropertiesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoPropertiesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_one_hundred_consistent_properties_without_images(): void
    {
        $this->seed(DemoPropertiesSeeder::class);

        $properties = DB::table('bienesraices')
            ->where('Codigo', 'like', 'DEM%')
            ->orderBy('Codigo')
            ->get();

        $this->assertCount(100, $properties);
        $this->assertSame('DEM001', $properties->first()->Codigo);
        $this->assertSame('DEM100', $properties->last()->Codigo);
        $this->assertSame(100, $properties->pluck('Codigo')->unique()->count());
        $this->assertSame(100, $properties->pluck('Slug')->unique()->count());
        $this->assertTrue($properties->every(fn (object $property): bool => preg_match('/\ADEM\d{3}\z/D', $property->Codigo) === 1));
        $this->assertTrue($properties->every(fn (object $property): bool => strlen($property->Codigo) <= 6));
        $this->assertTrue($properties->every(fn (object $property): bool => (bool) $property->Hab));
        $this->assertTrue($properties->every(fn (object $property): bool => ! (bool) $property->TieneFoto));
        $this->assertSame(0, DB::table('bienesraices_imagenes')->count());

        $propertiesWithVideo = $properties->filter(fn (object $property): bool => (bool) $property->TieneVideo);

        $this->assertGreaterThan(0, $propertiesWithVideo->count());
        $this->assertTrue($propertiesWithVideo->every(
            fn (object $property): bool => PropertyVideo::parse($property->VideoUrl) !== null,
        ));
        $this->assertTrue($properties
            ->reject(fn (object $property): bool => (bool) $property->TieneVideo)
            ->every(fn (object $property): bool => $property->VideoUrl === null));

        $this->assertSame(0, $this->invalidCatalogReferenceCount('IdTipologia', 'tip_tipologia', 'IdTipologia'));
        $this->assertSame(0, $this->invalidCatalogReferenceCount('IdUso', 'tip_uso', 'IdUso'));
        $this->assertSame(0, $this->invalidCatalogReferenceCount('Antiguedad', 'tip_antiguedad', 'IdAntiguedad'));
        $this->assertSame(0, $this->invalidCatalogReferenceCount('IdOrientacion', 'tip_orientacion', 'IdOrientacion'));
        $this->assertSame(0, $this->invalidCatalogReferenceCount('IdCochera', 'tip_cochera', 'IdCochera'));
        $this->assertSame(0, $this->invalidCatalogReferenceCount('IdVista', 'tip_vista', 'IdVista'));
        $this->assertSame(0, $this->invalidCatalogReferenceCount('IdComercializacion', 'tip_comercializacion', 'IdComercializacion'));
        $this->assertSame(0, $this->invalidCatalogReferenceCount('idTipoMonedaVta', 'tip_tipomoneda', 'idTipoMoneda'));
        $this->assertSame(0, $this->invalidCatalogReferenceCount('idTipoMonedaAlq', 'tip_tipomoneda', 'idTipoMoneda'));
    }

    public function test_it_is_repeatable_and_does_not_modify_existing_properties(): void
    {
        $now = now();

        DB::table('bienesraices')->insert([
            'Codigo' => 'COD001',
            'Descrip' => 'Propiedad existente',
            'Calle' => 'Calle existente',
            'Destacada' => false,
            'Slug' => 'propiedad-existente-cod001',
            'TieneFoto' => false,
            'TieneVideo' => false,
            'Hab' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seed(DemoPropertiesSeeder::class);
        $this->seed(DemoPropertiesSeeder::class);

        $this->assertDatabaseCount('bienesraices', 101);
        $this->assertSame(100, DB::table('bienesraices')->where('Codigo', 'like', 'DEM%')->count());
        $this->assertDatabaseHas('bienesraices', [
            'Codigo' => 'COD001',
            'Descrip' => 'Propiedad existente',
        ]);
    }

    private function invalidCatalogReferenceCount(string $propertyColumn, string $catalogTable, string $catalogColumn): int
    {
        return DB::table('bienesraices')
            ->leftJoin($catalogTable, "bienesraices.{$propertyColumn}", '=', "{$catalogTable}.{$catalogColumn}")
            ->where('bienesraices.Codigo', 'like', 'DEM%')
            ->whereNotNull("bienesraices.{$propertyColumn}")
            ->whereNull("{$catalogTable}.{$catalogColumn}")
            ->count();
    }
}
