<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1b5e20">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="AlfaFut">
    <meta name="mobile-web-app-capable" content="yes">

    <title>{{ $title ?? '' }}{{ isset($title) ? ' | ' : '' }}{{ config('app.name', 'AlfaFut') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto:400,500,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface text-on-surface">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-primary focus:text-on-primary focus:px-4 focus:py-2 focus:rounded">Pular para o conteudo</a>

    <header class="bg-primary text-on-primary" role="banner">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline shrink-0" aria-label="AlfaFut - Pagina inicial">
                <img src="{{ asset('images/logo/icon.svg') }}" alt="" class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-white p-1" aria-hidden="true">
                <span class="font-bold text-lg sm:text-xl text-white">AlfaFut</span>
            </a>
            <nav aria-label="Acesso" class="flex items-center gap-2 shrink-0">
                <a href="{{ route('home') }}" class="px-3 sm:px-4 py-2 rounded-full border-2 border-white text-white font-medium text-sm sm:text-base">Inicio</a>
            </nav>
        </div>
    </header>

    <main id="conteudo" role="main" class="flex flex-col items-center px-4 py-10 sm:py-16" tabindex="-1">
        <div class="w-full max-w-md card">
            {{ $slot }}
        </div>
    </main>

    <footer class="bg-gray-900 text-gray-100 mt-12" role="contentinfo">
        <div class="max-w-7xl mx-auto py-6 px-4 text-sm text-center">
            AlfaFut &middot; Atividade de Extensao Uniasselvi
        </div>
    </footer>

    @include('partials.pwa-script')
</body>
</html>
