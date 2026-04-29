<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AlfaFut - Sua turma organizada e acessivel</title>
    <meta name="description" content="Solucao colaborativa para gestao de turmas de futebol com suporte de acessibilidade.">
    <meta name="theme-color" content="#1b5e20">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192.png') }}">

    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="AlfaFut">
    <meta name="mobile-web-app-capable" content="yes">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto:400,500,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface" x-data="{ loginAberto: {{ $errors->any() && old('email') ? 'true' : 'false' }} }">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-primary focus:text-on-primary focus:px-4 focus:py-2 focus:rounded">Pular para o conteudo</a>

    <header class="bg-primary text-on-primary" role="banner">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline shrink-0" aria-label="AlfaFut - Pagina inicial">
                <img src="{{ asset('images/logo/icon.svg') }}" alt="" class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-white p-1" aria-hidden="true">
                <span class="font-bold text-lg sm:text-xl text-white">AlfaFut</span>
            </a>
            <nav aria-label="Acesso" class="flex items-center gap-1 sm:gap-2 shrink-0">
                <a href="{{ route('como-usar') }}" class="hidden md:inline-block px-4 py-2 rounded-full text-white font-medium hover:bg-white/10">Como usar</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3 sm:px-4 py-2 rounded-full bg-white font-medium text-sm sm:text-base" style="color: var(--md-sys-color-primary);">Painel</a>
                @else
                    <button type="button"
                            @click="loginAberto = !loginAberto"
                            :aria-expanded="loginAberto.toString()"
                            aria-controls="painel-login"
                            class="px-3 sm:px-4 py-2 rounded-full bg-white font-medium text-sm sm:text-base"
                            style="color: var(--md-sys-color-primary);">
                        <span x-text="loginAberto ? 'Fechar' : 'Entrar'">Entrar</span>
                    </button>
                    <a href="{{ route('register') }}" class="hidden sm:inline-block px-4 py-2 rounded-full border-2 border-white text-white font-medium">Criar conta</a>
                @endauth
            </nav>
        </div>

        @guest
            <div id="painel-login"
                 x-show="loginAberto"
                 x-cloak
                 x-transition
                 role="region"
                 aria-label="Formulario de login">
                <div class="max-w-6xl mx-auto px-4 pb-6">
                    <form method="POST" action="{{ route('login') }}"
                          class="bg-white rounded-2xl p-6 shadow-lg"
                          style="color: var(--md-sys-color-on-surface);">
                        @csrf
                        <h2 class="font-semibold text-lg mb-4" style="color: var(--md-sys-color-primary);">Entrar na sua conta</h2>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label for="login_email">E-mail</label>
                                <input id="login_email"
                                       name="email"
                                       type="email"
                                       class="input"
                                       required
                                       autofocus
                                       autocomplete="email"
                                       value="{{ old('email') }}"
                                       @error('email') aria-invalid="true" aria-describedby="login_email_erro" @enderror>
                                @error('email')<p id="login_email_erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="login_password">Senha</label>
                                <input id="login_password"
                                       name="password"
                                       type="password"
                                       class="input"
                                       required
                                       autocomplete="current-password">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
                            <label class="flex items-center gap-2 mb-0 text-sm">
                                <input type="checkbox" name="remember" value="1" class="h-4 w-4">
                                Lembrar de mim
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm underline" style="color: var(--md-sys-color-primary);">Esqueci minha senha</a>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary w-full mt-4">Entrar</button>
                    </form>
                </div>
            </div>
        @endguest
    </header>

    <main id="conteudo" role="main">
        <section class="relative overflow-hidden bg-primary text-on-primary py-10 sm:py-16"
                 style="background-image: linear-gradient(rgba(27,94,32,0.88), rgba(27,94,32,0.95)), url('{{ asset('images/eventos/evento-01.jpg') }}'); background-size: cover; background-position: center;"
                 aria-label="Foto de capa: jogadores em campo society">
            <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-6 md:gap-8 items-center relative">
                <div>
                    <h1 class="text-2xl sm:text-3xl md:text-5xl font-bold mb-3 sm:mb-4">Sua turma organizada do jeito que ela merece.</h1>
                    <p class="text-base sm:text-lg opacity-95 mb-5 sm:mb-6">Gerencie partidas, confirme presencas, divida despesas e converse com o time num espaco compartilhado e acessivel a todos.</p>
                    <a href="{{ route('register') }}" class="btn btn-secondary">Comecar gratis</a>
                </div>
                <div class="card text-on-surface">
                    <h2 class="text-xl font-semibold mb-3">Por que AlfaFut?</h2>
                    <ul class="space-y-2 list-disc pl-6">
                        <li>Confirmacao de presenca e lista de espera automatica.</li>
                        <li>Sorteio justo de times respeitando posicao e nivel.</li>
                        <li>Rateio automatico de despesas por partida.</li>
                        <li>Mural de comunicacao com todos da turma.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white" aria-labelledby="como-funciona-h">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 id="como-funciona-h" class="text-3xl font-bold">Veja como funciona</h2>
                    <p class="text-gray-700 mt-2 max-w-2xl mx-auto">Prints reais do AlfaFut em uso. Tudo pensado pra ser simples e direto - na web ou no app.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <figure class="card !p-3">
                        <img src="{{ asset('images/screenshots/04-dashboard.png') }}"
                             alt="Painel do AlfaFut com cards de resumo, lista de turmas e proximas partidas"
                             class="w-full h-auto rounded-lg"
                             loading="lazy">
                        <figcaption class="mt-3 px-2">
                            <strong class="block">📊 Painel principal</strong>
                            <span class="text-sm text-gray-700">Suas turmas, proximas partidas e o que voce ja confirmou - tudo em um lugar.</span>
                        </figcaption>
                    </figure>

                    <figure class="card !p-3">
                        <img src="{{ asset('images/screenshots/10-partida-detalhe.png') }}"
                             alt="Tela da partida com botoes de confirmar, recusar, sortear times e lista de confirmados"
                             class="w-full h-auto rounded-lg"
                             loading="lazy">
                        <figcaption class="mt-3 px-2">
                            <strong class="block">⚽ Confirmar e sortear</strong>
                            <span class="text-sm text-gray-700">Confirmacao em 1 toque, lista de espera automatica e sorteio justo de times.</span>
                        </figcaption>
                    </figure>

                    <figure class="card !p-3">
                        <img src="{{ asset('images/screenshots/09-despesa-detalhe.png') }}"
                             alt="Detalhe de despesa com valor total, pago, aberto e lista de pagamentos por jogador"
                             class="w-full h-auto rounded-lg"
                             loading="lazy">
                        <figcaption class="mt-3 px-2">
                            <strong class="block">💰 Rateio automatico</strong>
                            <span class="text-sm text-gray-700">Aluguel do campo dividido entre os confirmados. Cada um da baixa no proprio PIX.</span>
                        </figcaption>
                    </figure>

                    <figure class="card !p-3">
                        <img src="{{ asset('images/screenshots/07-mural.png') }}"
                             alt="Mural da turma com formulario de nova mensagem e lista de posts"
                             class="w-full h-auto rounded-lg"
                             loading="lazy">
                        <figcaption class="mt-3 px-2">
                            <strong class="block">💬 Mural da turma</strong>
                            <span class="text-sm text-gray-700">Avisos importantes ficam fixados no topo. Nada se perde no meio das conversas.</span>
                        </figcaption>
                    </figure>
                </div>

                <div class="text-center mt-10">
                    <a href="{{ route('como-usar') }}" class="btn btn-primary">Ver passo a passo completo</a>
                </div>
            </div>
        </section>

        <section class="py-16 bg-gray-50" aria-labelledby="galeria-h">
            <div class="max-w-6xl mx-auto px-4">
                <h2 id="galeria-h" class="text-2xl font-bold mb-2">A turma em acao</h2>
                <p class="text-gray-700 mb-6">Registros de partidas reais da comunidade que inspirou o projeto.</p>
                <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" role="list">
                    @for ($i = 1; $i <= 12; $i++)
                        <li>
                            <img src="{{ asset(sprintf('images/eventos/evento-%02d.jpg', $i)) }}"
                                 alt="Foto {{ $i }} de partida da turma - jogadores em campo society"
                                 class="w-full h-40 object-cover rounded-lg"
                                 loading="lazy">
                        </li>
                    @endfor
                </ul>
            </div>
        </section>

    </main>

    <footer class="bg-gray-900 text-gray-100 py-6" role="contentinfo">
        <div class="max-w-6xl mx-auto px-4 text-sm">
            AlfaFut &middot; Atividade de Extensao Uniasselvi - 2026 &middot; ADS / Gestao de Tecnologia.
        </div>
    </footer>

    <div id="banner-instalar"
         x-data="{ aberto: false }"
         x-show="aberto"
         x-cloak
         x-transition
         class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 bg-primary text-on-primary rounded-2xl shadow-2xl p-4 flex items-center gap-3 max-w-md w-[calc(100%-2rem)]">
        <img src="{{ asset('images/logo/icon.svg') }}" alt="" class="h-12 w-12 rounded-full bg-white p-1 shrink-0">
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm">Instalar AlfaFut</p>
            <p class="text-xs opacity-90">Adicione na tela inicial pra usar offline.</p>
        </div>
        <button id="btn-instalar" class="btn btn-secondary text-sm" @click="aberto = false">Instalar</button>
        <button class="text-on-primary opacity-70 hover:opacity-100" aria-label="Fechar" @click="aberto = false">✕</button>
    </div>

    @include('partials.pwa-script')
</body>
</html>
