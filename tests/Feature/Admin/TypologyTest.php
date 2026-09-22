<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TypologiesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TypologyTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_historical_typologies_seeder_normalizes_status_and_preserves_changes(): void
    {
        $this->seed(TypologiesSeeder::class);

        $this->assertDatabaseCount('tip_tipologia', 27);
        $this->assertDatabaseHas('tip_tipologia', [
            'IdTipologia' => 'DEPO',
            'Descrip' => 'Depósito',
            'TipoGral' => 'Industriales',
            'OrdTipoGral' => 70,
            'Hab' => true,
        ]);

        DB::table('tip_tipologia')
            ->where('IdTipologia', 'CASA')
            ->update([
                'Descrip' => 'Casa familiar',
                'OrdTipoGral' => 12,
            ]);

        $this->seed(TypologiesSeeder::class);

        $this->assertDatabaseCount('tip_tipologia', 27);
        $this->assertDatabaseHas('tip_tipologia', [
            'IdTipologia' => 'CASA',
            'Descrip' => 'Casa familiar',
            'OrdTipoGral' => 12,
        ]);
    }

    public function test_guests_are_redirected_from_typology_management(): void
    {
        $this->get(route('admin.catalogs.typologies.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.catalogs.typologies.create'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.catalogs.typologies.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_users_without_catalog_permission_cannot_manage_typologies(): void
    {
        $this->seed(TypologiesSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.catalogs.typologies.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.catalogs.typologies.store'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.catalogs.typologies.status.update', 'CASA'), ['hab' => false])
            ->assertForbidden();

        $this->assertDatabaseHas('tip_tipologia', [
            'IdTipologia' => 'CASA',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_view_typologies_grouped_in_configured_order(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.typologies.index'))
            ->assertOk()
            ->assertSee('Tipologías')
            ->assertSee('27 registros')
            ->assertSeeInOrder(['Casas', 'Departamentos', 'Lotes', 'Comerciales'])
            ->assertSee('data-catalog-menu', false)
            ->assertSee(route('admin.catalogs.typologies.index'), false)
            ->assertSee('title="Editar tipología"', false)
            ->assertSee('data-confirm', false);
    }

    public function test_typologies_are_paginated_by_fifteen_records(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.typologies.index'))
            ->assertOk()
            ->assertSee('page=2', false)
            ->assertDontSee('aria-label="Editar la tipología Oficina"', false);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.typologies.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('aria-label="Editar la tipología Oficina"', false);
    }

    public function test_administrators_can_create_a_typology(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.typologies.create'))
            ->assertOk()
            ->assertSee('Nueva tipología')
            ->assertSee('name="tipo_general"', false)
            ->assertSee('name="orden_tipo_general"', false);

        $this->actingAs($administrator)
            ->post(route('admin.catalogs.typologies.store'), [
                'id' => ' cnva ',
                'descripcion' => '  Casa   nueva ',
                'tipo_general' => '  Casas ',
                'orden_tipo_general' => '10',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.typologies.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_tipologia', [
            'IdTipologia' => 'CNVA',
            'Descrip' => 'Casa nueva',
            'TipoGral' => 'Casas',
            'OrdTipoGral' => 10,
            'Hab' => true,
        ]);
    }

    public function test_typology_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.typologies.create'))
            ->post(route('admin.catalogs.typologies.store'), [
                'id' => 'CASA',
                'descripcion' => 'Descripción demasiado extensa',
                'tipo_general' => 'Grupo demasiado extenso',
                'orden_tipo_general' => '-1',
                'hab' => 'inválido',
            ])
            ->assertRedirect(route('admin.catalogs.typologies.create'))
            ->assertSessionHasErrors(['id', 'descripcion', 'tipo_general', 'orden_tipo_general', 'hab']);

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.typologies.create'))
            ->post(route('admin.catalogs.typologies.store'), [
                ...$this->validData(),
                'id' => 'A/V',
            ])
            ->assertRedirect(route('admin.catalogs.typologies.create'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseCount('tip_tipologia', 27);
    }

    public function test_administrators_can_edit_a_typology_without_changing_its_code(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.typologies.edit', 'CASA'))
            ->assertOk()
            ->assertSee('Editar tipología')
            ->assertSee('value="CASA"', false)
            ->assertDontSee('name="id"', false);

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.typologies.update', 'CASA'), [
                'id' => 'OTRA',
                'descripcion' => '  Casa   familiar ',
                'tipo_general' => ' Residenciales ',
                'orden_tipo_general' => '5',
                'hab' => '0',
            ])
            ->assertRedirect(route('admin.catalogs.typologies.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_tipologia', [
            'IdTipologia' => 'CASA',
            'Descrip' => 'Casa familiar',
            'TipoGral' => 'Residenciales',
            'OrdTipoGral' => 5,
            'Hab' => false,
        ]);
        $this->assertDatabaseMissing('tip_tipologia', ['IdTipologia' => 'OTRA']);
    }

    public function test_typology_data_is_validated_before_update(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.typologies.edit', 'CASA'))
            ->put(route('admin.catalogs.typologies.update', 'CASA'), [
                'descripcion' => ' ',
                'tipo_general' => str_repeat('A', 16),
                'orden_tipo_general' => '1.5',
            ])
            ->assertRedirect(route('admin.catalogs.typologies.edit', 'CASA'))
            ->assertSessionHasErrors(['descripcion', 'tipo_general', 'orden_tipo_general', 'hab']);

        $this->assertDatabaseHas('tip_tipologia', [
            'IdTipologia' => 'CASA',
            'Descrip' => 'Casa',
            'TipoGral' => 'Casas',
            'OrdTipoGral' => 10,
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_enable_and_disable_typologies_with_confirmation_controls(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_tipologia')
            ->where('IdTipologia', 'NEGE')
            ->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.typologies.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Habilitar Negocios Especiales?"', false)
            ->assertSee('data-confirm-variant="success"', false)
            ->assertSee('data-confirm-title="¿Deshabilitar Oficina?"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.typologies.status.update', 'NEGE'), ['hab' => '1'])
            ->assertRedirect(route('admin.catalogs.typologies.index'))
            ->assertSessionHas('success');

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.typologies.status.update', 'CASA'), ['hab' => '0'])
            ->assertRedirect(route('admin.catalogs.typologies.index'));

        $this->assertDatabaseHas('tip_tipologia', [
            'IdTipologia' => 'NEGE',
            'Hab' => true,
        ]);
        $this->assertDatabaseHas('tip_tipologia', [
            'IdTipologia' => 'CASA',
            'Hab' => false,
        ]);
    }

    public function test_unknown_typologies_return_not_found(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.typologies.edit', 'XXXX'))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.typologies.update', 'XXXX'), [
                'descripcion' => 'Nueva',
                'tipo_general' => null,
                'orden_tipo_general' => '0',
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
            'descripcion' => 'Nueva',
            'tipo_general' => 'Otras',
            'orden_tipo_general' => '100',
            'hab' => '1',
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(TypologiesSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
