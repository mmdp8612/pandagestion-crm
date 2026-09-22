@extends('layouts.admin')

@section('title', 'Editar rol')

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
    <li><a href="{{ route('admin.roles.index') }}" class="transition hover:text-slate-800">Roles y permisos</a></li>
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">Editar</li>
@endsection

@section('content')
    <div class="max-w-4xl">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Configuración</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Editar rol</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Actualizá el nombre y los módulos disponibles para el rol {{ $role->name }}.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @csrf
            @method('PUT')

            <div class="space-y-8 p-6 sm:p-8">
                <div>
                    <label for="name" class="block text-sm font-bold text-slate-700">Nombre del rol</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $role->name) }}"
                        required
                        maxlength="255"
                        autocomplete="off"
                        class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                    >
                    <p class="mt-2 text-xs leading-5 text-slate-500">Se guardará en minúsculas y debe ser único.</p>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <fieldset>
                    <legend class="text-sm font-bold text-slate-700">Permisos de módulo</legend>
                    <p class="mt-2 text-xs leading-5 text-slate-500">Seleccioná al menos un permiso.</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/40">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->name }}"
                                    @checked(in_array($permission->name, old('permissions', $selectedPermissions), true))
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                                >
                                <span>
                                    <span class="block text-sm font-bold text-slate-800">{{ $permissionLabels[$permission->name] ?? $permission->name }}</span>
                                    <span class="mt-1 block text-xs text-slate-500">{{ $permission->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('permissions')
                        <p class="mt-3 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                    @error('permissions.*')
                        <p class="mt-3 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end sm:px-8">
                <a href="{{ route('admin.roles.index') }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
