@extends('layouts.public')

@php
    $catalogPage = $properties->currentPage();
    $catalogTitle = 'Propiedades disponibles'.($catalogPage > 1 && ! $hasFilters ? ' - Página '.$catalogPage : '').' | '.$brandName;
    $catalogDescription = 'Explorá y filtrá propiedades en venta y alquiler por ubicación, operación, tipología, ambientes y superficie.';
    $catalogCanonicalUrl = \App\Support\PublicUrl::route(
        'public.properties.index',
        ! $hasFilters && $catalogPage > 1 ? ['page' => $catalogPage] : []
    );
    $catalogSocialImage = \App\Support\PublicUrl::storage($agency?->Logo);
    $catalogItems = $properties->getCollection()
        ->values()
        ->map(fn ($property, int $index): array => [
            '@type' => 'ListItem',
            'position' => (($catalogPage - 1) * $properties->perPage()) + $index + 1,
            'url' => \App\Support\PublicUrl::route('public.properties.show', ['slug' => $property->Slug]),
            'name' => trim(($property->TipologiaDescripcion ?: 'Propiedad').(($property->Barrio ?: $property->Localidad) ? ' en '.($property->Barrio ?: $property->Localidad) : '')),
        ])
        ->all();
    $catalogStructuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $catalogCanonicalUrl.'#catalog',
        'url' => $catalogCanonicalUrl,
        'name' => $catalogTitle,
        'description' => $catalogDescription,
        'inLanguage' => 'es-AR',
        'isPartOf' => [
            '@type' => 'WebSite',
            '@id' => \App\Support\PublicUrl::route('home').'#website',
        ],
        'publisher' => [
            '@type' => $agency ? 'RealEstateAgent' : 'Organization',
            'name' => $brandName,
            'url' => \App\Support\PublicUrl::route('home'),
        ],
        'mainEntity' => [
            '@type' => 'ItemList',
            'numberOfItems' => $properties->total(),
            'itemListElement' => $catalogItems,
        ],
    ];
    $catalogStructuredDataJson = json_encode(
        $catalogStructuredData,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
    );
@endphp

@section('title', $catalogTitle)
@section('meta_description', $catalogDescription)
@section('canonical_url', $catalogCanonicalUrl)
@section('meta_robots', $hasFilters ? 'noindex,follow,max-image-preview:large' : 'index,follow,max-image-preview:large')
@if ($catalogSocialImage)
    @section('meta_image', $catalogSocialImage)
    @section('meta_image_alt', 'Logo de '.$brandName)
@endif

@unless ($hasFilters)
    @push('structured_data')
        <script type="application/ld+json">{!! $catalogStructuredDataJson !!}</script>
    @endpush
@endunless

@section('content')
    @include('public.partials.header')

    <main class="bg-[#f4f6f3]">
        <section class="relative overflow-hidden bg-[#123a34] text-white">
            <div class="pointer-events-none absolute inset-0 opacity-25" aria-hidden="true" style="background-image: radial-gradient(circle at 12% 20%, rgba(110,231,183,.42), transparent 26%), radial-gradient(circle at 88% 65%, rgba(125,211,252,.23), transparent 32%);"></div>
            <div class="relative mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-18 lg:px-10">
                <nav class="flex items-center gap-2 text-xs font-bold text-emerald-100/70" aria-label="Migas de pan">
                    <a href="{{ route('home') }}" class="transition hover:text-white">Inicio</a>
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
                    <span class="text-white">Propiedades</span>
                </nav>
                <div class="mt-7 max-w-3xl">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-200">Inventario publicado</p>
                    <h1 class="mt-4 font-serif text-4xl font-semibold tracking-tight sm:text-5xl">Encontrá tu próxima propiedad.</h1>
                    <p class="mt-5 max-w-2xl text-base leading-8 text-emerald-50/75">Filtrá el catálogo, cambiá el orden de los resultados y entrá a cada publicación para conocer todos sus detalles.</p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14 lg:px-10">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-800" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid items-start gap-8 lg:grid-cols-[19rem_minmax(0,1fr)] xl:gap-10">
                <aside class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" aria-label="Filtros de propiedades" data-public-filter-sidebar>
                    <button
                        type="button"
                        class="sticky top-24 z-20 flex w-full items-center justify-between gap-3 rounded-2xl bg-slate-950 px-4 py-3 text-left text-white shadow-lg shadow-slate-950/10 transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-200 lg:hidden"
                        aria-controls="public-property-filters-panel"
                        aria-expanded="false"
                        data-public-filter-toggle
                    >
                        <span class="flex min-w-0 items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10M10 18h4" /></svg>
                            <span class="min-w-0">
                                <span class="block text-sm font-black" data-public-filter-toggle-label>Mostrar filtros</span>
                                <span class="block truncate text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-300">{{ $properties->total() }} {{ $properties->total() === 1 ? 'resultado' : 'resultados' }}</span>
                            </span>
                        </span>
                        <span class="flex shrink-0 items-center gap-2">
                            @if ($activeFilters !== [])
                                <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-emerald-300 px-2 py-1 text-[10px] font-black text-emerald-950" aria-label="{{ count($activeFilters) }} filtros activos" data-public-active-filter-count>{{ count($activeFilters) }}</span>
                            @endif
                            <svg class="h-4 w-4 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" data-public-filter-toggle-icon><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                        </span>
                    </button>

                    <div id="public-property-filters-panel" class="hidden lg:block" data-public-filter-panel>
                        <div class="hidden items-center justify-between gap-4 border-b border-slate-100 pb-4 lg:flex">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">Afiná la búsqueda</p>
                                <h2 id="filters-title" class="mt-1 text-xl font-black text-slate-950">Filtros</h2>
                                <p class="mt-1 text-[11px] leading-4 text-slate-500">Los cambios se aplican automáticamente.</p>
                            </div>
                            @if ($hasFilters)
                                <a href="{{ route('public.properties.index') }}" class="text-xs font-black text-emerald-700 underline decoration-emerald-200 underline-offset-4 hover:text-emerald-900">Limpiar</a>
                            @endif
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-4 border-t border-slate-100 pt-4 lg:hidden">
                            <p class="text-[11px] leading-4 text-slate-500">Los cambios se aplican automáticamente.</p>
                            @if ($hasFilters)
                                <a href="{{ route('public.properties.index') }}" class="shrink-0 text-xs font-black text-emerald-700 underline decoration-emerald-200 underline-offset-4 hover:text-emerald-900">Limpiar</a>
                            @endif
                        </div>

                        <form action="{{ route('public.properties.index') }}" method="GET" class="mt-4 lg:mt-5" data-public-property-filters>
                        <label class="block">
                            <span class="text-xs font-black uppercase tracking-[0.12em] text-slate-600">Buscar zona</span>
                            <span class="relative mt-2 block">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6.75-6.37 6.75-12A6.75 6.75 0 1 0 5.25 9C5.25 14.63 12 21 12 21Z" /><circle cx="12" cy="9" r="2.25" /></svg>
                                <input type="search" name="ubicacion" value="{{ $filters['ubicacion'] ?? '' }}" maxlength="120" placeholder="Barrio, localidad o calle" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-normal placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                            </span>
                            <span class="mt-1.5 block text-[10px] leading-4 text-slate-400">Escribí y presioná Enter para buscar.</span>
                        </label>

                        @foreach ($filterGroups as $group)
                            @php
                                $selectedValues = array_map('strval', $filters[$group['key']] ?? []);
                            @endphp
                            <fieldset class="mt-5 border-t border-slate-100 pt-5">
                                <legend class="text-xs font-black uppercase tracking-[0.12em] text-slate-700">{{ $group['label'] }}</legend>
                                <div class="public-filter-options mt-3 max-h-72 space-y-2.5 overflow-y-auto overscroll-contain pr-2" data-filter-options-scroll="{{ $group['key'] }}">
                                    @foreach ($group['options'] as $option)
                                        @php($optionId = 'filter-'.$group['key'].'-'.$loop->index)
                                        <label for="{{ $optionId }}" class="group flex cursor-pointer items-start gap-2.5 text-sm text-slate-600">
                                            <input
                                                id="{{ $optionId }}"
                                                type="checkbox"
                                                name="{{ $group['key'] }}[]"
                                                value="{{ $option['value'] }}"
                                                @checked(in_array((string) $option['value'], $selectedValues, true))
                                                data-auto-submit-filter
                                                class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded border-slate-300 text-emerald-700 accent-emerald-700 focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1"
                                            >
                                            <span class="min-w-0 flex-1 leading-5 transition group-hover:text-slate-950">{{ $option['label'] }}</span>
                                            <span class="shrink-0 pt-0.5 text-[10px] font-bold tabular-nums text-slate-400" data-filter-option-count="{{ $group['key'] }}:{{ $option['value'] }}">{{ number_format($option['count'], 0, ',', '.') }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach

                        <label class="mt-5 block border-t border-slate-100 pt-5">
                            <span class="text-xs font-black uppercase tracking-[0.12em] text-slate-700">Ordenar resultados</span>
                            <select name="orden" data-auto-submit-filter class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                                <option value="recientes" @selected(($filters['orden'] ?? 'recientes') === 'recientes')>Más recientes</option>
                                <option value="antiguas" @selected(($filters['orden'] ?? null) === 'antiguas')>Más antiguas</option>
                                <option value="superficie_desc" @selected(($filters['orden'] ?? null) === 'superficie_desc')>Mayor superficie</option>
                                <option value="superficie_asc" @selected(($filters['orden'] ?? null) === 'superficie_asc')>Menor superficie</option>
                            </select>
                        </label>

                            <p class="sr-only" aria-live="polite" data-public-filter-status></p>
                        </form>
                    </div>
                </aside>

                <div class="min-w-0 scroll-mt-24 focus:outline-none" tabindex="-1" data-public-property-results>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Resultados</p>
                            <h2 id="properties-results-title" class="mt-2 scroll-mt-24 text-2xl font-black tracking-tight text-slate-950">
                                {{ $properties->total() }} {{ $properties->total() === 1 ? 'propiedad encontrada' : 'propiedades encontradas' }}
                            </h2>
                            @if ($properties->total() > 0)
                                <p class="mt-1 text-sm text-slate-500">Mostrando {{ $properties->firstItem() }}–{{ $properties->lastItem() }} · Página {{ $properties->currentPage() }} de {{ $properties->lastPage() }}</p>
                            @endif
                        </div>

                        @if ($hasFilters)
                            <a href="{{ route('public.properties.index') }}" class="w-fit text-sm font-black text-emerald-700 underline decoration-emerald-300 underline-offset-4 transition hover:text-emerald-900">Quitar todos los filtros</a>
                        @endif
                    </div>

                    @if ($activeFilters !== [])
                        <div class="mt-5 flex flex-wrap gap-2" aria-label="Filtros activos">
                            @foreach ($activeFilters as $activeFilter)
                                <a href="{{ $activeFilter['url'] }}" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-800 transition hover:border-emerald-300 hover:bg-emerald-100" aria-label="Quitar filtro {{ $activeFilter['label'] }}">
                                    {{ $activeFilter['label'] }}
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($properties->isNotEmpty())
                        <div class="mt-7 grid gap-6 md:grid-cols-2">
                            @foreach ($properties as $property)
                                @include('public.properties._card', ['property' => $property])
                            @endforeach
                        </div>

                        @if ($properties->hasPages())
                            <div class="mt-10">
                                {{ $properties->onEachSide(1)->links() }}
                            </div>
                        @endif
                    @else
                        <div class="mt-7 rounded-3xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm sm:px-10">
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path stroke-linecap="round" d="m20 20-4-4"></path></svg>
                            </span>
                            <h3 class="mt-5 text-xl font-black text-slate-950">No encontramos propiedades con esos criterios</h3>
                            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-600">Probá con una ubicación más amplia o quitá alguno de los filtros para ver más alternativas.</p>
                            @if ($hasFilters)
                                <a href="{{ route('public.properties.index') }}" class="mt-6 inline-flex items-center justify-center rounded-full bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">Ver todas las propiedades</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>

    @include('public.partials.footer')
@endsection
