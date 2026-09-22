@extends('layouts.admin')

@section('title', 'Comercialización')

@section('breadcrumb')
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li>Catálogos</li>
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">Comercialización</li>
@endsection

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Catálogos</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Comercialización</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Administrá las modalidades comerciales disponibles para las propiedades.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex w-fit items-center rounded-full bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700">
                {{ $commercializations->total() }} {{ $commercializations->total() === 1 ? 'registro' : 'registros' }}
            </span>
            <a href="{{ route('admin.catalogs.commercializations.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Nueva comercialización
            </a>
        </div>
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="commercializations-table-title">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 id="commercializations-table-title" class="font-bold text-slate-950">Valores configurados</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Código</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Descripción</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Estado</th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-6">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($commercializations as $commercialization)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-slate-700 sm:px-6">{{ $commercialization->IdComercializacion }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-900 sm:px-6">{{ $commercialization->Descrip }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 sm:px-6">
                                @if ($commercialization->Hab)
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
                            </td>
                            <td class="whitespace-nowrap px-5 py-2.5 text-right sm:px-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.catalogs.commercializations.edit', $commercialization->IdComercializacion) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1"
                                        title="Editar comercialización"
                                        aria-label="Editar la comercialización {{ $commercialization->Descrip }}"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L18.55 2.8M16.862 4.487 19.5 7.125" />
                                        </svg>
                                        <span class="sr-only">Editar comercialización</span>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.catalogs.commercializations.status.update', $commercialization->IdComercializacion) }}"
                                        class="inline-flex"
                                        data-confirm
                                        data-confirm-title="{{ $commercialization->Hab ? '¿Deshabilitar' : '¿Habilitar' }} {{ $commercialization->Descrip }}?"
                                        data-confirm-text="{{ $commercialization->Hab ? 'Dejará de estar disponible para nuevas propiedades.' : 'Volverá a estar disponible para nuevas propiedades.' }}"
                                        data-confirm-button="{{ $commercialization->Hab ? 'Sí, deshabilitar' : 'Sí, habilitar' }}"
                                        data-cancel-button="Cancelar"
                                        data-confirm-icon="{{ $commercialization->Hab ? 'warning' : 'question' }}"
                                        data-confirm-variant="{{ $commercialization->Hab ? 'danger' : 'success' }}"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="hab" value="{{ $commercialization->Hab ? 0 : 1 }}">
                                        <button
                                            type="submit"
                                            @class([
                                                'inline-flex h-9 w-9 items-center justify-center rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-1',
                                                'text-amber-600 hover:bg-amber-50 focus:ring-amber-500' => $commercialization->Hab,
                                                'text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-600' => ! $commercialization->Hab,
                                            ])
                                            title="{{ $commercialization->Hab ? 'Deshabilitar comercialización' : 'Habilitar comercialización' }}"
                                            aria-label="{{ $commercialization->Hab ? 'Deshabilitar' : 'Habilitar' }} la comercialización {{ $commercialization->Descrip }}"
                                        >
                                            @if ($commercialization->Hab)
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75a8.25 8.25 0 1 0 8.25 8.25A8.25 8.25 0 0 0 12 3.75Zm0 4.5v7.5" />
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            @endif
                                            <span class="sr-only">{{ $commercialization->Hab ? 'Deshabilitar' : 'Habilitar' }} comercialización</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <p class="font-semibold text-slate-700">No hay comercializaciones configuradas.</p>
                                <a href="{{ route('admin.catalogs.commercializations.create') }}" class="mt-3 inline-flex text-sm font-bold text-emerald-700 hover:underline">Crear la primera comercialización</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($commercializations->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                {{ $commercializations->links() }}
            </div>
        @endif
    </section>
@endsection
