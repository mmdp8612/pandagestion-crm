<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_the_login_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('PandaGestion')
            ->assertSee('Iniciar sesión');
    }

    public function test_guests_are_redirected_from_the_admin_dashboard_to_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_users_cannot_authenticate_with_an_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'incorrecta',
        ]);

        $this->assertGuest();
        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_authenticated_users_can_view_the_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create([
            'name' => 'dashboard',
            'guard_name' => 'web',
        ]));

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($user->name);
    }

    public function test_authenticated_users_can_logout_with_a_post_request(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
    }

    public function test_logout_is_not_available_through_a_get_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/logout')
            ->assertMethodNotAllowed();

        $this->assertAuthenticatedAs($user);
    }
}
