<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_property_summary_and_only_the_five_latest_properties(): void
    {
        $user = $this->createUserWithPermissions(['dashboard', 'bienesraices']);

        $properties = [
            ['code' => 'DASH001', 'enabled' => true, 'featured' => true, 'withPhoto' => true],
            ['code' => 'DASH002', 'enabled' => true, 'featured' => false, 'withPhoto' => false],
            ['code' => 'DASH003', 'enabled' => false, 'featured' => false, 'withPhoto' => true],
            ['code' => 'DASH004', 'enabled' => true, 'featured' => true, 'withPhoto' => false],
            ['code' => 'DASH005', 'enabled' => false, 'featured' => false, 'withPhoto' => true],
            ['code' => 'DASH006', 'enabled' => true, 'featured' => false, 'withPhoto' => false],
        ];

        foreach ($properties as $offset => $property) {
            $propertyId = $this->insertProperty(
                $property['code'],
                $property['enabled'],
                $property['featured'],
                $property['withPhoto'],
                $offset
            );

            if ($property['withPhoto']) {
                DB::table('bienesraices_imagenes')->insert([
                    'idBienRaiz' => $propertyId,
                    'Archivo' => "bienesraices/{$propertyId}/imagenes/{$property['code']}.jpg",
                    'Orden' => 1,
                    'Portada' => true,
                    'Hab' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $response = $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $response
            ->assertSee('aria-label="Total de propiedades: 6"', false)
            ->assertSee('aria-label="Propiedades habilitadas: 4"', false)
            ->assertSee('aria-label="Propiedades deshabilitadas: 2"', false)
            ->assertSee('aria-label="Propiedades destacadas: 2"', false)
            ->assertSee('aria-label="Propiedades con fotos: 3"', false)
            ->assertSee('aria-label="Propiedades sin fotos: 3"', false)
            ->assertSeeTextInOrder(['DASH006', 'DASH005', 'DASH004', 'DASH003', 'DASH002'])
            ->assertDontSee('DASH001')
            ->assertSee('bienesraices/5/imagenes/DASH005.jpg', false);
    }

    public function test_dashboard_does_not_expose_property_data_without_real_estate_permission(): void
    {
        $user = $this->createUserWithPermissions(['dashboard']);
        $this->insertProperty('PRIVATE001', true, true, false, 0);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Resumen de propiedades')
            ->assertDontSee('Últimas propiedades')
            ->assertDontSee('PRIVATE001')
            ->assertDontSee('data-dashboard-shortcut="properties"', false)
            ->assertSee('data-dashboard-shortcut="password"', false);
    }

    public function test_dashboard_shows_inquiry_summary_latest_contacts_and_menu_count(): void
    {
        $user = $this->createUserWithPermissions(['dashboard', 'consultas']);
        $propertyId = $this->insertProperty('LEAD001', true, false, false, 0);
        $statuses = ['nueva', 'respondida', 'nueva', 'en_proceso', 'nueva', 'en_proceso'];

        foreach ($statuses as $offset => $status) {
            $this->insertInquiry(
                propertyId: $propertyId,
                name: 'Contacto '.($offset + 1),
                status: $status,
                minutes: $offset
            );
        }

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Consultas nuevas: 3"', false)
            ->assertSee('aria-label="Consultas en proceso: 2"', false)
            ->assertSeeTextInOrder(['Contacto 6', 'Contacto 5', 'Contacto 4', 'Contacto 3', 'Contacto 2'])
            ->assertDontSee('Contacto 1')
            ->assertSee('title="3 consultas nuevas"', false)
            ->assertSee('data-inquiry-menu-count', false)
            ->assertSee('data-dashboard-shortcut="inquiries"', false)
            ->assertDontSee('Resumen de propiedades')
            ->assertDontSee('data-dashboard-shortcut="properties"', false);
    }

    public function test_dashboard_does_not_query_or_expose_inquiries_without_their_permission(): void
    {
        $user = $this->createUserWithPermissions(['dashboard']);
        $propertyId = $this->insertProperty('LEAD002', true, false, false, 0);
        $this->insertInquiry($propertyId, 'Contacto privado', 'nueva', 0);
        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Seguimiento de consultas')
            ->assertDontSee('Contacto privado')
            ->assertDontSee('data-inquiry-menu-count', false)
            ->assertDontSee('data-dashboard-shortcut="inquiries"', false);

        $this->assertFalse(collect($queries)->contains(
            fn (string $query): bool => str_contains(strtolower($query), 'consultas')
        ));
    }

    public function test_dashboard_quick_access_respects_each_module_permission(): void
    {
        $user = $this->createUserWithPermissions(['dashboard', 'catalogo', 'usuarios']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-dashboard-shortcut="catalogs"', false)
            ->assertSee('data-dashboard-shortcut="users"', false)
            ->assertSee('data-dashboard-shortcut="password"', false)
            ->assertDontSee('data-dashboard-shortcut="properties"', false)
            ->assertDontSee('data-dashboard-shortcut="inquiries"', false)
            ->assertDontSee('data-dashboard-shortcut="agency"', false)
            ->assertDontSee('data-dashboard-shortcut="roles"', false);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function createUserWithPermissions(array $permissions): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function insertProperty(
        string $code,
        bool $enabled,
        bool $featured,
        bool $withPhoto,
        int $minutes
    ): int {
        $timestamp = now()->addMinutes($minutes);

        return DB::table('bienesraices')->insertGetId([
            'Codigo' => $code,
            'Descrip' => "Descripción de {$code}",
            'Calle' => 'Avenida Siempre Viva',
            'Numero' => (string) (100 + $minutes),
            'Localidad' => 'Buenos Aires',
            'Destacada' => $featured,
            'Slug' => strtolower($code),
            'TieneFoto' => $withPhoto,
            'TieneVideo' => false,
            'Hab' => $enabled,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    private function insertInquiry(int $propertyId, string $name, string $status, int $minutes): int
    {
        $timestamp = now()->addMinutes($minutes);

        return DB::table('consultas')->insertGetId([
            'idBienRaiz' => $propertyId,
            'CodigoPropiedad' => 'LEAD001',
            'Nombre' => $name,
            'Email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
            'Telefono' => null,
            'Mensaje' => "Consulta enviada por {$name}.",
            'Estado' => $status,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
