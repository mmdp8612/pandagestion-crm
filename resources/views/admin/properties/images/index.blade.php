@extends('layouts.admin')

@section('title', 'Imágenes de '.$property->Codigo)

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
    <li><a href="{{ route('admin.properties.show', $property->id) }}" class="transition hover:text-slate-800">{{ $property->Codigo }}</a></li>
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">Imágenes</li>
@endsection

@section('content')
    <div class="max-w-6xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Bienes Raíces</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Imágenes de {{ $property->Codigo }}</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Cargá las fotografías y elegí cuál representará a la propiedad como portada.</p>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-full bg-slate-200 px-3 py-1.5 text-sm font-bold text-slate-700">
                    {{ $images->count() }} {{ $images->count() === 1 ? 'imagen' : 'imágenes' }} · {{ $enabledImagesCount }} {{ $enabledImagesCount === 1 ? 'habilitada' : 'habilitadas' }}
                </span>
                <a href="{{ route('admin.properties.show', $property->id) }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Volver a la propiedad
                </a>
            </div>
        </div>

        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="property-images-upload-title">
            <div>
                <h2 id="property-images-upload-title" class="text-lg font-bold text-slate-950">Cargar imágenes</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Seleccioná hasta 10 archivos JPG, PNG o WebP por vez. Cada imagen puede pesar hasta 5 MB.</p>
            </div>

            <form method="POST" action="{{ route('admin.properties.images.store', $property->id) }}" enctype="multipart/form-data" class="mt-5">
                @csrf

                <label for="imagenes" class="block text-sm font-bold text-slate-700">Archivos</label>
                <input
                    id="imagenes"
                    name="imagenes[]"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    required
                    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600"
                >
                @error('imagenes')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
                @error('imagenes.*')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror

                <div class="mt-5 flex justify-end">
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                        Cargar imágenes
                    </button>
                </div>
            </form>
        </section>

        <section class="mt-6" aria-labelledby="property-images-gallery-title">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="property-images-gallery-title" class="text-lg font-bold text-slate-950">Galería</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($images->count() > 1)
                            Arrastrá las imágenes o usá las flechas para cambiar su posición. Las deshabilitadas conservan su archivo y orden, pero no se muestran en la ficha.
                        @else
                            Las imágenes deshabilitadas conservan su archivo, pero no se muestran en la ficha de la propiedad.
                        @endif
                    </p>
                </div>

                @if ($images->count() > 1)
                    <form id="property-image-order-form" method="POST" action="{{ route('admin.properties.images.order.update', $property->id) }}">
                        @csrf
                        @method('PATCH')
                    </form>

                    <div class="flex flex-col items-start gap-2 sm:items-end">
                        <span class="text-xs font-semibold text-slate-500" data-image-sort-status aria-live="polite">El orden no tiene cambios.</span>
                        <button
                            type="submit"
                            form="property-image-order-form"
                            disabled
                            data-image-order-submit
                            class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:shadow-none"
                        >
                            Guardar orden
                        </button>
                    </div>
                @endif
            </div>

            @error('imagenes')
                <p class="mt-3 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700" role="alert">{{ $message }}</p>
            @enderror
            @error('imagen')
                <p class="mt-3 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700" role="alert">{{ $message }}</p>
            @enderror
            @error('hab')
                <p class="mt-3 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700" role="alert">{{ $message }}</p>
            @enderror

            @if ($images->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm">
                    <svg class="mx-auto h-10 w-10 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                    </svg>
                    <p class="mt-3 font-semibold text-slate-700">Todavía no hay imágenes cargadas.</p>
                    <p class="mt-1 text-sm text-slate-500">La propiedad aparecerá sin fotografías hasta que cargues la primera.</p>
                </div>
            @else
                <div
                    class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                    @if ($images->count() > 1)
                        data-image-sorter
                        data-image-order-form="property-image-order-form"
                    @endif
                >
                    @foreach ($images as $image)
                        <article
                            @class([
                                'overflow-hidden rounded-2xl border bg-white shadow-sm transition',
                                'border-slate-200' => $image->Hab,
                                'border-slate-300' => ! $image->Hab,
                            ])
                            data-image-sort-item
                            data-image-id="{{ $image->id }}"
                        >
                            @if ($images->count() > 1)
                                <input type="hidden" name="imagenes[]" value="{{ $image->id }}" form="property-image-order-form">
                            @endif

                            <div class="relative aspect-[4/3] bg-slate-100">
                                <img
                                    src="{{ Storage::disk('public')->url($image->Archivo) }}"
                                    alt="Imagen {{ $loop->iteration }} de la propiedad {{ $property->Codigo }}"
                                    @class([
                                        'h-full w-full object-cover transition',
                                        'grayscale opacity-50' => ! $image->Hab,
                                    ])
                                    loading="lazy"
                                >
                                @if ($image->Portada)
                                    <span class="absolute left-3 top-3 inline-flex items-center rounded-full bg-emerald-600 px-2.5 py-1 text-xs font-bold text-white shadow-sm">Portada</span>
                                @endif
                                @unless ($image->Hab)
                                    <span class="absolute right-3 top-3 inline-flex items-center rounded-full bg-slate-800 px-2.5 py-1 text-xs font-bold text-white shadow-sm">Deshabilitada</span>
                                @endunless
                            </div>

                            <div class="flex items-center justify-between gap-3 p-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-semibold text-slate-500" data-image-position>Posición {{ $loop->iteration }}</span>
                                    <span @class([
                                        'text-xs font-bold',
                                        'text-emerald-700' => $image->Hab,
                                        'text-slate-500' => ! $image->Hab,
                                    ])>{{ $image->Hab ? 'Habilitada' : 'Deshabilitada' }}</span>
                                </div>

                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    @if ($images->count() > 1)
                                        <button
                                            type="button"
                                            draggable="true"
                                            data-image-drag-handle
                                            class="hidden h-9 w-9 cursor-grab items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1 active:cursor-grabbing sm:inline-flex"
                                            title="Arrastrar para reordenar"
                                            aria-label="Arrastrar la imagen {{ $loop->iteration }} para reordenarla"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" d="M8.25 6.75h.008v.008H8.25V6.75Zm0 5.25h.008v.008H8.25V12Zm0 5.25h.008v.008H8.25v-.008Zm7.5-10.5h.008v.008h-.008V6.75Zm0 5.25h.008v.008h-.008V12Zm0 5.25h.008v.008h-.008v-.008Z" />
                                            </svg>
                                            <span class="sr-only">Arrastrar para reordenar</span>
                                        </button>

                                        <button
                                            type="button"
                                            data-image-move="previous"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:text-slate-300 disabled:hover:bg-transparent"
                                            title="Mover antes"
                                            aria-label="Mover la imagen {{ $loop->iteration }} una posición antes"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                                            </svg>
                                            <span class="sr-only">Mover antes</span>
                                        </button>

                                        <button
                                            type="button"
                                            data-image-move="next"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:text-slate-300 disabled:hover:bg-transparent"
                                            title="Mover después"
                                            aria-label="Mover la imagen {{ $loop->iteration }} una posición después"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                            </svg>
                                            <span class="sr-only">Mover después</span>
                                        </button>
                                    @endif

                                    @if ($image->Hab && ! $image->Portada)
                                        <form method="POST" action="{{ route('admin.properties.images.cover.update', [$property->id, $image->id]) }}" class="inline-flex">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-amber-600 transition hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1" title="Usar como portada" aria-label="Usar la imagen {{ $loop->iteration }} como portada">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.563.563 0 0 1 1.04 0l2.125 5.111 5.518.442c.499.04.701.663.321.988l-4.204 3.602 1.285 5.385a.562.562 0 0 1-.84.61L12 16.739l-4.725 2.898a.562.562 0 0 1-.84-.61l1.285-5.385-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442 2.125-5.111Z" />
                                                </svg>
                                                <span class="sr-only">Usar como portada</span>
                                            </button>
                                        </form>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('admin.properties.images.status.update', [$property->id, $image->id]) }}"
                                        class="inline-flex"
                                        data-confirm
                                        data-confirm-title="{{ $image->Hab ? '¿Deshabilitar esta imagen?' : '¿Habilitar esta imagen?' }}"
                                        data-confirm-text="{{ $image->Hab ? 'La fotografía dejará de mostrarse en la ficha y en la futura publicación, pero conservará su archivo y posición.' : 'La fotografía volverá a mostrarse en la ficha y en la futura publicación.' }}"
                                        data-confirm-button="{{ $image->Hab ? 'Sí, deshabilitar' : 'Sí, habilitar' }}"
                                        data-cancel-button="Cancelar"
                                        data-confirm-icon="{{ $image->Hab ? 'warning' : 'question' }}"
                                        data-confirm-variant="{{ $image->Hab ? 'danger' : 'success' }}"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="hab" value="{{ $image->Hab ? 0 : 1 }}">
                                        <button
                                            type="submit"
                                            @class([
                                                'inline-flex h-9 w-9 items-center justify-center rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-1',
                                                'text-amber-600 hover:bg-amber-50 focus:ring-amber-500' => $image->Hab,
                                                'text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-600' => ! $image->Hab,
                                            ])
                                            title="{{ $image->Hab ? 'Deshabilitar imagen' : 'Habilitar imagen' }}"
                                            aria-label="{{ $image->Hab ? 'Deshabilitar' : 'Habilitar' }} la imagen {{ $loop->iteration }}"
                                        >
                                            @if ($image->Hab)
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75a8.25 8.25 0 1 0 8.25 8.25A8.25 8.25 0 0 0 12 3.75Zm0 4.5v7.5" />
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            @endif
                                            <span class="sr-only">{{ $image->Hab ? 'Deshabilitar' : 'Habilitar' }} imagen</span>
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.properties.images.destroy', [$property->id, $image->id]) }}"
                                        class="inline-flex"
                                        data-confirm
                                        data-confirm-title="¿Eliminar esta imagen?"
                                        data-confirm-text="La imagen se quitará definitivamente de la propiedad."
                                        data-confirm-button="Sí, eliminar"
                                        data-cancel-button="Cancelar"
                                        data-confirm-icon="warning"
                                        data-confirm-variant="danger"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" title="Eliminar imagen" aria-label="Eliminar la imagen {{ $loop->iteration }}">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V4.477c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            <span class="sr-only">Eliminar imagen</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
