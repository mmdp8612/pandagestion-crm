<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_when_deleting_roles(): void
    {
        $role = Role::create(['name' => 'agente', 'guard_name' => 'web']);

        $this->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_users_without_module_permission_cannot_delete_roles(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'agente', 'guard_name' => 'web']);

        $this->actingAs($user)
            ->delete(route('admin.roles.destroy', $role))
            ->assertForbidden();

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_administrators_can_delete_unused_regular_roles(): void
    {
        $administrator = $this->createAdministrator();
        $role = Role::create(['name' => 'agente', 'guard_name' => 'web']);
        $role->givePermissionTo('dashboard');

        $this->actingAs($administrator)
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertDatabaseHas('permissions', ['name' => 'dashboard']);
    }

    public function test_roles_with_assigned_users_cannot_be_deleted(): void
    {
        $administrator = $this->createAdministrator();
        $role = Role::create(['name' => 'agente', 'guard_name' => 'web']);
        $assignedUser = User::factory()->create();
        $assignedUser->assignRole($role);

        $this->actingAs($administrator)
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
        $this->assertTrue($assignedUser->fresh()->hasRole('agente'));
    }

    public function test_administrator_role_cannot_be_deleted(): void
    {
        $administrator = $this->createAdministrator();
        $protectedRole = Role::findByName('administrador', 'web');

        $this->actingAs($administrator)
            ->delete(route('admin.roles.destroy', $protectedRole))
            ->assertForbidden();

        $this->assertDatabaseHas('roles', [
            'id' => $protectedRole->id,
            'name' => 'administrador',
        ]);
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
