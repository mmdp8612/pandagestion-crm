<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\OrientationsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrientationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_historical_orientations_seeder_normalizes_status_and_preserves_changes(): void
    {
        $this->seed(OrientationsSeeder::class);

        $this->assertDatabaseCount('tip_orientacion', 9);
        $this->assertDatabaseHas('tip_orientacion', [
            'IdOrientacion' => 'SN',
            'Descrip' => 'Indefinido',
            'Hab' => true,
        ]);

        DB::table('tip_orientacion')
            ->where('IdOrientacion', 'SN')
            ->update(['Descrip' => 'Sin definir']);

        $this->seed(OrientationsSeeder::class);

        $this->assertDatabaseCount('tip_orientacion', 9);
        $this->assertDatabaseHas('tip_orientacion', [
            'IdOrientacion' => 'SN',
            'Descrip' => 'Sin definir',
        ]);
    }

    public function test_guests_are_redirected_from_orientation_management(): void
    {
        $this->get(route('admin.catalogs.orientations.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.catalogs.orientations.create'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.catalogs.orientations.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_users_without_catalog_permission_cannot_manage_orientations(): void
    {
        $this->seed(OrientationsSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.catalogs.orientations.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.catalogs.orientations.store'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.catalogs.orientations.status.update', 'N'), ['hab' => false])
            ->assertForbidden();

        $this->assertDatabaseHas('tip_orientacion', [
            'IdOrientacion' => 'N',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_view_orientations_and_the_catalog_submenu(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.orientations.index'))
            ->assertOk()
            ->assertSee('Orientaciones')
            ->assertSee('9 registros')
            ->assertSee('Indefinido')
            ->assertSee('data-catalog-menu', false)
            ->assertSee(route('admin.catalogs.orientations.index'), false)
            ->assertSee('title="Editar orientación"', false)
            ->assertSee('data-confirm', false);
    }

    public function test_orientations_are_paginated_by_fifteen_records(): void
    {
        $administrator = $this->createAdministrator();
        $records = [];

        for ($index = 1; $index <= 7; $index++) {
            $records[] = [
                'IdOrientacion' => "Z{$index}",
                'Descrip' => sprintf('Zeta %02d', $index),
                'Hab' => true,
            ];
        }

        DB::table('tip_orientacion')->insert($records);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.orientations.index'))
            ->assertOk()
            ->assertSee('16 registros')
            ->assertSee('page=2', false)
            ->assertDontSee('aria-label="Editar la orientación Zeta 07"', false);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.orientations.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('aria-label="Editar la orientación Zeta 07"', false);
    }

    public function test_administrators_can_create_an_orientation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.orientations.create'))
            ->assertOk()
            ->assertSee('Nueva orientación')
            ->assertSee('name="id"', false);

        $this->actingAs($administrator)
            ->post(route('admin.catalogs.orientations.store'), [
                'id' => ' nv ',
                'descripcion' => '  Nueva   vista ',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.orientations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_orientacion', [
            'IdOrientacion' => 'NV',
            'Descrip' => 'Nueva vista',
            'Hab' => true,
        ]);
    }

    public function test_orientation_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.orientations.create'))
            ->post(route('admin.catalogs.orientations.store'), [
                'id' => 'NE',
                'descripcion' => 'Descripción demasiado extensa',
                'hab' => 'inválido',
            ])
            ->assertRedirect(route('admin.catalogs.orientations.create'))
            ->assertSessionHasErrors(['id', 'descripcion', 'hab']);

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.orientations.create'))
            ->post(route('admin.catalogs.orientations.store'), [
                ...$this->validData(),
                'id' => 'A/',
            ])
            ->assertRedirect(route('admin.catalogs.orientations.create'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseCount('tip_orientacion', 9);
    }

    public function test_administrators_can_edit_an_orientation_without_changing_its_code(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.orientations.edit', 'SN'))
            ->assertOk()
            ->assertSee('Editar orientación')
            ->assertSee('value="SN"', false)
            ->assertDontSee('name="id"', false);

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.orientations.update', 'SN'), [
                'id' => 'XX',
                'descripcion' => '  Sin   definir ',
                'hab' => '0',
            ])
            ->assertRedirect(route('admin.catalogs.orientations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_orientacion', [
            'IdOrientacion' => 'SN',
            'Descrip' => 'Sin definir',
            'Hab' => false,
        ]);
        $this->assertDatabaseMissing('tip_orientacion', ['IdOrientacion' => 'XX']);
    }

    public function test_orientation_data_is_validated_before_update(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.orientations.edit', 'N'))
            ->put(route('admin.catalogs.orientations.update', 'N'), [
                'descripcion' => ' ',
            ])
            ->assertRedirect(route('admin.catalogs.orientations.edit', 'N'))
            ->assertSessionHasErrors(['descripcion', 'hab']);

        $this->assertDatabaseHas('tip_orientacion', [
            'IdOrientacion' => 'N',
            'Descrip' => 'Norte',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_enable_and_disable_orientations_with_confirmation_controls(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_orientacion')
            ->where('IdOrientacion', 'SN')
            ->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.orientations.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Habilitar Indefinido?"', false)
            ->assertSee('data-confirm-variant="success"', false)
            ->assertSee('data-confirm-title="¿Deshabilitar Norte?"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.orientations.status.update', 'SN'), ['hab' => '1'])
            ->assertRedirect(route('admin.catalogs.orientations.index'))
            ->assertSessionHas('success');

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.orientations.status.update', 'N'), ['hab' => '0'])
            ->assertRedirect(route('admin.catalogs.orientations.index'));

        $this->assertDatabaseHas('tip_orientacion', [
            'IdOrientacion' => 'SN',
            'Hab' => true,
        ]);
        $this->assertDatabaseHas('tip_orientacion', [
            'IdOrientacion' => 'N',
            'Hab' => false,
        ]);
    }

    public function test_unknown_orientations_return_not_found(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.orientations.edit', 'XX'))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.orientations.update', 'XX'), [
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
            'id' => 'NV',
            'descripcion' => 'Nueva',
            'hab' => '1',
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrientationsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
