@extends('layouts.admin')

@section('title', 'Consulta de '.$inquiry->Nombre)

@section('breadcrumb')
    <li aria-hidden="true"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg></li>
    <li><a href="{{ route('admin.inquiries.index') }}" class="transition hover:text-slate-800">Consultas</a></li>
    <li aria-hidden="true"><svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg></li>
    <li class="font-medium text-slate-700" aria-current="page">#{{ $inquiry->id }}</li>
@endsection

@section('content')
    @php
        $statusClasses = [
            'nueva' => 'bg-sky-50 text-sky-700',
            'en_proceso' => 'bg-amber-50 text-amber-700',
            'respondida' => 'bg-emerald-50 text-emerald-700',
            'descartada' => 'bg-slate-200 text-slate-600',
        ];
        $propertyAddress = trim($inquiry->PropiedadCalle.' '.($inquiry->PropiedadNumero ?? ''));
        $propertyLocation = implode(', ', array_filter([$inquiry->PropiedadBarrio, $inquiry->PropiedadLocalidad]));
    @endphp

    <div class="max-w-5xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Consulta #{{ $inquiry->id }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-950">{{ $inquiry->Nombre }}</h1>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClasses[$inquiry->Estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$inquiry->Estado] ?? $inquiry->Estado }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-500">Recibida el {{ \Illuminate\Support\Carbon::parse($inquiry->created_at)->format('d/m/Y \a \l\a\s H:i') }}</p>
            </div>
            <a href="{{ route('admin.inquiries.index') }}" class="inline-flex w-fit items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6 6-6-6 6-6" /></svg>
                Volver a consultas
            </a>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-start">
            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="inquiry-message-title">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Mensaje</p>
                    <h2 id="inquiry-message-title" class="mt-2 text-xl font-bold text-slate-950">Consulta por {{ $inquiry->CodigoPropiedad }}</h2>
                    <p class="mt-5 whitespace-pre-line text-base leading-8 text-slate-700">{{ $inquiry->Mensaje }}</p>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="inquiry-property-title">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Propiedad consultada</p>
                            <h2 id="inquiry-property-title" class="mt-2 font-mono text-xl font-bold text-slate-950">{{ $inquiry->CodigoPropiedad }}</h2>
                            <p class="mt-2 text-sm font-semibold text-slate-700">{{ $propertyAddress ?: 'Domicilio a consultar' }}</p>
                            @if ($propertyLocation)<p class="mt-1 text-sm text-slate-500">{{ $propertyLocation }}</p>@endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @can('bienesraices')
                                <a href="{{ route('admin.properties.show', $inquiry->idBienRaiz) }}" class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Ficha administrativa</a>
                            @endcan
                            @if ($inquiry->PropiedadHabilitada)
                                <a href="{{ route('public.properties.show', $inquiry->PropiedadSlug) }}" target="_blank" rel="noopener noreferrer" class="inline-flex rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100">Ver publicación</a>
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-28">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="inquiry-contact-title">
                    <h2 id="inquiry-contact-title" class="font-bold text-slate-950">Datos de contacto</h2>
                    <dl class="mt-4 space-y-4">
                        <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Nombre</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $inquiry->Nombre }}</dd></div>
                        @if ($inquiry->Email)
                            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Correo</dt><dd class="mt-1 break-all text-sm font-semibold"><a href="mailto:{{ $inquiry->Email }}" class="text-emerald-700 hover:underline">{{ $inquiry->Email }}</a></dd></div>
                        @endif
                        @if ($inquiry->Telefono)
                            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Teléfono</dt><dd class="mt-1 text-sm font-semibold"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $inquiry->Telefono) }}" class="text-emerald-700 hover:underline">{{ $inquiry->Telefono }}</a></dd></div>
                        @endif
                    </dl>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="inquiry-status-title">
                    <h2 id="inquiry-status-title" class="font-bold text-slate-950">Seguimiento</h2>
                    <form method="POST" action="{{ route('admin.inquiries.status.update', $inquiry->id) }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <label for="estado" class="block text-sm font-bold text-slate-700">Estado</label>
                        <select id="estado" name="estado" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            @foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(old('estado', $inquiry->Estado) === $value)>{{ $label }}</option>@endforeach
                        </select>
                        @error('estado')<p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
                        <button type="submit" class="mt-3 inline-flex w-full justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-2">Guardar estado</button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
@endsection
