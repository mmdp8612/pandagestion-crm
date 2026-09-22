<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-5 px-5 sm:px-8 lg:px-10">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-4" aria-label="Inicio de {{ $brandName }}">
            @if ($agency?->Logo)
                <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <img src="{{ Storage::disk('public')->url($agency->Logo) }}" alt="Logo de {{ $brandName }}" class="h-full w-full object-contain p-1">
                </span>
            @else
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-950 text-lg font-black text-emerald-300 shadow-sm">P</span>
            @endif

            <span class="min-w-0 max-w-[11rem] sm:max-w-none">
                <span class="block truncate text-sm font-black tracking-tight text-slate-950 sm:text-base">{{ $brandName }}</span>
                <span class="hidden text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 sm:block">Portal de propiedades</span>
            </span>
        </a>

        <nav class="hidden items-center gap-8 text-sm font-bold text-slate-600 md:flex" aria-label="Navegación principal">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-emerald-800' : '' }} transition hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-4">Inicio</a>
            <a href="{{ route('public.properties.index') }}" class="{{ request()->routeIs('public.properties.*') ? 'text-emerald-800' : '' }} transition hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-4">Propiedades</a>
            <a href="{{ route('home') }}#como-funciona" class="transition hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-4">Cómo funciona</a>
            <a href="{{ route('home') }}#contacto" class="transition hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-4">Contacto</a>
        </nav>

        <a href="{{ route('public.properties.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-emerald-950 px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-4 sm:px-5 sm:text-sm">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="7"></circle>
                <path stroke-linecap="round" d="m20 20-4-4"></path>
            </svg>
            <span class="hidden sm:inline">Buscar propiedad</span>
            <span class="sm:hidden">Buscar</span>
        </a>
    </div>
</header>
