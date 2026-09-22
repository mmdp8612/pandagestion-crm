<?php

namespace Tests\Feature\PublicPortal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://propiedades.example',
            'filesystems.disks.public.url' => 'https://propiedades.example/storage',
        ]);
    }

    public function test_public_pages_render_canonical_social_and_indexing_metadata(): void
    {
        $this->insertAgency();
        $this->insertProperty('SEO001', 'casa-en-palermo-seo001');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<title>Propiedades en venta y alquiler | Marta González Propiedades</title>', false)
            ->assertSee('<link rel="canonical" href="https://propiedades.example/">', false)
            ->assertSee('<meta name="robots" content="index,follow,max-image-preview:large">', false)
            ->assertSee('<meta property="og:url" content="https://propiedades.example/">', false)
            ->assertSee('<meta property="og:image" content="https://propiedades.example/storage/inmobiliaria/logos/marta.png">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"@type":"RealEstateAgent"', false);

        $this->get(route('public.properties.index'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://propiedades.example/propiedades">', false)
            ->assertSee('<meta name="robots" content="index,follow,max-image-preview:large">', false)
            ->assertSee('"@type":"CollectionPage"', false)
            ->assertSee('https://propiedades.example/propiedades/casa-en-palermo-seo001', false);

        $this->get(route('public.properties.index', ['ubicacion' => 'Palermo']))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://propiedades.example/propiedades">', false)
            ->assertSee('<meta name="robots" content="noindex,follow,max-image-preview:large">', false)
            ->assertDontSee('"@type":"CollectionPage"', false)
            ->assertDontSee('ubicacion=Palermo" rel="canonical', false);
    }

    public function test_unfiltered_catalog_pages_keep_their_own_canonical_url(): void
    {
        for ($index = 1; $index <= 10; $index++) {
            $this->insertProperty(
                sprintf('SEO%03d', $index),
                sprintf('propiedad-seo-%03d', $index),
                minutes: $index
            );
        }

        $this->get(route('public.properties.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('<title>Propiedades disponibles - Página 2 | PandaGestion</title>', false)
            ->assertSee('<link rel="canonical" href="https://propiedades.example/propiedades?page=2">', false)
            ->assertSee('<meta name="robots" content="index,follow,max-image-preview:large">', false);
    }

    public function test_property_metadata_and_structured_data_use_only_published_information(): void
    {
        $this->insertAgency();
        $this->insertCatalogs();
        $propertyId = $this->insertProperty('SEO100', 'casa-en-palermo-seo100', overrides: [
            'IdTipologia' => 'CASA',
            'IdComercializacion' => 'VTA',
            'ImporteVta' => 250000,
            'idTipoMonedaVta' => 0,
            'Ambientes' => 4,
            'Latitud' => '-34.5780000',
            'Longitud' => '-58.4230000',
        ]);
        $now = now();

        DB::table('bienesraices_imagenes')->insert([
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
                'Orden' => 2,
                'Portada' => false,
                'Hab' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $response = $this->get(route('public.properties.show', 'casa-en-palermo-seo100'));

        $response
            ->assertOk()
            ->assertSee('<title>Casa en Palermo | Marta González Propiedades</title>', false)
            ->assertSee('<link rel="canonical" href="https://propiedades.example/propiedades/casa-en-palermo-seo100">', false)
            ->assertSee('<meta property="og:image" content="https://propiedades.example/storage/bienesraices/'.$propertyId.'/imagenes/portada.jpg">', false)
            ->assertDontSee("bienesraices/{$propertyId}/imagenes/privada.jpg", false);

        preg_match(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            (string) $response->getContent(),
            $matches
        );

        $this->assertArrayHasKey(1, $matches);
        $structuredData = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $graph = collect($structuredData['@graph']);
        $listing = $graph->firstWhere('@type', 'RealEstateListing');
        $place = $graph->firstWhere('@type', 'Place');

        $this->assertSame('SEO100', $listing['identifier']);
        $this->assertSame('https://propiedades.example/propiedades/casa-en-palermo-seo100', $listing['url']);
        $this->assertSame('Casa en Palermo', $place['name']);
        $this->assertSame('Buenos Aires', $place['address']['addressLocality']);
        $this->assertSame(-34.578, $place['geo']['latitude']);
        $this->assertSame('250000', $listing['about'][1]['price']);
        $this->assertSame('USD', $listing['about'][1]['priceCurrency']);
        $this->assertStringNotContainsString('privada.jpg', $matches[1]);
    }

    public function test_sitemap_and_robots_expose_only_public_urls(): void
    {
        $this->insertProperty('PUBLICA', 'propiedad-publica');
        $this->insertProperty('OCULTA', 'propiedad-oculta', enabled: false);

        $this->get(route('public.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee('<loc>https://propiedades.example/</loc>', false)
            ->assertSee('<loc>https://propiedades.example/propiedades</loc>', false)
            ->assertSee('<loc>https://propiedades.example/propiedades/propiedad-publica</loc>', false)
            ->assertSee('<lastmod>', false)
            ->assertDontSee('propiedad-oculta', false)
            ->assertDontSee('/admin', false)
            ->assertDontSee('/propiedades?', false);

        $this->get(route('public.robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee("User-agent: *\n", false)
            ->assertSee("Disallow: /admin\n", false)
            ->assertSee("Disallow: /login\n", false)
            ->assertSee('Sitemap: https://propiedades.example/sitemap.xml', false)
            ->assertDontSee('http://localhost', false);
    }

    private function insertAgency(): void
    {
        DB::table('inmobiliaria')->insert([
            'id' => 1,
            'RazonSocial' => 'Marta González Propiedades',
            'Telefonos' => '011 4444-5555',
            'Email' => 'contacto@marta.test',
            'Domicilio' => 'Avenida del Libertador 1500',
            'CodigoPostal' => 'C1425',
            'Provincia' => 'Ciudad Autónoma de Buenos Aires',
            'Localidad' => 'Buenos Aires',
            'Barrio' => 'Palermo',
            'Logo' => 'inmobiliaria/logos/marta.png',
            'Web' => 'https://marta.test',
            'Hab' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertCatalogs(): void
    {
        DB::table('tip_tipologia')->insert([
            'IdTipologia' => 'CASA',
            'Descrip' => 'Casa',
            'TipoGral' => 'Residencial',
            'OrdTipoGral' => 1,
            'Hab' => true,
        ]);

        DB::table('tip_comercializacion')->insert([
            'IdComercializacion' => 'VTA',
            'Descrip' => 'Venta',
            'Hab' => true,
        ]);

        DB::table('tip_tipomoneda')->insert([
            'idTipoMoneda' => 0,
            'Descrip' => 'Dólares',
            'Simbolo' => 'u$s',
            'Hab' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertProperty(
        string $code,
        string $slug,
        bool $enabled = true,
        int $minutes = 0,
        array $overrides = []
    ): int {
        $timestamp = now()->addMinutes($minutes);

        return DB::table('bienesraices')->insertGetId(array_merge([
            'Codigo' => $code,
            'Descrip' => 'Propiedad luminosa y en excelente ubicación.',
            'Calle' => 'Avenida Santa Fe',
            'Numero' => '3200',
            'Provincia' => 'Buenos Aires',
            'Partido' => 'Ciudad Autónoma de Buenos Aires',
            'Localidad' => 'Buenos Aires',
            'Barrio' => 'Palermo',
            'CodigoPostal' => 'C1425',
            'Slug' => $slug,
            'TieneFoto' => false,
            'TieneVideo' => false,
            'Hab' => $enabled,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $overrides));
    }
}
