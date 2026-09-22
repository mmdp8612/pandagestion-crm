<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_user_password_form(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.users.password.edit', $user))
            ->assertRedirect(route('login'));
    }

    public function test_users_without_module_permission_cannot_reset_passwords(): void
    {
        $authenticatedUser = User::factory()->create();
        $targetUser = User::factory()->create();
        $originalPassword = $targetUser->password;

        $this->actingAs($authenticatedUser)
            ->put(route('admin.users.password.update', $targetUser), [
                'password' => 'NuevaClave123',
                'password_confirmation' => 'NuevaClave123',
            ])
            ->assertForbidden();

        $this->assertSame($originalPassword, $targetUser->fresh()->password);
    }

    public function test_administrators_can_view_the_user_password_form(): void
    {
        $administrator = $this->createAdministrator();
        $targetUser = User::factory()->create([
            'name' => 'Usuario Objetivo',
            'email' => 'objetivo@example.com',
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.users.password.edit', $targetUser))
            ->assertOk()
            ->assertSee('Restablecer contraseña')
            ->assertSee('Usuario Objetivo')
            ->assertSee('objetivo@example.com')
            ->assertSee('data-confirm-button="Sí, restablecer"', false);
    }

    public function test_administrators_can_reset_another_users_password_and_sessions(): void
    {
        $administrator = $this->createAdministrator();
        $targetUser = User::factory()->create(['remember_token' => 'token-anterior']);

        DB::table(config('session.table', 'sessions'))->insert([
            'id' => 'sesion-del-usuario',
            'user_id' => $targetUser->id,
            'ip_address' => null,
            'user_agent' => null,
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($administrator)
            ->put(route('admin.users.password.update', $targetUser), [
                'password' => 'NuevaClave123',
                'password_confirmation' => 'NuevaClave123',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $targetUser->refresh();

        $this->assertTrue(Hash::check('NuevaClave123', $targetUser->password));
        $this->assertFalse(Hash::check('password', $targetUser->password));
        $this->assertNotSame('token-anterior', $targetUser->remember_token);
        $this->assertDatabaseMissing(config('session.table', 'sessions'), ['id' => 'sesion-del-usuario']);
    }

    public function test_admin_password_reset_data_is_validated(): void
    {
        $administrator = $this->createAdministrator();
        $targetUser = User::factory()->create();
        $originalPassword = $targetUser->password;

        $this->actingAs($administrator)
            ->from(route('admin.users.password.edit', $targetUser))
            ->put(route('admin.users.password.update', $targetUser), [
                'password' => 'corta',
                'password_confirmation' => 'diferente',
            ])
            ->assertRedirect(route('admin.users.password.edit', $targetUser))
            ->assertSessionHasErrors('password');

        $this->assertSame($originalPassword, $targetUser->fresh()->password);
    }

    public function test_new_password_must_differ_from_the_target_users_current_password(): void
    {
        $administrator = $this->createAdministrator();
        $targetUser = User::factory()->create(['password' => 'ClaveActual123']);

        $this->actingAs($administrator)
            ->from(route('admin.users.password.edit', $targetUser))
            ->put(route('admin.users.password.update', $targetUser), [
                'password' => 'ClaveActual123',
                'password_confirmation' => 'ClaveActual123',
            ])
            ->assertRedirect(route('admin.users.password.edit', $targetUser))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('ClaveActual123', $targetUser->fresh()->password));
    }

    public function test_administrators_cannot_use_this_flow_for_their_own_password(): void
    {
        $administrator = $this->createAdministrator();
        $originalPassword = $administrator->password;

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee(route('admin.users.password.edit', $administrator), false);

        $this->actingAs($administrator)
            ->get(route('admin.users.password.edit', $administrator))
            ->assertForbidden();

        $this->actingAs($administrator)
            ->put(route('admin.users.password.update', $administrator), [
                'password' => 'NuevaClave123',
                'password_confirmation' => 'NuevaClave123',
            ])
            ->assertForbidden();

        $this->assertSame($originalPassword, $administrator->fresh()->password);
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
