<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.roles.index'))
            ->assertRedirect(route('login'));
    }

    public function test_users_without_module_permission_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_administrators_can_see_roles_and_their_module_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        $this->actingAs($administrator)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('administrador')
            ->assertSee('Dashboard')
            ->assertSee('Bienes Raíces')
            ->assertSee('Consultas')
            ->assertSee('Catálogo')
            ->assertSee('Inmobiliaria')
            ->assertSee('Usuarios')
            ->assertSee('Roles y permisos')
            ->assertSee(route('admin.roles.index'), false);
    }

    public function test_roles_are_paginated_by_fifteen_records(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        foreach (range(1, 15) as $number) {
            Role::create([
                'name' => "rol-{$number}",
                'guard_name' => 'web',
            ]);
        }

        $this->actingAs($administrator)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertViewHas('roles', fn ($roles) => $roles->count() === 15
                && $roles->total() === 16
                && $roles->lastPage() === 2);
    }

    public function test_list_shows_assigned_users_and_only_offers_deletion_for_unused_roles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        $unusedRole = Role::create(['name' => 'sin usuarios', 'guard_name' => 'web']);
        $assignedRole = Role::create(['name' => 'con usuarios', 'guard_name' => 'web']);

        foreach (User::factory()->count(2)->create() as $user) {
            $user->assignRole($assignedRole);
        }

        $this->actingAs($administrator)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('2 usuarios')
            ->assertSee('class="flex items-center justify-end gap-1.5"', false)
            ->assertSee('title="Editar rol"', false)
            ->assertSee('title="Eliminar rol"', false)
            ->assertSee('aria-label="Eliminar el rol sin usuarios"', false)
            ->assertSee('data-confirm', false)
            ->assertSee('data-confirm-button="Sí, eliminar"', false)
            ->assertSee('action="'.route('admin.roles.destroy', $unusedRole).'"', false)
            ->assertDontSee('action="'.route('admin.roles.destroy', $assignedRole).'"', false);
    }
}
