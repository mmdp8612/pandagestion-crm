<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Administración') | PandaGestion</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        <div
            class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden"
            data-sidebar-overlay
            aria-hidden="true"
        ></div>

        <aside
            id="admin-sidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-slate-950 text-slate-200 shadow-2xl transition-transform duration-200 ease-out lg:translate-x-0 lg:shadow-none"
            aria-label="Menú principal"
        >
            <div class="flex h-20 shrink-0 items-center justify-between border-b border-white/10 px-5">
                <a href="{{ auth()->user()->can('dashboard') ? route('admin.dashboard') : route('admin.password.edit') }}" class="flex items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-slate-950">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400 text-lg font-black text-slate-950 shadow-lg shadow-emerald-950/30">
                        P
                    </span>
                    <span>
                        <span class="block font-bold tracking-tight text-white">PandaGestion</span>
                        <span class="block text-xs text-slate-400">Administrador inmobiliario</span>
                    </span>
                </a>

                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400 lg:hidden"
                    data-sidebar-close
                    aria-label="Cerrar menú"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-6">
                <p class="px-3 text-xs font-semibold uppercase tracking-widest text-slate-500">Principal</p>

                <div class="mt-3 space-y-1">
                    @can('dashboard')
                        <a
                            href="{{ route('admin.dashboard') }}"
                            @class([
                                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                'bg-emerald-400 text-slate-950 shadow-lg shadow-emerald-950/20' => request()->routeIs('admin.dashboard'),
                                'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.dashboard'),
                            ])
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3 10.5 9-7.5 9 7.5M5.25 9.75V21h13.5V9.75M9 21v-6h6v6" />
                            </svg>
                            Dashboard
                        </a>
                    @endcan

                    @can('bienesraices')
                        <a
                            href="{{ route('admin.properties.index') }}"
                            @class([
                                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                'bg-emerald-400 text-slate-950 shadow-lg shadow-emerald-950/20' => request()->routeIs('admin.properties.*'),
                                'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.properties.*'),
                            ])
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.2 3.05a1.13 1.13 0 0 1 1.6 0L21.75 12M4.5 9.75v10.5h15V9.75M9 20.25v-6h6v6" />
                            </svg>
                            <span class="min-w-0 flex-1 whitespace-nowrap">Bienes Raíces</span>
                        </a>
                    @endcan

                    @can('consultas')
                        <a
                            href="{{ route('admin.inquiries.index') }}"
                            @class([
                                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                'bg-emerald-400 text-slate-950 shadow-lg shadow-emerald-950/20' => request()->routeIs('admin.inquiries.*'),
                                'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.inquiries.*'),
                            ])
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6M21 12a8.25 8.25 0 0 1-11.94 7.38L3 21l1.62-6.06A8.25 8.25 0 1 1 21 12Z" />
                            </svg>
                            <span class="min-w-0 flex-1 whitespace-nowrap">Consultas</span>
                            @if (($newInquiryCount ?? 0) > 0)
                                <span
                                    @class([
                                        'inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-black tabular-nums',
                                        'bg-slate-950/10 text-slate-950' => request()->routeIs('admin.inquiries.*'),
                                        'bg-emerald-400/15 text-emerald-300' => ! request()->routeIs('admin.inquiries.*'),
                                    ])
                                    title="{{ $newInquiryCount }} {{ $newInquiryCount === 1 ? 'consulta nueva' : 'consultas nuevas' }}"
                                    aria-label="{{ $newInquiryCount }} {{ $newInquiryCount === 1 ? 'consulta nueva' : 'consultas nuevas' }}"
                                    data-inquiry-menu-count
                                >{{ $newInquiryCount > 99 ? '99+' : $newInquiryCount }}</span>
                            @endif
                        </a>
                    @endcan

                    @can('catalogo')
                        <details class="group" data-catalog-menu @if (request()->routeIs('admin.catalogs.*')) open @endif>
                            <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75a2.25 2.25 0 0 1-2.25-2.25V5.25ZM8.25 7.5h7.5M8.25 12h7.5M8.25 16.5h4.5" />
                                </svg>
                                <span class="flex-1">Catálogo</span>
                                <svg class="h-4 w-4 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="ml-5 mt-1 space-y-1 border-l border-white/10 pl-5">
                                <a
                                    href="{{ route('admin.catalogs.antiquities.index') }}"
                                    @class([
                                        'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                        'bg-white/10 text-emerald-300' => request()->routeIs('admin.catalogs.antiquities.*'),
                                        'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.catalogs.antiquities.*'),
                                    ])
                                >
                                    Antigüedades
                                </a>
                                <a
                                    href="{{ route('admin.catalogs.garages.index') }}"
                                    @class([
                                        'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                        'bg-white/10 text-emerald-300' => request()->routeIs('admin.catalogs.garages.*'),
                                        'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.catalogs.garages.*'),
                                    ])
                                >
                                    Cocheras
                                </a>
                                <a
                                    href="{{ route('admin.catalogs.commercializations.index') }}"
                                    @class([
                                        'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                        'bg-white/10 text-emerald-300' => request()->routeIs('admin.catalogs.commercializations.*'),
                                        'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.catalogs.commercializations.*'),
                                    ])
                                >
                                    Comercialización
                                </a>
                                <a
                                    href="{{ route('admin.catalogs.orientations.index') }}"
                                    @class([
                                        'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                        'bg-white/10 text-emerald-300' => request()->routeIs('admin.catalogs.orientations.*'),
                                        'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.catalogs.orientations.*'),
                                    ])
                                >
                                    Orientaciones
                                </a>
                                <a
                                    href="{{ route('admin.catalogs.typologies.index') }}"
                                    @class([
                                        'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                        'bg-white/10 text-emerald-300' => request()->routeIs('admin.catalogs.typologies.*'),
                                        'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.catalogs.typologies.*'),
                                    ])
                                >
                                    Tipologías
                                </a>
                                <a
                                    href="{{ route('admin.catalogs.currency-types.index') }}"
                                    @class([
                                        'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                        'bg-white/10 text-emerald-300' => request()->routeIs('admin.catalogs.currency-types.*'),
                                        'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.catalogs.currency-types.*'),
                                    ])
                                >
                                    Tipos de moneda
                                </a>
                                <a
                                    href="{{ route('admin.catalogs.uses.index') }}"
                                    @class([
                                        'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                        'bg-white/10 text-emerald-300' => request()->routeIs('admin.catalogs.uses.*'),
                                        'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.catalogs.uses.*'),
                                    ])
                                >
                                    Usos
                                </a>
                                <a
                                    href="{{ route('admin.catalogs.views.index') }}"
                                    @class([
                                        'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                        'bg-white/10 text-emerald-300' => request()->routeIs('admin.catalogs.views.*'),
                                        'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.catalogs.views.*'),
                                    ])
                                >
                                    Vistas
                                </a>
                            </div>
                        </details>
                    @endcan
                </div>

                @canany(['configuracion', 'usuarios', 'roles'])
                    <div class="mt-8">
                        <p class="px-3 text-xs font-semibold uppercase tracking-widest text-slate-500">Administración</p>

                        <details class="group mt-3" open>
                            <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400">
                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.6 3.8a1.5 1.5 0 0 1 2.8 0l.4 1.1c.2.5.7.8 1.2.7l1.2-.2a1.5 1.5 0 0 1 1.8 1.8l-.2 1.2c-.1.5.2 1 .7 1.2l1.1.4a1.5 1.5 0 0 1 0 2.8l-1.1.4c-.5.2-.8.7-.7 1.2l.2 1.2a1.5 1.5 0 0 1-1.8 1.8l-1.2-.2c-.5-.1-1 .2-1.2.7l-.4 1.1a1.5 1.5 0 0 1-2.8 0l-.4-1.1c-.2-.5-.7-.8-1.2-.7l-1.2.2A1.5 1.5 0 0 1 5 15.6l.2-1.2c.1-.5-.2-1-.7-1.2l-1.1-.4a1.5 1.5 0 0 1 0-2.8l1.1-.4c.5-.2.8-.7.7-1.2L5 7.2a1.5 1.5 0 0 1 1.8-1.8l1.2.2c.5.1 1-.2 1.2-.7l.4-1.1Z" />
                                    <circle cx="11" cy="11.4" r="2.5" />
                                </svg>
                                <span class="flex-1">Configuración</span>
                                <svg class="h-4 w-4 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            </summary>

                            <div class="ml-5 mt-1 space-y-1 border-l border-white/10 pl-5">
                                @can('configuracion')
                                    <a
                                        href="{{ route('admin.real-estate-agency.edit') }}"
                                        @class([
                                            'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                            'bg-white/10 text-emerald-300' => request()->routeIs('admin.real-estate-agency.*'),
                                            'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.real-estate-agency.*'),
                                        ])
                                    >
                                        Inmobiliaria
                                    </a>
                                @endcan
                                @can('usuarios')
                                    <a
                                        href="{{ route('admin.users.index') }}"
                                        @class([
                                            'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                            'bg-white/10 text-emerald-300' => request()->routeIs('admin.users.*'),
                                            'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.users.*'),
                                        ])
                                    >
                                        Usuarios
                                    </a>
                                @endcan
                                @can('roles')
                                    <a
                                        href="{{ route('admin.roles.index') }}"
                                        @class([
                                            'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                            'bg-white/10 text-emerald-300' => request()->routeIs('admin.roles.*'),
                                            'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.roles.*'),
                                        ])
                                    >
                                        Roles y permisos
                                    </a>
                                @endcan
                                @can('configuracion')
                                    <a
                                        href="{{ route('admin.password.edit') }}"
                                        @class([
                                            'block rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-400',
                                            'bg-white/10 text-emerald-300' => request()->routeIs('admin.password.*'),
                                            'text-slate-400 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.password.*'),
                                        ])
                                    >
                                        Cambiar contraseña
                                    </a>
                                @endcan
                            </div>
                        </details>
                    </div>
                @endcanany
            </nav>

            <div class="shrink-0 border-t border-white/10 p-4">
                <div class="mb-3 px-3">
                    <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ auth()->user()->email }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-red-500/10 hover:text-red-300 focus:outline-none focus:ring-2 focus:ring-red-400"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 8.25 19.5 12m0 0-3.75 3.75M19.5 12h-12M12 4.5H5.25A2.25 2.25 0 0 0 3 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25H12" />
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-h-screen lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur">
                <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 p-2 text-slate-600 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 lg:hidden"
                            data-sidebar-toggle
                            aria-controls="admin-sidebar"
                            aria-expanded="false"
                            aria-label="Abrir menú"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="min-w-0">
                            <nav aria-label="Migas de pan">
                                <ol class="flex items-center gap-2 text-xs text-slate-500">
                                    <li>Administración</li>
                                    @yield('breadcrumb')
                                </ol>
                            </nav>
                            <p class="mt-1 truncate text-lg font-bold tracking-tight text-slate-950">@yield('title', 'Administración')</p>
                        </div>
                    </div>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-3 rounded-xl px-2 py-1.5 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                            <span class="hidden text-right sm:block">
                                <span class="block max-w-48 truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                                <span class="block max-w-48 truncate text-xs text-slate-500">{{ auth()->user()->email }}</span>
                            </span>
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0 17.9 17.9 0 0 1-7.5 1.65A17.9 17.9 0 0 1 4.5 20.1Z" />
                                </svg>
                            </span>
                            <svg class="hidden h-4 w-4 text-slate-400 transition group-open:rotate-180 sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </summary>

                        <div class="absolute right-0 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-200/70">
                            <div class="border-b border-slate-100 px-3 py-2 sm:hidden">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('admin.password.edit') }}" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM12 9v3.75m0 0H8.25a2.25 2.25 0 0 0-2.25 2.25v3.75m6-6h3.75A2.25 2.25 0 0 1 18 15v3.75" />
                                </svg>
                                Cambiar contraseña
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 8.25 19.5 12m0 0-3.75 3.75M19.5 12h-12" />
                                    </svg>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            </header>

            <main class="px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <x-admin.flash-messages />
                    @yield('content')
                </div>
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
