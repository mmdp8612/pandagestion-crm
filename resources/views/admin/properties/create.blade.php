@extends('layouts.admin')

@section('title', 'Nueva propiedad')

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
    <li class="font-medium text-slate-700" aria-current="page">Nueva</li>
@endsection

@section('content')
    <div class="max-w-5xl">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Bienes Raíces</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Nueva propiedad</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Completá la identificación, clasificación, características, comercialización, ubicación, multimedia y publicación inicial.</p>
        </div>

        <form method="POST" action="{{ route('admin.properties.store') }}" class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @csrf

            @include('admin.properties._form', ['property' => null])

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end sm:px-8">
                <a href="{{ route('admin.properties.index') }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Crear propiedad
                </button>
            </div>
        </form>
    </div>
@endsection
