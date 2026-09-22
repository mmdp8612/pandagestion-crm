@extends('layouts.admin')

@section('title', 'Ficha de '.$property->Codigo)

@section('breadcrumb')
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li><a href="{{ route('admin.properties.index') }}" class="transition hover:text-slate-800">Bienes Raíces</a></li>
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">{{ $property->Codigo }}</li>
@endsection

@section('content')
    @php
        $hasValue = static fn (mixed $value): bool => $value !== null && $value !== '';
        $catalogValue = static fn (mixed $description, mixed $code): string => $description ?: ($hasValue($code) ? (string) $code : 'Sin completar');
        $decimalValue = static fn (mixed $value, string $unit): string => $value !== null
            ? number_format((float) $value, 2, ',', '.').' '.$unit
            : 'Sin completar';
        $integerValue = static fn (mixed $value): string => $value !== null ? number_format((int) $value, 0, ',', '.') : 'Sin completar';
        $priceValue = static function (mixed $amount, mixed $symbol, mixed $currency, mixed $currencyId): string {
            if ($amount === null) {
                return 'Sin completar';
            }

            $currencyLabel = $symbol ?: ($currency ?: ($currencyId !== null ? '#'.$currencyId : ''));

            return trim($currencyLabel.' '.number_format((float) $amount, 2, ',', '.'));
        };
        $streetAddress = trim($property->Calle.' '.($property->Numero ?? ''));
        $unitDetails = array_filter([
            $hasValue($property->Piso) ? 'Piso '.$property->Piso : null,
            $hasValue($property->Torre) ? 'Torre '.$property->Torre : null,
        ], $hasValue);
        $locationDetails = array_filter([
            $property->Barrio,
            $property->Localidad,
            $property->Partido,
            $property->Provincia,
        ], $hasValue);
    @endphp

    <div class="max-w-7xl">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Bienes Raíces</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-950">Ficha de {{ $property->Codigo }}</h1>
                    @if ($property->Hab)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                            Habilitada
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-bold text-slate-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                            Deshabilitada
                        </span>
                    @endif
                    @if ($property->Destacada)
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0L7.589 6.598l-4.01.321c-.833.067-1.171 1.107-.536 1.651l3.055 2.616-.933 3.911c-.194.813.691 1.456 1.405 1.02L10 14.023l3.43 2.094c.714.436 1.599-.207 1.405-1.02l-.933-3.911 3.055-2.616c.635-.544.297-1.584-.536-1.651l-4.01-.321-1.543-3.714Z" clip-rule="evenodd" />
                            </svg>
                            Destacada
                        </span>
                    @endif
                </div>
                <p class="mt-2 text-sm leading-6 text-slate-600">Consultá toda la información de la propiedad sin modificar sus datos.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.properties.index') }}" class="inline-flex flex-1 justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 sm:flex-none">
                    Volver al listado
                </a>
                <a href="{{ route('admin.properties.images.index', $property->id) }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm font-bold text-sky-700 transition hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2 sm:flex-none">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                    </svg>
                    Imágenes
                </a>
                <a href="{{ route('admin.properties.edit', $property->id) }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 sm:flex-none">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L18.55 2.8M16.862 4.487 19.5 7.125" />
                    </svg>
                    Editar
                </a>
            </div>
        </div>

        <section class="mt-6 grid overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:grid-cols-[minmax(0,1.55fr)_minmax(20rem,0.8fr)]" aria-labelledby="property-summary-title">
            <div class="min-h-72 bg-slate-100 lg:min-h-[28rem]">
                @if ($coverImage)
                    <a href="{{ Storage::disk('public')->url($coverImage->Archivo) }}" target="_blank" rel="noopener noreferrer" class="group block h-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-600" title="Abrir portada en tamaño completo">
                        <img src="{{ Storage::disk('public')->url($coverImage->Archivo) }}" alt="Portada de la propiedad {{ $property->Codigo }}" class="h-full min-h-72 w-full object-cover transition group-hover:brightness-95 lg:min-h-[28rem]">
                    </a>
                @else
                    <div class="flex h-full min-h-72 flex-col items-center justify-center gap-3 px-6 text-center text-slate-400 lg:min-h-[28rem]">
                        <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                        </svg>
                        <div>
                            <p class="font-bold text-slate-600">Sin imágenes</p>
                            <p class="mt-1 text-sm">Podés cargarlas desde la administración de la galería.</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex flex-col justify-center p-6 sm:p-8">
                <h2 id="property-summary-title" class="text-xl font-bold text-slate-950">{{ $streetAddress }}</h2>
                @if ($unitDetails)
                    <p class="mt-1 text-sm font-semibold text-slate-600">{{ implode(' · ', $unitDetails) }}</p>
                @endif
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $locationDetails ? implode(', ', $locationDetails) : 'Ubicación sin completar' }}</p>

                <dl class="mt-6 divide-y divide-slate-100 border-y border-slate-100">
                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-sm text-slate-500">Tipología</dt>
                        <dd class="text-right text-sm font-bold text-slate-900">{{ $catalogValue($property->TipologiaDescripcion, $property->IdTipologia) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-sm text-slate-500">Uso</dt>
                        <dd class="text-right text-sm font-bold text-slate-900">{{ $catalogValue($property->UsoDescripcion, $property->IdUso) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-sm text-slate-500">Comercialización</dt>
                        <dd class="text-right text-sm font-bold text-slate-900">{{ $catalogValue($property->ComercializacionDescripcion, $property->IdComercializacion) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-sm text-slate-500">Imágenes</dt>
                        <dd class="text-right text-sm font-bold text-slate-900">{{ $images->count() }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="property-classification-title">
                <h2 id="property-classification-title" class="text-lg font-bold text-slate-950">Clasificación</h2>
                <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Tipo general</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->TipologiaGrupo ?: 'Sin completar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Tipología</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $catalogValue($property->TipologiaDescripcion, $property->IdTipologia) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Uso</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $catalogValue($property->UsoDescripcion, $property->IdUso) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Antigüedad</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $catalogValue($property->AntiguedadDescripcion, $property->Antiguedad) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Orientación</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $catalogValue($property->OrientacionDescripcion, $property->IdOrientacion) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Vista</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $catalogValue($property->VistaDescripcion, $property->IdVista) }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Cochera</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $catalogValue($property->CocheraDescripcion, $property->IdCochera) }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="property-commercial-title">
                <h2 id="property-commercial-title" class="text-lg font-bold text-slate-950">Comercialización</h2>
                <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Modalidad</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $catalogValue($property->ComercializacionDescripcion, $property->IdComercializacion) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Importe de venta</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-950">{{ $priceValue($property->ImporteVta, $property->MonedaVentaSimbolo, $property->MonedaVentaDescripcion, $property->idTipoMonedaVta) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Importe de alquiler</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-950">{{ $priceValue($property->ImporteAlq, $property->MonedaAlquilerSimbolo, $property->MonedaAlquilerDescripcion, $property->idTipoMonedaAlq) }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="property-features-title">
            <h2 id="property-features-title" class="text-lg font-bold text-slate-950">Características</h2>
            <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Superficie cubierta</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $decimalValue($property->SupCubiertaPropia, 'm²') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Superficie terreno</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $decimalValue($property->SupTerreno, 'm²') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Frente</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $decimalValue($property->Frente, 'm²') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Fondo</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $decimalValue($property->Fondo, 'm²') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Metros de fondo</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $decimalValue($property->MtsFondo, 'm') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Plantas</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $integerValue($property->Plantas) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Ambientes</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $integerValue($property->Ambientes) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Dormitorios</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $integerValue($property->Dormitorios) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Dormitorios en suite</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $integerValue($property->Suite) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Baños</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $integerValue($property->Sanitarios) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Líneas telefónicas</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $integerValue($property->LineasTel) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Luminosidad</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->Luminosidad ?: 'Sin completar' }}</dd>
                </div>
            </dl>
        </section>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="property-description-title">
            <h2 id="property-description-title" class="text-lg font-bold text-slate-950">Descripción</h2>
            @if ($property->Descrip)
                <p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $property->Descrip }}</p>
            @else
                <p class="mt-4 text-sm text-slate-500">Sin descripción.</p>
            @endif
        </section>

        @if ($video)
            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="property-video-title">
                <div class="flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <div>
                        <h2 id="property-video-title" class="text-lg font-bold text-slate-950">Video de la propiedad</h2>
                        <p class="mt-1 text-sm text-slate-500">Reproductor de {{ $video['label'] }}</p>
                    </div>
                    <a href="{{ $video['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-fit text-sm font-bold text-emerald-700 hover:underline">Abrir en {{ $video['label'] }}</a>
                </div>
                <div class="aspect-video border-t border-slate-200 bg-slate-950">
                    <iframe
                        src="{{ $video['embed'] }}"
                        class="h-full w-full border-0"
                        title="Video de la propiedad {{ $property->Codigo }} en {{ $video['label'] }}"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>
            </section>
        @endif

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="property-location-title">
            <div class="p-6 sm:p-8">
                <h2 id="property-location-title" class="text-lg font-bold text-slate-950">Ubicación</h2>
                <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Domicilio</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $streetAddress }}{{ $unitDetails ? ' · '.implode(' · ', $unitDetails) : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Barrio</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->Barrio ?: 'Sin completar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Localidad</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->Localidad ?: 'Sin completar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Partido</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->Partido ?: 'Sin completar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Provincia</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->Provincia ?: 'Sin completar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Código postal</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->CodigoPostal ?: 'Sin completar' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Coordenadas</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                            @if ($mapLinks)
                                {{ $property->Latitud }}, {{ $property->Longitud }}
                            @else
                                Sin completar
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            @if ($mapLinks)
                <div class="border-t border-slate-200">
                    <div class="flex flex-col gap-2 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                        <p class="text-xs text-slate-500">Mapa © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer" class="font-semibold underline hover:text-slate-700">OpenStreetMap contributors</a></p>
                        <a href="{{ $mapLinks['full'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700 hover:text-emerald-800 hover:underline">
                            Abrir mapa completo
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18m0 0v4.5M18 6l-7.5 7.5M15 13.5V18H6V9h4.5" />
                            </svg>
                        </a>
                    </div>
                    <iframe src="{{ $mapLinks['embed'] }}" class="h-80 w-full border-0 lg:h-96" title="Mapa de la ubicación de la propiedad {{ $property->Codigo }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
            @endif
        </section>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="property-publication-title">
            <h2 id="property-publication-title" class="text-lg font-bold text-slate-950">Publicación y multimedia</h2>
            <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2 lg:col-span-4">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Slug</dt>
                    <dd class="mt-1 break-all font-mono text-sm font-semibold text-slate-900">{{ $property->Slug }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Estado</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->Hab ? 'Habilitada' : 'Deshabilitada' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Destacada</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->Destacada ? 'Sí' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Tiene fotos</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->TieneFoto ? 'Sí' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Tiene video</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $property->TieneVideo ? 'Sí' : 'No' }}</dd>
                </div>
                @if ($video)
                    <div class="sm:col-span-2 lg:col-span-4">
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">URL del video</dt>
                        <dd class="mt-1 break-all text-sm font-semibold"><a href="{{ $video['url'] }}" target="_blank" rel="noopener noreferrer" class="text-emerald-700 hover:underline">{{ $video['url'] }}</a></dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Creada</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($property->created_at)->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Última actualización</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($property->updated_at)->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </section>

        <section class="mt-6" aria-labelledby="property-gallery-title">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="property-gallery-title" class="text-lg font-bold text-slate-950">Galería</h2>
                    <p class="mt-1 text-sm text-slate-500">Las imágenes se muestran en el orden configurado para la propiedad.</p>
                </div>
                <a href="{{ route('admin.properties.images.index', $property->id) }}" class="inline-flex w-fit text-sm font-bold text-emerald-700 hover:underline">Administrar galería</a>
            </div>

            @if ($images->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center shadow-sm">
                    <p class="font-semibold text-slate-700">Todavía no hay imágenes habilitadas.</p>
                    <p class="mt-1 text-sm text-slate-500">Cargá fotografías desde la administración de la galería.</p>
                </div>
            @else
                <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($images as $image)
                        <a href="{{ Storage::disk('public')->url($image->Archivo) }}" target="_blank" rel="noopener noreferrer" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" title="Abrir imagen {{ $loop->iteration }} en tamaño completo">
                            <div class="relative aspect-[4/3] bg-slate-100">
                                <img src="{{ Storage::disk('public')->url($image->Archivo) }}" alt="Imagen {{ $loop->iteration }} de la propiedad {{ $property->Codigo }}" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy">
                                @if ($image->Portada)
                                    <span class="absolute left-3 top-3 inline-flex items-center rounded-full bg-emerald-600 px-2.5 py-1 text-xs font-bold text-white shadow-sm">Portada</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-3 p-4">
                                <span class="text-xs font-semibold text-slate-500">Posición {{ $loop->iteration }}</span>
                                <span class="text-xs font-bold text-emerald-700">Ver completa</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
