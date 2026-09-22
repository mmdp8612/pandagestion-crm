<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModulePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_module_permissions_and_administrator_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $role = Role::findByName('administrador', 'web');

        $this->assertDatabaseCount('permissions', count(RolesAndPermissionsSeeder::MODULE_PERMISSIONS));
        $this->assertDatabaseCount('roles', 1);

        foreach (RolesAndPermissionsSeeder::MODULE_PERMISSIONS as $permission) {
            $this->assertTrue($role->hasPermissionTo($permission));
        }
    }

    public function test_permissions_seeder_can_run_more_than_once(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertDatabaseCount('permissions', count(RolesAndPermissionsSeeder::MODULE_PERMISSIONS));
        $this->assertDatabaseCount('roles', 1);
    }

    public function test_authenticated_users_without_dashboard_permission_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_administrators_can_access_the_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('administrador');

        $response = $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $response
            ->assertSee('Bienes Raíces')
            ->assertSee('Consultas')
            ->assertSee('Catálogo')
            ->assertSee('data-catalog-menu', false)
            ->assertSee(route('admin.catalogs.antiquities.index'), false)
            ->assertSee(route('admin.catalogs.commercializations.index'), false)
            ->assertSee('Configuración')
            ->assertSee('Usuarios')
            ->assertSee('Roles y permisos');
    }

    public function test_menu_hides_modules_without_their_permissions(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create([
            'name' => 'dashboard',
            'guard_name' => 'web',
        ]));

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertDontSee('Bienes Raíces')
            ->assertDontSee('Consultas')
            ->assertDontSee('Catálogo')
            ->assertDontSee('Roles y permisos');
    }

    public function test_admin_layout_displays_flash_messages(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('administrador');

        $this->actingAs($user)
            ->withSession(['success' => 'Los cambios se guardaron correctamente.'])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Los cambios se guardaron correctamente.');
    }
}
