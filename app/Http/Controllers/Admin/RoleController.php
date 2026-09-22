<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const PROTECTED_ROLE = 'administrador';

    private const PERMISSION_LABELS = [
        'dashboard' => 'Dashboard',
        'bienesraices' => 'Bienes Raíces',
        'consultas' => 'Consultas',
        'catalogo' => 'Catálogo',
        'configuracion' => 'Inmobiliaria',
        'usuarios' => 'Usuarios',
        'roles' => 'Roles y permisos',
    ];

    public function index(): View
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount('users')
            ->with([
                'permissions' => fn ($query) => $query->orderBy('permissions.name'),
            ])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.roles.index', [
            'roles' => $roles,
            'permissionLabels' => self::PERMISSION_LABELS,
            'protectedRoleName' => self::PROTECTED_ROLE,
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'permissions' => $this->availablePermissions(),
            'permissionLabels' => self::PERMISSION_LABELS,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = DB::transaction(function () use ($data): Role {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($data['permissions']);

            return $role;
        });

        return to_route('admin.roles.index')
            ->with('success', "El rol {$role->name} se creó correctamente.");
    }

    public function edit(Role $role): View
    {
        $this->ensureRoleIsEditable($role);
        $role->load('permissions:id,name');

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $this->availablePermissions(),
            'permissionLabels' => self::PERMISSION_LABELS,
            'selectedPermissions' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->ensureRoleIsEditable($role);
        $data = $request->validated();

        DB::transaction(function () use ($data, $role): void {
            $role->update([
                'name' => $data['name'],
            ]);

            $role->syncPermissions($data['permissions']);
        });

        return to_route('admin.roles.index')
            ->with('success', "El rol {$role->name} se actualizó correctamente.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->ensureRoleIsEditable($role);
        $roleName = $role->name;

        $deleted = DB::transaction(function () use ($role): bool {
            $lockedRole = Role::query()
                ->whereKey($role->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureRoleIsEditable($lockedRole);

            if ($lockedRole->users()->exists()) {
                return false;
            }

            return (bool) $lockedRole->delete();
        });

        if (! $deleted) {
            return to_route('admin.roles.index')
                ->with('error', "No se puede eliminar el rol {$roleName} porque tiene usuarios asignados.");
        }

        return to_route('admin.roles.index')
            ->with('success', "El rol {$roleName} se eliminó correctamente.");
    }

    /**
     * @return Collection<int, Permission>
     */
    private function availablePermissions(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function ensureRoleIsEditable(Role $role): void
    {
        abort_unless($role->guard_name === 'web', 404);
        abort_if($role->name === self::PROTECTED_ROLE, 403);
    }
}
