<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\CommercializationsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommercializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_historical_commercializations_seeder_normalizes_status_and_preserves_changes(): void
    {
        $this->seed(CommercializationsSeeder::class);

        $this->assertDatabaseCount('tip_comercializacion', 5);
        $this->assertDatabaseHas('tip_comercializacion', [
            'IdComercializacion' => 'ALQ',
            'Descrip' => 'Alquiler',
            'Hab' => true,
        ]);
        $this->assertDatabaseHas('tip_comercializacion', [
            'IdComercializacion' => 'FON',
            'Descrip' => 'Fondo de Comerc',
            'Hab' => false,
        ]);

        DB::table('tip_comercializacion')
            ->where('IdComercializacion', 'ALQ')
            ->update(['Descrip' => 'Renta']);

        $this->seed(CommercializationsSeeder::class);

        $this->assertDatabaseCount('tip_comercializacion', 5);
        $this->assertDatabaseHas('tip_comercializacion', [
            'IdComercializacion' => 'ALQ',
            'Descrip' => 'Renta',
        ]);
    }

    public function test_guests_are_redirected_from_commercialization_management(): void
    {
        $this->get(route('admin.catalogs.commercializations.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.catalogs.commercializations.create'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.catalogs.commercializations.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_users_without_catalog_permission_cannot_manage_commercializations(): void
    {
        $this->seed(CommercializationsSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.catalogs.commercializations.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.catalogs.commercializations.store'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.catalogs.commercializations.status.update', 'ALQ'), ['hab' => false])
            ->assertForbidden();

        $this->assertDatabaseHas('tip_comercializacion', [
            'IdComercializacion' => 'ALQ',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_view_commercializations_and_the_catalog_submenu(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.commercializations.index'))
            ->assertOk()
            ->assertSee('Comercialización')
            ->assertSee('Fondo de Comerc')
            ->assertSee('Deshabilitada')
            ->assertSee('data-catalog-menu', false)
            ->assertSee(route('admin.catalogs.antiquities.index'), false)
            ->assertSee(route('admin.catalogs.commercializations.index'), false)
            ->assertSee('title="Editar comercialización"', false)
            ->assertSee('data-confirm', false);
    }

    public function test_commercializations_are_paginated_by_fifteen_records(): void
    {
        $administrator = $this->createAdministrator();
        $records = [];

        for ($index = 1; $index <= 11; $index++) {
            $records[] = [
                'IdComercializacion' => 'C'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'Descrip' => 'Extra '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'Hab' => true,
            ];
        }

        DB::table('tip_comercializacion')->insert($records);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.commercializations.index'))
            ->assertOk()
            ->assertSee('16 registros')
            ->assertSee('page=2', false)
            ->assertDontSee('aria-label="Editar la comercialización Venta"', false);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.commercializations.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('aria-label="Editar la comercialización Venta"', false);
    }

    public function test_administrators_can_create_a_commercialization(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.commercializations.create'))
            ->assertOk()
            ->assertSee('Nueva comercialización')
            ->assertSee('name="id"', false);

        $this->actingAs($administrator)
            ->post(route('admin.catalogs.commercializations.store'), [
                'id' => ' n-v ',
                'descripcion' => '  Nueva   Venta ',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.commercializations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_comercializacion', [
            'IdComercializacion' => 'N-V',
            'Descrip' => 'Nueva Venta',
            'Hab' => true,
        ]);
    }

    public function test_commercialization_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.commercializations.create'))
            ->post(route('admin.catalogs.commercializations.store'), [
                'id' => 'A-V',
                'descripcion' => 'Descripción demasiado extensa',
                'hab' => 'invalido',
            ])
            ->assertRedirect(route('admin.catalogs.commercializations.create'))
            ->assertSessionHasErrors(['id', 'descripcion', 'hab']);

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.commercializations.create'))
            ->post(route('admin.catalogs.commercializations.store'), [
                'id' => 'A/V',
                'descripcion' => 'Nueva',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.commercializations.create'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseCount('tip_comercializacion', 5);
    }

    public function test_administrators_can_edit_a_commercialization_without_changing_its_code(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.commercializations.edit', 'ALQ'))
            ->assertOk()
            ->assertSee('Editar comercialización')
            ->assertSee('value="ALQ"', false)
            ->assertDontSee('name="id"', false);

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.commercializations.update', 'ALQ'), [
                'id' => 'OTR',
                'descripcion' => '  Renta   anual ',
                'hab' => '0',
            ])
            ->assertRedirect(route('admin.catalogs.commercializations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_comercializacion', [
            'IdComercializacion' => 'ALQ',
            'Descrip' => 'Renta anual',
            'Hab' => false,
        ]);
        $this->assertDatabaseMissing('tip_comercializacion', ['IdComercializacion' => 'OTR']);
    }

    public function test_commercialization_data_is_validated_before_update(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.commercializations.edit', 'ALQ'))
            ->put(route('admin.catalogs.commercializations.update', 'ALQ'), [
                'descripcion' => ' ',
            ])
            ->assertRedirect(route('admin.catalogs.commercializations.edit', 'ALQ'))
            ->assertSessionHasErrors(['descripcion', 'hab']);

        $this->assertDatabaseHas('tip_comercializacion', [
            'IdComercializacion' => 'ALQ',
            'Descrip' => 'Alquiler',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_enable_and_disable_commercializations_with_confirmation_controls(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.commercializations.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Habilitar Fondo de Comerc?"', false)
            ->assertSee('data-confirm-variant="success"', false)
            ->assertSee('data-confirm-title="¿Deshabilitar Alquiler?"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.commercializations.status.update', 'FON'), ['hab' => '1'])
            ->assertRedirect(route('admin.catalogs.commercializations.index'))
            ->assertSessionHas('success');

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.commercializations.status.update', 'ALQ'), ['hab' => '0'])
            ->assertRedirect(route('admin.catalogs.commercializations.index'));

        $this->assertDatabaseHas('tip_comercializacion', [
            'IdComercializacion' => 'FON',
            'Hab' => true,
        ]);
        $this->assertDatabaseHas('tip_comercializacion', [
            'IdComercializacion' => 'ALQ',
            'Hab' => false,
        ]);
    }

    public function test_unknown_commercializations_return_not_found(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.commercializations.edit', 'XXX'))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.commercializations.update', 'XXX'), [
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
            'id' => 'NVO',
            'descripcion' => 'Nueva',
            'hab' => '1',
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CommercializationsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
