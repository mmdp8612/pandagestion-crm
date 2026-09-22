@extends('layouts.admin')

@section('title', 'Bienes Raíces')

@section('breadcrumb')
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">Bienes Raíces</li>
@endsection

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Administración</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Bienes Raíces</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Administrá las propiedades y accedé rápidamente a sus datos principales, imágenes y estado.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex w-fit items-center rounded-full bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700">
                {{ $properties->total() }} {{ $properties->total() === 1 ? 'propiedad' : 'propiedades' }}
            </span>
            <a href="{{ route('admin.properties.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Nueva propiedad
            </a>
        </div>
    </div>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="properties-filters-title">
        <div class="flex flex-col gap-1">
            <h2 id="properties-filters-title" class="font-bold text-slate-950">Buscar y filtrar</h2>
            <p class="text-sm text-slate-500">Combiná los criterios para encontrar rápidamente una propiedad.</p>
        </div>

        <form method="GET" action="{{ route('admin.properties.index') }}" class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-12 xl:items-end">
            <div class="md:col-span-2 xl:col-span-4">
                <label for="buscar" class="block text-sm font-bold text-slate-700">Código o ubicación</label>
                <div class="relative mt-2">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    <input
                        id="buscar"
                        name="buscar"
                        type="search"
                        value="{{ $filters['buscar'] ?? '' }}"
                        maxlength="120"
                        placeholder="Código, domicilio, barrio o localidad"
                        class="block w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                    >
                </div>
                @error('buscar')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="xl:col-span-2">
                <label for="tipologia" class="block text-sm font-bold text-slate-700">Tipología</label>
                <select
                    id="tipologia"
                    name="tipologia"
                    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                >
                    <option value="">Todas</option>
                    @foreach ($typologies as $typology)
                        <option value="{{ $typology->IdTipologia }}" @selected(($filters['tipologia'] ?? '') === $typology->IdTipologia)>
                            {{ $typology->Descrip }}{{ $typology->Hab ? '' : ' (deshabilitada)' }}
                        </option>
                    @endforeach
                </select>
                @error('tipologia')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="xl:col-span-2">
                <label for="comercializacion" class="block text-sm font-bold text-slate-700">Comercialización</label>
                <select
                    id="comercializacion"
                    name="comercializacion"
                    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                >
                    <option value="">Todas</option>
                    @foreach ($commercializations as $commercialization)
                        <option value="{{ $commercialization->IdComercializacion }}" @selected(($filters['comercializacion'] ?? '') === $commercialization->IdComercializacion)>
                            {{ $commercialization->Descrip }}{{ $commercialization->Hab ? '' : ' (deshabilitada)' }}
                        </option>
                    @endforeach
                </select>
                @error('comercializacion')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="xl:col-span-2">
                <label for="estado" class="block text-sm font-bold text-slate-700">Estado</label>
                <select
                    id="estado"
                    name="estado"
                    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                >
                    <option value="">Todos</option>
                    <option value="habilitadas" @selected(($filters['estado'] ?? '') === 'habilitadas')>Habilitadas</option>
                    <option value="deshabilitadas" @selected(($filters['estado'] ?? '') === 'deshabilitadas')>Deshabilitadas</option>
                </select>
                @error('estado')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="xl:col-span-2">
                <label for="destacada" class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                    <input
                        id="destacada"
                        name="destacada"
                        type="checkbox"
                        value="1"
                        @checked(($filters['destacada'] ?? null) === '1')
                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                    >
                    Solo destacadas
                </label>
                @error('destacada')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap justify-end gap-3 md:col-span-2 xl:col-span-12">
                <button type="submit" class="inline-flex flex-1 justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-2 sm:flex-none">
                    Aplicar filtros
                </button>
                @if ($hasFilters)
                    <a href="{{ route('admin.properties.index') }}" class="inline-flex flex-1 justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 sm:flex-none">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="properties-table-title">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 id="properties-table-title" class="font-bold text-slate-950">Propiedades cargadas</h2>
        </div>

        <div>
            <table class="w-full table-fixed divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="w-24 px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:w-32 sm:px-4">Imagen</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-4">Propiedad</th>
                        <th scope="col" class="hidden w-[22%] px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 2xl:table-cell 2xl:px-4">Características</th>
                        <th scope="col" class="hidden w-[20%] px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 xl:table-cell xl:px-4">Comercialización</th>
                        <th scope="col" class="hidden w-36 px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:table-cell sm:px-4">Estado</th>
                        <th scope="col" class="w-[148px] px-2 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 sm:w-[156px] sm:px-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($properties as $property)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-3 py-3 align-middle sm:px-4">
                                <a href="{{ route('admin.properties.images.index', $property->id) }}" class="group block h-16 w-full overflow-hidden rounded-xl bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" title="Administrar imágenes de {{ $property->Codigo }}">
                                    @if ($property->ImagenPrincipal)
                                        <img
                                            src="{{ Storage::disk('public')->url($property->ImagenPrincipal) }}"
                                            alt="Portada de la propiedad {{ $property->Codigo }}"
                                            class="h-full w-full object-cover transition group-hover:scale-105"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="flex h-full flex-col items-center justify-center gap-1 text-slate-400">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                            </svg>
                                            <span class="text-[10px] font-semibold">Sin imagen</span>
                                        </span>
                                    @endif
                                </a>
                            </td>

                            <td class="px-3 py-3 align-middle text-sm text-slate-700 sm:px-4">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <a href="{{ route('admin.properties.show', $property->id) }}" class="font-mono font-bold text-slate-950 transition hover:text-emerald-700 hover:underline">
                                            {{ $property->Codigo }}
                                        </a>
                                        @if ($property->Destacada)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0L7.589 6.598l-4.01.321c-.833.067-1.171 1.107-.536 1.651l3.055 2.616-.933 3.911c-.194.813.691 1.456 1.405 1.02L10 14.023l3.43 2.094c.714.436 1.599-.207 1.405-1.02l-.933-3.911 3.055-2.616c.635-.544.297-1.584-.536-1.651l-4.01-.321-1.543-3.714Z" clip-rule="evenodd" />
                                                </svg>
                                                Destacada
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 truncate font-semibold text-slate-900" title="{{ $property->Calle }}{{ $property->Numero ? ' '.$property->Numero : '' }}">
                                        {{ $property->Calle }}{{ $property->Numero ? ' '.$property->Numero : '' }}
                                        @if ($property->Piso)
                                            · Piso {{ $property->Piso }}
                                        @endif
                                    </p>

                                    <p class="mt-0.5 truncate text-xs text-slate-500" title="{{ implode(', ', array_filter([$property->Barrio, $property->Localidad, $property->Provincia])) }}">
                                        {{ implode(', ', array_filter([$property->Barrio, $property->Localidad, $property->Provincia])) ?: 'Ubicación sin completar' }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-medium text-slate-600">
                                        {{ implode(' · ', array_filter([
                                            $property->TipologiaDescripcion ?? $property->IdTipologia,
                                            $property->UsoDescripcion ?? $property->IdUso,
                                        ])) ?: 'Sin clasificar' }}
                                    </p>
                                </div>
                            </td>

                            <td class="hidden px-3 py-3 align-middle text-sm text-slate-700 2xl:table-cell 2xl:px-4">
                                @php
                                    $surfaces = array_filter([
                                        $property->SupCubiertaPropia !== null ? number_format((float) $property->SupCubiertaPropia, 2, ',', '.').' m² cubiertos' : null,
                                        $property->SupTerreno !== null ? number_format((float) $property->SupTerreno, 2, ',', '.').' m² terreno' : null,
                                    ]);
                                    $distribution = array_filter([
                                        $property->Ambientes !== null ? $property->Ambientes.' ambientes' : null,
                                        $property->Dormitorios !== null ? $property->Dormitorios.' dormitorios' : null,
                                        $property->Sanitarios !== null ? $property->Sanitarios.' baños' : null,
                                    ]);
                                @endphp

                                @if ($surfaces || $distribution)
                                    @if ($surfaces)
                                        <p class="font-semibold text-slate-900">{{ implode(' · ', $surfaces) }}</p>
                                    @endif
                                    @if ($distribution)
                                        <p @class(['text-xs text-slate-500', 'mt-1' => $surfaces])>{{ implode(' · ', $distribution) }}</p>
                                    @endif
                                @else
                                    <span class="text-slate-500">Sin completar</span>
                                @endif
                            </td>

                            <td class="hidden px-3 py-3 align-middle text-sm text-slate-700 xl:table-cell xl:px-4">
                                @php
                                    $saleCurrency = $property->MonedaVentaSimbolo ?? $property->MonedaVentaDescripcion ?? ($property->idTipoMonedaVta !== null ? '#'.$property->idTipoMonedaVta : 'Sin moneda');
                                    $rentCurrency = $property->MonedaAlquilerSimbolo ?? $property->MonedaAlquilerDescripcion ?? ($property->idTipoMonedaAlq !== null ? '#'.$property->idTipoMonedaAlq : 'Sin moneda');
                                @endphp

                                @if ($property->IdComercializacion || $property->ImporteVta !== null || $property->ImporteAlq !== null)
                                    @if ($property->IdComercializacion)
                                        <p class="font-semibold text-slate-900">{{ $property->ComercializacionDescripcion ?? $property->IdComercializacion }}</p>
                                    @endif
                                    @if ($property->ImporteVta !== null)
                                        <p @class(['text-xs text-slate-500', 'mt-1' => $property->IdComercializacion])>
                                            Venta: {{ $saleCurrency }} {{ number_format((float) $property->ImporteVta, 2, ',', '.') }}
                                        </p>
                                    @endif
                                    @if ($property->ImporteAlq !== null)
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            Alquiler: {{ $rentCurrency }} {{ number_format((float) $property->ImporteAlq, 2, ',', '.') }}
                                        </p>
                                    @endif
                                @else
                                    <span class="text-slate-500">Sin completar</span>
                                @endif
                            </td>

                            <td class="hidden px-3 py-3 align-middle sm:table-cell sm:px-4">
                                @if ($property->Hab)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                        Habilitada
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                                        Deshabilitada
                                    </span>
                                @endif

                                @if ($property->TieneVideo)
                                    <span class="mt-1.5 flex w-fit items-center gap-1 rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-bold text-sky-700">
                                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M6.3 3.75A2.55 2.55 0 0 0 3.75 6.3v7.4a2.55 2.55 0 0 0 2.55 2.55h7.4a2.55 2.55 0 0 0 2.55-2.55v-.493l1.47 1.05A.8.8 0 0 0 19 13.606V6.394a.8.8 0 0 0-1.28-.651l-1.47 1.05V6.3a2.55 2.55 0 0 0-2.55-2.55H6.3Z" />
                                        </svg>
                                        Video
                                    </span>
                                @endif

                                <p class="mt-1.5 text-[11px] text-slate-500">Actualizada {{ \Illuminate\Support\Carbon::parse($property->updated_at)->format('d/m/Y') }}</p>
                            </td>

                            <td class="px-2 py-3 align-middle sm:px-3">
                                <div class="flex items-center justify-end gap-0.5">
                                    <a
                                        href="{{ route('admin.properties.show', $property->id) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-600 focus:ring-offset-1"
                                        title="Ver ficha"
                                        aria-label="Ver la ficha de la propiedad {{ $property->Codigo }}"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                                            <circle cx="12" cy="12" r="2.75" />
                                        </svg>
                                        <span class="sr-only">Ver ficha</span>
                                    </a>

                                    <a
                                        href="{{ route('admin.properties.images.index', $property->id) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-sky-700 transition hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-1"
                                        title="Administrar imágenes"
                                        aria-label="Administrar las imágenes de la propiedad {{ $property->Codigo }}"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Z" />
                                        </svg>
                                        <span class="sr-only">Administrar imágenes</span>
                                    </a>

                                    <a
                                        href="{{ route('admin.properties.edit', $property->id) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1"
                                        title="Editar propiedad"
                                        aria-label="Editar la propiedad {{ $property->Codigo }}"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L18.55 2.8M16.862 4.487 19.5 7.125" />
                                        </svg>
                                        <span class="sr-only">Editar propiedad</span>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.properties.status.update', $property->id) }}"
                                        class="inline-flex"
                                        data-confirm
                                        data-confirm-title="{{ $property->Hab ? '¿Deshabilitar' : '¿Habilitar' }} {{ $property->Codigo }}?"
                                        data-confirm-text="{{ $property->Hab ? 'La propiedad dejará de estar disponible para su uso en el sistema.' : 'La propiedad volverá a estar disponible en el sistema.' }}"
                                        data-confirm-button="{{ $property->Hab ? 'Sí, deshabilitar' : 'Sí, habilitar' }}"
                                        data-cancel-button="Cancelar"
                                        data-confirm-icon="{{ $property->Hab ? 'warning' : 'question' }}"
                                        data-confirm-variant="{{ $property->Hab ? 'danger' : 'success' }}"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="hab" value="{{ $property->Hab ? 0 : 1 }}">
                                        <button
                                            type="submit"
                                            @class([
                                                'inline-flex h-8 w-8 items-center justify-center rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-1',
                                                'text-amber-600 hover:bg-amber-50 focus:ring-amber-500' => $property->Hab,
                                                'text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-600' => ! $property->Hab,
                                            ])
                                            title="{{ $property->Hab ? 'Deshabilitar propiedad' : 'Habilitar propiedad' }}"
                                            aria-label="{{ $property->Hab ? 'Deshabilitar' : 'Habilitar' }} la propiedad {{ $property->Codigo }}"
                                        >
                                            @if ($property->Hab)
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75a8.25 8.25 0 1 0 8.25 8.25A8.25 8.25 0 0 0 12 3.75Zm0 4.5v7.5" />
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            @endif
                                            <span class="sr-only">{{ $property->Hab ? 'Deshabilitar' : 'Habilitar' }} propiedad</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                @if ($hasFilters)
                                    <p class="font-semibold text-slate-700">No hay propiedades que coincidan con los filtros.</p>
                                    <p class="mt-1 text-sm text-slate-500">Probá cambiar algún criterio o limpiá la búsqueda.</p>
                                    <a href="{{ route('admin.properties.index') }}" class="mt-3 inline-flex text-sm font-bold text-emerald-700 hover:underline">Limpiar filtros</a>
                                @else
                                    <p class="font-semibold text-slate-700">Todavía no hay propiedades cargadas.</p>
                                    <p class="mt-1 text-sm text-slate-500">Creá la primera para comenzar a completar su información.</p>
                                    <a href="{{ route('admin.properties.create') }}" class="mt-3 inline-flex text-sm font-bold text-emerald-700 hover:underline">Crear la primera propiedad</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($properties->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                {{ $properties->links() }}
            </div>
        @endif
    </section>
@endsection
