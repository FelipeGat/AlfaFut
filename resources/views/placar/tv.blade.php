<!DOCTYPE html>
<html lang="pt-BR" data-tema="alto-contraste-tv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Placar - {{ $partida->titulo }} | AlfaFut</title>
    <meta name="theme-color" content="#0d3f12">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto:400,500,700,900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: radial-gradient(ellipse at top, #1b5e20 0%, #0a3a0e 60%, #000 100%);
            color: #fff;
            min-height: 100vh;
            margin: 0;
            font-family: 'Roboto', sans-serif;
        }
        .placar-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1.5rem;
        }
        .time-nome {
            font-size: clamp(1.5rem, 4vw, 3.5rem);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .time-gols {
            font-size: clamp(5rem, 18vw, 14rem);
            font-weight: 900;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            text-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }
        .relogio {
            font-size: clamp(2rem, 6vw, 5rem);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.04em;
        }
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.1em;
        }
        .ponto-ao-vivo {
            display: inline-block;
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
            background: #f44336;
        }
        .evento-item {
            display: grid;
            grid-template-columns: 4rem 2rem 1fr;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
        }
        @media (orientation: landscape) and (min-width: 1024px) {
            .layout-tv {
                height: 100vh;
                display: grid;
                grid-template-rows: auto 1fr auto;
            }
        }
    </style>
</head>
<body x-data="placarTv({{ $partida->id }})" x-init="iniciarPolling()">

    <div class="layout-tv">
        <header class="text-center pt-6 pb-2">
            <p class="text-sm sm:text-base uppercase tracking-widest opacity-80">{{ $partida->patota->nome }}</p>
            <h1 class="text-2xl sm:text-3xl font-bold mt-1">{{ $partida->titulo }}</h1>
            <div class="mt-3 flex items-center justify-center gap-3 flex-wrap">
                <template x-if="dados.em_andamento">
                    <span class="badge-status bg-red-600">
                        <span class="ponto-ao-vivo anim-piscar"></span>
                        AO VIVO
                    </span>
                </template>
                <template x-if="dados.pausada">
                    <span class="badge-status bg-yellow-500 text-black">⏸ PAUSADA</span>
                </template>
                <template x-if="dados.finalizada">
                    <span class="badge-status bg-gray-600">FINALIZADA</span>
                </template>
                <template x-if="!dados.em_andamento && !dados.pausada && !dados.finalizada">
                    <span class="badge-status bg-blue-600">AGUARDANDO INICIO</span>
                </template>
            </div>
        </header>

        <main class="px-4 sm:px-8 py-6 flex items-center justify-center">
            <div class="placar-grid w-full max-w-6xl">
                <div class="text-center anim-fade-in">
                    <template x-if="dados.times[0]?.brasao">
                        <img :src="'/' + dados.times[0].brasao" :alt="'Brasao ' + dados.times[0].nome"
                             class="mx-auto" style="height: clamp(60px, 14vw, 140px); width: auto; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4));">
                    </template>
                    <div class="time-nome mt-3" x-text="dados.times[0]?.nome ?? 'Time A'"></div>
                    <div class="time-gols mt-2"
                         :class="{'anim-contador-pop': flashA}"
                         x-text="dados.placar_a"></div>
                </div>

                <div class="text-center">
                    <div class="relogio" :class="{'anim-pulso': dados.em_andamento}" x-text="dados.tempo_formatado">00:00</div>
                    <p class="opacity-70 mt-2 text-sm uppercase tracking-wider">cronometro</p>
                </div>

                <div class="text-center anim-fade-in">
                    <template x-if="dados.times[1]?.brasao">
                        <img :src="'/' + dados.times[1].brasao" :alt="'Brasao ' + dados.times[1].nome"
                             class="mx-auto" style="height: clamp(60px, 14vw, 140px); width: auto; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4));">
                    </template>
                    <div class="time-nome mt-3" x-text="dados.times[1]?.nome ?? 'Time B'"></div>
                    <div class="time-gols mt-2"
                         :class="{'anim-contador-pop': flashB}"
                         x-text="dados.placar_b"></div>
                </div>
            </div>
        </main>

        <footer class="px-4 sm:px-8 pb-6">
            <h2 class="text-center text-sm uppercase tracking-widest opacity-70 mb-3">Ultimos eventos</h2>
            <div class="max-w-3xl mx-auto space-y-2">
                <template x-for="(ev, idx) in dados.ultimos_eventos" :key="ev.criado_em + idx">
                    <div class="evento-item anim-slide-down">
                        <span class="font-mono text-lg" x-text="ev.minuto + String.fromCharCode(39)"></span>
                        <span class="text-2xl" x-text="iconePorTipo(ev.tipo)"></span>
                        <span>
                            <strong x-text="ev.jogador ?? rotuloEvento(ev.tipo)"></strong>
                            <span x-show="ev.time" class="opacity-70" x-text="' · ' + ev.time"></span>
                        </span>
                    </div>
                </template>
                <p x-show="dados.ultimos_eventos.length === 0" class="text-center opacity-60 py-6">
                    Aguardando eventos...
                </p>
            </div>
        </footer>
    </div>

    <script>
        const DADOS_INICIAIS_TV = {!! json_encode([
            'placar_a' => $partida->placar_a,
            'placar_b' => $partida->placar_b,
            'tempo_formatado' => $partida->tempoFormatado(),
            'em_andamento' => $partida->emAndamento(),
            'pausada' => $partida->pausada(),
            'finalizada' => $partida->finalizada(),
            'times' => $partida->times->map(fn ($t) => [
                'id' => $t->id, 'nome' => $t->nome, 'cor' => $t->cor,
                'gols' => $t->gols, 'brasao' => $t->brasao,
            ])->values(),
            'ultimos_eventos' => [],
        ]) !!};
        function placarTv(partidaId) {
            return {
                dados: DADOS_INICIAIS_TV,
                flashA: false,
                flashB: false,
                iniciarPolling() {
                    this.atualizar();
                    setInterval(() => this.atualizar(), 3000);
                    // Tick local de relogio (entre os polls)
                    setInterval(() => {
                        if (this.dados.em_andamento) {
                            const partes = this.dados.tempo_formatado.split(':');
                            let total = parseInt(partes[0]) * 60 + parseInt(partes[1]) + 1;
                            const m = String(Math.floor(total/60)).padStart(2,'0');
                            const s = String(total%60).padStart(2,'0');
                            this.dados.tempo_formatado = `${m}:${s}`;
                        }
                    }, 1000);
                },
                async atualizar() {
                    try {
                        const r = await fetch(`/partidas/${partidaId}/dados`, {headers: {'Accept':'application/json'}});
                        if (!r.ok) return;
                        const novo = await r.json();
                        if (novo.placar_a > this.dados.placar_a) {
                            this.flashA = true;
                            setTimeout(() => this.flashA = false, 600);
                        }
                        if (novo.placar_b > this.dados.placar_b) {
                            this.flashB = true;
                            setTimeout(() => this.flashB = false, 600);
                        }
                        if (novo.finalizada && !this.dados.finalizada) {
                            setTimeout(() => window.location = `/partidas/${partidaId}/resultado`, 2000);
                        }
                        this.dados = novo;
                    } catch(e) {}
                },
                iconePorTipo(tipo) {
                    return {gol: '⚽', gol_contra: '😬', inicio: '▶️', pausa: '⏸️', retomada: '▶️', fim: '🏁'}[tipo] ?? '·';
                },
                rotuloEvento(tipo) {
                    return {inicio: 'Inicio da partida', pausa: 'Pausa', retomada: 'Retomada', fim: 'Fim da partida', gol: 'Gol', gol_contra: 'Gol contra'}[tipo] ?? tipo;
                },
            };
        }
    </script>
</body>
</html>
