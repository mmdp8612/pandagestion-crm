@extends('layouts.admin')

@section('title', 'Usos')

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
    <li class="font-medium text-slate-700" aria-current="page">Usos</li>
@endsection

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Catálogos</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Usos</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Administrá los destinos disponibles para las propiedades.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex w-fit items-center rounded-full bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700">
                {{ $propertyUses->total() }} {{ $propertyUses->total() === 1 ? 'registro' : 'registros' }}
            </span>
            <a href="{{ route('admin.catalogs.uses.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Nuevo uso
            </a>
        </div>
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="uses-table-title">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 id="uses-table-title" class="font-bold text-slate-950">Valores configurados</h2>
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
                    @forelse ($propertyUses as $propertyUse)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-slate-700 sm:px-6">{{ $propertyUse->IdUso }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-900 sm:px-6">{{ $propertyUse->Descrip }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 sm:px-6">
                                @if ($propertyUse->Hab)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                        Habilitado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                                        Deshabilitado
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-2.5 text-right sm:px-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.catalogs.uses.edit', $propertyUse->IdUso) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1"
                                        title="Editar uso"
                                        aria-label="Editar el uso {{ $propertyUse->Descrip }}"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L18.55 2.8M16.862 4.487 19.5 7.125" />
                                        </svg>
                                        <span class="sr-only">Editar uso</span>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.catalogs.uses.status.update', $propertyUse->IdUso) }}"
                                        class="inline-flex"
                                        data-confirm
                                        data-confirm-title="{{ $propertyUse->Hab ? '¿Deshabilitar' : '¿Habilitar' }} {{ $propertyUse->Descrip }}?"
                                        data-confirm-text="{{ $propertyUse->Hab ? 'Dejará de estar disponible para nuevas propiedades.' : 'Volverá a estar disponible para nuevas propiedades.' }}"
                                        data-confirm-button="{{ $propertyUse->Hab ? 'Sí, deshabilitar' : 'Sí, habilitar' }}"
                                        data-cancel-button="Cancelar"
                                        data-confirm-icon="{{ $propertyUse->Hab ? 'warning' : 'question' }}"
                                        data-confirm-variant="{{ $propertyUse->Hab ? 'danger' : 'success' }}"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="hab" value="{{ $propertyUse->Hab ? 0 : 1 }}">
                                        <button
                                            type="submit"
                                            @class([
                                                'inline-flex h-9 w-9 items-center justify-center rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-1',
                                                'text-amber-600 hover:bg-amber-50 focus:ring-amber-500' => $propertyUse->Hab,
                                                'text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-600' => ! $propertyUse->Hab,
                                            ])
                                            title="{{ $propertyUse->Hab ? 'Deshabilitar uso' : 'Habilitar uso' }}"
                                            aria-label="{{ $propertyUse->Hab ? 'Deshabilitar' : 'Habilitar' }} el uso {{ $propertyUse->Descrip }}"
                                        >
                                            @if ($propertyUse->Hab)
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75a8.25 8.25 0 1 0 8.25 8.25A8.25 8.25 0 0 0 12 3.75Zm0 4.5v7.5" />
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            @endif
                                            <span class="sr-only">{{ $propertyUse->Hab ? 'Deshabilitar' : 'Habilitar' }} uso</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <p class="font-semibold text-slate-700">No hay usos configurados.</p>
                                <a href="{{ route('admin.catalogs.uses.create') }}" class="mt-3 inline-flex text-sm font-bold text-emerald-700 hover:underline">Crear el primer uso</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($propertyUses->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                {{ $propertyUses->links() }}
            </div>
        @endif
    </section>
@endsection
