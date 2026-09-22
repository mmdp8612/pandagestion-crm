<footer class="bg-slate-950 text-slate-300">
    <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8 lg:px-10">
        <div class="grid gap-10 border-b border-white/10 pb-10 md:grid-cols-[1fr_auto] md:items-start">
            <div class="max-w-xl">
                <div class="flex items-center gap-3">
                    @if ($agency?->Logo)
                        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-white"><img src="{{ Storage::disk('public')->url($agency->Logo) }}" alt="" class="h-full w-full object-contain p-1"></span>
                    @else
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400 font-black text-slate-950">P</span>
                    @endif
                    <span class="font-black text-white">{{ $brandName }}</span>
                </div>
                <p class="mt-4 text-sm leading-6 text-slate-400">Portal de propiedades en venta y alquiler.</p>
                @if ($agency?->Matricula)
                    <p class="mt-2 text-sm text-slate-400">Matrícula {{ $agency->Matricula }}</p>
                @endif
                @if ($agencyAddress)
                    <p class="mt-2 text-sm leading-6 text-slate-400">{{ $agencyAddress }}</p>
                @endif
            </div>

            <address class="not-italic md:text-right">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Contacto</p>
                <div class="mt-4 space-y-2 text-sm font-semibold">
                    @if ($agency?->Whatsapp)
                        <p><a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-emerald-300">WhatsApp {{ $agency->Whatsapp }}</a></p>
                    @endif
                    @if ($agency?->Telefonos)
                        <p><a href="{{ $phoneUrl }}" class="transition hover:text-emerald-300">{{ $agency->Telefonos }}</a></p>
                    @endif
                    @if ($agency?->Email)
                        <p><a href="mailto:{{ $agency->Email }}" class="transition hover:text-emerald-300">{{ $agency->Email }}</a></p>
                    @endif
                    @if ($agency?->Web)
                        <p><a href="{{ $agency->Web }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-emerald-300">Sitio web</a></p>
                    @endif
                </div>
            </address>
        </div>

        <div class="flex flex-col gap-4 pt-7 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} {{ $brandName }}. Todos los derechos reservados.</p>
            <a href="{{ auth()->check() ? (auth()->user()->can('dashboard') ? route('admin.dashboard') : route('admin.password.edit')) : route('login') }}" class="w-fit transition hover:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-4 focus:ring-offset-slate-950">
                {{ auth()->check() ? 'Ir al panel' : 'Acceso administrativo' }}
            </a>
        </div>
    </div>
</footer>
