<?php

namespace Tests\Feature\PublicPortal;

use Database\Seeders\AntiquitiesSeeder;
use Database\Seeders\CommercializationsSeeder;
use Database\Seeders\CurrencyTypesSeeder;
use Database\Seeders\GaragesSeeder;
use Database\Seeders\OrientationsSeeder;
use Database\Seeders\PropertyUsesSeeder;
use Database\Seeders\PropertyViewsSeeder;
use Database\Seeders\TypologiesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PropertyShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_property_detail_shows_complete_published_information_and_enabled_gallery(): void
    {
        $this->seedPropertyCatalogs();
        $this->insertAgency();
        $propertyId = $this->insertProperty();
        $now = now();

        DB::table('bienesraices_imagenes')->insert([
            [
                'idBienRaiz' => $propertyId,
                'Archivo' => "bienesraices/{$propertyId}/imagenes/interior.jpg",
                'Orden' => 2,
                'Portada' => false,
                'Hab' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'idBienRaiz' => $propertyId,
                'Archivo' => "bienesraices/{$propertyId}/imagenes/portada.jpg",
                'Orden' => 1,
                'Portada' => true,
                'Hab' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'idBienRaiz' => $propertyId,
                'Archivo' => "bienesraices/{$propertyId}/imagenes/privada.jpg",
                'Orden' => 3,
                'Portada' => false,
                'Hab' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->get(route('public.properties.show', 'departamento-4-ambientes-san-nicolas-prop001'))
            ->assertOk()
            ->assertSee('Departamento en San Nicolás')
            ->assertSee('Avenida Corrientes 1234')
            ->assertSee('Piso 5')
            ->assertSee('Torre A')
            ->assertSee('Ambos V/A')
            ->assertSee('A Estrenar')
            ->assertSee('Norte')
            ->assertSee('Cochera Cubierta')
            ->assertSee('Contrafrente')
            ->assertSee('u$s 150.000')
            ->assertSee('$ 800.000,50')
            ->assertSee('85,5 m²')
            ->assertSee('120,75 m²')
            ->assertSee('Departamento luminoso y con excelente ubicación.')
            ->assertSee('https://www.openstreetmap.org/export/embed.html?', false)
            ->assertSee('https://player.vimeo.com/video/76979871?h=8272103f6e', false)
            ->assertSee('https://vimeo.com/76979871/8272103f6e', false)
            ->assertSee('OpenStreetMap contributors')
            ->assertSee("bienesraices/{$propertyId}/imagenes/portada.jpg", false)
            ->assertSee("bienesraices/{$propertyId}/imagenes/interior.jpg", false)
            ->assertDontSee("bienesraices/{$propertyId}/imagenes/privada.jpg", false)
            ->assertSee('Marta González Propiedades')
            ->assertSee('https://wa.me/5491145678901', false)
            ->assertSee('Consultar por PROP001');
    }

    public function test_disabled_and_unknown_properties_are_not_publicly_accessible(): void
    {
        $this->insertProperty(enabled: false, slug: 'propiedad-deshabilitada');

        $this->get(route('public.properties.show', 'propiedad-deshabilitada'))
            ->assertNotFound();

        $this->get(route('public.properties.show', 'propiedad-inexistente'))
            ->assertNotFound();
    }

    public function test_public_property_without_images_or_coordinates_uses_safe_fallbacks(): void
    {
        $this->insertProperty(
            slug: 'propiedad-sin-imagenes',
            overrides: [
                'Latitud' => null,
                'Longitud' => null,
            ]
        );

        $this->get(route('public.properties.show', 'propiedad-sin-imagenes'))
            ->assertOk()
            ->assertSee('PandaGestion')
            ->assertSee('Sin imágenes publicadas')
            ->assertSee('Precio a consultar')
            ->assertDontSee('https://www.openstreetmap.org/export/embed.html?', false)
            ->assertDontSee('youtube-nocookie.com/embed', false)
            ->assertDontSee('player.vimeo.com/video', false)
            ->assertDontSee('OpenStreetMap contributors');
    }

    private function seedPropertyCatalogs(): void
    {
        $this->seed([
            AntiquitiesSeeder::class,
            CommercializationsSeeder::class,
            CurrencyTypesSeeder::class,
            GaragesSeeder::class,
            OrientationsSeeder::class,
            PropertyUsesSeeder::class,
            PropertyViewsSeeder::class,
            TypologiesSeeder::class,
        ]);
    }

    private function insertAgency(): void
    {
        DB::table('inmobiliaria')->insert([
            'id' => 1,
            'RazonSocial' => 'Marta González Propiedades',
            'Telefonos' => '011 4444-5555',
            'Whatsapp' => '+54 9 11 4567-8901',
            'Email' => 'contacto@marta.test',
            'Domicilio' => 'Avenida del Libertador 1500',
            'Provincia' => 'Ciudad Autónoma de Buenos Aires',
            'Localidad' => 'Buenos Aires',
            'Barrio' => 'Palermo',
            'Matricula' => 'CPI 9876',
            'Hab' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertProperty(
        bool $enabled = true,
        string $slug = 'departamento-4-ambientes-san-nicolas-prop001',
        array $overrides = []
    ): int {
        return DB::table('bienesraices')->insertGetId(array_merge([
            'Codigo' => 'PROP001',
            'Descrip' => 'Departamento luminoso y con excelente ubicación.',
            'IdTipologia' => null,
            'IdUso' => null,
            'Antiguedad' => null,
            'IdOrientacion' => null,
            'IdCochera' => null,
            'IdVista' => null,
            'IdComercializacion' => null,
            'ImporteVta' => null,
            'idTipoMonedaVta' => null,
            'ImporteAlq' => null,
            'idTipoMonedaAlq' => null,
            'SupCubiertaPropia' => null,
            'SupTerreno' => null,
            'Frente' => null,
            'Fondo' => null,
            'MtsFondo' => null,
            'Luminosidad' => null,
            'Plantas' => null,
            'Ambientes' => null,
            'Sanitarios' => null,
            'Suite' => null,
            'Dormitorios' => null,
            'LineasTel' => null,
            'Calle' => 'Avenida Corrientes',
            'Numero' => '1234',
            'Piso' => null,
            'Torre' => null,
            'Provincia' => 'Ciudad Autónoma de Buenos Aires',
            'Partido' => 'Comuna 3',
            'Localidad' => 'Buenos Aires',
            'Barrio' => 'San Nicolás',
            'CodigoPostal' => 'C1043',
            'Latitud' => '-34.6037000',
            'Longitud' => '-58.3816000',
            'Destacada' => false,
            'Slug' => $slug,
            'TieneFoto' => false,
            'TieneVideo' => false,
            'VideoUrl' => null,
            'Hab' => $enabled,
            'created_at' => now(),
            'updated_at' => now(),
        ], $slug === 'departamento-4-ambientes-san-nicolas-prop001' ? [
            'IdTipologia' => 'DPTO',
            'IdUso' => 'VIVI',
            'Antiguedad' => 'AES',
            'IdOrientacion' => 'N',
            'IdCochera' => 'CCU',
            'IdVista' => 'C/FTE',
            'IdComercializacion' => 'A-V',
            'ImporteVta' => 150000,
            'idTipoMonedaVta' => 0,
            'ImporteAlq' => 800000.50,
            'idTipoMonedaAlq' => 1,
            'SupCubiertaPropia' => 85.50,
            'SupTerreno' => 120.75,
            'Frente' => 12.50,
            'Fondo' => 24.75,
            'MtsFondo' => 30.25,
            'Luminosidad' => 'Excelente',
            'Plantas' => 1,
            'Ambientes' => 4,
            'Sanitarios' => 2,
            'Suite' => 1,
            'Dormitorios' => 3,
            'LineasTel' => 2,
            'Piso' => '5',
            'Torre' => 'A',
            'Destacada' => true,
            'TieneFoto' => true,
            'TieneVideo' => true,
            'VideoUrl' => 'https://vimeo.com/76979871/8272103f6e',
        ] : [], $overrides));
    }
}
