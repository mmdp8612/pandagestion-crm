@extends('layouts.public')

@php
    $hasValue = static fn (mixed $value): bool => $value !== null && $value !== '';
    $formatDecimal = static function (mixed $value, string $unit): ?string {
        if ($value === null) {
            return null;
        }

        $formatted = rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');

        return $formatted.' '.$unit;
    };
    $formatPrice = static function (mixed $amount, mixed $symbol, mixed $description): ?string {
        if ($amount === null) {
            return null;
        }

        $value = (float) $amount;
        $decimals = floor($value) === $value ? 0 : 2;
        $currency = $symbol ?: $description ?: '';

        return trim($currency.' '.number_format($value, $decimals, ',', '.'));
    };
    $streetAddress = trim(implode(' ', array_filter([$property->Calle, $property->Numero], $hasValue)));
    $unitDetails = array_filter([
        $hasValue($property->Piso) ? 'Piso '.$property->Piso : null,
        $hasValue($property->Torre) ? 'Torre '.$property->Torre : null,
    ], $hasValue);
    $location = implode(', ', array_filter([
        $property->Barrio,
        $property->Localidad,
        $property->Partido,
        $property->Provincia,
    ], $hasValue));
    $titleLocation = $property->Barrio ?: $property->Localidad;
    $propertyTitle = trim(($property->TipologiaDescripcion ?: 'Propiedad').($titleLocation ? ' en '.$titleLocation : ''));
    $salePrice = $formatPrice($property->ImporteVta, $property->MonedaVentaSimbolo, $property->MonedaVentaDescripcion);
    $rentPrice = $formatPrice($property->ImporteAlq, $property->MonedaAlquilerSimbolo, $property->MonedaAlquilerDescripcion);
    $secondaryImages = $images
        ->reject(fn ($image) => $coverImage && $image->id === $coverImage->id)
        ->values();
    $contactUrl = $whatsappUrl
        ? $whatsappUrl.'?text='.rawurlencode('Hola, quisiera consultar por la propiedad '.$property->Codigo.' ('.route('public.properties.show', $property->Slug).').')
        : ($agency?->Email
            ? 'mailto:'.$agency->Email.'?subject='.rawurlencode('Consulta por '.$property->Codigo)
            : route('home').'#contacto');
    $opensExternally = $whatsappUrl !== null;
    $featureItems = array_filter([
        ['label' => 'Ambientes', 'value' => $hasValue($property->Ambientes) ? (string) $property->Ambientes : null],
        ['label' => 'Dormitorios', 'value' => $hasValue($property->Dormitorios) ? (string) $property->Dormitorios : null],
        ['label' => 'Baños', 'value' => $hasValue($property->Sanitarios) ? (string) $property->Sanitarios : null],
        ['label' => 'Sup. cubierta', 'value' => $formatDecimal($property->SupCubiertaPropia, 'm²')],
        ['label' => 'Sup. terreno', 'value' => $formatDecimal($property->SupTerreno, 'm²')],
    ], fn (array $item): bool => $item['value'] !== null);
    $classificationItems = array_filter([
        ['label' => 'Tipo de propiedad', 'value' => $property->TipologiaDescripcion],
        ['label' => 'Uso', 'value' => $property->UsoDescripcion],
        ['label' => 'Antigüedad', 'value' => $property->AntiguedadDescripcion],
        ['label' => 'Orientación', 'value' => $property->OrientacionDescripcion],
        ['label' => 'Cochera', 'value' => $property->CocheraDescripcion],
        ['label' => 'Vista', 'value' => $property->VistaDescripcion],
    ], fn (array $item): bool => $hasValue($item['value']));
    $physicalItems = array_filter([
        ['label' => 'Plantas', 'value' => $hasValue($property->Plantas) ? (string) $property->Plantas : null],
        ['label' => 'Dormitorios en suite', 'value' => $hasValue($property->Suite) ? (string) $property->Suite : null],
        ['label' => 'Frente', 'value' => $formatDecimal($property->Frente, 'm²')],
        ['label' => 'Fondo', 'value' => $formatDecimal($property->Fondo, 'm²')],
        ['label' => 'Metros de fondo', 'value' => $formatDecimal($property->MtsFondo, 'm')],
        ['label' => 'Luminosidad', 'value' => $property->Luminosidad],
        ['label' => 'Líneas telefónicas', 'value' => $hasValue($property->LineasTel) ? (string) $property->LineasTel : null],
    ], fn (array $item): bool => $hasValue($item['value']));

    $propertyCanonicalUrl = \App\Support\PublicUrl::route('public.properties.show', ['slug' => $property->Slug]);
    $propertyImageUrls = $images
        ->map(fn ($image): ?string => \App\Support\PublicUrl::storage($image->Archivo))
        ->filter()
        ->values()
        ->all();
    $propertySocialImage = $coverImage
        ? \App\Support\PublicUrl::storage($coverImage->Archivo)
        : \App\Support\PublicUrl::storage($agency?->Logo);
    $propertyMetaDescription = \Illuminate\Support\Str::limit(implode('. ', array_filter([
        $property->ComercializacionDescripcion
            ? $property->ComercializacionDescripcion.' de '.mb_strtolower($propertyTitle)
            : $propertyTitle,
        $hasValue($property->Ambientes) ? $property->Ambientes.' ambientes' : null,
        $salePrice ? 'Venta '.$salePrice : null,
        $rentPrice ? 'Alquiler '.$rentPrice : null,
        $location ? 'Ubicación: '.$location : null,
        'Código '.$property->Codigo,
    ])).'.', 160, '…');
    $homeCanonicalUrl = \App\Support\PublicUrl::route('home');
    $organizationId = $homeCanonicalUrl.'#organization';
    $organization = [
        '@type' => $agency ? 'RealEstateAgent' : 'Organization',
        '@id' => $organizationId,
        'name' => $brandName,
        'url' => $homeCanonicalUrl,
    ];

    if ($agency?->Email) {
        $organization['email'] = $agency->Email;
    }

    if ($agency?->Telefonos) {
        $organization['telephone'] = $agency->Telefonos;
    }

    if ($agency?->Web) {
        $organization['sameAs'] = [$agency->Web];
    }

    if ($agency?->Logo) {
        $organization['logo'] = \App\Support\PublicUrl::storage($agency->Logo);
    }

    $propertyPlaceId = $propertyCanonicalUrl.'#property';
    $propertyPlace = [
        '@type' => 'Place',
        '@id' => $propertyPlaceId,
        'name' => $propertyTitle,
        'description' => $property->Descrip ?: $propertyMetaDescription,
        'identifier' => $property->Codigo,
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $streetAddress ?: null,
            'addressLocality' => $property->Localidad ?: $property->Barrio,
            'addressRegion' => $property->Provincia,
            'postalCode' => $property->CodigoPostal,
        ]),
    ];

    if ($mapLinks) {
        $propertyPlace['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) $property->Latitud,
            'longitude' => (float) $property->Longitud,
        ];
    }

    if ($propertyImageUrls !== []) {
        $propertyPlace['image'] = $propertyImageUrls;
    }

    $currencyCode = static fn (mixed $currencyId): ?string => match ((int) $currencyId) {
        0 => 'USD',
        1 => 'ARS',
        default => null,
    };
    $offers = [];

    if ($property->ImporteVta !== null) {
        $offers[] = array_filter([
            '@type' => 'Offer',
            'name' => 'Venta de '.$propertyTitle,
            'price' => (string) $property->ImporteVta,
            'priceCurrency' => $currencyCode($property->idTipoMonedaVta),
            'availability' => 'https://schema.org/InStock',
            'businessFunction' => 'http://purl.org/goodrelations/v1#Sell',
            'url' => $propertyCanonicalUrl,
            'seller' => ['@id' => $organizationId],
        ], $hasValue);
    }

    if ($property->ImporteAlq !== null) {
        $offers[] = array_filter([
            '@type' => 'Offer',
            'name' => 'Alquiler de '.$propertyTitle,
            'price' => (string) $property->ImporteAlq,
            'priceCurrency' => $currencyCode($property->idTipoMonedaAlq),
            'availability' => 'https://schema.org/InStock',
            'businessFunction' => 'http://purl.org/goodrelations/v1#LeaseOut',
            'url' => $propertyCanonicalUrl,
            'seller' => ['@id' => $organizationId],
        ], $hasValue);
    }

    $listing = [
        '@type' => 'RealEstateListing',
        '@id' => $propertyCanonicalUrl.'#listing',
        'url' => $propertyCanonicalUrl,
        'name' => $propertyTitle,
        'description' => $propertyMetaDescription,
        'identifier' => $property->Codigo,
        'inLanguage' => 'es-AR',
        'datePosted' => \Carbon\CarbonImmutable::parse($property->created_at)->toAtomString(),
        'dateModified' => \Carbon\CarbonImmutable::parse($property->updated_at)->toAtomString(),
        'publisher' => ['@id' => $organizationId],
        'mainEntity' => ['@id' => $propertyPlaceId],
        'about' => array_merge([['@id' => $propertyPlaceId]], $offers),
    ];

    if ($propertyImageUrls !== []) {
        $listing['image'] = $propertyImageUrls;
        $listing['primaryImageOfPage'] = [
            '@type' => 'ImageObject',
            'contentUrl' => $propertySocialImage,
        ];
    }

    $propertyStructuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $listing,
            $propertyPlace,
            $organization,
            [
                '@type' => 'BreadcrumbList',
                '@id' => $propertyCanonicalUrl.'#breadcrumbs',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Inicio',
                        'item' => $homeCanonicalUrl,
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Propiedades',
                        'item' => \App\Support\PublicUrl::route('public.properties.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $propertyTitle,
                        'item' => $propertyCanonicalUrl,
                    ],
                ],
            ],
        ],
    ];
    $propertyStructuredDataJson = json_encode(
        $propertyStructuredData,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
    );
@endphp

@section('title', $propertyTitle.' | '.$brandName)
@section('meta_description', $propertyMetaDescription)
@section('canonical_url', $propertyCanonicalUrl)
@if ($propertySocialImage)
    @section('meta_image', $propertySocialImage)
    @section('meta_image_alt', $coverImage ? 'Imagen principal de la propiedad '.$property->Codigo : 'Logo de '.$brandName)
@endif

@push('structured_data')
    <script type="application/ld+json">{!! $propertyStructuredDataJson !!}</script>
@endpush

@section('content')
    @include('public.partials.header')

    <main class="bg-[#f4f6f3]">
        <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10 lg:px-10">
            <nav aria-label="Migas de pan" class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500">
                <a href="{{ route('home') }}" class="transition hover:text-emerald-700">Inicio</a>
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L11.94 10 7.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                <a href="{{ route('public.properties.index') }}" class="transition hover:text-emerald-700">Propiedades</a>
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.22 4.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L11.94 10 7.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                <span class="text-slate-800" aria-current="page">{{ $property->Codigo }}</span>
            </nav>

            <div class="mt-7 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-4xl">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($property->ComercializacionDescripcion)
                            <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-emerald-800">{{ $property->ComercializacionDescripcion }}</span>
                        @endif
                        @if ($property->Destacada)
                            <span class="rounded-full bg-amber-100 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-amber-800">Destacada</span>
                        @endif
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 font-mono text-[10px] font-bold text-slate-600">Código {{ $property->Codigo }}</span>
                    </div>
                    <h1 class="mt-4 font-serif text-4xl font-semibold leading-tight tracking-tight text-slate-950 sm:text-5xl">{{ $propertyTitle }}</h1>
                    <p class="mt-4 flex items-start gap-2 text-base leading-7 text-slate-600">
                        <svg class="mt-1 h-5 w-5 shrink-0 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s6.75-6.37 6.75-12A6.75 6.75 0 1 0 5.25 9C5.25 14.63 12 21 12 21Z" /><circle cx="12" cy="9" r="2.25" /></svg>
                        <span>{{ implode(' · ', array_filter([$streetAddress, $unitDetails ? implode(' · ', $unitDetails) : null, $location], $hasValue)) }}</span>
                    </p>
                </div>

                <div class="shrink-0 lg:text-right">
                    @if ($salePrice)
                        <p class="text-3xl font-black tracking-tight text-slate-950">{{ $salePrice }}</p>
                        <p class="mt-1 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-700">Venta</p>
                    @elseif ($rentPrice)
                        <p class="text-3xl font-black tracking-tight text-slate-950">{{ $rentPrice }}</p>
                        <p class="mt-1 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-700">Alquiler</p>
                    @else
                        <p class="text-xl font-black text-slate-950">Precio a consultar</p>
                    @endif
                    @if ($salePrice && $rentPrice)
                        <p class="mt-2 text-sm font-bold text-slate-500">Alquiler: {{ $rentPrice }}</p>
                    @endif
                </div>
            </div>

            <section class="mt-8 grid gap-3 overflow-hidden rounded-3xl lg:grid-cols-[minmax(0,1.7fr)_minmax(18rem,0.8fr)]" aria-label="Galería principal">
                <div class="min-h-72 overflow-hidden rounded-3xl bg-slate-200 lg:min-h-[34rem]">
                    @if ($coverImage)
                        <a href="{{ Storage::disk('public')->url($coverImage->Archivo) }}" target="_blank" rel="noopener noreferrer" class="group block h-full focus:outline-none focus:ring-4 focus:ring-inset focus:ring-emerald-500" aria-label="Abrir imagen principal de {{ $property->Codigo }}">
                            <img src="{{ Storage::disk('public')->url($coverImage->Archivo) }}" alt="Imagen principal de la propiedad {{ $property->Codigo }}" class="h-full min-h-72 w-full object-cover transition duration-500 group-hover:scale-[1.015] lg:min-h-[34rem]" fetchpriority="high">
                        </a>
                    @else
                        <div class="flex h-full min-h-72 flex-col items-center justify-center bg-gradient-to-br from-slate-100 to-stone-200 px-6 text-center text-slate-400 lg:min-h-[34rem]">
                            <svg class="h-16 w-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.91 2.91M3.75 19.5h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                            <p class="mt-4 text-sm font-black uppercase tracking-[0.14em] text-slate-500">Sin imágenes publicadas</p>
                        </div>
                    @endif
                </div>

                @if ($secondaryImages->isNotEmpty())
                    <div class="grid grid-cols-2 gap-3 lg:grid-cols-1 lg:grid-rows-2">
                        @foreach ($secondaryImages->take(2) as $image)
                            <a href="{{ Storage::disk('public')->url($image->Archivo) }}" target="_blank" rel="noopener noreferrer" class="group relative min-h-36 overflow-hidden rounded-3xl bg-slate-200 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-emerald-500 lg:min-h-0" aria-label="Abrir imagen {{ $loop->iteration + 1 }} de {{ $property->Codigo }}">
                                <img src="{{ Storage::disk('public')->url($image->Archivo) }}" alt="Imagen {{ $loop->iteration + 1 }} de la propiedad {{ $property->Codigo }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                @if ($loop->last && $secondaryImages->count() > 2)
                                    <span class="absolute inset-0 flex items-center justify-center bg-slate-950/55 text-sm font-black text-white backdrop-blur-[1px]">+ {{ $secondaryImages->count() - 2 }} imágenes</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
                <div class="space-y-8">
                    @if ($featureItems)
                        <section class="grid grid-cols-2 gap-px overflow-hidden rounded-3xl border border-slate-200 bg-slate-200 shadow-sm sm:grid-cols-3 lg:grid-cols-5" aria-label="Características principales">
                            @foreach ($featureItems as $item)
                                <div class="{{ count($featureItems) % 2 === 1 && $loop->last ? 'col-span-2 lg:col-span-1' : '' }} bg-white px-4 py-5 text-center">
                                    <p class="text-lg font-black text-slate-950">{{ $item['value'] }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-[0.13em] text-slate-400">{{ $item['label'] }}</p>
                                </div>
                            @endforeach
                        </section>
                    @endif

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="description-title">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Sobre la propiedad</p>
                        <h2 id="description-title" class="mt-3 font-serif text-3xl font-semibold text-slate-950">Descripción</h2>
                        @if ($property->Descrip)
                            <p class="mt-5 whitespace-pre-line text-base leading-8 text-slate-600">{{ $property->Descrip }}</p>
                        @else
                            <p class="mt-5 text-base leading-8 text-slate-500">Consultanos para conocer más información sobre esta propiedad.</p>
                        @endif
                    </section>

                    @if ($video)
                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm" aria-labelledby="video-title">
                            <div class="flex flex-col gap-3 p-6 sm:flex-row sm:items-end sm:justify-between sm:p-8">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Recorrido audiovisual</p>
                                    <h2 id="video-title" class="mt-3 font-serif text-3xl font-semibold text-slate-950">Conocé la propiedad en video</h2>
                                </div>
                                <a href="{{ $video['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-fit text-sm font-black text-emerald-700 hover:underline">Ver en {{ $video['label'] }}</a>
                            </div>
                            <div class="aspect-video border-t border-slate-200 bg-slate-950">
                                <iframe
                                    src="{{ $video['embed'] }}"
                                    class="h-full w-full border-0"
                                    title="Video de la propiedad {{ $property->Codigo }} en {{ $video['label'] }}"
                                    loading="lazy"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </section>
                    @endif

                    <section id="consulta" class="scroll-mt-28 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="inquiry-form-title">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Hablemos</p>
                        <h2 id="inquiry-form-title" class="mt-3 font-serif text-3xl font-semibold text-slate-950">Consultá por esta propiedad</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Dejanos tus datos y tu mensaje. La consulta quedará asociada a la referencia <strong>{{ $property->Codigo }}</strong>.</p>

                        @if (session('success'))
                            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                                <p class="font-black">Consulta enviada</p>
                                <p class="mt-1">{{ session('success') }}</p>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                                <p class="font-black">Revisá los datos ingresados</p>
                                <p class="mt-1">Hay campos que necesitan tu atención antes de enviar la consulta.</p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('public.properties.inquiries.store', $property->Slug) }}#consulta" class="mt-7 grid gap-5 sm:grid-cols-2">
                            @csrf

                            <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                                <label for="website">Sitio web</label>
                                <input id="website" name="website" type="text" value="" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="nombre" class="block text-sm font-black text-slate-700">Nombre y apellido</label>
                                <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}" maxlength="120" autocomplete="name" required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" placeholder="¿Cómo te llamás?">
                                @error('nombre')<p class="mt-2 text-sm font-semibold text-red-600" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-black text-slate-700">Correo electrónico</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="190" autocomplete="email" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" placeholder="nombre@correo.com">
                                @error('email')<p class="mt-2 text-sm font-semibold text-red-600" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="telefono" class="block text-sm font-black text-slate-700">Teléfono</label>
                                <input id="telefono" name="telefono" type="tel" value="{{ old('telefono') }}" maxlength="50" autocomplete="tel" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" placeholder="Ej.: 11 5555-5555">
                                @error('telefono')<p class="mt-2 text-sm font-semibold text-red-600" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="mensaje" class="block text-sm font-black text-slate-700">Mensaje</label>
                                <textarea id="mensaje" name="mensaje" rows="5" maxlength="2000" required class="mt-2 block w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-950 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" placeholder="Contanos qué te gustaría saber sobre esta propiedad.">{{ old('mensaje') }}</textarea>
                                @error('mensaje')<p class="mt-2 text-sm font-semibold text-red-600" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div class="flex flex-col gap-3 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs leading-5 text-slate-500">Completá al menos un correo electrónico o un teléfono para que podamos responderte.</p>
                                <button type="submit" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-emerald-900/15 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                                    Enviar consulta
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4 4 17 8-17 8 3-8-3-8Zm3 8h14" /></svg>
                                </button>
                            </div>
                        </form>
                    </section>

                    @if ($classificationItems || $physicalItems)
                        <section class="grid gap-6 md:grid-cols-2" aria-label="Detalles de la propiedad">
                            @if ($classificationItems)
                                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                                    <h2 class="font-serif text-2xl font-semibold text-slate-950">Clasificación</h2>
                                    <dl class="mt-6 divide-y divide-slate-100">
                                        @foreach ($classificationItems as $item)
                                            <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                                <dt class="text-sm text-slate-500">{{ $item['label'] }}</dt>
                                                <dd class="text-right text-sm font-black text-slate-900">{{ $item['value'] }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </article>
                            @endif

                            @if ($physicalItems)
                                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                                    <h2 class="font-serif text-2xl font-semibold text-slate-950">Características</h2>
                                    <dl class="mt-6 divide-y divide-slate-100">
                                        @foreach ($physicalItems as $item)
                                            <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                                <dt class="text-sm text-slate-500">{{ $item['label'] }}</dt>
                                                <dd class="text-right text-sm font-black text-slate-900">{{ $item['value'] }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </article>
                            @endif
                        </section>
                    @endif

                    @if ($images->count() > 3)
                        <section aria-labelledby="gallery-title">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Todos los ambientes</p>
                                <h2 id="gallery-title" class="mt-3 font-serif text-3xl font-semibold text-slate-950">Galería completa</h2>
                            </div>
                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                @foreach ($images as $image)
                                    <a href="{{ Storage::disk('public')->url($image->Archivo) }}" target="_blank" rel="noopener noreferrer" class="group relative aspect-[4/3] overflow-hidden rounded-3xl bg-slate-200 shadow-sm focus:outline-none focus:ring-4 focus:ring-emerald-300" aria-label="Abrir imagen {{ $loop->iteration }} de {{ $property->Codigo }}">
                                        <img src="{{ Storage::disk('public')->url($image->Archivo) }}" alt="Imagen {{ $loop->iteration }} de la propiedad {{ $property->Codigo }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm" aria-labelledby="location-title">
                        <div class="p-6 sm:p-8">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Dónde está</p>
                            <h2 id="location-title" class="mt-3 font-serif text-3xl font-semibold text-slate-950">Ubicación</h2>
                            <p class="mt-4 text-base font-bold text-slate-800">{{ $streetAddress ?: 'Domicilio a consultar' }}</p>
                            @if ($unitDetails)
                                <p class="mt-1 text-sm text-slate-500">{{ implode(' · ', $unitDetails) }}</p>
                            @endif
                            @if ($location)
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $location }}</p>
                            @endif
                            @if ($property->CodigoPostal)
                                <p class="mt-1 text-xs font-semibold text-slate-500">Código postal {{ $property->CodigoPostal }}</p>
                            @endif
                        </div>

                        @if ($mapLinks)
                            <div class="border-t border-slate-200">
                                <iframe src="{{ $mapLinks['embed'] }}" class="h-80 w-full border-0 lg:h-96" title="Mapa de la ubicación de la propiedad {{ $property->Codigo }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                <div class="flex flex-col gap-2 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                                    <p class="text-xs text-slate-500">Mapa © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer" class="font-semibold underline hover:text-slate-700">OpenStreetMap contributors</a></p>
                                    <a href="{{ $mapLinks['full'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-black text-emerald-700 hover:underline">
                                        Abrir mapa completo
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18m0 0v4.5M18 6l-7.5 7.5M15 13.5V18H6V9h4.5" /></svg>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="order-first rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/5 lg:order-none lg:sticky lg:top-28" aria-labelledby="contact-property-title">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">Consulta directa</p>
                    <h2 id="contact-property-title" class="mt-3 text-2xl font-black tracking-tight text-slate-950">¿Te interesa esta propiedad?</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Contactanos indicando la referencia <strong>{{ $property->Codigo }}</strong> y te brindamos más información.</p>

                    <div class="mt-6 rounded-2xl bg-slate-50 p-4">
                        @if ($salePrice)
                            <p class="text-xl font-black text-slate-950">{{ $salePrice }}</p>
                            <p class="mt-1 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Valor de venta</p>
                        @endif
                        @if ($rentPrice)
                            <p class="{{ $salePrice ? 'mt-4' : '' }} text-xl font-black text-slate-950">{{ $rentPrice }}</p>
                            <p class="mt-1 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Valor de alquiler</p>
                        @endif
                        @if (! $salePrice && ! $rentPrice)
                            <p class="font-black text-slate-950">Precio a consultar</p>
                        @endif
                    </div>

                    <a href="#consulta" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-emerald-900/15 transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                        Consultar por {{ $property->Codigo }}
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                    </a>

                    @if ($whatsappUrl || $agency?->Email)
                        <a href="{{ $contactUrl }}" @if ($opensExternally) target="_blank" rel="noopener noreferrer" @endif class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">
                            {{ $whatsappUrl ? 'Consultar por WhatsApp' : 'Enviar un correo directo' }}
                        </a>
                    @endif

                    <div class="mt-6 border-t border-slate-100 pt-6">
                        <div class="flex items-center gap-3">
                            @if ($agency?->Logo)
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white"><img src="{{ Storage::disk('public')->url($agency->Logo) }}" alt="" class="h-full w-full object-contain p-1"></span>
                            @else
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-950 font-black text-emerald-300">P</span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-slate-950">{{ $brandName }}</p>
                                @if ($agency?->Matricula)
                                    <p class="mt-0.5 text-xs text-slate-500">Matrícula {{ $agency->Matricula }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 space-y-2 text-sm font-semibold text-slate-600">
                            @if ($agency?->Telefonos)
                                <p><a href="{{ $phoneUrl }}" class="transition hover:text-emerald-700">{{ $agency->Telefonos }}</a></p>
                            @endif
                            @if ($agency?->Email)
                                <p class="break-all"><a href="mailto:{{ $agency->Email }}" class="transition hover:text-emerald-700">{{ $agency->Email }}</a></p>
                            @endif
                        </div>
                    </div>
                </aside>
            </div>

            <div class="mt-12 border-t border-slate-200 pt-8">
                <a href="{{ route('public.properties.index') }}" class="inline-flex items-center gap-2 text-sm font-black text-emerald-700 transition hover:text-emerald-900">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6 6-6-6 6-6" /></svg>
                    Volver a todas las propiedades
                </a>
            </div>
        </div>
    </main>

    @include('public.partials.footer')
@endsection
