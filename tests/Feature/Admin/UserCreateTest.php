<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_create_form(): void
    {
        $this->get(route('admin.users.create'))
            ->assertRedirect(route('login'));
    }

    public function test_users_without_module_permission_cannot_create_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.users.store'), [
                'name' => 'Usuario no autorizado',
                'email' => 'sin-permiso@example.com',
                'password' => 'ClaveSegura123',
                'password_confirmation' => 'ClaveSegura123',
                'role_id' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'sin-permiso@example.com']);
    }

    public function test_administrators_can_view_the_create_form(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Nuevo usuario')
            ->assertSee('administrador');
    }

    public function test_administrators_can_create_a_user_with_a_role(): void
    {
        $administrator = $this->createAdministrator();
        $role = Role::findByName('administrador', 'web');

        $response = $this->actingAs($administrator)
            ->post(route('admin.users.store'), [
                'name' => 'Nuevo Usuario',
                'email' => 'NUEVO@EXAMPLE.COM',
                'password' => 'ClaveSegura123',
                'password_confirmation' => 'ClaveSegura123',
                'role_id' => $role->id,
            ]);

        $response
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $user = User::query()->where('email', 'nuevo@example.com')->firstOrFail();

        $this->assertSame('Nuevo Usuario', $user->name);
        $this->assertTrue(Hash::check('ClaveSegura123', $user->password));
        $this->assertTrue($user->hasRole('administrador'));
    }

    public function test_user_data_is_validated_before_creation(): void
    {
        $administrator = $this->createAdministrator();
        $existingUser = User::factory()->create(['email' => 'existente@example.com']);

        $this->actingAs($administrator)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => '',
                'email' => strtoupper($existingUser->email),
                'password' => 'corta',
                'password_confirmation' => 'diferente',
                'role_id' => 999999,
            ])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors(['name', 'email', 'password', 'role_id']);

        $this->assertDatabaseCount('users', 2);
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
