@extends('layouts.public')

@php
    $homeCanonicalUrl = \App\Support\PublicUrl::route('home');
    $homeCatalogUrl = \App\Support\PublicUrl::route('public.properties.index');
    $homeMetaDescription = 'Buscá propiedades en venta y alquiler por ubicación, operación y tipología. Consultá directamente con '.$brandName.'.';
    $homeSocialImage = \App\Support\PublicUrl::storage($agency?->Logo);
    $organizationId = $homeCanonicalUrl.'#organization';
    $organization = [
        '@type' => $agency ? 'RealEstateAgent' : 'Organization',
        '@id' => $organizationId,
        'name' => $brandName,
        'url' => $homeCanonicalUrl,
    ];

    if ($homeSocialImage) {
        $organization['logo'] = $homeSocialImage;
        $organization['image'] = $homeSocialImage;
    }

    if ($agency?->Email) {
        $organization['email'] = $agency->Email;
    }

    if ($agency?->Telefonos) {
        $organization['telephone'] = $agency->Telefonos;
    }

    if ($agency?->Web) {
        $organization['sameAs'] = [$agency->Web];
    }

    if ($agency && array_filter([$agency->Domicilio, $agency->Localidad, $agency->Provincia, $agency->CodigoPostal])) {
        $organization['address'] = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $agency->Domicilio,
            'addressLocality' => $agency->Localidad,
            'addressRegion' => $agency->Provincia,
            'postalCode' => $agency->CodigoPostal,
        ]);
    }

    $homeStructuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => $homeCanonicalUrl.'#website',
                'url' => $homeCanonicalUrl,
                'name' => $brandName,
                'description' => $homeMetaDescription,
                'inLanguage' => 'es-AR',
                'publisher' => ['@id' => $organizationId],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => $homeCatalogUrl.'?ubicacion={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            $organization,
        ],
    ];
    $homeStructuredDataJson = json_encode(
        $homeStructuredData,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
    );

    $contactUrl = $whatsappUrl
        ?? ($agency?->Email ? 'mailto:'.$agency->Email : '#contacto');
    $opensExternally = $whatsappUrl !== null;
    $formatPrice = static function (mixed $amount, mixed $symbol, mixed $description): ?string {
        if ($amount === null) {
            return null;
        }

        $value = (float) $amount;
        $decimals = floor($value) === $value ? 0 : 2;
        $currency = $symbol ?: $description ?: '';

        return trim($currency.' '.number_format($value, $decimals, ',', '.'));
    };
@endphp

@section('title', 'Propiedades en venta y alquiler | '.$brandName)
@section('meta_description', $homeMetaDescription)
@section('canonical_url', $homeCanonicalUrl)
@if ($homeSocialImage)
    @section('meta_image', $homeSocialImage)
    @section('meta_image_alt', 'Logo de '.$brandName)
@endif

@push('structured_data')
    <script type="application/ld+json">{!! $homeStructuredDataJson !!}</script>
@endpush

@section('content')
    @include('public.partials.header')

    <main>
        <section class="relative overflow-hidden bg-[#123a34] text-white">
            <div class="pointer-events-none absolute inset-0 opacity-20" aria-hidden="true" style="background-image: radial-gradient(circle at 18% 10%, rgba(110,231,183,.45), transparent 27%), radial-gradient(circle at 86% 70%, rgba(125,211,252,.28), transparent 30%);"></div>
            <div class="relative mx-auto grid max-w-7xl gap-10 px-5 pb-24 pt-14 sm:px-8 sm:pb-28 sm:pt-20 lg:grid-cols-[minmax(0,1.08fr)_minmax(360px,0.92fr)] lg:items-center lg:px-10 lg:pb-32 lg:pt-24">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200/20 bg-white/10 px-3.5 py-2 text-xs font-black uppercase tracking-[0.18em] text-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Propiedades para cada proyecto
                    </span>
                    <h1 class="mt-6 font-serif text-4xl font-semibold leading-[1.07] tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Encontrá la propiedad que estás buscando.
                    </h1>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-emerald-50/75 sm:text-lg">
                        Explorá las publicaciones disponibles, compará sus características y consultá directamente por la opción que te interesa.
                    </p>
                    <a href="{{ route('public.properties.index') }}" class="mt-8 inline-flex items-center gap-2 text-sm font-black text-emerald-200 transition hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:ring-offset-4 focus:ring-offset-[#123a34]">
                        Ver propiedades publicadas
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                    </a>
                </div>

                <div class="relative mx-auto hidden w-full max-w-lg lg:block" aria-hidden="true">
                    <div class="absolute -inset-5 rotate-3 rounded-[2.25rem] border border-white/10 bg-white/5"></div>
                    <div class="relative overflow-hidden rounded-[2rem] border border-white/15 bg-[#e9f0e9] p-5 shadow-2xl shadow-black/25">
                        <div class="rounded-[1.4rem] bg-white p-4 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex gap-1.5"><span class="h-2 w-2 rounded-full bg-rose-300"></span><span class="h-2 w-2 rounded-full bg-amber-300"></span><span class="h-2 w-2 rounded-full bg-emerald-300"></span></div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-slate-400">Propiedades</span>
                            </div>
                            <div class="relative mt-4 h-52 overflow-hidden rounded-2xl bg-[#dbe8df]">
                                <svg class="absolute inset-0 h-full w-full text-emerald-900/15" viewBox="0 0 500 250" fill="none" stroke="currentColor">
                                    <path d="M-20 45 80 75l70-48 80 75 85-45 80 55 125-54M-20 180l90-42 85 50 85-60 105 58 90-45 85 35M110-20l-5 290M260-20l25 290M405-20l-35 290" stroke-width="16" />
                                    <path d="M-20 45 80 75l70-48 80 75 85-45 80 55 125-54M-20 180l90-42 85 50 85-60 105 58 90-45 85 35M110-20l-5 290M260-20l25 290M405-20l-35 290" stroke="white" stroke-width="5" />
                                </svg>
                                <span class="absolute left-[28%] top-[34%] flex h-10 w-10 items-center justify-center rounded-full border-4 border-white bg-emerald-700 text-white shadow-lg">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a7.13 7.13 0 0 0-7.13 7.13c0 5.34 7.13 12.37 7.13 12.37s7.13-7.03 7.13-12.37A7.13 7.13 0 0 0 12 2.25Zm0 9.63a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z" /></svg>
                                </span>
                                <span class="absolute bottom-[21%] right-[19%] flex h-8 w-8 items-center justify-center rounded-full border-4 border-white bg-sky-600 text-white shadow-lg"></span>
                            </div>
                            <div class="mt-4 grid grid-cols-[1fr_auto] items-center gap-4">
                                <div>
                                    <span class="block h-2.5 w-24 rounded-full bg-slate-200"></span>
                                    <span class="mt-2 block h-2 w-40 rounded-full bg-slate-100"></span>
                                </div>
                                <span class="rounded-full bg-emerald-700 px-4 py-2 text-[10px] font-black uppercase tracking-wider text-white">Consultar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="buscar" class="relative z-10 -mt-14 scroll-mt-24 px-5 sm:px-8">
            <div class="mx-auto max-w-7xl rounded-3xl border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-900/10 sm:p-6">
                <form action="{{ route('public.properties.index') }}" method="GET" class="grid gap-3 lg:grid-cols-[minmax(220px,1.35fr)_minmax(180px,0.9fr)_minmax(180px,0.9fr)_auto]" aria-label="Buscar propiedades">
                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-500">Ubicación</span>
                        <span class="relative block">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6.75-6.37 6.75-12A6.75 6.75 0 1 0 5.25 9C5.25 14.63 12 21 12 21Z" /><circle cx="12" cy="9" r="2.25" /></svg>
                            <input type="search" name="ubicacion" value="{{ $filters['ubicacion'] ?? '' }}" maxlength="120" placeholder="Barrio, localidad o calle" class="h-13 w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-normal placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                        </span>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-500">Operación</span>
                        <select name="operacion" class="h-13 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                            <option value="">Todas</option>
                            @foreach ($commercializations as $commercialization)
                                <option value="{{ $commercialization->IdComercializacion }}" @selected(($filters['operacion'] ?? null) === $commercialization->IdComercializacion)>{{ $commercialization->Descrip }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-500">Tipo de propiedad</span>
                        <select name="tipologia" class="h-13 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                            <option value="">Todas</option>
                            @foreach ($typologies as $typology)
                                <option value="{{ $typology->IdTipologia }}" @selected(($filters['tipologia'] ?? null) === $typology->IdTipologia)>{{ $typology->Descrip }}</option>
                            @endforeach
                        </select>
                    </label>

                    <button type="submit" class="mt-auto inline-flex h-13 items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-emerald-900/15 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 lg:min-w-36">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path stroke-linecap="round" d="m20 20-4-4"></path></svg>
                        Buscar
                    </button>
                </form>

                @if ($errors->any())
                    <p class="mt-4 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $errors->first() }}</p>
                @endif
            </div>
        </section>

        <section id="propiedades" class="scroll-mt-24 bg-[#f4f6f3] py-18 sm:py-24">
            <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-700">Recién publicadas</p>
                        <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">
                            Últimas propiedades
                        </h2>
                        <p class="mt-4 text-base leading-7 text-slate-600">Conocé las seis incorporaciones más recientes o ingresá al catálogo completo para filtrar y ordenar todas las publicaciones.</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-4">
                        <span class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700">{{ $properties->count() }} {{ $properties->count() === 1 ? 'novedad' : 'novedades' }}</span>
                        <a href="{{ route('public.properties.index') }}" class="text-sm font-black text-emerald-700 underline decoration-emerald-300 underline-offset-4 transition hover:text-emerald-900">Ver catálogo completo</a>
                    </div>
                </div>

                @if ($properties->isNotEmpty())
                    <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($properties as $property)
                            @php
                                $location = implode(', ', array_filter([$property->Barrio, $property->Localidad, $property->Provincia]));
                                $address = trim(implode(' ', array_filter([$property->Calle, $property->Numero])));
                                $salePrice = $formatPrice($property->ImporteVta, $property->MonedaVentaSimbolo, $property->MonedaVentaDescripcion);
                                $rentPrice = $formatPrice($property->ImporteAlq, $property->MonedaAlquilerSimbolo, $property->MonedaAlquilerDescripcion);
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
                                            <span>{{ implode(' · ', array_filter([$address, $location])) ?: 'Ubicación a consultar' }}</span>
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
                                            <p class="mt-5 h-12 overflow-hidden text-sm leading-6 text-slate-500">{{ $property->Descrip }}</p>
                                        @endif

                                        <a href="{{ route('public.properties.show', $property->Slug) }}" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-3.5 text-sm font-black text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200" aria-label="Ver el detalle de la propiedad {{ $property->Codigo }}">
                                            Ver propiedad
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                @else
                    <div class="mt-10 overflow-hidden rounded-3xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm sm:px-10">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path stroke-linecap="round" d="m20 20-4-4"></path></svg>
                        </span>
                        <h3 class="mt-5 text-xl font-black text-slate-950">Todavía no hay propiedades publicadas</h3>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-600">Estamos preparando las próximas publicaciones. También podés contarnos qué estás buscando para recibir atención directa.</p>
                        <a href="{{ $contactUrl }}" @if ($opensExternally) target="_blank" rel="noopener noreferrer" @endif class="mt-6 inline-flex items-center justify-center rounded-full bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">Contanos qué buscás</a>
                    </div>
                @endif
            </div>
        </section>

        <section class="border-y border-slate-200 bg-white py-18 sm:py-24">
            <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                <div class="grid gap-10 lg:grid-cols-[0.75fr_1.25fr] lg:items-center">
                    <div class="max-w-xl">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-700">Una búsqueda más simple</p>
                        <h2 class="mt-4 font-serif text-3xl font-semibold leading-tight tracking-tight text-slate-950 sm:text-4xl">La información importante, separada y fácil de consultar.</h2>
                        <p class="mt-5 text-base leading-8 text-slate-600">El portal organiza cada publicación para que puedas encontrar alternativas y avanzar sin vueltas.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <article class="rounded-3xl border border-slate-200 bg-[#f7f8f5] p-6">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-800"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 9 17.25 19.5 6.75" /></svg></span>
                            <h3 class="mt-5 font-black text-slate-950">Publicaciones claras</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Datos esenciales, valores y características visibles desde el listado.</p>
                        </article>
                        <article class="rounded-3xl border border-slate-200 bg-[#f7f8f5] p-6">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-700"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 11.25 9-7.5 9 7.5M5.25 9.75v10.5h13.5V9.75M9.75 20.25v-6h4.5v6" /></svg></span>
                            <h3 class="mt-5 font-black text-slate-950">Inventario actualizado</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Sólo se muestran propiedades e imágenes habilitadas para publicación.</p>
                        </article>
                        <article class="rounded-3xl border border-slate-200 bg-[#f7f8f5] p-6">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-700"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 10.5h7.5m-7.5 3h4.5M21 12a8.25 8.25 0 0 1-12.7 6.94L3 20.25l1.31-5.25A8.25 8.25 0 1 1 21 12Z" /></svg></span>
                            <h3 class="mt-5 font-black text-slate-950">Consulta directa</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Cada publicación abre un canal de contacto por esa propiedad.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section id="como-funciona" class="scroll-mt-24 bg-slate-950 py-18 text-white sm:py-24">
            <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                <div class="max-w-2xl">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-300">Cómo funciona</p>
                    <h2 class="mt-4 font-serif text-3xl font-semibold tracking-tight sm:text-4xl">De la búsqueda a la consulta, en tres pasos.</h2>
                </div>

                <ol class="mt-12 grid gap-8 md:grid-cols-3">
                    <li class="border-t border-white/15 pt-6">
                        <span class="font-mono text-sm font-black text-emerald-300">01</span>
                        <h3 class="mt-4 text-xl font-black">Buscá</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-400">Combiná ubicación, operación y tipología para encontrar opciones relevantes.</p>
                    </li>
                    <li class="border-t border-white/15 pt-6">
                        <span class="font-mono text-sm font-black text-emerald-300">02</span>
                        <h3 class="mt-4 text-xl font-black">Explorá</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-400">Compará publicaciones, precios, superficies y ambientes desde una grilla ordenada.</p>
                    </li>
                    <li class="border-t border-white/15 pt-6">
                        <span class="font-mono text-sm font-black text-emerald-300">03</span>
                        <h3 class="mt-4 text-xl font-black">Consultá</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-400">Contactanos indicando el código de la propiedad y coordinamos los próximos pasos.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section id="contacto" class="scroll-mt-20 bg-emerald-400">
            <div class="mx-auto grid max-w-7xl gap-10 px-5 py-16 sm:px-8 sm:py-20 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:px-10">
                <div class="max-w-3xl">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-950/70">¿No encontraste lo que buscabas?</p>
                    <h2 class="mt-4 font-serif text-3xl font-semibold leading-tight tracking-tight text-slate-950 sm:text-4xl">Contanos qué tipo de propiedad necesitás.</h2>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-emerald-950/80">Podemos orientarte sobre las publicaciones actuales y responder tus dudas.</p>
                </div>

                <a href="{{ $contactUrl }}" @if ($opensExternally) target="_blank" rel="noopener noreferrer" @endif class="inline-flex w-fit items-center justify-center gap-3 rounded-full bg-slate-950 px-7 py-4 text-sm font-black text-white shadow-xl shadow-emerald-900/20 transition hover:bg-white hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-4 focus:ring-offset-emerald-400">
                    Iniciar una consulta
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                </a>
            </div>
        </section>
    </main>

    @include('public.partials.footer')
@endsection
