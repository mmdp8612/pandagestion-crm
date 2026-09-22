@extends('layouts.admin')

@section('title', 'Editar usuario')

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
    <li><a href="{{ route('admin.users.index') }}" class="transition hover:text-slate-800">Usuarios</a></li>
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">Editar</li>
@endsection

@section('content')
    <div class="max-w-3xl">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Configuración</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Editar usuario</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Actualizá los datos generales y el rol asignado a {{ $user->name }}.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @csrf
            @method('PUT')

            <div class="space-y-6 p-6 sm:p-8">
                <div>
                    <label for="name" class="block text-sm font-bold text-slate-700">Nombre</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        required
                        maxlength="255"
                        autocomplete="name"
                        class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                    >
                    @error('name')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700">Correo electrónico</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        maxlength="255"
                        autocomplete="email"
                        class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role_id" class="block text-sm font-bold text-slate-700">Rol</label>
                    <select
                        id="role_id"
                        name="role_id"
                        required
                        class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                    >
                        <option value="">Seleccioná un rol</option>
                        @foreach ($roles as $role)
                            <option
                                value="{{ $role->id }}"
                                @selected((string) old('role_id', $user->roles->first()?->id) === (string) $role->id)
                            >
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                    La contraseña no se modifica desde este formulario.
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end sm:px-8">
                <a href="{{ route('admin.users.index') }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
