<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AlfaFut - Sua patota organizada e acessivel</title>
    <meta name="description" content="Solucao colaborativa para gestao de patota de futebol com suporte de acessibilidade.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto:400,500,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-primary focus:text-on-primary focus:px-4 focus:py-2 focus:rounded">Pular para o conteudo</a>

    <header class="bg-primary text-on-primary" role="banner">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-primary font-bold" aria-hidden="true">A</span>
                <span class="font-bold text-xl">AlfaFut</span>
            </div>
            <nav aria-label="Acesso">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-full bg-white text-primary font-medium">Entrar no painel</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-full bg-white text-primary font-medium">Entrar</a>
                    <a href="{{ route('register') }}" class="ms-2 px-4 py-2 rounded-full border border-white text-white font-medium">Criar conta</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="conteudo" role="main">
        <section class="relative overflow-hidden bg-primary text-on-primary py-16"
                 style="background-image: linear-gradient(rgba(27,94,32,0.85), rgba(27,94,32,0.95)), url('{{ asset('images/eventos/evento-01.jpg') }}'); background-size: cover; background-position: center;"
                 aria-label="Foto de capa: jogadores em campo society">
            <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-8 items-center relative">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Sua patota organizada do jeito que ela merece.</h1>
                    <p class="text-lg opacity-95 mb-6">Gerencie partidas, confirme presencas, divida despesas e converse com o time num espaco compartilhado e acessivel a todos.</p>
                    <a href="{{ route('register') }}" class="btn btn-secondary text-lg">Comecar gratis</a>
                </div>
                <div class="card text-on-surface">
                    <h2 class="text-xl font-semibold mb-3">Por que AlfaFut?</h2>
                    <ul class="space-y-2 list-disc pl-6">
                        <li>Confirmacao de presenca e lista de espera automatica.</li>
                        <li>Sorteio justo de times respeitando posicao e nivel.</li>
                        <li>Rateio automatico de despesas por partida.</li>
                        <li>Mural de comunicacao com todos da patota.</li>
                        <li>Acessibilidade (WCAG 2.1): alto contraste, fonte ajustavel, leitor de tela.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white" aria-labelledby="galeria-h">
            <div class="max-w-6xl mx-auto px-4">
                <h2 id="galeria-h" class="text-2xl font-bold mb-2">A patota em acao</h2>
                <p class="text-gray-700 mb-6">Registros de partidas reais da comunidade que inspirou o projeto.</p>
                <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" role="list">
                    @for ($i = 1; $i <= 12; $i++)
                        <li>
                            <img src="{{ asset(sprintf('images/eventos/evento-%02d.jpg', $i)) }}"
                                 alt="Foto {{ $i }} de partida da patota - jogadores em campo society"
                                 class="w-full h-40 object-cover rounded-lg"
                                 loading="lazy">
                        </li>
                    @endfor
                </ul>
            </div>
        </section>

        <section class="py-16">
            <div class="max-w-6xl mx-auto px-4">
                <h2 class="text-2xl font-bold mb-6">Modelo 3C de Colaboracao</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="card">
                        <h3>Comunicacao</h3>
                        <p class="mt-2 text-gray-700">Mural da patota, avisos fixados e mensagens por partida para todos saberem o que esta acontecendo.</p>
                    </div>
                    <div class="card">
                        <h3>Coordenacao</h3>
                        <p class="mt-2 text-gray-700">Calendario de partidas, confirmacao de presenca, lista de espera e formacao de times.</p>
                    </div>
                    <div class="card">
                        <h3>Cooperacao</h3>
                        <p class="mt-2 text-gray-700">Rateio de despesas, registro de pagamentos e acompanhamento de saldos em conjunto.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-gray-900 text-gray-100 py-6" role="contentinfo">
        <div class="max-w-6xl mx-auto px-4 text-sm">
            AlfaFut &middot; Atividade de Extensao Uniasselvi - 2026 &middot; ADS / Gestao de Tecnologia.
        </div>
    </footer>
</body>
</html>
