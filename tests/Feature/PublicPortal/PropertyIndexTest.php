<?php

namespace Tests\Feature\PublicPortal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PropertyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_property_catalog_is_available_without_authentication(): void
    {
        $this->get(route('public.properties.index'))
            ->assertOk()
            ->assertSee('Encontrá tu próxima propiedad.')
            ->assertSee('0 propiedades encontradas')
            ->assertSee('No encontramos propiedades con esos criterios')
            ->assertSee('name="ubicacion"', false)
            ->assertSee('name="orden"', false)
            ->assertSee('Los cambios se aplican automáticamente.')
            ->assertSee('data-public-property-filters', false)
            ->assertDontSee('Aplicar filtros');
    }

    public function test_sidebar_renders_checkbox_groups_with_real_enabled_property_counts(): void
    {
        $this->insertCatalogs();

        $sharedFilters = [
            'IdComercializacion' => 'ALQ',
            'Provincia' => 'Buenos Aires',
            'IdCochera' => 'CUB',
            'Antiguedad' => 'EST',
            'IdOrientacion' => 'N',
            'IdVista' => 'FTE',
            'idTipoMonedaVta' => 0,
            'ImporteVta' => 100000,
        ];

        $this->insertProperty('FACETA1', minutes: 1, overrides: array_merge($sharedFilters, [
            'IdTipologia' => 'DEP',
            'Ambientes' => 3,
            'Partido' => 'La Matanza',
            'Localidad' => 'San Justo',
            'idTipoMonedaAlq' => 1,
            'ImporteAlq' => 200000,
        ]));

        $this->insertProperty('FACETA2', minutes: 2, overrides: array_merge($sharedFilters, [
            'IdTipologia' => 'CAS',
            'Ambientes' => 4,
            'Partido' => 'Morón',
            'Localidad' => 'Morón',
        ]));

        $this->insertProperty('FACETAOCULTA', minutes: 3, enabled: false, overrides: array_merge($sharedFilters, [
            'IdTipologia' => 'DEP',
            'Ambientes' => 3,
            'Partido' => 'La Matanza',
            'Localidad' => 'San Justo',
        ]));

        $this->get(route('public.properties.index'))
            ->assertOk()
            ->assertSee('name="operacion[]"', false)
            ->assertSee('name="tipologia[]"', false)
            ->assertSee('name="ambientes[]"', false)
            ->assertSee('name="provincia[]"', false)
            ->assertSee('name="partido[]"', false)
            ->assertSee('name="localidad[]"', false)
            ->assertSee('name="cochera[]"', false)
            ->assertSee('name="antiguedad[]"', false)
            ->assertSee('name="orientacion[]"', false)
            ->assertSee('name="vista[]"', false)
            ->assertSee('name="moneda_venta[]"', false)
            ->assertSee('name="moneda_alquiler[]"', false)
            ->assertSee('data-public-filter-toggle', false)
            ->assertSee('aria-controls="public-property-filters-panel"', false)
            ->assertSee('data-public-filter-panel', false)
            ->assertSee('class="hidden lg:block" data-public-filter-panel', false)
            ->assertSee('data-public-property-results', false)
            ->assertSee('data-filter-options-scroll', false)
            ->assertSee('max-h-72', false)
            ->assertSee('overflow-y-auto', false)
            ->assertSee('data-auto-submit-filter', false)
            ->assertSee('data-filter-option-count="operacion:ALQ">2', false)
            ->assertSee('data-filter-option-count="tipologia:DEP">1', false)
            ->assertSee('data-filter-option-count="tipologia:CAS">1', false)
            ->assertSee('data-filter-option-count="ambientes:3">1', false)
            ->assertSee('data-filter-option-count="provincia:Buenos Aires">2', false)
            ->assertSee('data-filter-option-count="cochera:CUB">2', false)
            ->assertSee('data-filter-option-count="moneda_venta:0">2', false)
            ->assertSee('data-filter-option-count="moneda_alquiler:1">1', false)
            ->assertDontSee('FACETAOCULTA');
    }

    public function test_catalog_lists_enabled_properties_by_recency_and_paginates_by_nine(): void
    {
        for ($index = 1; $index <= 10; $index++) {
            $this->insertProperty(
                sprintf('CATALOGO%02d', $index),
                minutes: $index,
                featured: false
            );
        }

        $this->insertProperty('DESTACADAANTIGUA', minutes: 0, featured: true);
        $this->insertProperty('OCULTA', minutes: 20, enabled: false);

        $this->get(route('public.properties.index'))
            ->assertOk()
            ->assertSeeTextInOrder([
                'CATALOGO10',
                'CATALOGO09',
                'CATALOGO08',
                'CATALOGO07',
                'CATALOGO06',
                'CATALOGO05',
                'CATALOGO04',
                'CATALOGO03',
                'CATALOGO02',
            ])
            ->assertDontSee('CATALOGO01')
            ->assertDontSee('DESTACADAANTIGUA')
            ->assertDontSee('OCULTA')
            ->assertSee('11 propiedades encontradas')
            ->assertSee('Página 1 de 2')
            ->assertSee('page=2', false);

        $this->get(route('public.properties.index', ['page' => 2]))
            ->assertOk()
            ->assertSeeTextInOrder(['CATALOGO01', 'DESTACADAANTIGUA'])
            ->assertDontSee('CATALOGO02');
    }

    public function test_catalog_combines_filters_preserves_them_and_allows_removing_each_one(): void
    {
        $this->insertCatalogs();

        for ($index = 1; $index <= 10; $index++) {
            $this->insertProperty(
                sprintf('MATCH%02d', $index),
                minutes: $index,
                overrides: [
                    'Calle' => 'Avenida Santa Fe',
                    'Numero' => (string) (1200 + $index),
                    'Barrio' => 'Palermo',
                    'Localidad' => 'Buenos Aires',
                    'IdComercializacion' => 'ALQ',
                    'IdTipologia' => 'DEP',
                    'Ambientes' => $index === 1 ? 6 : 5,
                ]
            );
        }

        $this->insertProperty('OTRAOPERACION', minutes: 20, overrides: [
            'Barrio' => 'Palermo',
            'IdComercializacion' => 'VTA',
            'IdTipologia' => 'DEP',
            'Ambientes' => 5,
        ]);

        $this->insertProperty('OTRAUBICACION', minutes: 21, overrides: [
            'Barrio' => 'Belgrano',
            'IdComercializacion' => 'ALQ',
            'IdTipologia' => 'DEP',
            'Ambientes' => 5,
        ]);

        $response = $this->get(route('public.properties.index', [
            'ubicacion' => '  Palermo  ',
            'operacion' => ['alq'],
            'tipologia' => ['dep'],
            'ambientes' => ['5', '6'],
            'orden' => 'antiguas',
        ]));

        $response
            ->assertOk()
            ->assertSee('10 propiedades encontradas')
            ->assertSeeTextInOrder(['MATCH01', 'MATCH02', 'MATCH03'])
            ->assertDontSee('OTRAOPERACION')
            ->assertDontSee('OTRAUBICACION')
            ->assertSee('value="Palermo"', false)
            ->assertSee('name="operacion[]"', false)
            ->assertSee('value="ALQ"', false)
            ->assertSee('name="tipologia[]"', false)
            ->assertSee('value="DEP"', false)
            ->assertSee('name="ambientes[]"', false)
            ->assertSee('value="5"', false)
            ->assertSee('value="6"', false)
            ->assertSee('value="antiguas" selected', false)
            ->assertSee('Ubicación: Palermo')
            ->assertSee('Operación: Alquiler')
            ->assertSee('Tipo de propiedad: Departamento')
            ->assertSee('Ambientes: 5')
            ->assertSee('Ambientes: 6')
            ->assertSee('Orden: Más antiguas')
            ->assertSee('aria-label="6 filtros activos"', false)
            ->assertSee('ubicacion=Palermo', false)
            ->assertSee('operacion%5B0%5D=ALQ', false)
            ->assertSee('tipologia%5B0%5D=DEP', false)
            ->assertSee('ambientes%5B0%5D=5', false)
            ->assertSee('ambientes%5B1%5D=6', false)
            ->assertSee('orden=antiguas', false)
            ->assertSee('data-filter-option-count="operacion:ALQ">11', false)
            ->assertSee('data-filter-option-count="tipologia:DEP">12', false)
            ->assertSee('data-filter-option-count="ambientes:5">11', false)
            ->assertSee('data-filter-option-count="ambientes:6">1', false)
            ->assertSee('page=2', false);

        $this->get(route('public.properties.index', [
            'ubicacion' => 'Palermo',
            'operacion' => ['ALQ'],
            'tipologia' => ['DEP'],
            'ambientes' => ['5', '6'],
            'orden' => 'antiguas',
            'page' => 2,
        ]))
            ->assertOk()
            ->assertSee('MATCH10')
            ->assertDontSee('MATCH01');
    }

    public function test_catalog_filters_by_location_complementary_catalogs_and_zero_currency_id(): void
    {
        $this->insertCatalogs();

        $this->insertProperty('OBJETIVO', minutes: 1, overrides: [
            'Provincia' => 'Buenos Aires',
            'Partido' => 'La Matanza',
            'Localidad' => 'San Justo',
            'IdCochera' => 'CUB',
            'Antiguedad' => 'EST',
            'IdOrientacion' => 'N',
            'IdVista' => 'FTE',
            'idTipoMonedaVta' => 0,
            'ImporteVta' => 100000,
            'idTipoMonedaAlq' => 1,
            'ImporteAlq' => 500000,
        ]);

        $this->insertProperty('OTROFILTRO', minutes: 2, overrides: [
            'Provincia' => 'Córdoba',
            'Partido' => 'Capital',
            'Localidad' => 'Córdoba',
        ]);

        $this->get(route('public.properties.index', [
            'provincia' => ['Buenos Aires'],
            'partido' => ['La Matanza'],
            'localidad' => ['San Justo'],
            'cochera' => ['cub'],
            'antiguedad' => ['est'],
            'orientacion' => ['n'],
            'vista' => ['fte'],
            'moneda_venta' => ['0'],
            'moneda_alquiler' => ['1'],
        ]))
            ->assertOk()
            ->assertSee('OBJETIVO')
            ->assertDontSee('OTROFILTRO')
            ->assertSee('Provincia: Buenos Aires')
            ->assertSee('Partido: La Matanza')
            ->assertSee('Localidad: San Justo')
            ->assertSee('Cochera: Cubierta')
            ->assertSee('Antigüedad: A estrenar')
            ->assertSee('Orientación: Norte')
            ->assertSee('Vista: Al frente')
            ->assertSee('Moneda de venta: Dólares')
            ->assertSee('Moneda de alquiler: Pesos');
    }

    public function test_catalog_can_order_by_covered_surface_with_missing_values_last(): void
    {
        $this->insertProperty('SUPERFICIE20', minutes: 1, overrides: ['SupCubiertaPropia' => 20]);
        $this->insertProperty('SUPERFICIE100', minutes: 2, overrides: ['SupCubiertaPropia' => 100]);
        $this->insertProperty('SINSUPERFICIE', minutes: 3, overrides: ['SupCubiertaPropia' => null]);

        $this->get(route('public.properties.index', ['orden' => 'superficie_desc']))
            ->assertOk()
            ->assertSeeTextInOrder(['SUPERFICIE100', 'SUPERFICIE20', 'SINSUPERFICIE']);

        $this->get(route('public.properties.index', ['orden' => 'superficie_asc']))
            ->assertOk()
            ->assertSeeTextInOrder(['SUPERFICIE20', 'SUPERFICIE100', 'SINSUPERFICIE']);
    }

    public function test_catalog_rejects_disabled_catalog_values_and_unknown_sorting(): void
    {
        $this->insertCatalogs();

        DB::table('tip_tipologia')->insert([
            'IdTipologia' => 'OFF',
            'Descrip' => 'Deshabilitada',
            'TipoGral' => 'Otros',
            'OrdTipoGral' => 99,
            'Hab' => false,
        ]);

        $this->from(route('public.properties.index'))
            ->get(route('public.properties.index', ['tipologia' => 'off']))
            ->assertRedirect(route('public.properties.index'))
            ->assertSessionHasErrors('tipologia.0');

        $this->from(route('public.properties.index'))
            ->get(route('public.properties.index', ['orden' => 'precio']))
            ->assertRedirect(route('public.properties.index'))
            ->assertSessionHasErrors('orden');
    }

    private function insertCatalogs(): void
    {
        DB::table('tip_comercializacion')->insert([
            ['IdComercializacion' => 'ALQ', 'Descrip' => 'Alquiler', 'Hab' => true],
            ['IdComercializacion' => 'VTA', 'Descrip' => 'Venta', 'Hab' => true],
        ]);

        DB::table('tip_tipologia')->insert([
            [
                'IdTipologia' => 'DEP',
                'Descrip' => 'Departamento',
                'TipoGral' => 'Residencial',
                'OrdTipoGral' => 1,
                'Hab' => true,
            ],
            [
                'IdTipologia' => 'CAS',
                'Descrip' => 'Casa',
                'TipoGral' => 'Residencial',
                'OrdTipoGral' => 1,
                'Hab' => true,
            ],
        ]);

        DB::table('tip_cochera')->insert([
            'IdCochera' => 'CUB',
            'Descrip' => 'Cubierta',
            'Hab' => true,
        ]);

        DB::table('tip_antiguedad')->insert([
            'IdAntiguedad' => 'EST',
            'Descrip' => 'A estrenar',
            'Orden' => 1,
            'Hab' => true,
        ]);

        DB::table('tip_orientacion')->insert([
            'IdOrientacion' => 'N',
            'Descrip' => 'Norte',
            'Hab' => true,
        ]);

        DB::table('tip_vista')->insert([
            'IdVista' => 'FTE',
            'Descrip' => 'Al frente',
            'Hab' => true,
        ]);

        DB::table('tip_tipomoneda')->insert([
            ['idTipoMoneda' => 0, 'Descrip' => 'Dólares', 'Simbolo' => 'u$s', 'Hab' => true],
            ['idTipoMoneda' => 1, 'Descrip' => 'Pesos', 'Simbolo' => '$', 'Hab' => true],
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertProperty(
        string $code,
        int $minutes,
        bool $enabled = true,
        bool $featured = false,
        array $overrides = []
    ): int {
        $timestamp = now()->addMinutes($minutes);

        return DB::table('bienesraices')->insertGetId(array_merge([
            'Codigo' => $code,
            'Descrip' => "Descripción pública de {$code}",
            'Calle' => 'Calle Pública',
            'Numero' => (string) (100 + $minutes),
            'Localidad' => 'Buenos Aires',
            'Ambientes' => 3,
            'Dormitorios' => 2,
            'Sanitarios' => 1,
            'SupCubiertaPropia' => 75.5,
            'Destacada' => $featured,
            'Slug' => strtolower($code),
            'TieneFoto' => false,
            'TieneVideo' => false,
            'Hab' => $enabled,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $overrides));
    }
}
