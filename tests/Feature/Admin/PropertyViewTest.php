<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\PropertyViewsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PropertyViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_historical_property_views_seeder_normalizes_status_and_preserves_changes(): void
    {
        $this->seed(PropertyViewsSeeder::class);

        $this->assertDatabaseCount('tip_vista', 4);
        $this->assertDatabaseHas('tip_vista', [
            'IdVista' => 'C/FTE',
            'Descrip' => 'Contrafrente',
            'Hab' => true,
        ]);

        DB::table('tip_vista')
            ->where('IdVista', 'C/FTE')
            ->update(['Descrip' => 'Contra frente']);

        $this->seed(PropertyViewsSeeder::class);

        $this->assertDatabaseCount('tip_vista', 4);
        $this->assertDatabaseHas('tip_vista', [
            'IdVista' => 'C/FTE',
            'Descrip' => 'Contra frente',
        ]);
    }

    public function test_guests_are_redirected_from_property_view_management(): void
    {
        $this->get(route('admin.catalogs.views.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.catalogs.views.create'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.catalogs.views.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_users_without_catalog_permission_cannot_manage_property_views(): void
    {
        $this->seed(PropertyViewsSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.catalogs.views.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.catalogs.views.store'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.catalogs.views.status.update', 'C/FTE'), ['hab' => false])
            ->assertForbidden();

        $this->assertDatabaseHas('tip_vista', [
            'IdVista' => 'C/FTE',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_view_property_views_and_the_catalog_submenu(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.views.index'))
            ->assertOk()
            ->assertSee('Vistas')
            ->assertSee('4 registros')
            ->assertSee('Contrafrente')
            ->assertSee('data-catalog-menu', false)
            ->assertSee(route('admin.catalogs.views.index'), false)
            ->assertSee('title="Editar vista"', false)
            ->assertSee('data-confirm', false);
    }

    public function test_property_views_are_paginated_by_fifteen_records(): void
    {
        $administrator = $this->createAdministrator();
        $records = [];

        for ($index = 1; $index <= 12; $index++) {
            $records[] = [
                'IdVista' => sprintf('Z%03d', $index),
                'Descrip' => sprintf('Zeta %02d', $index),
                'Hab' => true,
            ];
        }

        DB::table('tip_vista')->insert($records);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.views.index'))
            ->assertOk()
            ->assertSee('16 registros')
            ->assertSee('page=2', false)
            ->assertDontSee('aria-label="Editar la vista Zeta 12"', false);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.views.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('aria-label="Editar la vista Zeta 12"', false);
    }

    public function test_administrators_can_create_a_property_view_with_a_slash_in_its_code(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.views.create'))
            ->assertOk()
            ->assertSee('Nueva vista')
            ->assertSee('name="id"', false);

        $this->actingAs($administrator)
            ->post(route('admin.catalogs.views.store'), [
                'id' => ' p/fte ',
                'descripcion' => '  Patio   frente ',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.views.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_vista', [
            'IdVista' => 'P/FTE',
            'Descrip' => 'Patio frente',
            'Hab' => true,
        ]);
    }

    public function test_property_view_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.views.create'))
            ->post(route('admin.catalogs.views.store'), [
                'id' => 'C/FTE',
                'descripcion' => 'Descripción demasiado extensa',
                'hab' => 'inválido',
            ])
            ->assertRedirect(route('admin.catalogs.views.create'))
            ->assertSessionHasErrors(['id', 'descripcion', 'hab']);

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.views.create'))
            ->post(route('admin.catalogs.views.store'), [
                ...$this->validData(),
                'id' => 'A@B',
            ])
            ->assertRedirect(route('admin.catalogs.views.create'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseCount('tip_vista', 4);
    }

    public function test_administrators_can_edit_a_property_view_with_a_slash_without_changing_its_code(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.views.edit', 'C/FTE'))
            ->assertOk()
            ->assertSee('Editar vista')
            ->assertSee('value="C/FTE"', false)
            ->assertDontSee('name="id"', false);

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.views.update', 'C/FTE'), [
                'id' => 'OTRA',
                'descripcion' => '  Contra frente ',
                'hab' => '0',
            ])
            ->assertRedirect(route('admin.catalogs.views.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_vista', [
            'IdVista' => 'C/FTE',
            'Descrip' => 'Contra frente',
            'Hab' => false,
        ]);
        $this->assertDatabaseMissing('tip_vista', ['IdVista' => 'OTRA']);
    }

    public function test_property_view_data_is_validated_before_update(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.views.edit', 'FRENTE'))
            ->put(route('admin.catalogs.views.update', 'FRENTE'), [
                'descripcion' => ' ',
            ])
            ->assertRedirect(route('admin.catalogs.views.edit', 'FRENTE'))
            ->assertSessionHasErrors(['descripcion', 'hab']);

        $this->assertDatabaseHas('tip_vista', [
            'IdVista' => 'FRENTE',
            'Descrip' => 'Al Frente',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_enable_and_disable_property_views_with_confirmation_controls(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_vista')
            ->where('IdVista', 'C/FTE')
            ->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.views.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Habilitar Contrafrente?"', false)
            ->assertSee('data-confirm-variant="success"', false)
            ->assertSee('data-confirm-title="¿Deshabilitar Interno?"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.views.status.update', 'C/FTE'), ['hab' => '1'])
            ->assertRedirect(route('admin.catalogs.views.index'))
            ->assertSessionHas('success');

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.views.status.update', 'INTERNO'), ['hab' => '0'])
            ->assertRedirect(route('admin.catalogs.views.index'));

        $this->assertDatabaseHas('tip_vista', [
            'IdVista' => 'C/FTE',
            'Hab' => true,
        ]);
        $this->assertDatabaseHas('tip_vista', [
            'IdVista' => 'INTERNO',
            'Hab' => false,
        ]);
    }

    public function test_unknown_property_views_return_not_found(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.views.edit', 'XXXXXXX'))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.views.update', 'XXXXXXX'), [
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
            'id' => 'NUEVA',
            'descripcion' => 'Nueva',
            'hab' => '1',
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(PropertyViewsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
