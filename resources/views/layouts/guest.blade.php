<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Acceso') | PandaGestion</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-900 antialiased">
        <div class="auth-background fixed inset-0 overflow-hidden" aria-hidden="true">
            <div class="auth-background-pattern absolute inset-0"></div>
        </div>

        <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 sm:py-12">
            @yield('content')
        </main>
    </body>
</html>
