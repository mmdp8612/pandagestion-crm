@extends('layouts.admin')

@section('title', 'Consultas')

@section('breadcrumb')
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">Consultas</li>
@endsection

@section('content')
    @php
        $statusClasses = [
            'nueva' => 'bg-sky-50 text-sky-700',
            'en_proceso' => 'bg-amber-50 text-amber-700',
            'respondida' => 'bg-emerald-50 text-emerald-700',
            'descartada' => 'bg-slate-200 text-slate-600',
        ];
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Seguimiento comercial</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Consultas</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Centralizá los mensajes enviados desde las fichas públicas y registrá el avance de cada contacto.</p>
        </div>
        <span class="inline-flex w-fit items-center rounded-full bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700">
            {{ $inquiries->total() }} {{ $inquiries->total() === 1 ? 'consulta' : 'consultas' }}
        </span>
    </div>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="inquiries-filters-title">
        <div>
            <h2 id="inquiries-filters-title" class="font-bold text-slate-950">Buscar y filtrar</h2>
            <p class="mt-1 text-sm text-slate-500">Buscá por persona, correo, teléfono o código de propiedad.</p>
        </div>

        <form method="GET" action="{{ route('admin.inquiries.index') }}" class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(13rem,18rem)_auto] lg:items-end">
            <div>
                <label for="buscar" class="block text-sm font-bold text-slate-700">Búsqueda</label>
                <div class="relative mt-2">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m16 16 4 4" /></svg>
                    <input id="buscar" name="buscar" type="search" value="{{ $filters['buscar'] ?? '' }}" maxlength="120" placeholder="Ej.: Laura, 11 5555 o PROP001" class="block w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                </div>
                @error('buscar')<p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="estado" class="block text-sm font-bold text-slate-700">Estado</label>
                <select id="estado" name="estado" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">Todos los estados</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['estado'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('estado')<p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex flex-1 justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-2 lg:flex-none">Aplicar filtros</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.inquiries.index') }}" class="inline-flex flex-1 justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 lg:flex-none">Limpiar</a>
                @endif
            </div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="inquiries-table-title">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 id="inquiries-table-title" class="font-bold text-slate-950">Mensajes recibidos</h2>
        </div>

        <table class="w-full table-fixed divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="w-[42%] px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:w-[30%] sm:px-6">Contacto</th>
                    <th scope="col" class="hidden w-[22%] px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 md:table-cell">Propiedad</th>
                    <th scope="col" class="hidden px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 lg:table-cell">Mensaje</th>
                    <th scope="col" class="w-[34%] px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:w-[20%]">Estado</th>
                    <th scope="col" class="w-14 px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 sm:w-20 sm:px-6"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($inquiries as $inquiry)
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="px-4 py-4 align-top sm:px-6">
                            <a href="{{ route('admin.inquiries.show', $inquiry->id) }}" class="block truncate text-sm font-bold text-slate-900 hover:text-emerald-700">{{ $inquiry->Nombre }}</a>
                            <p class="mt-1 truncate text-xs text-slate-500">{{ $inquiry->Email ?: $inquiry->Telefono }}</p>
                            <p class="mt-2 text-[11px] font-semibold text-slate-400">{{ \Illuminate\Support\Carbon::parse($inquiry->created_at)->format('d/m/Y H:i') }}</p>
                        </td>
                        <td class="hidden px-4 py-4 align-top md:table-cell">
                            <p class="truncate font-mono text-xs font-bold text-slate-800">{{ $inquiry->CodigoPropiedad }}</p>
                            <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ trim($inquiry->PropiedadCalle.' '.($inquiry->PropiedadNumero ?? '')) }}{{ $inquiry->PropiedadBarrio ? ' · '.$inquiry->PropiedadBarrio : '' }}</p>
                        </td>
                        <td class="hidden px-4 py-4 align-top lg:table-cell">
                            <p class="line-clamp-2 text-sm leading-5 text-slate-600">{{ $inquiry->Mensaje }}</p>
                        </td>
                        <td class="px-4 py-4 align-top">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClasses[$inquiry->Estado] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$inquiry->Estado] ?? $inquiry->Estado }}</span>
                        </td>
                        <td class="px-3 py-3 text-right align-top sm:px-6">
                            <a href="{{ route('admin.inquiries.show', $inquiry->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1" title="Ver consulta" aria-label="Ver consulta de {{ $inquiry->Nombre }}">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6S2.25 12 2.25 12Z" /><circle cx="12" cy="12" r="2.75" /></svg>
                                <span class="sr-only">Ver consulta</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-14 text-center">
                            <p class="font-semibold text-slate-700">{{ $hasFilters ? 'No hay consultas que coincidan con los filtros.' : 'Todavía no se recibieron consultas.' }}</p>
                            @if ($hasFilters)<a href="{{ route('admin.inquiries.index') }}" class="mt-3 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-800">Limpiar filtros</a>@endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($inquiries->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">{{ $inquiries->links() }}</div>
        @endif
    </section>
@endsection
