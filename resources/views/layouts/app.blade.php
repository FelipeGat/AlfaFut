<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-tema="{{ auth()->user()?->alto_contraste ? 'alto-contraste' : 'padrao' }}"
      data-fonte="{{ auth()->user()?->tamanho_fonte ?? 'media' }}"
      data-reduzir-movimento="{{ auth()->user()?->reduzir_movimento ? '1' : '0' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="AlfaFut - Solucao colaborativa para gestao de patota de futebol com suporte de acessibilidade.">
    <meta name="theme-color" content="#1b5e20">

    <title>{{ $title ?? '' }}{{ isset($title) ? ' | ' : '' }}{{ config('app.name', 'AlfaFut') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto:400,500,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface text-on-surface">
    {{-- WCAG 2.4.1 Bypass Blocks: skip link --}}
    <a href="#conteudo-principal" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-primary focus:text-on-primary focus:px-4 focus:py-2 focus:rounded">
        Pular para o conteudo principal
    </a>

    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        @isset($header)
            <header class="bg-primary text-on-primary shadow-md" role="banner">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main id="conteudo-principal" class="flex-1" role="main" tabindex="-1">
            @if (session('status'))
                <div role="status" aria-live="polite" class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                    <div class="bg-green-50 border-l-4 border-green-700 text-green-900 p-4 rounded">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="bg-gray-900 text-gray-100 mt-12" role="contentinfo">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-sm">
                <p>AlfaFut &middot; Atividade de Extensao Uniasselvi &middot; ADS / Gestao de Tecnologia</p>
                <p class="mt-1 opacity-80">Desenvolvido seguindo Modelo 3C (Comunicacao, Coordenacao, Cooperacao), Material Design e WCAG 2.1.</p>
            </div>
        </footer>
    </div>
</body>
</html>
