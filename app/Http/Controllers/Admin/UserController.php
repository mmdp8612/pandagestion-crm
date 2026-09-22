<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Http\Requests\Admin\UserIndexRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const PROTECTED_ROLE = 'administrador';

    public function index(UserIndexRequest $request): View
    {
        $validated = $request->validated();
        $search = $validated['search'] ?? null;
        $roleId = isset($validated['role_id']) ? (int) $validated['role_id'] : null;

        $users = User::query()
            ->select(['id', 'name', 'email', 'is_active', 'created_at'])
            ->with('roles:id,name')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleId, function ($query, int $roleId): void {
                $query->whereHas('roles', function ($query) use ($roleId): void {
                    $query
                        ->whereKey($roleId)
                        ->where('guard_name', 'web');
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15);

        $filters = array_filter([
            'search' => $search,
            'role_id' => $roleId,
        ], fn ($value) => $value !== null && $value !== '');

        $users->appends($filters);

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $this->availableRoles(),
            'filters' => $filters,
            'hasFilters' => $filters !== [],
            'protectedAdministratorId' => $this->protectedAdministratorId(),
        ]);
    }

    public function create(): View
    {
        $roles = $this->availableRoles();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $role = Role::findById($data['role_id'], 'web');
            $user->assignRole($role);

            return $user;
        });

        return to_route('admin.users.index')
            ->with('success', "El usuario {$user->name} se creó correctamente.");
    }

    public function edit(User $user): View
    {
        $user->load('roles:id,name');
        $roles = $this->availableRoles();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $user): void {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            $role = Role::findById($data['role_id'], 'web');
            $user->syncRoles([$role]);
        });

        return to_route('admin.users.index')
            ->with('success', "El usuario {$user->name} se actualizó correctamente.");
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $isActive = $request->boolean('is_active');

        $attributes = [
            'is_active' => $isActive,
        ];

        if (! $isActive) {
            $attributes['remember_token'] = str()->random(60);
        }

        $user->forceFill($attributes)->save();

        $status = $isActive ? 'activó' : 'desactivó';

        return to_route('admin.users.index')
            ->with('success', "El usuario {$user->name} se {$status} correctamente.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(auth()->id() === $user->id, 403);

        $userName = $user->name;

        $deleted = DB::transaction(function () use ($user): bool {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isLastActiveAdministrator($lockedUser)) {
                return false;
            }

            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $lockedUser->getKey())
                ->delete();

            DB::table('password_reset_tokens')
                ->where('email', $lockedUser->email)
                ->delete();

            return (bool) $lockedUser->delete();
        });

        if (! $deleted) {
            return to_route('admin.users.index')
                ->with('error', "No se puede eliminar a {$userName} porque es el último administrador activo.");
        }

        return to_route('admin.users.index')
            ->with('success', "El usuario {$userName} se eliminó correctamente.");
    }

    /**
     * @return Collection<int, Role>
     */
    private function availableRoles(): Collection
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function protectedAdministratorId(): ?int
    {
        $administratorIds = $this->activeAdministratorsQuery()
            ->limit(2)
            ->pluck('users.id');

        return $administratorIds->count() === 1
            ? (int) $administratorIds->first()
            : null;
    }

    private function isLastActiveAdministrator(User $user): bool
    {
        if (! $user->is_active || ! $user->hasRole(self::PROTECTED_ROLE)) {
            return false;
        }

        return $this->activeAdministratorsQuery()
            ->lockForUpdate()
            ->limit(2)
            ->pluck('users.id')
            ->count() <= 1;
    }

    private function activeAdministratorsQuery(): Builder
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', function ($query): void {
                $query
                    ->where('name', self::PROTECTED_ROLE)
                    ->where('guard_name', 'web');
            })
            ->orderBy('users.id');
    }
}
