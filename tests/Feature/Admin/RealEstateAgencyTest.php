<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RealEstateAgencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_real_estate_agency_form(): void
    {
        $this->get(route('admin.real-estate-agency.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_users_without_configuration_permission_cannot_manage_the_agency(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.real-estate-agency.edit'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.real-estate-agency.update'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.real-estate-agency.logo.destroy'))
            ->assertForbidden();

        $this->assertDatabaseCount('inmobiliaria', 0);
    }

    public function test_administrators_can_view_the_real_estate_agency_form(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.real-estate-agency.edit'))
            ->assertOk()
            ->assertSee('Datos de la inmobiliaria')
            ->assertSee('name="razon_social"', false)
            ->assertSee('name="latitud"', false)
            ->assertSee('name="longitud"', false)
            ->assertSee('data-address-search', false)
            ->assertSee(route('admin.real-estate-agency.addresses.search'), false)
            ->assertSee('OpenStreetMap contributors')
            ->assertSee('data-address-map', false)
            ->assertSee('data-address-map-frame', false)
            ->assertSee('data-address-map-link', false)
            ->assertSee(route('admin.real-estate-agency.edit'), false)
            ->assertSee(route('admin.real-estate-agency.update'), false);
    }

    public function test_administrators_can_save_the_agency_general_data(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->put(route('admin.real-estate-agency.update'), $this->validData())
            ->assertRedirect(route('admin.real-estate-agency.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inmobiliaria', [
            'id' => 1,
            'RazonSocial' => 'Panda Propiedades SRL',
            'Telefonos' => '011 4444-5555, 011 5555-6666',
            'Whatsapp' => '+54 9 11 1234-5678',
            'Email' => 'contacto@panda.test',
            'Domicilio' => 'Avenida Siempre Viva 123',
            'CodigoPostal' => 'C1000AAA',
            'Provincia' => 'Buenos Aires',
            'Partido' => 'La Plata',
            'Localidad' => 'La Plata',
            'Barrio' => 'Centro',
            'Latitud' => -34.9214,
            'Longitud' => -57.9544,
            'Web' => 'https://panda.test',
            'Matricula' => 'CPI 1234',
            'Hab' => true,
            'Logo' => null,
        ]);
    }

    public function test_saving_again_updates_the_single_agency_record(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->put(route('admin.real-estate-agency.update'), $this->validData())
            ->assertRedirect(route('admin.real-estate-agency.edit'));

        $updatedData = $this->validData();
        $updatedData['razon_social'] = 'Nueva Razón Social SA';
        $updatedData['email'] = 'NUEVO@PANDA.TEST';
        $updatedData['hab'] = '0';

        $this->actingAs($administrator)
            ->put(route('admin.real-estate-agency.update'), $updatedData)
            ->assertRedirect(route('admin.real-estate-agency.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('inmobiliaria', 1);
        $this->assertDatabaseHas('inmobiliaria', [
            'id' => 1,
            'RazonSocial' => 'Nueva Razón Social SA',
            'Email' => 'nuevo@panda.test',
            'Hab' => false,
        ]);
    }

    public function test_agency_data_is_validated_before_saving(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.real-estate-agency.edit'))
            ->put(route('admin.real-estate-agency.update'), [
                'razon_social' => ' ',
                'email' => 'correo-invalido',
                'latitud' => '91',
                'longitud' => '-181',
                'web' => 'ftp://panda.test',
                'hab' => 'valor-invalido',
            ])
            ->assertRedirect(route('admin.real-estate-agency.edit'))
            ->assertSessionHasErrors([
                'razon_social',
                'email',
                'latitud',
                'longitud',
                'web',
                'hab',
            ]);

        $this->assertDatabaseCount('inmobiliaria', 0);
    }

    public function test_administrators_can_upload_and_view_a_safe_logo(): void
    {
        Storage::fake('public');
        $administrator = $this->createAdministrator();
        $data = $this->validData();
        $data['logo'] = UploadedFile::fake()->image('logo.png', 600, 300)->size(500);

        $this->actingAs($administrator)
            ->put(route('admin.real-estate-agency.update'), $data)
            ->assertRedirect(route('admin.real-estate-agency.edit'))
            ->assertSessionHas('success');

        $logoPath = DB::table('inmobiliaria')->where('id', 1)->value('Logo');

        $this->assertIsString($logoPath);
        $this->assertStringStartsWith('inmobiliaria/logos/', $logoPath);
        Storage::disk('public')->assertExists($logoPath);

        $this->actingAs($administrator)
            ->get(route('admin.real-estate-agency.edit'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee(Storage::disk('public')->url($logoPath), false)
            ->assertSee(route('admin.real-estate-agency.logo.destroy'), false)
            ->assertSee('data-confirm-title="¿Eliminar el logo?"', false);
    }

    public function test_replacing_the_logo_removes_the_previous_file_without_clearing_it_on_regular_updates(): void
    {
        Storage::fake('public');
        $administrator = $this->createAdministrator();
        $firstData = $this->validData();
        $firstData['logo'] = UploadedFile::fake()->image('logo-anterior.jpg')->size(400);

        $this->actingAs($administrator)
            ->put(route('admin.real-estate-agency.update'), $firstData)
            ->assertRedirect(route('admin.real-estate-agency.edit'));

        $firstLogoPath = DB::table('inmobiliaria')->where('id', 1)->value('Logo');

        $this->actingAs($administrator)
            ->put(route('admin.real-estate-agency.update'), $this->validData())
            ->assertRedirect(route('admin.real-estate-agency.edit'));

        $this->assertSame($firstLogoPath, DB::table('inmobiliaria')->where('id', 1)->value('Logo'));
        Storage::disk('public')->assertExists($firstLogoPath);

        $replacementData = $this->validData();
        $replacementData['logo'] = UploadedFile::fake()->image('logo-nuevo.webp')->size(450);

        $this->actingAs($administrator)
            ->put(route('admin.real-estate-agency.update'), $replacementData)
            ->assertRedirect(route('admin.real-estate-agency.edit'));

        $newLogoPath = DB::table('inmobiliaria')->where('id', 1)->value('Logo');

        $this->assertNotSame($firstLogoPath, $newLogoPath);
        Storage::disk('public')->assertMissing($firstLogoPath);
        Storage::disk('public')->assertExists($newLogoPath);
    }

    public function test_administrators_can_delete_the_current_logo(): void
    {
        Storage::fake('public');
        $administrator = $this->createAdministrator();
        $data = $this->validData();
        $data['logo'] = UploadedFile::fake()->image('logo.png')->size(500);

        $this->actingAs($administrator)
            ->put(route('admin.real-estate-agency.update'), $data)
            ->assertRedirect(route('admin.real-estate-agency.edit'));

        $logoPath = DB::table('inmobiliaria')->where('id', 1)->value('Logo');

        $this->actingAs($administrator)
            ->delete(route('admin.real-estate-agency.logo.destroy'))
            ->assertRedirect(route('admin.real-estate-agency.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inmobiliaria', ['id' => 1, 'Logo' => null]);
        Storage::disk('public')->assertMissing($logoPath);
    }

    public function test_logo_must_use_an_allowed_image_format_and_size(): void
    {
        Storage::fake('public');
        $administrator = $this->createAdministrator();
        $invalidFormat = $this->validData();
        $invalidFormat['logo'] = UploadedFile::fake()->create('logo.svg', 100, 'image/svg+xml');

        $this->actingAs($administrator)
            ->from(route('admin.real-estate-agency.edit'))
            ->put(route('admin.real-estate-agency.update'), $invalidFormat)
            ->assertRedirect(route('admin.real-estate-agency.edit'))
            ->assertSessionHasErrors('logo');

        $oversized = $this->validData();
        $oversized['logo'] = UploadedFile::fake()->image('logo.png')->size(2049);

        $this->actingAs($administrator)
            ->from(route('admin.real-estate-agency.edit'))
            ->put(route('admin.real-estate-agency.update'), $oversized)
            ->assertRedirect(route('admin.real-estate-agency.edit'))
            ->assertSessionHasErrors('logo');

        $this->assertDatabaseCount('inmobiliaria', 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'razon_social' => ' Panda Propiedades SRL ',
            'telefonos' => '011 4444-5555, 011 5555-6666',
            'whatsapp' => '+54 9 11 1234-5678',
            'email' => ' CONTACTO@PANDA.TEST ',
            'domicilio' => 'Avenida Siempre Viva 123',
            'codigo_postal' => 'C1000AAA',
            'provincia' => 'Buenos Aires',
            'partido' => 'La Plata',
            'localidad' => 'La Plata',
            'barrio' => 'Centro',
            'latitud' => '-34.9214000',
            'longitud' => '-57.9544000',
            'web' => 'https://panda.test',
            'matricula' => 'CPI 1234',
            'hab' => '1',
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
