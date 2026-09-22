<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_when_deleting_users(): void
    {
        $user = User::factory()->create();

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_users_without_module_permission_cannot_delete_users(): void
    {
        $authenticatedUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $this->actingAs($authenticatedUser)
            ->delete(route('admin.users.destroy', $targetUser))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
    }

    public function test_administrators_can_delete_other_users_and_their_access_data(): void
    {
        $administrator = $this->createAdministrator();
        $role = Role::create(['name' => 'operador', 'guard_name' => 'web']);
        $targetUser = User::factory()->create([
            'name' => 'Usuario Eliminable',
            'email' => 'eliminable@example.com',
        ]);
        $targetUser->assignRole($role);
        $targetUser->givePermissionTo('dashboard');

        DB::table(config('session.table', 'sessions'))->insert([
            'id' => 'sesion-del-usuario-eliminable',
            'user_id' => $targetUser->id,
            'ip_address' => null,
            'user_agent' => null,
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $targetUser->email,
            'token' => 'token-de-restablecimiento',
            'created_at' => now(),
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('action="'.route('admin.users.destroy', $targetUser).'"', false)
            ->assertSee('title="Eliminar usuario"', false)
            ->assertSee('data-confirm-button="Sí, eliminar"', false);

        $this->actingAs($administrator)
            ->delete(route('admin.users.destroy', $targetUser))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
        $this->assertDatabaseMissing(config('session.table', 'sessions'), ['user_id' => $targetUser->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $targetUser->email]);
        $this->assertDatabaseMissing(config('permission.table_names.model_has_roles'), [
            'model_id' => $targetUser->id,
            'model_type' => $targetUser->getMorphClass(),
        ]);
        $this->assertDatabaseMissing(config('permission.table_names.model_has_permissions'), [
            'model_id' => $targetUser->id,
            'model_type' => $targetUser->getMorphClass(),
        ]);
    }

    public function test_users_cannot_delete_their_own_account(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee('action="'.route('admin.users.destroy', $administrator).'"', false);

        $this->actingAs($administrator)
            ->delete(route('admin.users.destroy', $administrator))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $administrator->id]);
    }

    public function test_last_active_administrator_cannot_be_deleted(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $managerRole = Role::create(['name' => 'gestor de usuarios', 'guard_name' => 'web']);
        $managerRole->givePermissionTo('usuarios');

        $manager = User::factory()->create();
        $manager->assignRole($managerRole);

        $lastAdministrator = User::factory()->create(['name' => 'Último Administrador']);
        $lastAdministrator->assignRole('administrador');

        $this->actingAs($manager)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('title="Último administrador activo"', false)
            ->assertDontSee('action="'.route('admin.users.destroy', $lastAdministrator).'"', false);

        $this->actingAs($manager)
            ->delete(route('admin.users.destroy', $lastAdministrator))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $lastAdministrator->id]);
        $this->assertTrue($lastAdministrator->fresh()->hasRole('administrador'));
    }

    public function test_one_administrator_can_be_deleted_when_another_active_administrator_remains(): void
    {
        $administrator = $this->createAdministrator();
        $targetAdministrator = User::factory()->create();
        $targetAdministrator->assignRole('administrador');

        $this->actingAs($administrator)
            ->delete(route('admin.users.destroy', $targetAdministrator))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $targetAdministrator->id]);
        $this->assertDatabaseHas('users', ['id' => $administrator->id]);
        $this->assertTrue($administrator->fresh()->hasRole('administrador'));
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
