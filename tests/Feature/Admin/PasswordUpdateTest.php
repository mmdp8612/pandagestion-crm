<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_password_form(): void
    {
        $this->get(route('admin.password.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_any_authenticated_user_can_view_the_password_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.password.edit'))
            ->assertOk()
            ->assertSee('Cambiar contraseña');
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $this->actingAs($user)
            ->from(route('admin.password.edit'))
            ->put(route('admin.password.update'), [
                'current_password' => 'incorrecta',
                'password' => 'NuevaClave123',
                'password_confirmation' => 'NuevaClave123',
            ])
            ->assertRedirect(route('admin.password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertSame($originalPassword, $user->fresh()->password);
    }

    public function test_new_password_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.password.edit'))
            ->put(route('admin.password.update'), [
                'current_password' => 'password',
                'password' => 'corta',
                'password_confirmation' => 'diferente',
            ])
            ->assertRedirect(route('admin.password.edit'))
            ->assertSessionHasErrors('password');
    }

    public function test_new_password_must_differ_from_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.password.edit'))
            ->put(route('admin.password.update'), [
                'current_password' => 'password',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('admin.password.edit'))
            ->assertSessionHasErrors('password');
    }

    public function test_authenticated_users_can_update_their_password(): void
    {
        $user = User::factory()->create();
        $originalRememberToken = $user->remember_token;

        $response = $this->actingAs($user)
            ->put(route('admin.password.update'), [
                'current_password' => 'password',
                'password' => 'NuevaClave123',
                'password_confirmation' => 'NuevaClave123',
            ]);

        $response
            ->assertRedirect(route('admin.password.edit'))
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('NuevaClave123', $user->password));
        $this->assertFalse(Hash::check('password', $user->password));
        $this->assertNotSame($originalRememberToken, $user->remember_token);
    }
}
