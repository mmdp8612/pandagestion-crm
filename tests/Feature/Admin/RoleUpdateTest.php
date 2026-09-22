<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_edit_form(): void
    {
        $role = Role::create(['name' => 'agente', 'guard_name' => 'web']);

        $this->get(route('admin.roles.edit', $role))
            ->assertRedirect(route('login'));
    }

    public function test_users_without_module_permission_cannot_update_roles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $role = Role::create(['name' => 'agente', 'guard_name' => 'web']);
        $role->givePermissionTo('dashboard');

        $this->actingAs($user)
            ->put(route('admin.roles.update', $role), [
                'name' => 'agente modificado',
                'permissions' => ['catalogo'],
            ])
            ->assertForbidden();

        $this->assertSame('agente', $role->fresh()->name);
        $this->assertTrue($role->fresh()->hasPermissionTo('dashboard'));
    }

    public function test_administrators_can_view_the_edit_form_for_regular_roles(): void
    {
        $administrator = $this->createAdministrator();
        $role = Role::create(['name' => 'agente', 'guard_name' => 'web']);
        $role->givePermissionTo('dashboard');

        $this->actingAs($administrator)
            ->get(route('admin.roles.edit', $role))
            ->assertOk()
            ->assertSee('Editar rol')
            ->assertSee('agente')
            ->assertSee('Dashboard');
    }

    public function test_administrators_can_update_regular_roles_and_their_permissions(): void
    {
        $administrator = $this->createAdministrator();
        $role = Role::create(['name' => 'agente', 'guard_name' => 'web']);
        $role->givePermissionTo('dashboard');

        $this->actingAs($administrator)
            ->put(route('admin.roles.update', $role), [
                'name' => '  Coordinador   Comercial  ',
                'permissions' => ['catalogo', 'usuarios'],
            ])
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        $role->refresh();

        $this->assertSame('coordinador comercial', $role->name);
        $this->assertTrue($role->hasPermissionTo('catalogo'));
        $this->assertTrue($role->hasPermissionTo('usuarios'));
        $this->assertFalse($role->hasPermissionTo('dashboard'));
    }

    public function test_role_data_is_validated_before_update(): void
    {
        $administrator = $this->createAdministrator();
        Role::create(['name' => 'agente', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'supervisor', 'guard_name' => 'web']);
        $role->givePermissionTo('dashboard');

        $this->actingAs($administrator)
            ->from(route('admin.roles.edit', $role))
            ->put(route('admin.roles.update', $role), [
                'name' => ' AGENTE ',
                'permissions' => ['permiso-inexistente'],
            ])
            ->assertRedirect(route('admin.roles.edit', $role))
            ->assertSessionHasErrors(['name', 'permissions.0']);

        $this->actingAs($administrator)
            ->from(route('admin.roles.edit', $role))
            ->put(route('admin.roles.update', $role), [
                'name' => 'supervisor',
                'permissions' => [],
            ])
            ->assertRedirect(route('admin.roles.edit', $role))
            ->assertSessionHasErrors(['permissions']);

        $this->assertSame('supervisor', $role->fresh()->name);
        $this->assertTrue($role->fresh()->hasPermissionTo('dashboard'));
    }

    public function test_administrator_role_cannot_be_edited(): void
    {
        $administrator = $this->createAdministrator();
        $protectedRole = Role::findByName('administrador', 'web');

        $this->actingAs($administrator)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Protegido')
            ->assertDontSee(route('admin.roles.edit', $protectedRole), false);

        $this->actingAs($administrator)
            ->get(route('admin.roles.edit', $protectedRole))
            ->assertForbidden();

        $this->actingAs($administrator)
            ->put(route('admin.roles.update', $protectedRole), [
                'name' => 'administrador modificado',
                'permissions' => ['dashboard'],
            ])
            ->assertForbidden();

        $protectedRole->refresh();

        $this->assertSame('administrador', $protectedRole->name);
        $this->assertCount(count(RolesAndPermissionsSeeder::MODULE_PERMISSIONS), $protectedRole->permissions);
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
