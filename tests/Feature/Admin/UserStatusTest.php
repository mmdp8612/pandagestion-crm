<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_when_updating_user_status(): void
    {
        $user = User::factory()->create();

        $this->patch(route('admin.users.status.update', $user), ['is_active' => false])
            ->assertRedirect(route('login'));

        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_users_without_module_permission_cannot_update_user_status(): void
    {
        $authenticatedUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $this->actingAs($authenticatedUser)
            ->patch(route('admin.users.status.update', $targetUser), ['is_active' => false])
            ->assertForbidden();

        $this->assertTrue($targetUser->fresh()->is_active);
    }

    public function test_administrators_can_deactivate_users(): void
    {
        $administrator = $this->createAdministrator();
        $targetUser = User::factory()->create(['remember_token' => 'token-anterior']);

        $this->actingAs($administrator)
            ->patch(route('admin.users.status.update', $targetUser), ['is_active' => false])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $targetUser->refresh();

        $this->assertFalse($targetUser->is_active);
        $this->assertNotSame('token-anterior', $targetUser->remember_token);

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Inactiva')
            ->assertSee('data-confirm-variant="success"', false)
            ->assertSee('Sí, activar');
    }

    public function test_administrators_can_reactivate_users(): void
    {
        $administrator = $this->createAdministrator();
        $targetUser = User::factory()->create(['is_active' => false]);

        $this->actingAs($administrator)
            ->patch(route('admin.users.status.update', $targetUser), ['is_active' => true])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertTrue($targetUser->fresh()->is_active);
    }

    public function test_administrators_cannot_deactivate_their_own_account(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Cuenta actual')
            ->assertDontSee('action="'.route('admin.users.status.update', $administrator).'"', false);

        $this->actingAs($administrator)
            ->patch(route('admin.users.status.update', $administrator), ['is_active' => false])
            ->assertForbidden();

        $this->assertTrue($administrator->fresh()->is_active);
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_authenticated_users_are_logged_out_on_their_next_request(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->get(route('admin.password.edit'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
