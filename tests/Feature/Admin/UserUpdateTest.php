<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_edit_form(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.users.edit', $user))
            ->assertRedirect(route('login'));
    }

    public function test_users_without_module_permission_cannot_update_users(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.users.update', $target), [
                'name' => 'Cambio no autorizado',
                'email' => 'cambio@example.com',
                'role_id' => 1,
            ])
            ->assertForbidden();

        $this->assertNotSame('Cambio no autorizado', $target->fresh()->name);
    }

    public function test_administrators_can_view_the_edit_form(): void
    {
        $administrator = $this->createAdministrator();
        $target = User::factory()->create([
            'name' => 'Usuario a editar',
            'email' => 'editar@example.com',
        ]);
        $target->assignRole('administrador');

        $this->actingAs($administrator)
            ->get(route('admin.users.edit', $target))
            ->assertOk()
            ->assertSee('Usuario a editar')
            ->assertSee('editar@example.com')
            ->assertSee('La contraseña no se modifica desde este formulario.');
    }

    public function test_administrators_can_update_user_data_and_role_without_changing_password(): void
    {
        $administrator = $this->createAdministrator();
        $operatorRole = Role::create([
            'name' => 'operador',
            'guard_name' => 'web',
        ]);

        $target = User::factory()->create();
        $target->assignRole('administrador');
        $originalPassword = $target->password;

        $response = $this->actingAs($administrator)
            ->put(route('admin.users.update', $target), [
                'name' => 'Usuario Actualizado',
                'email' => 'ACTUALIZADO@EXAMPLE.COM',
                'role_id' => $operatorRole->id,
            ]);

        $response
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $target->refresh();

        $this->assertSame('Usuario Actualizado', $target->name);
        $this->assertSame('actualizado@example.com', $target->email);
        $this->assertSame($originalPassword, $target->password);
        $this->assertTrue($target->hasRole('operador'));
        $this->assertFalse($target->hasRole('administrador'));
    }

    public function test_updated_user_data_is_validated(): void
    {
        $administrator = $this->createAdministrator();
        $existingUser = User::factory()->create(['email' => 'existente@example.com']);
        $target = User::factory()->create();

        $this->actingAs($administrator)
            ->from(route('admin.users.edit', $target))
            ->put(route('admin.users.update', $target), [
                'name' => '',
                'email' => strtoupper($existingUser->email),
                'role_id' => 999999,
            ])
            ->assertRedirect(route('admin.users.edit', $target))
            ->assertSessionHasErrors(['name', 'email', 'role_id']);

        $this->assertSame($target->email, $target->fresh()->email);
    }

    private function createAdministrator(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('administrador');

        return $administrator;
    }
}
