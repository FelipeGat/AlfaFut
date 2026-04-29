<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sem conexao - AlfaFut</title>
    <meta name="theme-color" content="#1b5e20">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto:400,500,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface" style="min-height: 100vh;">
    <main class="flex flex-col items-center justify-center min-h-screen px-4 text-center">
        <img src="{{ asset('images/logo/icon.svg') }}" alt="AlfaFut" class="h-24 w-24 mb-6 anim-pulso">
        <h1 class="text-2xl sm:text-3xl font-bold mb-2" style="color: var(--md-sys-color-primary);">Voce esta offline</h1>
        <p class="text-gray-700 max-w-md mb-6">
            Sem conexao com a internet no momento. Verifique seu sinal de Wi-Fi ou dados moveis e tente novamente.
        </p>
        <button onclick="location.reload()" class="btn btn-primary">Tentar novamente</button>
        <p class="text-xs text-gray-500 mt-8">
            Algumas paginas podem estar disponiveis em cache. Tente navegar pelos seus favoritos.
        </p>
    </main>
</body>
</html>
