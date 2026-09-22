<div class="space-y-8 p-6 sm:p-8">
    <section aria-labelledby="property-identification-title">
        <div>
            <h2 id="property-identification-title" class="text-lg font-bold text-slate-950">Identificación</h2>
            <p class="mt-1 text-sm text-slate-500">Definí el código interno y una descripción general de la propiedad.</p>
        </div>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label for="codigo" class="block text-sm font-bold text-slate-700">Código</label>
                <input
                    id="codigo"
                    name="codigo"
                    type="text"
                    value="{{ old('codigo', $property->Codigo ?? '') }}"
                    required
                    maxlength="30"
                    autocomplete="off"
                    class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 font-mono uppercase text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                >
                <p class="mt-2 text-xs leading-5 text-slate-500">Hasta 30 letras o números. Se guarda en mayúsculas y debe ser único.</p>
                @error('codigo')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="block text-sm font-bold text-slate-700">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4"
                    maxlength="5000"
                    class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                >{{ old('descripcion', $property->Descrip ?? '') }}</textarea>
                <p class="mt-2 text-xs leading-5 text-slate-500">Opcional. Usala para resumir los aspectos más importantes de la propiedad.</p>
                @error('descripcion')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 pt-8" aria-labelledby="property-classification-title">
        <div>
            <h2 id="property-classification-title" class="text-lg font-bold text-slate-950">Clasificación</h2>
            <p class="mt-1 text-sm text-slate-500">Relacioná la propiedad con los catálogos disponibles. Todos los datos son opcionales.</p>
        </div>

        <div class="mt-5 grid gap-5 md:grid-cols-3">
            <div>
                <label for="id_tipologia" class="block text-sm font-bold text-slate-700">Tipología</label>
                <select id="id_tipologia" name="id_tipologia" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">Sin especificar</option>
                    @foreach ($typologies->groupBy(fn ($typology) => $typology->TipoGral ?: 'Sin grupo') as $group => $groupTypologies)
                        <optgroup label="{{ $group }}">
                            @foreach ($groupTypologies as $typology)
                                <option value="{{ $typology->IdTipologia }}" @selected(old('id_tipologia', $property->IdTipologia ?? '') === $typology->IdTipologia)>
                                    {{ $typology->Descrip }}{{ $typology->Hab ? '' : ' (deshabilitada)' }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('id_tipologia')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="id_uso" class="block text-sm font-bold text-slate-700">Uso</label>
                <select id="id_uso" name="id_uso" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">Sin especificar</option>
                    @foreach ($propertyUses as $propertyUse)
                        <option value="{{ $propertyUse->IdUso }}" @selected(old('id_uso', $property->IdUso ?? '') === $propertyUse->IdUso)>
                            {{ $propertyUse->Descrip }}{{ $propertyUse->Hab ? '' : ' (deshabilitado)' }}
                        </option>
                    @endforeach
                </select>
                @error('id_uso')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="antiguedad" class="block text-sm font-bold text-slate-700">Antigüedad</label>
                <select id="antiguedad" name="antiguedad" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">Sin especificar</option>
                    @foreach ($antiquities as $antiquity)
                        <option value="{{ $antiquity->IdAntiguedad }}" @selected(old('antiguedad', $property->Antiguedad ?? '') === $antiquity->IdAntiguedad)>
                            {{ $antiquity->Descrip }}{{ $antiquity->Hab ? '' : ' (deshabilitada)' }}
                        </option>
                    @endforeach
                </select>
                @error('antiguedad')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="id_orientacion" class="block text-sm font-bold text-slate-700">Orientación</label>
                <select id="id_orientacion" name="id_orientacion" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">Sin especificar</option>
                    @foreach ($orientations as $orientation)
                        <option value="{{ $orientation->IdOrientacion }}" @selected(old('id_orientacion', $property->IdOrientacion ?? '') === $orientation->IdOrientacion)>
                            {{ $orientation->Descrip }}{{ $orientation->Hab ? '' : ' (deshabilitada)' }}
                        </option>
                    @endforeach
                </select>
                @error('id_orientacion')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="id_cochera" class="block text-sm font-bold text-slate-700">Cochera</label>
                <select id="id_cochera" name="id_cochera" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">Sin especificar</option>
                    @foreach ($garages as $garage)
                        <option value="{{ $garage->IdCochera }}" @selected(old('id_cochera', $property->IdCochera ?? '') === $garage->IdCochera)>
                            {{ $garage->Descrip }}{{ $garage->Hab ? '' : ' (deshabilitada)' }}
                        </option>
                    @endforeach
                </select>
                @error('id_cochera')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="id_vista" class="block text-sm font-bold text-slate-700">Vista</label>
                <select id="id_vista" name="id_vista" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    <option value="">Sin especificar</option>
                    @foreach ($propertyViews as $propertyView)
                        <option value="{{ $propertyView->IdVista }}" @selected(old('id_vista', $property->IdVista ?? '') === $propertyView->IdVista)>
                            {{ $propertyView->Descrip }}{{ $propertyView->Hab ? '' : ' (deshabilitada)' }}
                        </option>
                    @endforeach
                </select>
                @error('id_vista')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 pt-8" aria-labelledby="property-physical-characteristics-title">
        <div>
            <h2 id="property-physical-characteristics-title" class="text-lg font-bold text-slate-950">Superficies, ambientes y características</h2>
            <p class="mt-1 text-sm text-slate-500">Completá las medidas, cantidades y características conocidas. Todos los campos son opcionales.</p>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-1 lg:col-span-2">
                <label for="sup_cubierta_propia" class="block text-sm font-bold text-slate-700">Superficie cubierta propia</label>
                <div class="relative mt-2">
                    <input id="sup_cubierta_propia" name="sup_cubierta_propia" type="number" value="{{ old('sup_cubierta_propia', $property->SupCubiertaPropia ?? '') }}" min="0" max="9999999999.99" step="0.01" inputmode="decimal" class="block w-full rounded-lg border border-slate-300 py-2.5 pl-3 pr-12 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm font-semibold text-slate-500">m²</span>
                </div>
                @error('sup_cubierta_propia')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-1 lg:col-span-2">
                <label for="sup_terreno" class="block text-sm font-bold text-slate-700">Superficie del terreno</label>
                <div class="relative mt-2">
                    <input id="sup_terreno" name="sup_terreno" type="number" value="{{ old('sup_terreno', $property->SupTerreno ?? '') }}" min="0" max="9999999999.99" step="0.01" inputmode="decimal" class="block w-full rounded-lg border border-slate-300 py-2.5 pl-3 pr-12 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm font-semibold text-slate-500">m²</span>
                </div>
                @error('sup_terreno')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            @foreach ([
                ['name' => 'frente', 'column' => 'Frente', 'label' => 'Frente', 'unit' => 'm²'],
                ['name' => 'fondo', 'column' => 'Fondo', 'label' => 'Fondo', 'unit' => 'm²'],
                ['name' => 'metros_fondo', 'column' => 'MtsFondo', 'label' => 'Metros de fondo', 'unit' => 'm'],
            ] as $field)
                <div>
                    <label for="{{ $field['name'] }}" class="block text-sm font-bold text-slate-700">{{ $field['label'] }}</label>
                    <div class="relative mt-2">
                        <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" type="number" value="{{ old($field['name'], $property->{$field['column']} ?? '') }}" min="0" max="9999999999.99" step="0.01" inputmode="decimal" class="block w-full rounded-lg border border-slate-300 py-2.5 pl-3 pr-12 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm font-semibold text-slate-500">{{ $field['unit'] }}</span>
                    </div>
                    @error($field['name'])
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div>
                <label for="luminosidad" class="block text-sm font-bold text-slate-700">Luminosidad</label>
                <input id="luminosidad" name="luminosidad" type="text" value="{{ old('luminosidad', $property->Luminosidad ?? '') }}" maxlength="30" placeholder="Ej.: Excelente" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('luminosidad')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            @foreach ([
                ['name' => 'plantas', 'column' => 'Plantas', 'label' => 'Plantas'],
                ['name' => 'ambientes', 'column' => 'Ambientes', 'label' => 'Ambientes'],
                ['name' => 'sanitarios', 'column' => 'Sanitarios', 'label' => 'Baños / sanitarios'],
                ['name' => 'dormitorios', 'column' => 'Dormitorios', 'label' => 'Dormitorios'],
                ['name' => 'suite', 'column' => 'Suite', 'label' => 'Dormitorios en suite'],
                ['name' => 'lineas_telefonicas', 'column' => 'LineasTel', 'label' => 'Líneas telefónicas'],
            ] as $field)
                <div>
                    <label for="{{ $field['name'] }}" class="block text-sm font-bold text-slate-700">{{ $field['label'] }}</label>
                    <input
                        id="{{ $field['name'] }}"
                        name="{{ $field['name'] }}"
                        type="number"
                        value="{{ old($field['name'], $property->{$field['column']} ?? '') }}"
                        min="0"
                        max="65535"
                        step="1"
                        inputmode="numeric"
                        class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20"
                    >
                    @error($field['name'])
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </section>

    <section class="border-t border-slate-200 pt-8" aria-labelledby="property-commercialization-title">
        <div>
            <h2 id="property-commercialization-title" class="text-lg font-bold text-slate-950">Comercialización</h2>
            <p class="mt-1 text-sm text-slate-500">Indicá la modalidad y los importes conocidos. Cada importe debe guardarse junto con su moneda.</p>
        </div>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="id_comercializacion" class="block text-sm font-bold text-slate-700">Modalidad comercial</label>
                <select id="id_comercializacion" name="id_comercializacion" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 md:max-w-md">
                    <option value="">Sin especificar</option>
                    @foreach ($commercializations as $commercialization)
                        <option value="{{ $commercialization->IdComercializacion }}" @selected(old('id_comercializacion', $property->IdComercializacion ?? '') === $commercialization->IdComercializacion)>
                            {{ $commercialization->Descrip }}{{ $commercialization->Hab ? '' : ' (deshabilitada)' }}
                        </option>
                    @endforeach
                </select>
                @error('id_comercializacion')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-xl border border-slate-200 p-4">
                <h3 class="text-sm font-bold text-slate-800">Venta</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-[minmax(0,1fr)_11rem]">
                    <div>
                        <label for="importe_venta" class="block text-sm font-semibold text-slate-700">Importe</label>
                        <input id="importe_venta" name="importe_venta" type="number" value="{{ old('importe_venta', $property->ImporteVta ?? '') }}" min="0" max="9999999999999.99" step="0.01" inputmode="decimal" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                        @error('importe_venta')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="id_tipo_moneda_venta" class="block text-sm font-semibold text-slate-700">Moneda</label>
                        <select id="id_tipo_moneda_venta" name="id_tipo_moneda_venta" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            <option value="">Seleccionar</option>
                            @foreach ($saleCurrencyTypes as $currencyType)
                                <option value="{{ $currencyType->idTipoMoneda }}" @selected((string) old('id_tipo_moneda_venta', $property->idTipoMonedaVta ?? '') === (string) $currencyType->idTipoMoneda)>
                                    {{ $currencyType->Descrip }}{{ $currencyType->Simbolo ? ' ('.$currencyType->Simbolo.')' : '' }}{{ $currencyType->Hab ? '' : ' - deshabilitada' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_tipo_moneda_venta')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 p-4">
                <h3 class="text-sm font-bold text-slate-800">Alquiler</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-[minmax(0,1fr)_11rem]">
                    <div>
                        <label for="importe_alquiler" class="block text-sm font-semibold text-slate-700">Importe</label>
                        <input id="importe_alquiler" name="importe_alquiler" type="number" value="{{ old('importe_alquiler', $property->ImporteAlq ?? '') }}" min="0" max="9999999999999.99" step="0.01" inputmode="decimal" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                        @error('importe_alquiler')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="id_tipo_moneda_alquiler" class="block text-sm font-semibold text-slate-700">Moneda</label>
                        <select id="id_tipo_moneda_alquiler" name="id_tipo_moneda_alquiler" class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                            <option value="">Seleccionar</option>
                            @foreach ($rentCurrencyTypes as $currencyType)
                                <option value="{{ $currencyType->idTipoMoneda }}" @selected((string) old('id_tipo_moneda_alquiler', $property->idTipoMonedaAlq ?? '') === (string) $currencyType->idTipoMoneda)>
                                    {{ $currencyType->Descrip }}{{ $currencyType->Simbolo ? ' ('.$currencyType->Simbolo.')' : '' }}{{ $currencyType->Hab ? '' : ' - deshabilitada' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_tipo_moneda_alquiler')
                            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 pt-8" aria-labelledby="property-location-title">
        <div>
            <h2 id="property-location-title" class="text-lg font-bold text-slate-950">Ubicación</h2>
            <p class="mt-1 text-sm text-slate-500">Buscá una dirección para completar los datos disponibles o ingresalos manualmente.</p>
        </div>

        <div
            class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4"
            data-address-search
            data-search-url="{{ route('admin.properties.addresses.search') }}"
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
            <div class="md:col-span-2">
                <label for="calle" class="block text-sm font-bold text-slate-700">Calle</label>
                <input id="calle" name="calle" type="text" value="{{ old('calle', $property->Calle ?? '') }}" required maxlength="120" autocomplete="address-line1" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('calle')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="numero" class="block text-sm font-bold text-slate-700">Número</label>
                <input id="numero" name="numero" type="text" value="{{ old('numero', $property->Numero ?? '') }}" maxlength="20" autocomplete="address-line2" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('numero')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="piso" class="block text-sm font-bold text-slate-700">Piso</label>
                <input id="piso" name="piso" type="text" value="{{ old('piso', $property->Piso ?? '') }}" maxlength="20" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('piso')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="torre" class="block text-sm font-bold text-slate-700">Torre</label>
                <input id="torre" name="torre" type="text" value="{{ old('torre', $property->Torre ?? '') }}" maxlength="50" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('torre')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="codigo_postal" class="block text-sm font-bold text-slate-700">Código postal</label>
                <input id="codigo_postal" name="codigo_postal" type="text" value="{{ old('codigo_postal', $property->CodigoPostal ?? '') }}" maxlength="20" autocomplete="postal-code" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('codigo_postal')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="provincia" class="block text-sm font-bold text-slate-700">Provincia</label>
                <input id="provincia" name="provincia" type="text" value="{{ old('provincia', $property->Provincia ?? '') }}" maxlength="100" autocomplete="address-level1" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('provincia')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="partido" class="block text-sm font-bold text-slate-700">Partido</label>
                <input id="partido" name="partido" type="text" value="{{ old('partido', $property->Partido ?? '') }}" maxlength="100" autocomplete="address-level2" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('partido')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="localidad" class="block text-sm font-bold text-slate-700">Localidad</label>
                <input id="localidad" name="localidad" type="text" value="{{ old('localidad', $property->Localidad ?? '') }}" maxlength="100" autocomplete="address-level2" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('localidad')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="barrio" class="block text-sm font-bold text-slate-700">Barrio</label>
                <input id="barrio" name="barrio" type="text" value="{{ old('barrio', $property->Barrio ?? '') }}" maxlength="100" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('barrio')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="latitud" class="block text-sm font-bold text-slate-700">Latitud</label>
                <input id="latitud" name="latitud" type="number" value="{{ old('latitud', $property->Latitud ?? '') }}" min="-90" max="90" step="0.0000001" placeholder="-34.6037000" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('latitud')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="longitud" class="block text-sm font-bold text-slate-700">Longitud</label>
                <input id="longitud" name="longitud" type="number" value="{{ old('longitud', $property->Longitud ?? '') }}" min="-180" max="180" step="0.0000001" placeholder="-58.3816000" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-950 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20">
                @error('longitud')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-5 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" data-address-map>
            <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Ubicación en el mapa</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Verificá que el marcador coincida con la propiedad.</p>
                </div>
                <a href="#" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700 hover:text-emerald-800 hover:underline" data-address-map-link>
                    Abrir mapa completo
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M11.75 3a.75.75 0 0 0 0 1.5h2.69l-5.47 5.47a.75.75 0 1 0 1.06 1.06l5.47-5.47v2.69a.75.75 0 0 0 1.5 0v-4.5a.75.75 0 0 0-.75-.75h-4.5Z" />
                        <path d="M4.5 5.25a.75.75 0 0 0-.75.75v10.25h10.25a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 14 17.75H3.75a1.5 1.5 0 0 1-1.5-1.5V6A2.25 2.25 0 0 1 4.5 3.75h4a.75.75 0 0 1 0 1.5h-4Z" />
                    </svg>
                </a>
            </div>
            <iframe class="h-72 w-full border-0 sm:h-80" title="Mapa de la ubicación de la propiedad" loading="lazy" data-address-map-frame></iframe>
        </div>

        <div class="mt-5 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-6 text-sky-900">
            Al elegir un resultado se completan los campos disponibles. Revisalos antes de guardar: todos pueden corregirse manualmente.
        </div>
    </section>

    <section class="border-t border-slate-200 pt-8" aria-labelledby="property-multimedia-title">
        <div>
            <h2 id="property-multimedia-title" class="text-lg font-bold text-slate-950">Multimedia</h2>
            <p class="mt-1 text-sm text-slate-500">Las fotografías se administran en una galería separada y su indicador se sincroniza automáticamente.</p>
        </div>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div class="rounded-xl border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-slate-800">Fotografías</p>
                        @if ($property)
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                {{ $property->TieneFoto ? 'La propiedad tiene imágenes cargadas.' : 'La propiedad todavía no tiene imágenes.' }}
                            </p>
                        @else
                            <p class="mt-1 text-sm leading-5 text-slate-500">Podrás cargar las imágenes después de crear la propiedad.</p>
                        @endif
                    </div>

                    @if ($property)
                        <span @class([
                            'inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-bold',
                            'bg-emerald-50 text-emerald-700' => $property->TieneFoto,
                            'bg-slate-100 text-slate-600' => ! $property->TieneFoto,
                        ])>
                            {{ $property->TieneFoto ? 'Con fotos' : 'Sin fotos' }}
                        </span>
                    @endif
                </div>

                @if ($property)
                    <a href="{{ route('admin.properties.images.index', $property->id) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-800 hover:underline">
                        Administrar imágenes
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3.25 10a.75.75 0 0 1 .75-.75h10.19L10.97 6.03a.75.75 0 1 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 1 1-1.06-1.06l3.22-3.22H4a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label for="video_url" class="block text-sm font-bold text-slate-800">Video de la propiedad</label>
                    @if ($property?->TieneVideo)
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Video disponible</span>
                    @endif
                </div>
                <input
                    id="video_url"
                    name="video_url"
                    type="url"
                    value="{{ old('video_url', $property->VideoUrl ?? '') }}"
                    maxlength="500"
                    inputmode="url"
                    autocomplete="url"
                    placeholder="https://www.youtube.com/watch?v=..."
                    class="mt-3 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100"
                    aria-describedby="video_url_help"
                >
                <p id="video_url_help" class="mt-2 text-sm leading-5 text-slate-500">Pegá la URL pública de YouTube o Vimeo. El indicador de video se actualizará automáticamente.</p>
                @error('video_url')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 pt-8" aria-labelledby="property-publication-title">
        <div>
            <h2 id="property-publication-title" class="text-lg font-bold text-slate-950">Publicación</h2>
            <p class="mt-1 text-sm text-slate-500">Definí si la propiedad debe destacarse. El slug se utiliza como identificador estable para su futura URL pública.</p>
        </div>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <input type="hidden" name="destacada" value="0">
                <label for="destacada" class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50">
                    <input
                        id="destacada"
                        name="destacada"
                        type="checkbox"
                        value="1"
                        @checked((bool) old('destacada', $property->Destacada ?? false))
                        class="mt-0.5 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
                    >
                    <span>
                        <span class="block text-sm font-bold text-slate-800">Propiedad destacada</span>
                        <span class="mt-1 block text-sm leading-5 text-slate-500">Podrá recibir una ubicación preferente en el futuro portal público.</span>
                    </span>
                </label>
                @error('destacada')
                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            @if ($property)
                <div>
                    <label for="property_slug" class="block text-sm font-bold text-slate-700">Slug actual</label>
                    <input id="property_slug" type="text" value="{{ $property->Slug }}" readonly class="mt-2 block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 font-mono text-sm text-slate-700 shadow-sm">
                    <p class="mt-2 text-xs leading-5 text-slate-500">Se conserva aunque cambien los datos de la propiedad para no romper su futura URL.</p>
                </div>

                <div class="md:col-span-2">
                    <input type="hidden" name="regenerar_slug" value="0">
                    <label for="regenerar_slug" class="flex cursor-pointer items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 transition hover:bg-amber-100/70">
                        <input
                            id="regenerar_slug"
                            name="regenerar_slug"
                            type="checkbox"
                            value="1"
                            @checked((bool) old('regenerar_slug', false))
                            class="mt-0.5 h-5 w-5 rounded border-amber-400 text-amber-600 focus:ring-amber-600"
                        >
                        <span>
                            <span class="block text-sm font-bold text-amber-950">Regenerar slug al guardar</span>
                            <span class="mt-1 block text-sm leading-5 text-amber-800">Usará la tipología, ambientes, barrio o localidad y código actuales. Esta acción puede cambiar la futura URL pública.</span>
                        </span>
                    </label>
                    @error('regenerar_slug')
                        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-6 text-sky-900">
                    El slug se generará automáticamente al crear la propiedad usando su tipología, ambientes, barrio o localidad y código.
                </div>
            @endif
        </div>
    </section>

    <section class="border-t border-slate-200 pt-8" aria-labelledby="property-status-title">
        <h2 id="property-status-title" class="text-lg font-bold text-slate-950">Estado</h2>

        <input type="hidden" name="hab" value="0">
        <label for="hab" class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50">
            <input
                id="hab"
                name="hab"
                type="checkbox"
                value="1"
                @checked((bool) old('hab', $property->Hab ?? true))
                class="mt-0.5 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600"
            >
            <span>
                <span class="block text-sm font-bold text-slate-800">Propiedad habilitada</span>
                <span class="mt-1 block text-sm leading-5 text-slate-500">Estará disponible para continuar su carga y utilizarla en el sistema.</span>
            </span>
        </label>
        @error('hab')
            <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
        @enderror
    </section>
</div>
