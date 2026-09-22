@extends('layouts.admin')

@section('title', 'Restablecer contraseña')

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
    <li class="font-medium text-slate-700" aria-current="page">Restablecer contraseña</li>
@endsection

@section('content')
    <div class="max-w-3xl">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Configuración</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Restablecer contraseña</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Definí una nueva contraseña para {{ $user->name }} ({{ $user->email }}).
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('admin.users.password.update', $user) }}"
            class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            data-confirm
            data-confirm-title="¿Restablecer la contraseña?"
            data-confirm-text="Se cerrarán las sesiones activas de {{ $user->name }}."
            data-confirm-button="Sí, restablecer"
            data-confirm-icon="warning"
            data-confirm-variant="danger"
        >
            @csrf
            @method('PUT')

            <div class="space-y-6 p-6 sm:p-8">
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-950">
                    Al guardar, se invalidarán el acceso persistente y las sesiones registradas de este usuario.
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-bold text-slate-700">Nueva contraseña</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-slate-700">Confirmar contraseña</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                        >
                    </div>
                </div>

                <p class="rounded-lg bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-600">
                    Debe tener al menos 8 caracteres e incluir mayúsculas, minúsculas y números. También debe ser diferente de la contraseña actual.
                </p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end sm:px-8">
                <a href="{{ route('admin.users.index') }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Restablecer contraseña
                </button>
            </div>
        </form>
    </div>
@endsection
