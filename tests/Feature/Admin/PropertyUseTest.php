<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\PropertyUsesSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PropertyUseTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_historical_property_uses_seeder_normalizes_status_and_preserves_changes(): void
    {
        $this->seed(PropertyUsesSeeder::class);

        $this->assertDatabaseCount('tip_uso', 5);
        $this->assertDatabaseHas('tip_uso', [
            'IdUso' => 'APRO',
            'Descrip' => 'Apta Profes',
            'Hab' => true,
        ]);

        DB::table('tip_uso')
            ->where('IdUso', 'APRO')
            ->update(['Descrip' => 'Profesional']);

        $this->seed(PropertyUsesSeeder::class);

        $this->assertDatabaseCount('tip_uso', 5);
        $this->assertDatabaseHas('tip_uso', [
            'IdUso' => 'APRO',
            'Descrip' => 'Profesional',
        ]);
    }

    public function test_guests_are_redirected_from_property_use_management(): void
    {
        $this->get(route('admin.catalogs.uses.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.catalogs.uses.create'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.catalogs.uses.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_users_without_catalog_permission_cannot_manage_property_uses(): void
    {
        $this->seed(PropertyUsesSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.catalogs.uses.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.catalogs.uses.store'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.catalogs.uses.status.update', 'VIVI'), ['hab' => false])
            ->assertForbidden();

        $this->assertDatabaseHas('tip_uso', [
            'IdUso' => 'VIVI',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_view_property_uses_and_the_catalog_submenu(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.uses.index'))
            ->assertOk()
            ->assertSee('Usos')
            ->assertSee('5 registros')
            ->assertSee('Todo Destino')
            ->assertSee('data-catalog-menu', false)
            ->assertSee(route('admin.catalogs.uses.index'), false)
            ->assertSee('title="Editar uso"', false)
            ->assertSee('data-confirm', false);
    }

    public function test_property_uses_are_paginated_by_fifteen_records(): void
    {
        $administrator = $this->createAdministrator();
        $records = [];

        for ($index = 1; $index <= 11; $index++) {
            $records[] = [
                'IdUso' => sprintf('Z%03d', $index),
                'Descrip' => sprintf('Zeta %02d', $index),
                'Hab' => true,
            ];
        }

        DB::table('tip_uso')->insert($records);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.uses.index'))
            ->assertOk()
            ->assertSee('16 registros')
            ->assertSee('page=2', false)
            ->assertDontSee('aria-label="Editar el uso Zeta 11"', false);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.uses.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('aria-label="Editar el uso Zeta 11"', false);
    }

    public function test_administrators_can_create_a_property_use(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.uses.create'))
            ->assertOk()
            ->assertSee('Nuevo uso')
            ->assertSee('name="id"', false);

        $this->actingAs($administrator)
            ->post(route('admin.catalogs.uses.store'), [
                'id' => ' nuev ',
                'descripcion' => '  Uso   nuevo ',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.uses.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_uso', [
            'IdUso' => 'NUEV',
            'Descrip' => 'Uso nuevo',
            'Hab' => true,
        ]);
    }

    public function test_property_use_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.uses.create'))
            ->post(route('admin.catalogs.uses.store'), [
                'id' => 'TODO',
                'descripcion' => 'Descripción demasiado extensa',
                'hab' => 'inválido',
            ])
            ->assertRedirect(route('admin.catalogs.uses.create'))
            ->assertSessionHasErrors(['id', 'descripcion', 'hab']);

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.uses.create'))
            ->post(route('admin.catalogs.uses.store'), [
                ...$this->validData(),
                'id' => 'A/V',
            ])
            ->assertRedirect(route('admin.catalogs.uses.create'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseCount('tip_uso', 5);
    }

    public function test_administrators_can_edit_a_property_use_without_changing_its_code(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.uses.edit', 'APRO'))
            ->assertOk()
            ->assertSee('Editar uso')
            ->assertSee('value="APRO"', false)
            ->assertDontSee('name="id"', false);

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.uses.update', 'APRO'), [
                'id' => 'OTRO',
                'descripcion' => '  Profesional ',
                'hab' => '0',
            ])
            ->assertRedirect(route('admin.catalogs.uses.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_uso', [
            'IdUso' => 'APRO',
            'Descrip' => 'Profesional',
            'Hab' => false,
        ]);
        $this->assertDatabaseMissing('tip_uso', ['IdUso' => 'OTRO']);
    }

    public function test_property_use_data_is_validated_before_update(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.uses.edit', 'VIVI'))
            ->put(route('admin.catalogs.uses.update', 'VIVI'), [
                'descripcion' => ' ',
            ])
            ->assertRedirect(route('admin.catalogs.uses.edit', 'VIVI'))
            ->assertSessionHasErrors(['descripcion', 'hab']);

        $this->assertDatabaseHas('tip_uso', [
            'IdUso' => 'VIVI',
            'Descrip' => 'Vivienda',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_enable_and_disable_property_uses_with_confirmation_controls(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_uso')
            ->where('IdUso', 'APRO')
            ->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.uses.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Habilitar Apta Profes?"', false)
            ->assertSee('data-confirm-variant="success"', false)
            ->assertSee('data-confirm-title="¿Deshabilitar Vivienda?"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.uses.status.update', 'APRO'), ['hab' => '1'])
            ->assertRedirect(route('admin.catalogs.uses.index'))
            ->assertSessionHas('success');

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.uses.status.update', 'VIVI'), ['hab' => '0'])
            ->assertRedirect(route('admin.catalogs.uses.index'));

        $this->assertDatabaseHas('tip_uso', [
            'IdUso' => 'APRO',
            'Hab' => true,
        ]);
        $this->assertDatabaseHas('tip_uso', [
            'IdUso' => 'VIVI',
            'Hab' => false,
        ]);
    }

    public function test_unknown_property_uses_return_not_found(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.uses.edit', 'XXXX'))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.uses.update', 'XXXX'), [
                'descripcion' => 'Nuevo',
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
            'id' => 'NUEV',
            'descripcion' => 'Nuevo',
            'hab' => '1',
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(PropertyUsesSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
