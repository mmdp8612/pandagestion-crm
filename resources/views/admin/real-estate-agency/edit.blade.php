@extends('layouts.admin')

@section('title', 'Inmobiliaria')

@section('breadcrumb')
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li>Configuración</li>
    <li aria-hidden="true">
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L12.94 10 8.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
    </li>
    <li class="font-medium text-slate-700" aria-current="page">Inmobiliaria</li>
@endsection

@section('content')
    <div class="max-w-5xl">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-700">Configuración</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Datos de la inmobiliaria</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                Administrá la identidad, los canales de contacto y la ubicación que utilizará PandaGestion.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.real-estate-agency.update') }}" enctype="multipart/form-data" class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @csrf
            @method('PUT')

            <div class="space-y-8 p-6 sm:p-8">
                <section aria-labelledby="agency-identity-title">
                    <div>
                        <h2 id="agency-identity-title" class="text-lg font-bold text-slate-950">Identidad</h2>
                        <p class="mt-1 text-sm text-slate-500">Información legal y presencia digital de la empresa.</p>
                    </div>

                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="razon_social" class="block text-sm font-bold text-slate-700">Razón social</label>
                            <input
                                id="razon_social"
                                name="razon_social"
                                type="text"
                                value="{{ old('razon_social', $agency->RazonSocial ?? '') }}"
                                required
                                maxlength="150"
                                autocomplete="organization"
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                            >
                            @error('razon_social')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="matricula" class="block text-sm font-bold text-slate-700">Matrícula profesional</label>
                            <input
                                id="matricula"
                                name="matricula"
                                type="text"
                                value="{{ old('matricula', $agency->Matricula ?? '') }}"
                                maxlength="150"
                                placeholder="Ej.: CUCICBA 1234"
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                            >
                            @error('matricula')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="web" class="block text-sm font-bold text-slate-700">Sitio web</label>
                            <input
                                id="web"
                                name="web"
                                type="url"
                                value="{{ old('web', $agency->Web ?? '') }}"
                                maxlength="255"
                                placeholder="https://www.ejemplo.com.ar"
                                autocomplete="url"
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                            >
                            @error('web')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-200 pt-8" aria-labelledby="agency-logo-title">
                    <div>
                        <h2 id="agency-logo-title" class="text-lg font-bold text-slate-950">Identidad visual</h2>
                        <p class="mt-1 text-sm text-slate-500">Cargá el logo que identificará a la inmobiliaria.</p>
                    </div>

                    <div class="mt-5 grid gap-5 md:grid-cols-[14rem_minmax(0,1fr)] md:items-start">
                        <div class="flex min-h-36 items-center justify-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo actual de {{ $agency->RazonSocial }}" class="max-h-28 max-w-full object-contain">
                            @else
                                <div class="text-center text-slate-400">
                                    <svg class="mx-auto h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="16" rx="2" />
                                        <circle cx="8.5" cy="9" r="1.5" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4 17 4.5-4.5 3 3 2-2L20 20" />
                                    </svg>
                                    <span class="mt-2 block text-xs font-semibold">Sin logo</span>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label for="logo" class="block text-sm font-bold text-slate-700">
                                {{ $logoUrl ? 'Reemplazar logo' : 'Seleccionar logo' }}
                            </label>
                            <input
                                id="logo"
                                name="logo"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="mt-2 block w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-sm text-slate-600 shadow-sm file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-slate-700 hover:file:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-600/20"
                            >
                            <p class="mt-2 text-xs leading-5 text-slate-500">JPG, PNG o WebP. Tamaño máximo: 2 MB.</p>
                            @error('logo')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror

                            @if ($logoUrl)
                                <button
                                    type="submit"
                                    form="agency-logo-delete-form"
                                    class="mt-4 inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-10 0 .75 12h6.5L16 7m-6-3h4l1 3H9l1-3Zm1 7v5m2-5v5" />
                                    </svg>
                                    Eliminar logo actual
                                </button>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-200 pt-8" aria-labelledby="agency-contact-title">
                    <div>
                        <h2 id="agency-contact-title" class="text-lg font-bold text-slate-950">Contacto</h2>
                        <p class="mt-1 text-sm text-slate-500">Canales que se mostrarán en futuras comunicaciones y publicaciones.</p>
                    </div>

                    <div class="mt-5 grid gap-5 md:grid-cols-3">
                        <div>
                            <label for="telefonos" class="block text-sm font-bold text-slate-700">Teléfonos</label>
                            <input
                                id="telefonos"
                                name="telefonos"
                                type="text"
                                value="{{ old('telefonos', $agency->Telefonos ?? '') }}"
                                maxlength="255"
                                placeholder="Separados por coma"
                                autocomplete="tel"
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                            >
                            @error('telefonos')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="whatsapp" class="block text-sm font-bold text-slate-700">WhatsApp</label>
                            <input
                                id="whatsapp"
                                name="whatsapp"
                                type="tel"
                                value="{{ old('whatsapp', $agency->Whatsapp ?? '') }}"
                                maxlength="50"
                                placeholder="Ej.: +54 9 11 1234-5678"
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                            >
                            @error('whatsapp')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-bold text-slate-700">Correo electrónico</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', $agency->Email ?? '') }}"
                                maxlength="255"
                                autocomplete="email"
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                            >
                            @error('email')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-200 pt-8" aria-labelledby="agency-location-title">
                    <div>
                        <h2 id="agency-location-title" class="text-lg font-bold text-slate-950">Ubicación</h2>
                        <p class="mt-1 text-sm text-slate-500">Buscá una dirección para completar los datos de ubicación o ingresalos manualmente.</p>
                    </div>

                    <div
                        class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4"
                        data-address-search
                        data-search-url="{{ route('admin.real-estate-agency.addresses.search') }}"
                    >
                        <label for="address_search" class="block text-sm font-bold text-slate-700">Buscar dirección</label>
                        <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                            <input
                                id="address_search"
                                type="search"
                                minlength="5"
                                maxlength="200"
                                autocomplete="off"
                                placeholder="Ej.: Av. Corrientes 1234, Buenos Aires"
                                aria-describedby="address-search-help address-search-status"
                                aria-controls="address-search-results"
                                class="block min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                                data-address-search-input
                            >
                            <button
                                type="button"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
                                data-address-search-button
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.032 4.87l3.049 3.049a.75.75 0 1 1-1.06 1.06l-3.05-3.048A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                                </svg>
                                <span data-address-search-button-label>Buscar</span>
                            </button>
                        </div>
                        <p id="address-search-help" class="mt-2 text-xs leading-5 text-slate-500">
                            Escribí calle, altura y localidad. La búsqueda se realiza únicamente al presionar Buscar.
                        </p>
                        <p id="address-search-status" class="mt-2 hidden text-sm" role="status" aria-live="polite" data-address-search-status></p>
                        <div id="address-search-results" class="mt-3 hidden overflow-hidden rounded-lg border border-slate-200 bg-white" data-address-search-results></div>
                        <p class="mt-3 text-xs text-slate-500">
                            Resultados ©
                            <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-700 underline decoration-emerald-300 underline-offset-2 hover:text-emerald-800">
                                OpenStreetMap contributors
                            </a>
                            (ODbL).
                        </p>
                    </div>

                    <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="domicilio" class="block text-sm font-bold text-slate-700">Domicilio</label>
                            <input
                                id="domicilio"
                                name="domicilio"
                                type="text"
                                value="{{ old('domicilio', $agency->Domicilio ?? '') }}"
                                maxlength="255"
                                autocomplete="street-address"
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                            >
                            @error('domicilio')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="provincia" class="block text-sm font-bold text-slate-700">Provincia</label>
                            <input id="provincia" name="provincia" type="text" value="{{ old('provincia', $agency->Provincia ?? '') }}" maxlength="100" autocomplete="address-level1" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            @error('provincia')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="partido" class="block text-sm font-bold text-slate-700">Partido</label>
                            <input id="partido" name="partido" type="text" value="{{ old('partido', $agency->Partido ?? '') }}" maxlength="100" autocomplete="address-level2" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            @error('partido')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="localidad" class="block text-sm font-bold text-slate-700">Localidad</label>
                            <input id="localidad" name="localidad" type="text" value="{{ old('localidad', $agency->Localidad ?? '') }}" maxlength="100" autocomplete="address-level2" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            @error('localidad')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="barrio" class="block text-sm font-bold text-slate-700">Barrio</label>
                            <input id="barrio" name="barrio" type="text" value="{{ old('barrio', $agency->Barrio ?? '') }}" maxlength="100" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            @error('barrio')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="codigo_postal" class="block text-sm font-bold text-slate-700">Código postal</label>
                            <input id="codigo_postal" name="codigo_postal" type="text" value="{{ old('codigo_postal', $agency->CodigoPostal ?? '') }}" maxlength="20" autocomplete="postal-code" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            @error('codigo_postal')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="latitud" class="block text-sm font-bold text-slate-700">Latitud</label>
                            <input id="latitud" name="latitud" type="number" value="{{ old('latitud', $agency->Latitud ?? '') }}" min="-90" max="90" step="0.0000001" placeholder="-34.6037000" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            @error('latitud')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="longitud" class="block text-sm font-bold text-slate-700">Longitud</label>
                            <input id="longitud" name="longitud" type="number" value="{{ old('longitud', $agency->Longitud ?? '') }}" min="-180" max="180" step="0.0000001" placeholder="-58.3816000" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            @error('longitud')
                                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-5 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" data-address-map>
                        <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800">Ubicación en el mapa</h3>
                                <p class="mt-0.5 text-xs text-slate-500">Verificá que el marcador coincida con el domicilio seleccionado.</p>
                            </div>
                            <a
                                href="#"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700 hover:text-emerald-800 hover:underline"
                                data-address-map-link
                            >
                                Abrir mapa completo
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M11.75 3a.75.75 0 0 0 0 1.5h2.69l-5.47 5.47a.75.75 0 1 0 1.06 1.06l5.47-5.47v2.69a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5Z" />
                                    <path d="M4.5 5.25a.75.75 0 0 0-.75.75v10.25h10.25a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 14 17.75H3.75a1.5 1.5 0 0 1-1.5-1.5V6A2.25 2.25 0 0 1 4.5 3.75h4a.75.75 0 0 1 0 1.5h-4Z" />
                                </svg>
                            </a>
                        </div>
                        <iframe
                            class="h-72 w-full border-0 sm:h-80"
                            title="Mapa de la ubicación de la inmobiliaria"
                            loading="lazy"
                            data-address-map-frame
                        ></iframe>
                    </div>

                    <div class="mt-5 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-6 text-sky-900">
                        Al elegir un resultado se completan los campos disponibles. Revisalos antes de guardar: todos pueden corregirse manualmente.
                    </div>
                </section>

                <section class="border-t border-slate-200 pt-8" aria-labelledby="agency-status-title">
                    <h2 id="agency-status-title" class="text-lg font-bold text-slate-950">Estado</h2>

                    <input type="hidden" name="hab" value="0">
                    <label for="hab" class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50">
                        <input
                            id="hab"
                            name="hab"
                            type="checkbox"
                            value="1"
                            @checked((bool) old('hab', $agency->Hab ?? true))
                            class="mt-0.5 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                        >
                        <span>
                            <span class="block text-sm font-bold text-slate-800">Inmobiliaria habilitada</span>
                            <span class="mt-1 block text-sm leading-5 text-slate-500">Este estado podrá utilizarse posteriormente para controlar su publicación en el portal.</span>
                        </span>
                    </label>
                    @error('hab')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </section>
            </div>

            <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-6 py-4 sm:px-8">
                <button type="submit" class="inline-flex justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Guardar datos
                </button>
            </div>
        </form>

        @if ($logoUrl)
            <form
                id="agency-logo-delete-form"
                method="POST"
                action="{{ route('admin.real-estate-agency.logo.destroy') }}"
                class="hidden"
                data-confirm
                data-confirm-title="¿Eliminar el logo?"
                data-confirm-text="El archivo actual se eliminará de forma permanente."
                data-confirm-button="Sí, eliminar"
                data-cancel-button="Cancelar"
                data-confirm-icon="warning"
                data-confirm-variant="danger"
            >
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
@endsection
