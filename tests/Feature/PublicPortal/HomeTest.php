<?php

namespace Tests\Feature\PublicPortal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_is_available_without_authentication_and_handles_empty_data(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Encontrá la propiedad que estás buscando.')
            ->assertSee('Últimas propiedades')
            ->assertSee('Todavía no hay propiedades publicadas')
            ->assertSee('name="ubicacion"', false)
            ->assertSee('name="operacion"', false)
            ->assertSee('name="tipologia"', false)
            ->assertSee('action="'.route('public.properties.index').'"', false)
            ->assertSee('Acceso administrativo');
    }

    public function test_public_home_uses_enabled_agency_identity_and_contact_information(): void
    {
        $this->insertAgency();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Marta González Propiedades')
            ->assertSee('contacto@marta.test')
            ->assertSee('+54 9 11 4567-8901')
            ->assertSee('CPI 9876')
            ->assertSee('inmobiliaria/logos/marta.png', false)
            ->assertSee('https://wa.me/5491145678901', false)
            ->assertSee('Avenida del Libertador 1500, Palermo, Buenos Aires, Ciudad Autónoma de Buenos Aires');
    }

    public function test_public_home_does_not_expose_disabled_agency_information(): void
    {
        $this->insertAgency(false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('PandaGestion')
            ->assertDontSee('Marta González Propiedades')
            ->assertDontSee('contacto@marta.test')
            ->assertDontSee('inmobiliaria/logos/marta.png', false);
    }

    public function test_public_home_only_shows_the_six_most_recent_enabled_properties(): void
    {
        $propertyIds = [];

        for ($index = 1; $index <= 10; $index++) {
            $propertyIds[$index] = $this->insertProperty(
                sprintf('PORTAL%03d', $index),
                enabled: true,
                featured: false,
                minutes: $index
            );
        }

        $this->insertProperty('DESTACADAANTIGUA', enabled: true, featured: true, minutes: 0);
        $this->insertProperty('OCULTAESTADO', enabled: false, featured: true, minutes: 30);

        DB::table('bienesraices')->where('id', $propertyIds[10])->update(['TieneFoto' => true]);

        DB::table('bienesraices_imagenes')->insert([
            [
                'idBienRaiz' => $propertyIds[10],
                'Archivo' => 'bienesraices/portal/imagen-deshabilitada.jpg',
                'Orden' => 1,
                'Portada' => true,
                'Hab' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'idBienRaiz' => $propertyIds[10],
                'Archivo' => 'bienesraices/portal/imagen-publica.jpg',
                'Orden' => 2,
                'Portada' => false,
                'Hab' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeTextInOrder([
                'PORTAL010',
                'PORTAL009',
                'PORTAL008',
                'PORTAL007',
                'PORTAL006',
                'PORTAL005',
            ])
            ->assertDontSee('PORTAL004')
            ->assertDontSee('PORTAL001')
            ->assertDontSee('DESTACADAANTIGUA')
            ->assertDontSee('OCULTAESTADO')
            ->assertSee('bienesraices/portal/imagen-publica.jpg', false)
            ->assertDontSee('bienesraices/portal/imagen-deshabilitada.jpg', false)
            ->assertSee(route('public.properties.show', 'portal010'), false)
            ->assertSee(route('public.properties.index'), false)
            ->assertSee('6 novedades')
            ->assertDontSee('page=2', false);
    }

    private function insertAgency(bool $enabled = true): void
    {
        DB::table('inmobiliaria')->insert([
            'id' => 1,
            'RazonSocial' => 'Marta González Propiedades',
            'Telefonos' => '011 4444-5555',
            'Whatsapp' => '+54 9 11 4567-8901',
            'Email' => 'contacto@marta.test',
            'Domicilio' => 'Avenida del Libertador 1500',
            'CodigoPostal' => 'C1425',
            'Provincia' => 'Ciudad Autónoma de Buenos Aires',
            'Partido' => 'Comuna 14',
            'Localidad' => 'Buenos Aires',
            'Barrio' => 'Palermo',
            'Logo' => 'inmobiliaria/logos/marta.png',
            'Web' => 'https://marta.test',
            'Matricula' => 'CPI 9876',
            'Hab' => $enabled,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertProperty(string $code, bool $enabled, bool $featured, int $minutes): int
    {
        $timestamp = now()->addMinutes($minutes);

        return DB::table('bienesraices')->insertGetId([
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
        ]);
    }
}
