<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Como usar o AlfaFut - Guia em 12 passos</title>
    <meta name="description" content="Aprenda a usar o AlfaFut em 12 passos: criar turma, partidas, sortear times, placar ao vivo, despesas, mural e mais.">
    <meta name="theme-color" content="#1b5e20">

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="AlfaFut">
    <meta name="mobile-web-app-capable" content="yes">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:700,900|roboto:400,500,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .jornal {
            font-family: 'Playfair Display', Georgia, serif;
        }
        .jornal h1, .jornal h2, .jornal h3, .jornal .titulo {
            font-family: 'Playfair Display', Georgia, serif;
        }
        .jornal-corpo {
            font-family: 'Roboto', sans-serif;
        }
        .papel {
            background:
                repeating-linear-gradient(0deg, transparent 0, transparent 28px, rgba(0,0,0,0.025) 28px, rgba(0,0,0,0.025) 29px),
                #faf6ee;
            color: #1a1a1a;
            border: 1px solid #d8cfa9;
            box-shadow: 0 6px 24px rgba(0,0,0,0.18);
        }
        .topo-jornal {
            border-bottom: 4px double #1a1a1a;
        }
        .titulo-edicao {
            font-size: clamp(2rem, 6vw, 4rem);
            line-height: 1;
            letter-spacing: -0.02em;
            font-weight: 900;
            color: #1a1a1a;
        }
        .subtitulo {
            font-size: clamp(1.25rem, 3vw, 1.875rem);
            font-weight: 700;
            color: #1b5e20;
        }
        .lead {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 1.125rem;
            color: #444;
            border-left: 4px solid #1b5e20;
            padding-left: 1rem;
        }
        .colunas {
            column-count: 1;
            column-gap: 2rem;
        }
        @media (min-width: 768px) {
            .colunas { column-count: 2; }
        }
        .colunas p { break-inside: avoid; margin-bottom: 0.75rem; }
        .first-letter::first-letter {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 3.5rem;
            font-weight: 900;
            float: left;
            line-height: 0.9;
            margin-right: 0.5rem;
            margin-top: 0.2rem;
            color: #1b5e20;
        }
        .selo {
            display: inline-block;
            background: #1b5e20;
            color: #fff;
            font-family: 'Roboto', sans-serif;
            font-weight: 900;
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            padding: 0.25rem 0.75rem;
            transform: rotate(-2deg);
            text-transform: uppercase;
        }
        .seta-nav {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #1b5e20;
            color: #fff;
            font-size: 1.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 0;
        }
        .seta-nav:hover { background: #f57c00; transform: scale(1.1); }
        .seta-nav:disabled { background: #aaa; cursor: not-allowed; transform: none; }
        .navegacao-fixa {
            position: sticky;
            bottom: 1rem;
            z-index: 30;
        }
        .indicador-passos {
            display: flex;
            gap: 4px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .indicador-passos span {
            display: inline-block;
            width: 24px;
            height: 6px;
            background: rgba(0,0,0,0.15);
            border-radius: 3px;
            transition: background 0.2s ease;
        }
        .indicador-passos span.ativo {
            background: #1b5e20;
            width: 36px;
        }
        @keyframes virar-pagina {
            from { opacity: 0; transform: translateX(40px) rotateY(-8deg); }
            to { opacity: 1; transform: translateX(0) rotateY(0); }
        }
        @keyframes virar-pagina-volta {
            from { opacity: 0; transform: translateX(-40px) rotateY(8deg); }
            to { opacity: 1; transform: translateX(0) rotateY(0); }
        }
        .pagina-vai { animation: virar-pagina 0.45s ease-out; }
        .pagina-volta { animation: virar-pagina-volta 0.45s ease-out; }
        html[data-reduzir-movimento="1"] .pagina-vai,
        html[data-reduzir-movimento="1"] .pagina-volta { animation: none; }

        .img-jornal {
            border: 1px solid #d8cfa9;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: #fff;
        }
        .legenda {
            font-family: 'Roboto', sans-serif;
            font-style: italic;
            font-size: 0.85rem;
            color: #666;
            text-align: center;
            margin-top: 0.5rem;
        }
        .destaque-numero {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 900;
            font-size: clamp(4rem, 14vw, 8rem);
            line-height: 1;
            color: #1b5e20;
            opacity: 0.15;
            position: absolute;
            top: -1rem;
            right: 1rem;
            user-select: none;
            pointer-events: none;
        }
        .pagina {
            position: relative;
        }
        .data-jornal {
            font-family: 'Roboto', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #555;
        }
    </style>
</head>
<body class="bg-gray-200 min-h-screen jornal" x-data="jornal()" x-init="iniciar()">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:bg-primary focus:text-on-primary focus:px-4 focus:py-2 focus:rounded">Pular para o conteudo</a>

    <header class="bg-primary text-on-primary" role="banner">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline shrink-0" aria-label="AlfaFut - Pagina inicial">
                <img src="{{ asset('images/logo/icon.svg') }}" alt="" class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-white p-1" aria-hidden="true">
                <span class="font-bold text-lg sm:text-xl text-white">AlfaFut</span>
            </a>
            <nav aria-label="Acesso" class="flex items-center gap-2 shrink-0">
                <a href="{{ route('home') }}" class="hidden sm:inline-block px-4 py-2 rounded-full border-2 border-white text-white font-medium text-sm">Inicio</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3 sm:px-4 py-2 rounded-full bg-white font-medium text-sm" style="color: var(--md-sys-color-primary);">Painel</a>
                @else
                    <a href="{{ route('register') }}" class="px-3 sm:px-4 py-2 rounded-full bg-white font-medium text-sm" style="color: var(--md-sys-color-primary);">Criar conta</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="conteudo" role="main" tabindex="-1" class="py-6 sm:py-10">

        <div class="max-w-5xl mx-auto px-3 sm:px-6">

            {{-- Capa do jornal --}}
            <article class="papel p-5 sm:p-10 mb-4 pagina">
                <div class="topo-jornal pb-3 mb-4 flex items-baseline justify-between text-xs">
                    <span class="data-jornal">Edicao Especial · 12 paginas</span>
                    <span class="data-jornal">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
                </div>
                <p class="data-jornal text-center mb-2">Boletim Oficial</p>
                <h1 class="titulo-edicao text-center">O Diario do AlfaFut</h1>
                <p class="text-center mt-3 italic text-base sm:text-lg">"Tudo o que voce precisa para organizar sua turma de futebol em um so lugar"</p>
                <hr class="my-4 border-gray-400">

                <div class="grid sm:grid-cols-3 gap-4 items-center">
                    <div class="text-center">
                        <span class="selo">12 PASSOS</span>
                        <p class="mt-2 text-3xl font-bold">📋</p>
                    </div>
                    <div class="text-center">
                        <h2 class="subtitulo mb-2">Use as setas para navegar</h2>
                        <p class="text-sm jornal-corpo">Cada passo conta uma funcionalidade. Voce pode tambem clicar nos circulos abaixo para pular para um especifico.</p>
                    </div>
                    <div class="text-center">
                        <span class="selo" style="background: #f57c00;">GRATUITO</span>
                        <p class="mt-2 text-3xl font-bold">🏆</p>
                    </div>
                </div>
            </article>

            {{-- Container das paginas (uma visivel por vez) --}}
            <div class="relative">

                {{-- =========================================================
                     PASSO 1 - CRIAR CONTA
                     ========================================================= --}}
                <section x-show="passo === 1" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t1">
                    <span class="destaque-numero" aria-hidden="true">01</span>
                    <p class="data-jornal mb-2">Cap. 1 / Onboarding</p>
                    <h2 id="t1" class="text-3xl sm:text-5xl font-black mb-1">Crie sua conta em 1 minuto</h2>
                    <p class="lead my-4">O cadastro e simples e gratis. So precisa de nome, e-mail e uma senha forte (8+ caracteres).</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <div class="first-letter">
                            <p>Para comecar a usar o AlfaFut, o primeiro passo e clicar em <strong>Criar conta</strong> no canto superior direito da pagina inicial. O formulario aparece em uma tela limpa e direta. Voce informa seu nome completo (que aparecera para os colegas da turma), e-mail valido e uma senha de no minimo 8 caracteres.</p>
                            <p>Apos criar a conta, voce ja entra automaticamente. Da proxima vez, basta usar e-mail + senha. Se esquecer a senha, ha um link "Esqueci minha senha" para recuperar via e-mail.</p>
                            <p class="text-sm text-gray-700"><strong>Dica do redator:</strong> defina seu apelido depois no perfil para ser facilmente reconhecido na turma.</p>
                        </div>
                        <figure>
                            <img src="{{ asset('images/screenshots/03-register.png') }}" alt="Tela de cadastro com nome, e-mail, senha e confirmacao." class="img-jornal w-full">
                            <figcaption class="legenda">Foto 1: tela de cadastro com validacao em tempo real.</figcaption>
                        </figure>
                    </div>
                </section>

                {{-- PASSO 2 - LOGIN E DASHBOARD --}}
                <section x-show="passo === 2" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t2">
                    <span class="destaque-numero" aria-hidden="true">02</span>
                    <p class="data-jornal mb-2">Cap. 2 / Acesso</p>
                    <h2 id="t2" class="text-3xl sm:text-5xl font-black mb-1">O painel: tudo em um lugar</h2>
                    <p class="lead my-4">Apos o login, voce cai no dashboard com 3 resumos: suas turmas, proximas partidas e quantas voce ja confirmou.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <figure>
                            <img src="{{ asset('images/screenshots/04-dashboard.png') }}" alt="Dashboard com cards de resumo, lista de turmas e proximas partidas." class="img-jornal w-full">
                            <figcaption class="legenda">Foto 2: o painel principal apresenta tudo em uma rolagem.</figcaption>
                        </figure>
                        <div class="first-letter">
                            <p>O dashboard foi pensado para responder, em 3 segundos, as perguntas mais comuns: "ja confirmei a pelada?", "quantas turmas eu participo?" e "quando e o proximo jogo?".</p>
                            <p>No menu superior voce navega entre <strong>Dashboard</strong>, <strong>Turmas</strong>, <strong>Partidas</strong>, <strong>Acessibilidade</strong> e <strong>Como usar</strong>. O avatar com inicial (no canto direito) abre o menu de perfil e a opcao <strong>Sair</strong>.</p>
                            <p>O cabecalho da pagina muda de cor para destacar a area atual: dashboard, partida individual, mural, despesas. Cada um tem identidade visual propria.</p>
                        </div>
                    </div>
                </section>

                {{-- PASSO 3 - CRIAR TURMA + ESCOLHER BRASAO --}}
                <section x-show="passo === 3" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t3">
                    <span class="destaque-numero" aria-hidden="true">03</span>
                    <p class="data-jornal mb-2">Cap. 3 / Turmas</p>
                    <h2 id="t3" class="text-3xl sm:text-5xl font-black mb-1">Crie sua turma com brasao oficial</h2>
                    <p class="lead my-4">Toda turma tem um <strong>brasao</strong> escolhido entre 20 clubes ficticios. Pareca um clube de verdade desde o primeiro dia.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <div class="first-letter">
                            <p>Ao clicar em <strong>+ Nova turma</strong> voce informa o nome (ex: "Galera de quarta"), cidade, mensalidade opcional e a configuracao do jogo: jogadores por time (futsal 4, society 5, campo 11) e quantidade de times.</p>
                            <p>Depois aparece a galeria com 20 brasoes ja desenhados: Aguia Dourada, Leoes da Vila, Fenix FC, Panteras Negras... voce escolhe clicando.</p>
                            <p>O criador da turma vira automaticamente o <strong>responsavel</strong> e pode passar essa funcao para qualquer membro depois (Cap. 8).</p>
                            <div class="flex gap-2 flex-wrap mt-3">
                                <img src="{{ asset('images/brasoes/01-aguia-dourada.svg') }}" alt="Brasao Aguia Dourada" class="h-16 w-auto">
                                <img src="{{ asset('images/brasoes/05-coroa-real.svg') }}" alt="Brasao Coroa Real" class="h-16 w-auto">
                                <img src="{{ asset('images/brasoes/10-dragoes-vermelhos.svg') }}" alt="Brasao Dragoes Vermelhos" class="h-16 w-auto">
                                <img src="{{ asset('images/brasoes/18-fenix-fc.svg') }}" alt="Brasao Fenix FC" class="h-16 w-auto">
                                <img src="{{ asset('images/brasoes/20-panteras-negras.svg') }}" alt="Brasao Panteras" class="h-16 w-auto">
                                <span class="text-xs italic text-gray-700 self-center">+ 15 brasoes</span>
                            </div>
                        </div>
                        <figure>
                            <img src="{{ asset('images/screenshots/04-dashboard.png') }}" alt="Botao + Nova turma no dashboard" class="img-jornal w-full">
                            <figcaption class="legenda">Foto 3: clicar em "+ Nova" abre o formulario com galeria de brasoes.</figcaption>
                        </figure>
                    </div>
                </section>

                {{-- PASSO 4 - CONVIDAR --}}
                <section x-show="passo === 4" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t4">
                    <span class="destaque-numero" aria-hidden="true">04</span>
                    <p class="data-jornal mb-2">Cap. 4 / Convites</p>
                    <h2 id="t4" class="text-3xl sm:text-5xl font-black mb-1">Codigo de 8 letras convoca o time</h2>
                    <p class="lead my-4">Sem precisar adicionar pessoa por pessoa. Compartilhe um codigo e quem chega usa ele para entrar.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <figure>
                            <img src="{{ asset('images/screenshots/06-patota-detalhe.png') }}" alt="Detalhe da turma com codigo de convite e lista de membros" class="img-jornal w-full">
                            <figcaption class="legenda">Foto 4: o codigo so e visivel para o administrador.</figcaption>
                        </figure>
                        <div class="first-letter">
                            <p>Ao criar a turma, o sistema gera um <strong>codigo de convite de 8 caracteres</strong> em maiusculas (ex: <code>X7K9P2QM</code>). Esse codigo aparece em destaque na tela da turma, mas <strong>so para o administrador</strong>.</p>
                            <p>Compartilhe via WhatsApp, e-mail ou ate verbalmente. Quem quiser entrar na turma usa o botao <strong>Entrar com codigo</strong> no dashboard, digita o codigo e ja vira membro com papel "membro" (sem privilegios de admin).</p>
                            <p>Se o codigo "vazar" e voce quiser invalidar, basta editar a turma e o sistema gera um novo.</p>
                        </div>
                    </div>
                </section>

                {{-- PASSO 5 - PARTIDA --}}
                <section x-show="passo === 5" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t5">
                    <span class="destaque-numero" aria-hidden="true">05</span>
                    <p class="data-jornal mb-2">Cap. 5 / Agenda</p>
                    <h2 id="t5" class="text-3xl sm:text-5xl font-black mb-1">Agende a pelada da semana</h2>
                    <p class="lead my-4">Data, hora, local com indicacao de acessibilidade, vagas calculadas automaticamente e prazo para confirmar.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <div class="first-letter">
                            <p>Apenas o administrador da turma cria partidas. O formulario tem campos diretos: titulo (ex: "Pelada de sabado"), data, hora, duracao (padrao 90 min), valor por jogador e prazo final para confirmar.</p>
                            <p>O numero de vagas <strong>e calculado automaticamente</strong> com base na configuracao da turma (jogadores por time × quantidade de times). Se for 5x5 com 2 times, ja vem 10 vagas pre-definidas.</p>
                            <p>O local e selecionado de uma lista da turma. Cada local marca se tem <strong>vestiario, estacionamento e acessibilidade para cadeirantes</strong> - aparece como icone na partida.</p>
                        </div>
                        <figure>
                            <img src="{{ asset('images/screenshots/10-partida-detalhe.png') }}" alt="Pagina da partida com data, hora, local, vagas e botoes" class="img-jornal w-full">
                            <figcaption class="legenda">Foto 5: pagina da partida com todas as informacoes.</figcaption>
                        </figure>
                    </div>
                </section>

                {{-- PASSO 6 - CONFIRMAR --}}
                <section x-show="passo === 6" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t6">
                    <span class="destaque-numero" aria-hidden="true">06</span>
                    <p class="data-jornal mb-2">Cap. 6 / Presencas</p>
                    <h2 id="t6" class="text-3xl sm:text-5xl font-black mb-1">Confirme em 1 toque</h2>
                    <p class="lead my-4">Sem mensagem perdida no grupo de WhatsApp. Cada um clica e o sistema cuida do resto - inclusive lista de espera.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <figure>
                            <img src="{{ asset('images/screenshots/10-partida-detalhe.png') }}" alt="Botoes Confirmar/Recusar e lista de confirmados" class="img-jornal w-full">
                            <figcaption class="legenda">Foto 6: confirmar ou recusar em um clique.</figcaption>
                        </figure>
                        <div class="first-letter">
                            <p>Ao abrir uma partida, o membro tem dois botoes grandes: <strong>Confirmar presenca</strong> ou <strong>Nao vou</strong>. A escolha e instantanea e aparece na lista publica.</p>
                            <p>Se a partida estiver <strong>cheia</strong>, novos confirmadores entram automaticamente em uma <strong>lista de espera ordenada</strong>. Quando alguem cancela, o primeiro da fila e promovido sem ninguem precisar mexer.</p>
                            <p>Sua posicao fica visivel ("posicao 2 da lista de espera"). E voce pode trocar de "vou" para "nao vou" a qualquer momento.</p>
                        </div>
                    </div>
                </section>

                {{-- PASSO 7 - SORTEIO --}}
                <section x-show="passo === 7" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t7">
                    <span class="destaque-numero" aria-hidden="true">07</span>
                    <p class="data-jornal mb-2">Cap. 7 / Sorteio</p>
                    <h2 id="t7" class="text-3xl sm:text-5xl font-black mb-1">Times sorteados como em um classico</h2>
                    <p class="lead my-4">Cada time recebe nome e brasao de clube ficticio. Os 20 escudos disponiveis sao redistribuidos a cada partida.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <div class="first-letter">
                            <p>Quando ha confirmadores suficientes, o admin clica em <strong>🎲 Sortear times</strong>. O algoritmo balanceia <strong>posicao</strong> (1 goleiro por time se houver) e <strong>nivel de habilidade</strong> (snake-draft pelo nivel cadastrado).</p>
                            <p>Cada time sorteado recebe um <strong>brasao + nome de clube</strong> escolhido aleatoriamente entre os 20: hoje pode ser <em>Onças do Sul × Fenix FC</em>, na proxima <em>Aguia Dourada × Tubaroes da Praia</em>.</p>
                            <p>Quer rebalancear? Clique novamente e o sorteio refaz tudo - inclusive os brasoes.</p>
                        </div>
                        <figure class="bg-white p-6 rounded-lg img-jornal">
                            <div class="grid grid-cols-2 gap-4 items-center">
                                <div class="text-center">
                                    <img src="{{ asset('images/brasoes/11-oncas-do-sul.svg') }}" alt="Oncas" class="h-24 w-auto mx-auto">
                                    <p class="font-bold mt-2">ONCAS</p>
                                </div>
                                <div class="text-center">
                                    <img src="{{ asset('images/brasoes/18-fenix-fc.svg') }}" alt="Fenix" class="h-24 w-auto mx-auto">
                                    <p class="font-bold mt-2">FENIX</p>
                                </div>
                            </div>
                            <figcaption class="legenda">Times sorteados na partida demo.</figcaption>
                        </figure>
                    </div>
                </section>

                {{-- PASSO 8 - PLACAR TV --}}
                <section x-show="passo === 8" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t8">
                    <span class="destaque-numero" aria-hidden="true">08</span>
                    <p class="data-jornal mb-2">Cap. 8 / Transmissao</p>
                    <h2 id="t8" class="text-3xl sm:text-5xl font-black mb-1">Placar gigante para a TV da quadra</h2>
                    <p class="lead my-4">Conecte uma TV ou projetor, abra a tela <strong>📺 Placar TV</strong> e tenha um placar profissional em tempo real.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <figure>
                            <img src="{{ asset('images/screenshots/16-tv-com-brasoes.png') }}" alt="Tela TV com placar gigante e brasoes" class="img-jornal w-full">
                            <figcaption class="legenda">Foto 7: TV mode com brasoes, cronometro e badge "AO VIVO" pulsante.</figcaption>
                        </figure>
                        <div class="first-letter">
                            <p>A tela TV tem fundo verde gradiente, brasoes gigantes de cada time, placar em fonte enorme (ate 14rem) e cronometro pulsante. Tudo atualiza ao vivo - cada gol marcado pelo responsavel aparece em segundos.</p>
                            <p>Quando algum time marca, o numero do placar <strong>pula com animacao</strong> (efeito "contador-pop") e o evento aparece em uma faixa que desliza no rodape com o nome do autor do gol.</p>
                            <p>Funciona em qualquer tela: TV, projetor, tablet, notebook. Sem necessidade de instalar nada.</p>
                        </div>
                    </div>
                </section>

                {{-- PASSO 9 - CONTROLE AO VIVO --}}
                <section x-show="passo === 9" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t9">
                    <span class="destaque-numero" aria-hidden="true">09</span>
                    <p class="data-jornal mb-2">Cap. 9 / Mesario</p>
                    <h2 id="t9" class="text-3xl sm:text-5xl font-black mb-1">Voce no comando: iniciar, pausar, marcar gol</h2>
                    <p class="lead my-4">O responsavel da turma controla a partida em tempo real pelo celular. Botoes grandes, dedos felizes.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <div class="first-letter">
                            <p>O <strong>responsavel</strong> da turma (escolhido na edicao da turma) tem acesso a tela <strong>🎮 Controle ao vivo</strong>. Tres botoes gigantes:</p>
                            <ul class="list-disc pl-5 my-2 space-y-1">
                                <li><strong>▶️ Iniciar / Retomar</strong> - dispara o cronometro</li>
                                <li><strong>⏸️ Pausar</strong> - cronometro congela, retomavel</li>
                                <li><strong>🏁 Finalizar</strong> - encerra e leva para a tela de resultado</li>
                            </ul>
                            <p>Para registrar um gol, basta selecionar o jogador no card do time correspondente. O placar atualiza, a TV recebe o gol, e o evento entra na linha do tempo. Marcou errado? Clique em "remover" no evento.</p>
                        </div>
                        <figure>
                            <img src="{{ asset('images/screenshots/17-controle-com-brasoes.png') }}" alt="Painel de controle com botoes Pausar, Finalizar e cards Marcar gol" class="img-jornal w-full">
                            <figcaption class="legenda">Foto 8: dois cards "Marcar gol", um por time, com brasao e select de jogador.</figcaption>
                        </figure>
                    </div>
                </section>

                {{-- PASSO 10 - RESULTADO --}}
                <section x-show="passo === 10" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t10">
                    <span class="destaque-numero" aria-hidden="true">10</span>
                    <p class="data-jornal mb-2">Cap. 10 / Resultado</p>
                    <h2 id="t10" class="text-3xl sm:text-5xl font-black mb-1">Apito final, confetti e artilharia</h2>
                    <p class="lead my-4">Ao apitar, o sistema mostra o placar final com brasoes, anuncia o vencedor e ranqueia a artilharia da partida.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <figure>
                            <img src="{{ asset('images/screenshots/15-resultado.png') }}" alt="Tela de resultado com placar final e artilharia" class="img-jornal w-full">
                            <figcaption class="legenda">Foto 9: confetti caindo + artilharia ranqueada com 🥇🥈🥉.</figcaption>
                        </figure>
                        <div class="first-letter">
                            <p>A tela de resultado celebra: o placar final em fonte enorme, os brasoes dos dois times, e a frase "🏆 TimeX venceu!". Se houve empate, fica "🤝 Empate".</p>
                            <p>Logo abaixo, a <strong>artilharia da partida</strong> em formato de podio (medalhas para os 3 primeiros). Cada artilheiro mostra inicial + nome + numero de gols + time.</p>
                            <p>A linha do tempo lista todos os eventos importantes (inicio, gols, fim) com minuto e jogador. Tudo registrado como historico permanente da turma.</p>
                            <p>E sim - tem <strong>confetti</strong> caindo. Voce mereceu.</p>
                        </div>
                    </div>
                </section>

                {{-- PASSO 11 - DESPESAS --}}
                <section x-show="passo === 11" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t11">
                    <span class="destaque-numero" aria-hidden="true">11</span>
                    <p class="data-jornal mb-2">Cap. 11 / Financeiro</p>
                    <h2 id="t11" class="text-3xl sm:text-5xl font-black mb-1">Rateio automatico e PIX em paz</h2>
                    <p class="lead my-4">O aluguel do campo dividido sozinho entre os confirmados. Cada um paga o seu, voce ve quem ja quitou.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <div class="first-letter">
                            <p>Cada turma tem uma area <strong>💰 Despesas</strong>. O administrador lanca a despesa (locacao, arbitragem, material, alimentacao), informa valor total e marca <em>"ratear entre os confirmados da partida"</em>.</p>
                            <p>O sistema cria um <strong>pagamento individual</strong> para cada confirmado, dividindo o valor igualmente. Cada pessoa abre a despesa, ve quanto deve, registra o pagamento informando a forma (PIX, dinheiro, transferencia, cartao) e pronto - aparece como "pago" para todos.</p>
                            <p>O painel da despesa mostra <strong>total pago</strong>, <strong>saldo aberto</strong> e o status individual de cada pagamento.</p>
                        </div>
                        <figure>
                            <img src="{{ asset('images/screenshots/09-despesa-detalhe.png') }}" alt="Detalhe da despesa com lista de pagamentos por jogador" class="img-jornal w-full">
                            <figcaption class="legenda">Foto 10: rateio automatico, ninguem mais cobra ninguem por mensagem.</figcaption>
                        </figure>
                    </div>
                </section>

                {{-- PASSO 12 - MURAL + ACESSIBILIDADE --}}
                <section x-show="passo === 12" :class="direcao === 'frente' ? 'pagina-vai' : 'pagina-volta'" class="papel p-5 sm:p-10 pagina" aria-labelledby="t12">
                    <span class="destaque-numero" aria-hidden="true">12</span>
                    <p class="data-jornal mb-2">Cap. 12 / Comunicacao + Inclusao</p>
                    <h2 id="t12" class="text-3xl sm:text-5xl font-black mb-1">Mural da turma e acessibilidade para todos</h2>
                    <p class="lead my-4">Mensagens importantes ficam fixadas. Cada usuario configura suas proprias preferencias de visualizacao.</p>
                    <div class="grid md:grid-cols-2 gap-6 jornal-corpo">
                        <figure>
                            <img src="{{ asset('images/screenshots/07-mural.png') }}" alt="Mural da turma com mensagens" class="img-jornal w-full mb-3">
                            <figcaption class="legenda">Foto 11: mural com mensagens fixadas e por partida.</figcaption>
                            <img src="{{ asset('images/screenshots/11-acessibilidade.png') }}" alt="Tela de acessibilidade" class="img-jornal w-full mt-3">
                            <figcaption class="legenda">Foto 12: 4 ajustes de acessibilidade + necessidades especificas.</figcaption>
                        </figure>
                        <div class="first-letter">
                            <p>O <strong>💬 Mural da turma</strong> e o lugar onde nada se perde. Avisos importantes podem ser <strong>fixados no topo</strong> pelo administrador. Mensagens podem ser publicadas para a turma toda ou vinculadas a uma partida especifica.</p>
                            <p>E porque AlfaFut e um projeto de extensao com foco em <strong>acessibilidade</strong>: cada usuario tem sua propria pagina <strong>♿ Acessibilidade</strong> onde escolhe alto contraste (tema preto/amarelo AAA), tamanho de fonte (4 niveis), reducao de animacoes (WCAG 2.3.3) e otimizacao para leitor de tela.</p>
                            <p>Tambem se pode declarar <strong>necessidades especificas</strong> (cadeirante, surdo, baixa visao, comunicacao em Libras) para que o organizador adapte a partida adequadamente.</p>
                            <p class="text-sm italic mt-3 text-gray-700"><strong>Fim da edicao.</strong> Agora e so jogar. Boa pelada!</p>
                        </div>
                    </div>
                </section>

            </div>

            {{-- Navegacao por setas --}}
            <div class="navegacao-fixa mt-6">
                <div class="papel p-4 flex items-center justify-between gap-3">
                    <button class="seta-nav" @click="anterior()" :disabled="passo === 1" aria-label="Passo anterior">
                        ←
                    </button>
                    <div class="text-center min-w-0 flex-1 jornal-corpo">
                        <p class="text-xs text-gray-700">Passo</p>
                        <p class="text-2xl font-bold" style="color: var(--md-sys-color-primary);">
                            <span x-text="passo"></span> / 12
                        </p>
                        <div class="indicador-passos mt-2">
                            <template x-for="i in 12" :key="i">
                                <span :class="i === passo ? 'ativo' : ''"
                                      role="button"
                                      tabindex="0"
                                      @click="ir(i)"
                                      @keydown.enter="ir(i)"
                                      :aria-label="'Ir para o passo ' + i"></span>
                            </template>
                        </div>
                    </div>
                    <button class="seta-nav" @click="proximo()" :disabled="passo === 12" aria-label="Proximo passo">
                        →
                    </button>
                </div>
            </div>

            <div class="text-center mt-6">
                <a href="{{ route('register') }}" class="btn btn-primary">Pronto para comecar - criar conta gratis</a>
            </div>

        </div>
    </main>

    <footer class="bg-gray-900 text-gray-100 mt-10" role="contentinfo">
        <div class="max-w-7xl mx-auto py-6 px-4 text-sm text-center">
            AlfaFut · Atividade de Extensao Uniasselvi
        </div>
    </footer>

    <script>
        function jornal() {
            return {
                passo: 1,
                direcao: 'frente',
                iniciar() {
                    // Permite navegar com teclado: setas
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowRight') this.proximo();
                        if (e.key === 'ArrowLeft') this.anterior();
                    });
                    // Hash da URL controla o passo
                    const m = location.hash.match(/passo-(\d+)/);
                    if (m) this.passo = Math.min(12, Math.max(1, parseInt(m[1])));
                },
                proximo() {
                    if (this.passo < 12) {
                        this.direcao = 'frente';
                        this.passo++;
                        this.atualizarHash();
                    }
                },
                anterior() {
                    if (this.passo > 1) {
                        this.direcao = 'volta';
                        this.passo--;
                        this.atualizarHash();
                    }
                },
                ir(n) {
                    this.direcao = n > this.passo ? 'frente' : 'volta';
                    this.passo = n;
                    this.atualizarHash();
                },
                atualizarHash() {
                    history.replaceState(null, '', '#passo-' + this.passo);
                    window.scrollTo({top: 0, behavior: 'smooth'});
                },
            };
        }
    </script>

    @include('partials.pwa-script')
</body>
</html>
