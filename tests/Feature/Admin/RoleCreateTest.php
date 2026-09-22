<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_create_form(): void
    {
        $this->get(route('admin.roles.create'))
            ->assertRedirect(route('login'));
    }

    public function test_users_without_module_permission_cannot_create_roles(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.roles.store'), [
                'name' => 'agente',
                'permissions' => ['dashboard'],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('roles', ['name' => 'agente']);
    }

    public function test_administrators_can_view_the_create_form(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.roles.create'))
            ->assertOk()
            ->assertSee('Nuevo rol')
            ->assertSee('Dashboard')
            ->assertSee('Bienes Raíces')
            ->assertSee('Catálogo')
            ->assertSee('Inmobiliaria')
            ->assertSee('Usuarios')
            ->assertSee('Roles y permisos');
    }

    public function test_administrators_can_create_a_role_with_module_permissions(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->post(route('admin.roles.store'), [
                'name' => '  Agente   Comercial  ',
                'permissions' => ['dashboard', 'bienesraices'],
            ])
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        $role = Role::findByName('agente comercial', 'web');

        $this->assertTrue($role->hasPermissionTo('dashboard'));
        $this->assertTrue($role->hasPermissionTo('bienesraices'));
        $this->assertFalse($role->hasPermissionTo('usuarios'));
    }

    public function test_role_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->from(route('admin.roles.create'))
            ->post(route('admin.roles.store'), [
                'name' => ' ADMINISTRADOR ',
                'permissions' => ['permiso-inexistente'],
            ])
            ->assertRedirect(route('admin.roles.create'))
            ->assertSessionHasErrors(['name', 'permissions.0']);

        $this->actingAs($administrator)
            ->from(route('admin.roles.create'))
            ->post(route('admin.roles.store'), [
                'name' => 'sin permisos',
                'permissions' => [],
            ])
            ->assertRedirect(route('admin.roles.create'))
            ->assertSessionHasErrors(['permissions']);

        $this->assertDatabaseCount('roles', 1);
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
