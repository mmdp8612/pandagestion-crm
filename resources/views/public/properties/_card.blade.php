@php
    $propertyLocation = implode(', ', array_filter([$property->Barrio, $property->Localidad, $property->Provincia]));
    $propertyAddress = trim(implode(' ', array_filter([$property->Calle, $property->Numero])));
    $formatCardPrice = static function (mixed $amount, mixed $symbol, mixed $description): ?string {
        if ($amount === null) {
            return null;
        }

        $value = (float) $amount;
        $decimals = floor($value) === $value ? 0 : 2;
        $currency = $symbol ?: $description ?: '';

        return trim($currency.' '.number_format($value, $decimals, ',', '.'));
    };
    $salePrice = $formatCardPrice($property->ImporteVta, $property->MonedaVentaSimbolo, $property->MonedaVentaDescripcion);
    $rentPrice = $formatCardPrice($property->ImporteAlq, $property->MonedaAlquilerSimbolo, $property->MonedaAlquilerDescripcion);
    $mainPrice = $salePrice ?: $rentPrice;
    $mainPriceLabel = $salePrice ? 'Venta' : ($rentPrice ? 'Alquiler' : null);
    $titleLocation = $property->Barrio ?: $property->Localidad;
    $propertyTitle = trim(($property->TipologiaDescripcion ?: 'Propiedad').($titleLocation ? ' en '.$titleLocation : ''));
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl hover:shadow-slate-200/70" data-public-property="{{ $property->Codigo }}">
    <a href="{{ route('public.properties.show', $property->Slug) }}" class="relative block aspect-[16/10] overflow-hidden bg-slate-100 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-emerald-400" aria-label="Ver la propiedad {{ $property->Codigo }}">
        @if ($property->ImagenPrincipal)
            <img src="{{ Storage::disk('public')->url($property->ImagenPrincipal) }}" alt="Imagen de la propiedad {{ $property->Codigo }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        @else
            <div class="flex h-full flex-col items-center justify-center bg-gradient-to-br from-slate-100 to-stone-200 text-slate-400">
                <svg class="h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.91 2.91M3.75 19.5h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                <span class="mt-2 text-xs font-black uppercase tracking-[0.14em]">Sin imagen publicada</span>
            </div>
        @endif

        <div class="absolute inset-x-4 top-4 flex items-start justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                @if ($property->ComercializacionDescripcion)
                    <span class="rounded-full bg-white/95 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.12em] text-slate-800 shadow-sm backdrop-blur">{{ $property->ComercializacionDescripcion }}</span>
                @endif
                @if ($property->Destacada)
                    <span class="rounded-full bg-emerald-500 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.12em] text-emerald-950 shadow-sm">Destacada</span>
                @endif
            </div>
            <span class="shrink-0 rounded-full bg-slate-950/85 px-3 py-1.5 font-mono text-[10px] font-bold text-white backdrop-blur">{{ $property->Codigo }}</span>
        </div>
    </a>

    <div class="flex flex-1 flex-col p-6">
        <div class="border-b border-slate-100 pb-5">
            @if ($mainPrice)
                <p class="text-2xl font-black tracking-tight text-slate-950">{{ $mainPrice }}</p>
                <p class="mt-0.5 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-700">{{ $mainPriceLabel }}</p>
                @if ($salePrice && $rentPrice)
                    <p class="mt-2 text-xs font-bold text-slate-500">Alquiler: {{ $rentPrice }}</p>
                @endif
            @else
                <p class="text-lg font-black text-slate-950">Precio a consultar</p>
            @endif
        </div>

        <div class="flex flex-1 flex-col pt-5">
            <p class="text-xs font-black uppercase tracking-[0.15em] text-emerald-700">{{ $property->UsoDescripcion ?: 'Propiedad' }}</p>
            <h3 class="mt-2 text-xl font-black leading-tight tracking-tight text-slate-950">
                <a href="{{ route('public.properties.show', $property->Slug) }}" class="rounded transition hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">{{ $propertyTitle }}</a>
            </h3>
            <p class="mt-3 flex items-start gap-2 text-sm leading-6 text-slate-600">
                <svg class="mt-1 h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6.75-6.37 6.75-12A6.75 6.75 0 1 0 5.25 9C5.25 14.63 12 21 12 21Z" /><circle cx="12" cy="9" r="2.25" /></svg>
                <span>{{ implode(' · ', array_filter([$propertyAddress, $propertyLocation])) ?: 'Ubicación a consultar' }}</span>
            </p>

            <dl class="mt-5 grid grid-cols-3 divide-x divide-slate-100 rounded-2xl bg-slate-50 py-3 text-center">
                <div class="px-2">
                    <dt class="text-[9px] font-black uppercase tracking-wider text-slate-400">Amb.</dt>
                    <dd class="mt-1 text-sm font-black text-slate-800">{{ $property->Ambientes ?? '—' }}</dd>
                </div>
                <div class="px-2">
                    <dt class="text-[9px] font-black uppercase tracking-wider text-slate-400">Dorm.</dt>
                    <dd class="mt-1 text-sm font-black text-slate-800">{{ $property->Dormitorios ?? '—' }}</dd>
                </div>
                <div class="px-2">
                    <dt class="text-[9px] font-black uppercase tracking-wider text-slate-400">Superficie</dt>
                    <dd class="mt-1 text-sm font-black text-slate-800">{{ $property->SupCubiertaPropia !== null ? rtrim(rtrim(number_format((float) $property->SupCubiertaPropia, 2, ',', '.'), '0'), ',').' m²' : '—' }}</dd>
                </div>
            </dl>

            @if ($property->Descrip)
                <p class="mt-5 line-clamp-2 text-sm leading-6 text-slate-500">{{ $property->Descrip }}</p>
            @endif

            <a href="{{ route('public.properties.show', $property->Slug) }}" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-3.5 text-sm font-black text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200" aria-label="Ver el detalle de la propiedad {{ $property->Codigo }}">
                Ver propiedad
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
            </a>
        </div>
    </div>
</article>
