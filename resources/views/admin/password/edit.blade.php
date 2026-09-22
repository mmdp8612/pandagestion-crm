@extends('layouts.admin')

@section('title', 'Cambiar contraseña')

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
    <li class="font-medium text-slate-700" aria-current="page">Cambiar contraseña</li>
@endsection

@section('content')
    <div class="max-w-3xl">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Seguridad de la cuenta</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Cambiar contraseña</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Para proteger tu cuenta, confirmá la contraseña actual antes de establecer una nueva.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.password.update') }}" class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @csrf
            @method('PUT')

            <div class="space-y-6 p-6 sm:p-8">
                <div>
                    <label for="current_password" class="block text-sm font-bold text-slate-700">Contraseña actual</label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                    >
                    @error('current_password')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
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
                        <label for="password_confirmation" class="block text-sm font-bold text-slate-700">Confirmar nueva contraseña</label>
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
                    La nueva contraseña debe tener al menos 8 caracteres e incluir mayúsculas, minúsculas y números.
                </p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end sm:px-8">
                <a href="{{ auth()->user()->can('dashboard') ? route('admin.dashboard') : url('/') }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Actualizar contraseña
                </button>
            </div>
        </form>
    </div>
@endsection
