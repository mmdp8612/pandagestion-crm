@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="w-full max-w-md">
        <section class="overflow-hidden rounded-3xl border border-white/70 bg-white/95 shadow-2xl shadow-slate-950/45 backdrop-blur-sm">
            <div class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 px-6 py-8 text-white sm:px-8">
                <div class="absolute -right-12 -top-16 h-40 w-40 rounded-full border border-emerald-300/15" aria-hidden="true"></div>
                <div class="absolute -right-4 -top-8 h-24 w-24 rounded-full border border-emerald-300/10" aria-hidden="true"></div>

                <div class="relative flex items-center gap-4">
                    <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl bg-emerald-400 text-xl font-black text-slate-950 shadow-lg shadow-emerald-950/30 ring-1 ring-white/20">
                        P
                    </div>
                    <div>
                        <p class="text-[0.68rem] font-bold uppercase tracking-[0.22em] text-emerald-300">Administración inmobiliaria</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight">PandaGestion</h1>
                    </div>
                </div>

                <p class="relative mt-5 max-w-sm text-sm leading-6 text-slate-300">
                    Gestioná propiedades, contactos y operaciones desde un solo lugar.
                </p>
            </div>

            <div class="px-6 py-8 sm:px-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mb-6">
                    <h2 class="text-lg font-bold text-slate-950">Iniciar sesión</h2>
                    <p class="mt-1 text-sm text-slate-500">Ingresá tus credenciales para acceder al panel.</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Correo electrónico</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">Contraseña</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                        <input
                            name="remember"
                            type="checkbox"
                            value="1"
                            @checked(old('remember'))
                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                        >
                        Recordar sesión
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm shadow-emerald-900/20 transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                    >
                        Iniciar sesión
                    </button>
                </form>
            </div>
        </section>

        <p class="mt-5 flex items-center justify-center gap-2 text-center text-xs font-medium text-slate-300/80">
            <svg class="h-4 w-4 text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 5.75 6v5.1c0 4.1 2.58 7.72 6.25 9.15 3.67-1.43 6.25-5.05 6.25-9.15V6L12 3.75Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m9.5 12 1.65 1.65 3.6-3.8" />
            </svg>
            Acceso seguro al administrador
        </p>
    </div>
@endsection
