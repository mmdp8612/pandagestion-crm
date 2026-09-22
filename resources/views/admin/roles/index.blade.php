@extends('layouts.admin')

@section('title', 'Roles y permisos')

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
    <li class="font-medium text-slate-700" aria-current="page">Roles y permisos</li>
@endsection

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Configuración</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Roles y permisos</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Consulta los roles disponibles y los módulos a los que permite acceder cada uno.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex w-fit items-center rounded-full bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700">
                {{ $roles->total() }} {{ $roles->total() === 1 ? 'rol' : 'roles' }}
            </span>
            <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Nuevo rol
            </a>
        </div>
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="roles-table-title">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 id="roles-table-title" class="font-bold text-slate-950">Roles configurados</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Rol</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Usuarios</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Permisos de módulo</th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($roles as $role)
                        <tr class="align-top transition hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                <span class="font-semibold text-slate-900">{{ $role->name }}</span>
                                <span class="mt-1 block text-xs text-slate-500">
                                    {{ $role->permissions->count() }} {{ $role->permissions->count() === 1 ? 'permiso' : 'permisos' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700">
                                    {{ $role->users_count }} {{ $role->users_count === 1 ? 'usuario' : 'usuarios' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($role->permissions as $permission)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700" title="{{ $permission->name }}">
                                            {{ $permissionLabels[$permission->name] ?? $permission->name }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-slate-400">Sin permisos asignados</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right sm:px-6">
                                @if ($role->name === $protectedRoleName)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500" title="Este rol garantiza el acceso administrativo crítico.">
                                        Protegido
                                    </span>
                                @else
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a
                                            href="{{ route('admin.roles.edit', $role) }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1"
                                            title="Editar rol"
                                            aria-label="Editar el rol {{ $role->name }}"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L18.55 2.8M16.862 4.487 19.5 7.125" />
                                            </svg>
                                            <span class="sr-only">Editar rol</span>
                                        </a>

                                        @if ($role->users_count === 0)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.roles.destroy', $role) }}"
                                                class="inline-flex"
                                                data-confirm
                                                data-confirm-title="¿Eliminar {{ $role->name }}?"
                                                data-confirm-text="Esta acción no se puede deshacer."
                                                data-confirm-button="Sí, eliminar"
                                                data-cancel-button="Cancelar"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                                    title="Eliminar rol"
                                                    aria-label="Eliminar el rol {{ $role->name }}"
                                                >
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-10 0 .75 12h6.5L16 7m-6-3h4l1 3H9l1-3Zm1 7v5m2-5v5" />
                                                    </svg>
                                                    <span class="sr-only">Eliminar rol</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500" title="Reasigná sus usuarios antes de eliminar este rol.">En uso</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <p class="font-semibold text-slate-700">No hay roles configurados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                {{ $roles->links() }}
            </div>
        @endif
    </section>
@endsection
