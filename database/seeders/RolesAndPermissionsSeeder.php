<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public const MODULE_PERMISSIONS = [
        'dashboard',
        'bienesraices',
        'consultas',
        'catalogo',
        'configuracion',
        'usuarios',
        'roles',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(self::MODULE_PERMISSIONS)
            ->map(fn (string $name) => Permission::findOrCreate($name, 'web'));

        Role::findOrCreate('administrador', 'web')
            ->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
