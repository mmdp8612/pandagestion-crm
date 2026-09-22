<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_users_without_module_permission_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_administrators_can_see_registered_users_and_their_roles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        $listedUser = User::factory()->create([
            'name' => 'Usuario Listado',
            'email' => 'listado@example.com',
        ]);
        $listedUser->assignRole('administrador');

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Usuario Listado')
            ->assertSee('listado@example.com')
            ->assertSee('administrador')
            ->assertSee('class="flex items-center justify-end gap-1.5"', false)
            ->assertSee('title="Editar usuario"', false)
            ->assertSee('title="Restablecer contraseña"', false)
            ->assertSee('title="Desactivar usuario"', false)
            ->assertSee('aria-label="Restablecer la contraseña de Usuario Listado"', false)
            ->assertSee(route('admin.users.index'), false);
    }

    public function test_users_are_paginated_by_fifteen_records(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');
        User::factory()->count(16)->create();

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->count() === 15
                && $users->total() === 17
                && $users->lastPage() === 2);
    }

    public function test_users_can_be_searched_by_name_or_email(): void
    {
        $administrator = $this->createAdministrator();

        User::factory()->create([
            'name' => 'Persona Objetivo',
            'email' => 'nombre@example.com',
        ]);
        User::factory()->create([
            'name' => 'Coincidencia por correo',
            'email' => 'objetivo@example.com',
        ]);
        User::factory()->create([
            'name' => 'Usuario Oculto',
            'email' => 'oculto@example.com',
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.users.index', ['search' => 'objetivo']))
            ->assertOk()
            ->assertSee('Persona Objetivo')
            ->assertSee('objetivo@example.com')
            ->assertDontSee('Usuario Oculto');
    }

    public function test_users_can_be_filtered_by_role(): void
    {
        $administrator = $this->createAdministrator();
        $agentRole = Role::create(['name' => 'agente', 'guard_name' => 'web']);

        $agent = User::factory()->create(['name' => 'Usuario Agente']);
        $agent->assignRole($agentRole);

        $otherUser = User::factory()->create(['name' => 'Usuario Otro Rol']);
        $otherUser->assignRole('administrador');

        $this->actingAs($administrator)
            ->get(route('admin.users.index', ['role_id' => $agentRole->id]))
            ->assertOk()
            ->assertSee('Usuario Agente')
            ->assertDontSee('Usuario Otro Rol');
    }

    public function test_combined_filters_are_preserved_during_pagination(): void
    {
        $administrator = $this->createAdministrator();
        $agentRole = Role::create(['name' => 'agente', 'guard_name' => 'web']);

        foreach (range(1, 16) as $number) {
            $user = User::factory()->create([
                'name' => sprintf('Coincidencia %02d', $number),
            ]);
            $user->assignRole($agentRole);
        }

        $filters = [
            'search' => 'Coincidencia',
            'role_id' => $agentRole->id,
        ];

        $this->actingAs($administrator)
            ->get(route('admin.users.index', $filters))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->count() === 15
                && $users->total() === 16
                && $users->lastPage() === 2)
            ->assertSee(route('admin.users.index', [...$filters, 'page' => 2]));
    }

    public function test_user_filters_are_validated(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.users.index'))
            ->get(route('admin.users.index', ['role_id' => 999999]))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors(['role_id']);
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
