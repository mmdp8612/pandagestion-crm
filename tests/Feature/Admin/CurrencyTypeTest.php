<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\CurrencyTypesSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CurrencyTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_historical_currency_types_seeder_preserves_zero_id_status_symbols_and_changes(): void
    {
        $this->seed(CurrencyTypesSeeder::class);

        $this->assertDatabaseCount('tip_tipomoneda', 2);
        $this->assertDatabaseHas('tip_tipomoneda', [
            'idTipoMoneda' => 0,
            'Descrip' => 'Dolares',
            'Simbolo' => 'u$s',
            'Hab' => true,
        ]);

        DB::table('tip_tipomoneda')
            ->where('idTipoMoneda', 0)
            ->update([
                'Descrip' => 'Dólares',
                'Simbolo' => 'USD',
            ]);

        $this->seed(CurrencyTypesSeeder::class);

        $this->assertDatabaseCount('tip_tipomoneda', 2);
        $this->assertDatabaseHas('tip_tipomoneda', [
            'idTipoMoneda' => 0,
            'Descrip' => 'Dólares',
            'Simbolo' => 'USD',
        ]);
    }

    public function test_guests_are_redirected_from_currency_type_management(): void
    {
        $this->get(route('admin.catalogs.currency-types.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.catalogs.currency-types.create'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.catalogs.currency-types.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_users_without_catalog_permission_cannot_manage_currency_types(): void
    {
        $this->seed(CurrencyTypesSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.catalogs.currency-types.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.catalogs.currency-types.store'), $this->validData())
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.catalogs.currency-types.status.update', 0), ['hab' => false])
            ->assertForbidden();

        $this->assertDatabaseHas('tip_tipomoneda', [
            'idTipoMoneda' => 0,
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_view_currency_types_and_the_catalog_submenu(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.currency-types.index'))
            ->assertOk()
            ->assertSee('Tipos de moneda')
            ->assertSee('2 registros')
            ->assertSee('Dolares')
            ->assertSee('u$s')
            ->assertSee('data-catalog-menu', false)
            ->assertSee(route('admin.catalogs.currency-types.index'), false)
            ->assertSee('title="Editar tipo de moneda"', false)
            ->assertSee('data-confirm', false);
    }

    public function test_currency_types_are_paginated_by_fifteen_records(): void
    {
        $administrator = $this->createAdministrator();
        $records = [];

        for ($index = 1; $index <= 14; $index++) {
            $records[] = [
                'idTipoMoneda' => 99 + $index,
                'Descrip' => sprintf('Moneda %02d', $index),
                'Simbolo' => null,
                'Hab' => true,
            ];
        }

        DB::table('tip_tipomoneda')->insert($records);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.currency-types.index'))
            ->assertOk()
            ->assertSee('16 registros')
            ->assertSee('page=2', false)
            ->assertDontSee('aria-label="Editar el tipo de moneda Pesos"', false);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.currency-types.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('aria-label="Editar el tipo de moneda Pesos"', false);
    }

    public function test_administrators_can_create_a_currency_type(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.currency-types.create'))
            ->assertOk()
            ->assertSee('Nuevo tipo de moneda')
            ->assertSee('name="id"', false);

        $this->actingAs($administrator)
            ->post(route('admin.catalogs.currency-types.store'), [
                'id' => ' 2 ',
                'descripcion' => '  Euro ',
                'simbolo' => ' EUR ',
                'hab' => '1',
            ])
            ->assertRedirect(route('admin.catalogs.currency-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_tipomoneda', [
            'idTipoMoneda' => 2,
            'Descrip' => 'Euro',
            'Simbolo' => 'EUR',
            'Hab' => true,
        ]);
    }

    public function test_currency_type_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.currency-types.create'))
            ->post(route('admin.catalogs.currency-types.store'), [
                'id' => 0,
                'descripcion' => 'Descripción demasiado extensa',
                'simbolo' => 'USDX',
                'hab' => 'inválido',
            ])
            ->assertRedirect(route('admin.catalogs.currency-types.create'))
            ->assertSessionHasErrors(['id', 'descripcion', 'simbolo', 'hab']);

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.currency-types.create'))
            ->post(route('admin.catalogs.currency-types.store'), [
                ...$this->validData(),
                'id' => -1,
            ])
            ->assertRedirect(route('admin.catalogs.currency-types.create'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseCount('tip_tipomoneda', 2);
    }

    public function test_administrators_can_edit_the_zero_id_currency_type_without_changing_its_id(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.currency-types.edit', 0))
            ->assertOk()
            ->assertSee('Editar tipo de moneda')
            ->assertSee('value="0"', false)
            ->assertDontSee('name="id"', false);

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.currency-types.update', 0), [
                'id' => 2,
                'descripcion' => '  Dólares ',
                'simbolo' => '   ',
                'hab' => '0',
            ])
            ->assertRedirect(route('admin.catalogs.currency-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tip_tipomoneda', [
            'idTipoMoneda' => 0,
            'Descrip' => 'Dólares',
            'Simbolo' => null,
            'Hab' => false,
        ]);
        $this->assertDatabaseMissing('tip_tipomoneda', ['idTipoMoneda' => 2]);
    }

    public function test_currency_type_data_is_validated_before_update(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.catalogs.currency-types.edit', 1))
            ->put(route('admin.catalogs.currency-types.update', 1), [
                'descripcion' => ' ',
                'simbolo' => 'PESO',
            ])
            ->assertRedirect(route('admin.catalogs.currency-types.edit', 1))
            ->assertSessionHasErrors(['descripcion', 'simbolo', 'hab']);

        $this->assertDatabaseHas('tip_tipomoneda', [
            'idTipoMoneda' => 1,
            'Descrip' => 'Pesos',
            'Simbolo' => '$',
            'Hab' => true,
        ]);
    }

    public function test_administrators_can_enable_and_disable_currency_types_with_confirmation_controls(): void
    {
        $administrator = $this->createAdministrator();

        DB::table('tip_tipomoneda')
            ->where('idTipoMoneda', 0)
            ->update(['Hab' => false]);

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.currency-types.index'))
            ->assertOk()
            ->assertSee('data-confirm-title="¿Habilitar Dolares?"', false)
            ->assertSee('data-confirm-variant="success"', false)
            ->assertSee('data-confirm-title="¿Deshabilitar Pesos?"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.currency-types.status.update', 0), ['hab' => '1'])
            ->assertRedirect(route('admin.catalogs.currency-types.index'))
            ->assertSessionHas('success');

        $this->actingAs($administrator)
            ->patch(route('admin.catalogs.currency-types.status.update', 1), ['hab' => '0'])
            ->assertRedirect(route('admin.catalogs.currency-types.index'));

        $this->assertDatabaseHas('tip_tipomoneda', [
            'idTipoMoneda' => 0,
            'Hab' => true,
        ]);
        $this->assertDatabaseHas('tip_tipomoneda', [
            'idTipoMoneda' => 1,
            'Hab' => false,
        ]);
    }

    public function test_unknown_currency_types_return_not_found(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.catalogs.currency-types.edit', 999))
            ->assertNotFound();

        $this->actingAs($administrator)
            ->put(route('admin.catalogs.currency-types.update', 999), [
                'descripcion' => 'Nueva',
                'simbolo' => null,
                'hab' => '1',
            ])
            ->assertNotFound();
    }

    /**
     * @return array<string, int|string>
     */
    private function validData(): array
    {
        return [
            'id' => 2,
            'descripcion' => 'Euro',
            'simbolo' => 'EUR',
            'hab' => '1',
        ];
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CurrencyTypesSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
