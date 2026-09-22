@extends('layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">Dashboard</li>
@endsection

@section('content')
    @php
        $inquiryStatusClasses = [
            'nueva' => 'bg-sky-50 text-sky-700',
            'en_proceso' => 'bg-amber-50 text-amber-700',
            'respondida' => 'bg-emerald-50 text-emerald-700',
            'descartada' => 'bg-slate-100 text-slate-500',
        ];
        $inquiryStatusLabels = [
            'nueva' => 'Nueva',
            'en_proceso' => 'En proceso',
            'respondida' => 'Respondida',
            'descartada' => 'Descartada',
        ];
    @endphp

    <section class="overflow-hidden rounded-2xl bg-slate-950 px-6 py-7 text-white shadow-xl shadow-slate-300/40 sm:px-8 sm:py-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-widest text-emerald-400">Panel de administración</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Hola, {{ auth()->user()->name }}</h1>
                <p class="mt-3 max-w-2xl leading-7 text-slate-300">
                    @canany(['bienesraices', 'consultas'])
                        Este es el estado actual de tu actividad. Desde acá podés revisar las novedades y acceder a las tareas más frecuentes.
                    @else
                        Accedé rápidamente a los módulos habilitados para tu usuario y administrá tu cuenta.
                    @endcanany
                </p>
            </div>

            @can('bienesraices')
                <a href="{{ route('admin.properties.create') }}" class="inline-flex w-fit items-center gap-2 rounded-lg bg-emerald-400 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:ring-offset-2 focus:ring-offset-slate-950">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>
                    Nueva propiedad
                </a>
            @endcan
        </div>
    </section>

    @can('consultas')
        <section class="mt-6" aria-labelledby="inquiry-summary-title">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="inquiry-summary-title" class="text-lg font-bold text-slate-950">Seguimiento de consultas</h2>
                    <p class="mt-1 text-sm text-slate-600">Mensajes pendientes y contactos recibidos recientemente.</p>
                </div>
                <a href="{{ route('admin.inquiries.index') }}" class="mt-2 inline-flex w-fit items-center gap-1 text-sm font-bold text-emerald-700 transition hover:text-emerald-800 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 sm:mt-0">
                    Ver bandeja
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
                </a>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-[15rem_minmax(0,1fr)]">
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-1">
                    <a href="{{ route('admin.inquiries.index', ['estado' => 'nueva']) }}" aria-label="Consultas nuevas: {{ $inquiryStats['new'] }}" class="group rounded-2xl border border-sky-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-sky-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2" data-dashboard-inquiry-stat="new">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6M21 12a8.25 8.25 0 0 1-11.94 7.38L3 21l1.62-6.06A8.25 8.25 0 1 1 21 12Z" /></svg>
                        </span>
                        <p class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $inquiryStats['new'] }}</p>
                        <p class="mt-0.5 text-xs font-semibold text-slate-500">Nuevas</p>
                    </a>

                    <a href="{{ route('admin.inquiries.index', ['estado' => 'en_proceso']) }}" aria-label="Consultas en proceso: {{ $inquiryStats['in_progress'] }}" class="group rounded-2xl border border-amber-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2" data-dashboard-inquiry-stat="in-progress">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </span>
                        <p class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $inquiryStats['in_progress'] }}</p>
                        <p class="mt-0.5 text-xs font-semibold text-slate-500">En proceso</p>
                    </a>
                </div>

                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="latest-inquiries-title">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
                        <div>
                            <h3 id="latest-inquiries-title" class="font-bold text-slate-950">Consultas recientes</h3>
                            <p class="mt-1 text-sm text-slate-500">Los cinco contactos más recientes.</p>
                        </div>
                        <a href="{{ route('admin.inquiries.index') }}" class="shrink-0 text-sm font-bold text-emerald-700 transition hover:text-emerald-800 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Ver todas</a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse ($latestInquiries as $inquiry)
                            <a href="{{ route('admin.inquiries.show', $inquiry->id) }}" class="group flex items-center gap-3 px-5 py-3.5 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:px-6" data-dashboard-inquiry="{{ $inquiry->id }}">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-black uppercase text-white">{{ \Illuminate\Support\Str::substr($inquiry->Nombre, 0, 1) }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="truncate text-sm font-bold text-slate-900">{{ $inquiry->Nombre }}</span>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $inquiryStatusClasses[$inquiry->Estado] ?? 'bg-slate-100 text-slate-500' }}">{{ $inquiryStatusLabels[$inquiry->Estado] ?? $inquiry->Estado }}</span>
                                    </span>
                                    <span class="mt-0.5 block truncate text-xs text-slate-500">
                                        <span class="font-mono font-bold text-slate-600">{{ $inquiry->CodigoPropiedad }}</span>
                                        <span aria-hidden="true"> · </span>
                                        {{ \Illuminate\Support\Str::limit($inquiry->Mensaje, 80) }}
                                    </span>
                                </span>
                                <span class="hidden shrink-0 text-right sm:block">
                                    <span class="block text-[11px] font-semibold text-slate-400">{{ \Illuminate\Support\Carbon::parse($inquiry->created_at)->format('d/m/Y') }}</span>
                                    <span class="mt-0.5 block text-[11px] text-slate-400">{{ \Illuminate\Support\Carbon::parse($inquiry->created_at)->format('H:i') }}</span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
                            </a>
                        @empty
                            <div class="px-6 py-10 text-center">
                                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6M21 12a8.25 8.25 0 0 1-11.94 7.38L3 21l1.62-6.06A8.25 8.25 0 1 1 21 12Z" /></svg>
                                </span>
                                <h4 class="mt-3 font-bold text-slate-900">Todavía no hay consultas</h4>
                                <p class="mt-1 text-sm text-slate-500">Los mensajes enviados desde el portal aparecerán acá.</p>
                            </div>
                        @endforelse
                    </div>
                </article>
            </div>
        </section>
    @endcan

    @can('bienesraices')
        <section class="mt-6" aria-labelledby="property-summary-title">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="property-summary-title" class="text-lg font-bold text-slate-950">Resumen de propiedades</h2>
                    <p class="mt-1 text-sm text-slate-600">Indicadores generales de la cartera cargada.</p>
                </div>
                <a href="{{ route('admin.properties.index') }}" class="mt-2 inline-flex w-fit items-center gap-1 text-sm font-bold text-emerald-700 transition hover:text-emerald-800 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 sm:mt-0">
                    Ver todas
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <a href="{{ route('admin.properties.index') }}" aria-label="Total de propiedades: {{ $propertyStats['total'] }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" data-dashboard-stat="total">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700 transition group-hover:bg-slate-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.2 3.05a1.13 1.13 0 0 1 1.6 0L21.75 12M4.5 9.75v10.5h15V9.75M9 20.25v-6h6v6" />
                        </svg>
                    </span>
                    <p class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $propertyStats['total'] }}</p>
                    <p class="mt-0.5 text-xs font-semibold text-slate-500">Total</p>
                </a>

                <a href="{{ route('admin.properties.index', ['estado' => 'habilitadas']) }}" aria-label="Propiedades habilitadas: {{ $propertyStats['enabled'] }}" class="group rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" data-dashboard-stat="enabled">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                    </span>
                    <p class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $propertyStats['enabled'] }}</p>
                    <p class="mt-0.5 text-xs font-semibold text-slate-500">Habilitadas</p>
                </a>

                <a href="{{ route('admin.properties.index', ['estado' => 'deshabilitadas']) }}" aria-label="Propiedades deshabilitadas: {{ $propertyStats['disabled'] }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" data-dashboard-stat="disabled">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M8.5 12h7" />
                        </svg>
                    </span>
                    <p class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $propertyStats['disabled'] }}</p>
                    <p class="mt-0.5 text-xs font-semibold text-slate-500">Deshabilitadas</p>
                </a>

                <a href="{{ route('admin.properties.index', ['destacada' => '1']) }}" aria-label="Propiedades destacadas: {{ $propertyStats['featured'] }}" class="group rounded-2xl border border-amber-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" data-dashboard-stat="featured">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linejoin="round" d="m12 3 2.7 5.47 6.04.88-4.37 4.26 1.03 6.02L12 16.8l-5.4 2.83 1.03-6.02-4.37-4.26 6.04-.88L12 3Z" />
                        </svg>
                    </span>
                    <p class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $propertyStats['featured'] }}</p>
                    <p class="mt-0.5 text-xs font-semibold text-slate-500">Destacadas</p>
                </a>

                <article aria-label="Propiedades con fotos: {{ $propertyStats['with_photos'] }}" class="rounded-2xl border border-sky-200 bg-white p-4 shadow-sm" data-dashboard-stat="with-photos">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.91 2.91M3.75 19.5h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                        </svg>
                    </span>
                    <p class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $propertyStats['with_photos'] }}</p>
                    <p class="mt-0.5 text-xs font-semibold text-slate-500">Con fotos</p>
                </article>

                <article aria-label="Propiedades sin fotos: {{ $propertyStats['without_photos'] }}" class="rounded-2xl border border-rose-200 bg-white p-4 shadow-sm" data-dashboard-stat="without-photos">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.59l-3.17-3.18a2.25 2.25 0 0 0-3.18 0L2.25 9.39V18a1.5 1.5 0 0 0 1.5 1.5h15.64M14.25 14.25l1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.91 2.91V6a1.5 1.5 0 0 0-1.5-1.5H8.5" />
                        </svg>
                    </span>
                    <p class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $propertyStats['without_photos'] }}</p>
                    <p class="mt-0.5 text-xs font-semibold text-slate-500">Sin fotos</p>
                </article>
            </div>
        </section>
    @endcan

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        @can('bienesraices')
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="latest-properties-title">
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div>
                        <h2 id="latest-properties-title" class="font-bold text-slate-950">Últimas propiedades</h2>
                        <p class="mt-1 text-sm text-slate-500">Las cinco altas más recientes.</p>
                    </div>
                    <a href="{{ route('admin.properties.index') }}" class="shrink-0 text-sm font-bold text-emerald-700 transition hover:text-emerald-800 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Ver listado</a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($latestProperties as $property)
                        <a href="{{ route('admin.properties.show', $property->id) }}" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:px-6">
                            <span class="flex h-16 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                @if ($property->ImagenPrincipal)
                                    <img src="{{ Storage::disk('public')->url($property->ImagenPrincipal) }}" alt="Portada de la propiedad {{ $property->Codigo }}" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy">
                                @else
                                    <span class="flex h-full w-full flex-col items-center justify-center gap-1 text-slate-400">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.91 2.91M3.75 19.5h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                        </svg>
                                        <span class="text-[10px] font-semibold">Sin foto</span>
                                    </span>
                                @endif
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-sm font-bold text-slate-950">{{ $property->Codigo }}</span>
                                    @if ($property->Destacada)
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">Destacada</span>
                                    @endif
                                    <span @class([
                                        'rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                        'bg-emerald-50 text-emerald-700' => $property->Hab,
                                        'bg-slate-100 text-slate-500' => ! $property->Hab,
                                    ])>{{ $property->Hab ? 'Habilitada' : 'Deshabilitada' }}</span>
                                </span>
                                <span class="mt-1 block truncate text-sm font-semibold text-slate-800">
                                    {{ $property->Calle }}{{ $property->Numero ? ' '.$property->Numero : '' }}
                                </span>
                                <span class="mt-0.5 block truncate text-xs text-slate-500">
                                    {{ implode(' · ', array_filter([
                                        $property->TipologiaDescripcion,
                                        $property->ComercializacionDescripcion,
                                        $property->Barrio ?: $property->Localidad,
                                    ])) ?: 'Sin datos complementarios' }}
                                </span>
                            </span>

                            <svg class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </a>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.2 3.05a1.13 1.13 0 0 1 1.6 0L21.75 12M4.5 9.75v10.5h15V9.75M9 20.25v-6h6v6" />
                                </svg>
                            </span>
                            <h3 class="mt-4 font-bold text-slate-950">Todavía no hay propiedades</h3>
                            <p class="mt-1 text-sm text-slate-500">La primera alta aparecerá en este espacio.</p>
                            <a href="{{ route('admin.properties.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                                Cargar propiedad
                            </a>
                        </div>
                    @endforelse
                </div>
            </section>
        @else
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="dashboard-welcome-title">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </span>
                <h2 id="dashboard-welcome-title" class="mt-4 text-lg font-bold text-slate-950">Todo listo para trabajar</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Tu sesión está protegida y el panel muestra únicamente los módulos que tenés habilitados.</p>
            </section>
        @endcan

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="quick-access-title">
            <h2 id="quick-access-title" class="font-bold text-slate-950">Accesos rápidos</h2>
            <p class="mt-1 text-sm text-slate-500">Atajos según tus permisos.</p>

            <div class="mt-4 space-y-2">
                @can('bienesraices')
                    <a href="{{ route('admin.properties.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600" data-dashboard-shortcut="properties">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.2 3.05a1.13 1.13 0 0 1 1.6 0L21.75 12M4.5 9.75v10.5h15V9.75" />
                            </svg>
                        </span>
                        Gestionar propiedades
                    </a>
                @endcan

                @can('consultas')
                    <a href="{{ route('admin.inquiries.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600" data-dashboard-shortcut="inquiries">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6M21 12a8.25 8.25 0 0 1-11.94 7.38L3 21l1.62-6.06A8.25 8.25 0 1 1 21 12Z" /></svg>
                        </span>
                        Gestionar consultas
                        @if (($newInquiryCount ?? 0) > 0)
                            <span class="ml-auto rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-black text-sky-700">{{ $newInquiryCount > 99 ? '99+' : $newInquiryCount }}</span>
                        @endif
                    </a>
                @endcan

                @can('catalogo')
                    <a href="{{ route('admin.catalogs.antiquities.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600" data-dashboard-shortcut="catalogs">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75a2.25 2.25 0 0 1-2.25-2.25V5.25ZM8.25 7.5h7.5M8.25 12h7.5M8.25 16.5h4.5" />
                            </svg>
                        </span>
                        Administrar catálogos
                    </a>
                @endcan

                @can('configuracion')
                    <a href="{{ route('admin.real-estate-agency.edit') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600" data-dashboard-shortcut="agency">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M5.25 21V6.75h13.5V21M8.25 10.5h1.5m4.5 0h1.5m-7.5 3.75h1.5m4.5 0h1.5M9.75 21v-3.75h4.5V21M4.5 6.75 12 3l7.5 3.75" />
                            </svg>
                        </span>
                        Datos de la inmobiliaria
                    </a>
                @endcan

                @can('usuarios')
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600" data-dashboard-shortcut="users">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0" />
                            </svg>
                        </span>
                        Gestionar usuarios
                    </a>
                @endcan

                @can('roles')
                    <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600" data-dashboard-shortcut="roles">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 4.5 6.9v4.85c0 4.53 3.2 7.88 7.5 8.5 4.3-.62 7.5-3.97 7.5-8.5V6.9L12 3.75Zm0 4.5v4.5m0 3h.01" />
                            </svg>
                        </span>
                        Roles y permisos
                    </a>
                @endcan

                <a href="{{ route('admin.password.edit') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600" data-dashboard-shortcut="password">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6.75a3.75 3.75 0 0 0-7.5 0v3.75m-1.5 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21H6.75a2.25 2.25 0 0 1-2.25-2.25v-6a2.25 2.25 0 0 1 2.25-2.25Z" />
                        </svg>
                    </span>
                    Cambiar mi contraseña
                </a>
            </div>
        </section>
    </div>
@endsection
