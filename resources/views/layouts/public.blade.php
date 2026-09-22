<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
    <head>
        @php
            $seoTitle = trim($__env->yieldContent('title', config('app.name', 'PandaGestion')));
            $seoDescription = trim($__env->yieldContent('meta_description', 'Buscá propiedades disponibles y consultá directamente por cada publicación.'));
            $seoCanonicalUrl = trim($__env->yieldContent('canonical_url', \App\Support\PublicUrl::route('home')));
            $seoRobots = trim($__env->yieldContent('meta_robots', 'index,follow,max-image-preview:large'));
            $seoType = trim($__env->yieldContent('meta_type', 'website'));
            $seoSiteName = trim($__env->yieldContent('site_name', $brandName ?? config('app.name', 'PandaGestion')));
            $seoImageUrl = trim($__env->yieldContent('meta_image'));
            $seoImageAlt = trim($__env->yieldContent('meta_image_alt', $seoTitle));
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $seoDescription }}">
        <meta name="robots" content="{{ $seoRobots }}">
        <meta name="theme-color" content="#0f172a">

        <title>{{ $seoTitle }}</title>
        <link rel="canonical" href="{{ $seoCanonicalUrl }}">

        <meta property="og:locale" content="es_AR">
        <meta property="og:type" content="{{ $seoType }}">
        <meta property="og:site_name" content="{{ $seoSiteName }}">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:url" content="{{ $seoCanonicalUrl }}">

        <meta name="twitter:card" content="{{ $seoImageUrl !== '' ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">

        @if ($seoImageUrl !== '')
            <meta property="og:image" content="{{ $seoImageUrl }}">
            <meta property="og:image:alt" content="{{ $seoImageAlt }}">
            <meta name="twitter:image" content="{{ $seoImageUrl }}">
            <meta name="twitter:image:alt" content="{{ $seoImageAlt }}">
        @endif

        @stack('structured_data')

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/public.js'])
        @endif
    </head>
    <body class="min-h-screen overflow-x-hidden bg-stone-50 font-sans text-slate-900 antialiased selection:bg-emerald-200 selection:text-emerald-950">
        @yield('content')
    </body>
</html>
