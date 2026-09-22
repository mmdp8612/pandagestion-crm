<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\GaragesSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GarageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_historical_garages_seeder_normalizes_status_and_preserves_changes(): void
    {
        $this->seed(GaragesSeeder::class);

        $this->assertDatabaseCount('tip_cochera', 13);
        $this->assertDatabaseHas('tip_cochera', [
            'IdCochera' => 'CCU',
            'Descrip' => 'Cochera Cubierta',
            'Hab' => true,
        ]);

        DB::table('tip_cochera')
            ->where('IdCochera', 'CCU')
            ->update(['Descrip' => 'Cubierta']);

        $this->seed(GaragesSeeder::class);

        $this->assertDatabaseCount('tip_cochera', 13);
        $this->assertDatabaseHas('tip_cochera', [
            'IdCochera' => 'CCU',
            'Descrip' => 'Cubierta',
        ]);
    }

    public function test_guests_are_redirected_from_garage_management(): void
    {
        $this->get(route('admin.catalogs.garages.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.catalogs.garages.create'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.catalogs.garages.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_users_without_catalog_permission_cannot_manage_garages(): void
    {
        $this->seed(GaragesSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.catalogs.garages.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.catalogs.garages.store'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.catalogs.garages.status.update', 'GAR'), ['hab' => false])
            ->assertForbidden();

        $this->assertDatabaseHas('tip_cochera', [
            'IdCochera' => 'GAR',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_view_garages_and_the_catalog_submenu(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.garages.index'))
            ->assertOk()
            ->assertSee('Cocheras')
            ->assertSee('13 registros')
            ->assertSee('Playa Estacionam')
            ->assertSee('data-catalog-menu', false)
            ->assertSee(route('admin.catalogs.garages.index'), false)
            ->assertSee('title="Editar cochera"', false)
            ->assertSee('data-confirm', false);
    }

    public function test_garages_are_paginated_by_fifteen_records(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_cochera')->insert([
            ['IdCochera' => 'Z01', 'Descrip' => 'Zeta 01', 'Hab' => true],
            ['IdCochera' => 'Z02', 'Descrip' => 'Zeta 02', 'Hab' => true],
            ['IdCochera' => 'Z03', 'Descrip' => 'Zeta 03', 'Hab' => true],
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.garages.index'))
            ->assertOk()
            ->assertSee('16 registros')
            ->assertSee('page=2', false)
            ->assertDontSee('aria-label="Editar la cochera Zeta 03"', false);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.garages.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('aria-label="Editar la cochera Zeta 03"', false);
    }

    public function test_administrators_can_create_a_garage(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.garages.create'))
            ->assertOk()
            ->assertSee('Nueva cochera')
            ->assertSee('name="id"', false);

        $this->actingAs($administrator)
            ->post(route('admin.catalogs.garages.store'), [
                'id' => ' nva ',
                'descripcion' => '  Cochera   móvil ',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.garages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_cochera', [
            'IdCochera' => 'NVA',
            'Descrip' => 'Cochera móvil',
            'Hab' => true,
        ]);
    }

    public function test_garage_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.garages.create'))
            ->post(route('admin.catalogs.garages.store'), [
                'id' => 'GAR',
                'descripcion' => 'Descripción demasiado extensa para cochera',
                'hab' => 'inválido',
            ])
            ->assertRedirect(route('admin.catalogs.garages.create'))
            ->assertSessionHasErrors(['id', 'descripcion', 'hab']);

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.garages.create'))
            ->post(route('admin.catalogs.garages.store'), [
                ...$this->validData(),
                'id' => 'A/V',
            ])
            ->assertRedirect(route('admin.catalogs.garages.create'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseCount('tip_cochera', 13);
    }

    public function test_administrators_can_edit_a_garage_without_changing_its_code(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.garages.edit', 'GAR'))
            ->assertOk()
            ->assertSee('Editar cochera')
            ->assertSee('value="GAR"', false)
            ->assertDontSee('name="id"', false);

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.garages.update', 'GAR'), [
                'id' => 'OTR',
                'descripcion' => '  Garage   doble ',
                'hab' => '0',
            ])
            ->assertRedirect(route('admin.catalogs.garages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_cochera', [
            'IdCochera' => 'GAR',
            'Descrip' => 'Garage doble',
            'Hab' => false,
        ]);
        $this->assertDatabaseMissing('tip_cochera', ['IdCochera' => 'OTR']);
    }

    public function test_garage_data_is_validated_before_update(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.garages.edit', 'GAR'))
            ->put(route('admin.catalogs.garages.update', 'GAR'), [
                'descripcion' => ' ',
            ])
            ->assertRedirect(route('admin.catalogs.garages.edit', 'GAR'))
            ->assertSessionHasErrors(['descripcion', 'hab']);

        $this->assertDatabaseHas('tip_cochera', [
            'IdCochera' => 'GAR',
            'Descrip' => 'Garage',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_enable_and_disable_garages_with_confirmation_controls(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_cochera')
            ->where('IdCochera', 'OPT')
            ->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.garages.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Habilitar Optativa?"', false)
            ->assertSee('data-confirm-variant="success"', false)
            ->assertSee('data-confirm-title="¿Deshabilitar Garage?"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.garages.status.update', 'OPT'), ['hab' => '1'])
            ->assertRedirect(route('admin.catalogs.garages.index'))
            ->assertSessionHas('success');

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.garages.status.update', 'GAR'), ['hab' => '0'])
            ->assertRedirect(route('admin.catalogs.garages.index'));

        $this->assertDatabaseHas('tip_cochera', [
            'IdCochera' => 'OPT',
            'Hab' => true,
        ]);
        $this->assertDatabaseHas('tip_cochera', [
            'IdCochera' => 'GAR',
            'Hab' => false,
        ]);
    }

    public function test_unknown_garages_return_not_found(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.garages.edit', 'XXX'))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.garages.update', 'XXX'), [
                'descripcion' => 'Nueva',
                'hab' => '1',
            ])
            ->assertNotFound();
    }

    /**
     * @return array<string, string>
     */
    private function validData(): array
    {
        return [
            'id' => 'NUE',
            'descripcion' => 'Nueva',
            'hab' => '1',
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GaragesSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
