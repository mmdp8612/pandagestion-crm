<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\AntiquitiesSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AntiquityTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_historical_antiquities_seeder_is_repeatable_without_overwriting_changes(): void
    {
        $this->seed(AntiquitiesSeeder::class);

        $this->assertDatabaseCount('tip_antiguedad', 14);
        $this->assertDatabaseHas('tip_antiguedad', [
            'IdAntiguedad' => 'ENC',
            'Descrip' => 'En Construcción',
            'Orden' => 5,
            'Hab' => true,
        ]);

        DB::table('tip_antiguedad')
            ->where('IdAntiguedad', 'A10')
            ->update(['Descrip' => 'Hasta diez']);

        $this->seed(AntiquitiesSeeder::class);

        $this->assertDatabaseCount('tip_antiguedad', 14);
        $this->assertDatabaseHas('tip_antiguedad', [
            'IdAntiguedad' => 'A10',
            'Descrip' => 'Hasta diez',
        ]);
    }

    public function test_guests_are_redirected_from_antiquity_management(): void
    {
        $this->get(route('admin.catalogs.antiquities.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.catalogs.antiquities.create'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.catalogs.antiquities.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_users_without_catalog_permission_cannot_manage_antiquities(): void
    {
        $this->seed(AntiquitiesSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.catalogs.antiquities.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.catalogs.antiquities.store'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.catalogs.antiquities.status.update', 'A10'), ['hab' => false])
            ->assertForbidden();

        $this->assertDatabaseHas('tip_antiguedad', [
            'IdAntiguedad' => 'A10',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_view_antiquities_in_configured_order(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.antiquities.index'))
            ->assertOk()
            ->assertSee('Antigüedades')
            ->assertSeeInOrder([
                'Sin Determinar',
                'En Construcción',
                'A Estrenar',
                'Menor a 5',
            ])
            ->assertSee(route('admin.catalogs.antiquities.index'), false)
            ->assertSee(route('admin.catalogs.antiquities.create'), false)
            ->assertSee('title="Editar antigüedad"', false)
            ->assertSee('data-confirm', false);
    }

    public function test_antiquities_are_paginated_by_fifteen_records(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_antiguedad')->insert([
            ['IdAntiguedad' => 'X01', 'Descrip' => 'Extra 1', 'Orden' => 101, 'Hab' => true],
            ['IdAntiguedad' => 'X02', 'Descrip' => 'Extra 2', 'Orden' => 102, 'Hab' => true],
            ['IdAntiguedad' => 'X03', 'Descrip' => 'Extra 3', 'Orden' => 103, 'Hab' => true],
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.antiquities.index'))
            ->assertOk()
            ->assertSee('17 registros')
            ->assertSee('page=2', false)
            ->assertDontSee('Extra 3');

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.antiquities.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Extra 3');
    }

    public function test_administrators_can_create_an_antiquity(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.antiquities.create'))
            ->assertOk()
            ->assertSee('Nueva antigüedad')
            ->assertSee('name="id"', false);

        $this->actingAs($administrator)
            ->post(route('admin.catalogs.antiquities.store'), [
                'id' => '  n10 ',
                'descripcion' => '  Hasta   10 años ',
                'orden' => '25',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.antiquities.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_antiguedad', [
            'IdAntiguedad' => 'N10',
            'Descrip' => 'Hasta 10 años',
            'Orden' => 25,
            'Hab' => true,
        ]);
    }

    public function test_antiquity_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.antiquities.create'))
            ->post(route('admin.catalogs.antiquities.store'), [
                'id' => 'A10',
                'descripcion' => 'Descripción demasiado extensa',
                'orden' => '-1',
                'hab' => 'invalido',
            ])
            ->assertRedirect(route('admin.catalogs.antiquities.create'))
            ->assertSessionHasErrors(['id', 'descripcion', 'orden', 'hab']);

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.antiquities.create'))
            ->post(route('admin.catalogs.antiquities.store'), [
                'id' => 'A-1',
                'descripcion' => 'Nueva',
                'orden' => '10',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.antiquities.create'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseCount('tip_antiguedad', 14);
    }

    public function test_administrators_can_edit_an_antiquity_without_changing_its_code(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.antiquities.edit', 'A10'))
            ->assertOk()
            ->assertSee('Editar antigüedad')
            ->assertSee('value="A10"', false)
            ->assertDontSee('name="id"', false);

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.antiquities.update', 'A10'), [
                'id' => 'OTR',
                'descripcion' => '  Hasta   10 años ',
                'orden' => '35',
                'hab' => '0',
            ])
            ->assertRedirect(route('admin.catalogs.antiquities.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_antiguedad', [
            'IdAntiguedad' => 'A10',
            'Descrip' => 'Hasta 10 años',
            'Orden' => 35,
            'Hab' => false,
        ]);
        $this->assertDatabaseMissing('tip_antiguedad', ['IdAntiguedad' => 'OTR']);
    }

    public function test_antiquity_data_is_validated_before_update(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.antiquities.edit', 'A10'))
            ->put(route('admin.catalogs.antiquities.update', 'A10'), [
                'descripcion' => ' ',
                'orden' => '1.5',
            ])
            ->assertRedirect(route('admin.catalogs.antiquities.edit', 'A10'))
            ->assertSessionHasErrors(['descripcion', 'orden', 'hab']);

        $this->assertDatabaseHas('tip_antiguedad', [
            'IdAntiguedad' => 'A10',
            'Descrip' => 'Menor a 10',
            'Orden' => 30,
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_disable_and_enable_antiquities_with_confirmation_controls(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.antiquities.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Deshabilitar Menor a 10?"', false)
            ->assertSee('Sí, deshabilitar');

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.antiquities.status.update', 'A10'), ['hab' => '0'])
            ->assertRedirect(route('admin.catalogs.antiquities.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_antiguedad', [
            'IdAntiguedad' => 'A10',
            'Hab' => false,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.antiquities.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Habilitar Menor a 10?"', false)
            ->assertSee('data-confirm-variant="success"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.antiquities.status.update', 'A10'), ['hab' => '1'])
            ->assertRedirect(route('admin.catalogs.antiquities.index'));

        $this->assertDatabaseHas('tip_antiguedad', [
            'IdAntiguedad' => 'A10',
            'Hab' => true,
        ]);
    }

    public function test_unknown_antiquities_return_not_found(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.antiquities.edit', 'XXX'))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.antiquities.update', 'XXX'), [
                'descripcion' => 'Nueva',
                'orden' => '10',
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
            'id' => 'NVO',
            'descripcion' => 'Nueva',
            'orden' => '90',
            'hab' => '1',
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AntiquitiesSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
