@extends('layouts.admin')

@section('title', 'Usuarios')

@section('breadcrumb')
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li>Configuración</li>
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">Usuarios</li>
@endsection

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Configuración</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Usuarios</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Consulta las personas que pueden acceder al administrador y los roles que tienen asignados.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex w-fit items-center rounded-full bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700">
                {{ $users->total() }} {{ $users->total() === 1 ? 'usuario' : 'usuarios' }}
            </span>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Nuevo usuario
            </a>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="users-filters-title">
        <div class="flex flex-col gap-1">
            <h2 id="users-filters-title" class="font-bold text-slate-950">Buscar y filtrar</h2>
            <p class="text-sm text-slate-500">Podés combinar la búsqueda con un rol.</p>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(14rem,20rem)_auto] lg:items-end">
            <div>
                <label for="search" class="block text-sm font-bold text-slate-700">Nombre o correo electrónico</label>
                <div class="relative mt-2">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $filters['search'] ?? '' }}"
                        maxlength="255"
                        placeholder="Ej.: María o nombre@empresa.com"
                        class="block w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                    >
                </div>
                @error('search')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role_id" class="block text-sm font-bold text-slate-700">Rol</label>
                <select
                    id="role_id"
                    name="role_id"
                    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                >
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) ($filters['role_id'] ?? '') === (string) $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex flex-1 justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-2 lg:flex-none">
                    Aplicar filtros
                </button>
                @if ($hasFilters)
                    <a href="{{ route('admin.users.index') }}" class="inline-flex flex-1 justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 lg:flex-none">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="users-table-title">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 id="users-table-title" class="font-bold text-slate-950">Usuarios registrados</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Usuario</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Correo electrónico</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Roles</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Estado</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Alta</th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($users as $user)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-white">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0" />
                                        </svg>
                                    </span>
                                    <span class="font-semibold text-slate-900">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 sm:px-6">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($user->roles as $role)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-sm text-slate-400">Sin rol</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                <div class="flex flex-col items-start gap-1">
                                    @if ($user->is_active)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Activa</span>
                                    @else
                                        <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700">Inactiva</span>
                                    @endif

                                    @if (auth()->id() === $user->id)
                                        <span class="text-xs font-semibold text-slate-400">Cuenta actual</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500 sm:px-6">{{ $user->created_at?->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right sm:px-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1"
                                        title="Editar usuario"
                                        aria-label="Editar a {{ $user->name }}"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L18.55 2.8M16.862 4.487 19.5 7.125" />
                                        </svg>
                                        <span class="sr-only">Editar usuario</span>
                                    </a>

                                    @if (auth()->id() !== $user->id)
                                        <a
                                            href="{{ route('admin.users.password.edit', $user) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-sky-700 transition hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-1"
                                            title="Restablecer contraseña"
                                            aria-label="Restablecer la contraseña de {{ $user->name }}"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <circle cx="8.5" cy="15.5" r="3.5" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m11 13 8-8m-2 2 2 2m-5 1 2 2" />
                                            </svg>
                                            <span class="sr-only">Restablecer contraseña</span>
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.status.update', $user) }}"
                                            class="inline-flex"
                                            data-confirm
                                            data-confirm-title="¿{{ $user->is_active ? 'Desactivar' : 'Activar' }} a {{ $user->name }}?"
                                            data-confirm-text="{{ $user->is_active ? 'No podrá iniciar sesión y su sesión actual se cerrará.' : 'Recuperará el acceso al administrador.' }}"
                                            data-confirm-button="Sí, {{ $user->is_active ? 'desactivar' : 'activar' }}"
                                            data-confirm-icon="{{ $user->is_active ? 'warning' : 'question' }}"
                                            data-confirm-variant="{{ $user->is_active ? 'danger' : 'success' }}"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}">
                                            <button
                                                type="submit"
                                                title="{{ $user->is_active ? 'Desactivar usuario' : 'Activar usuario' }}"
                                                aria-label="{{ $user->is_active ? 'Desactivar' : 'Activar' }} a {{ $user->name }}"
                                                @class([
                                                'inline-flex h-9 w-9 items-center justify-center rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-1',
                                                'text-red-600 hover:bg-red-50 focus:ring-red-500' => $user->is_active,
                                                'text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-600' => ! $user->is_active,
                                            ])
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <circle cx="9" cy="8" r="3" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19a5.25 5.25 0 0 1 10.5 0" />
                                                    @if ($user->is_active)
                                                        <path stroke-linecap="round" d="M16 11h5" />
                                                    @else
                                                        <path stroke-linecap="round" d="M18.5 8.5v5M16 11h5" />
                                                    @endif
                                                </svg>
                                                <span class="sr-only">{{ $user->is_active ? 'Desactivar usuario' : 'Activar usuario' }}</span>
                                            </button>
                                        </form>

                                        @if ($protectedAdministratorId === $user->id)
                                            <span
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400"
                                                title="Último administrador activo"
                                                aria-label="No se puede eliminar a {{ $user->name }} porque es el último administrador activo"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <rect x="5" y="10" width="14" height="10" rx="2" />
                                                    <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3" />
                                                </svg>
                                                <span class="sr-only">Último administrador activo</span>
                                            </span>
                                        @else
                                            <form
                                                method="POST"
                                                action="{{ route('admin.users.destroy', $user) }}"
                                                class="inline-flex"
                                                data-confirm
                                                data-confirm-title="¿Eliminar a {{ $user->name }}?"
                                                data-confirm-text="Se eliminarán su acceso, sus sesiones y sus asignaciones. Esta acción no se puede deshacer."
                                                data-confirm-button="Sí, eliminar"
                                                data-cancel-button="Cancelar"
                                                data-confirm-icon="warning"
                                                data-confirm-variant="danger"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                                    title="Eliminar usuario"
                                                    aria-label="Eliminar a {{ $user->name }}"
                                                >
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-10 0 .75 12h6.5L16 7m-6-3h4l1 3H9l1-3Zm1 7v5m2-5v5" />
                                                    </svg>
                                                    <span class="sr-only">Eliminar usuario</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <p class="font-semibold text-slate-700">
                                    {{ $hasFilters ? 'No hay usuarios que coincidan con los filtros.' : 'No hay usuarios registrados.' }}
                                </p>
                                @if ($hasFilters)
                                    <a href="{{ route('admin.users.index') }}" class="mt-3 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-800">Limpiar filtros</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                {{ $users->links() }}
            </div>
        @endif
    </section>
@endsection
