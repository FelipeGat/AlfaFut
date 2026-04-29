<x-app-layout>
    <x-slot name="title">Controle - {{ $partida->titulo }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="opacity-90 text-sm">{{ $partida->patota->nome }}</p>
                <h1 class="font-bold text-xl sm:text-2xl">🎮 Controle ao vivo</h1>
            </div>
            <a href="{{ route('partidas.tv', $partida) }}" target="_blank" class="btn btn-secondary shrink-0">
                📺 Abrir placar TV
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="controle({{ $partida->id }})"
         x-init="iniciarPolling()">

        <!-- Placar resumido -->
        <section class="card text-center" aria-live="polite">
            <div class="grid grid-cols-3 items-center gap-2">
                <div>
                    @if ($partida->times->count() > 0)
                        @if ($partida->times[0]->brasao)
                            <img src="{{ asset($partida->times[0]->brasao) }}" alt="Brasao {{ $partida->times[0]->nome }}" class="h-16 sm:h-24 w-auto mx-auto" loading="lazy">
                        @endif
                        <p class="text-sm sm:text-base uppercase tracking-wider text-gray-700 mt-1 truncate">{{ $partida->times[0]->nome }}</p>
                        <p class="text-5xl sm:text-7xl font-black mt-1"
                           :class="{'anim-contador-pop': flashA}"
                           x-text="dados.placar_a">{{ $partida->placar_a }}</p>
                    @endif
                </div>
                <div class="text-center">
                    <p class="text-sm uppercase tracking-wider text-gray-600">Cronometro</p>
                    <p class="text-3xl sm:text-5xl font-bold font-mono"
                       :class="{'anim-pulso': dados.em_andamento}"
                       x-text="dados.tempo_formatado">{{ $partida->tempoFormatado() }}</p>
                </div>
                <div>
                    @if ($partida->times->count() > 1)
                        @if ($partida->times[1]->brasao)
                            <img src="{{ asset($partida->times[1]->brasao) }}" alt="Brasao {{ $partida->times[1]->nome }}" class="h-16 sm:h-24 w-auto mx-auto" loading="lazy">
                        @endif
                        <p class="text-sm sm:text-base uppercase tracking-wider text-gray-700 mt-1 truncate">{{ $partida->times[1]->nome }}</p>
                        <p class="text-5xl sm:text-7xl font-black mt-1"
                           :class="{'anim-contador-pop': flashB}"
                           x-text="dados.placar_b">{{ $partida->placar_b }}</p>
                    @endif
                </div>
            </div>
        </section>

        <!-- Botoes principais -->
        <section class="card">
            <h2 class="text-lg font-semibold mb-4">Controle do tempo</h2>
            <div class="grid grid-cols-2 gap-3">
                @if (! $partida->finalizada())
                    @if (! $partida->emAndamento())
                        <form method="POST" action="{{ route('partidas.iniciar', $partida) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-full text-base py-4 anim-fade-in">
                                ▶️ {{ $partida->iniciada_em ? 'Retomar' : 'Iniciar' }}
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('partidas.pausar', $partida) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-full text-base py-4">
                                ⏸️ Pausar
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('partidas.finalizar', $partida) }}"
                          onsubmit="return confirm('Encerrar a partida agora? Esta acao nao pode ser desfeita.');">
                        @csrf
                        <button type="submit" class="btn btn-danger w-full text-base py-4">
                            🏁 Finalizar
                        </button>
                    </form>
                @else
                    <a href="{{ route('partidas.resultado', $partida) }}" class="btn btn-primary col-span-2 text-base py-4">
                        Ver resultado final
                    </a>
                @endif
            </div>
        </section>

        @if (! $partida->finalizada() && $partida->times->count() >= 2)
            <!-- Marcar gol -->
            <section class="card">
                <h2 class="text-lg font-semibold mb-4">⚽ Marcar gol</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach ($partida->times as $time)
                        <div class="border rounded-xl p-4">
                            @if ($time->brasao)
                                <img src="{{ asset($time->brasao) }}" alt="Brasao {{ $time->nome }}" class="h-16 w-auto mx-auto mb-2" loading="lazy">
                            @endif
                            <h3 class="font-semibold text-center text-lg mb-3" style="color: var(--md-sys-color-primary);">
                                {{ $time->nome }}
                            </h3>
                            <form method="POST" action="{{ route('partidas.gol', $partida) }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="time_id" value="{{ $time->id }}">
                                <div>
                                    <label for="jogador-{{ $time->id }}" class="text-sm">Quem fez o gol?</label>
                                    <select id="jogador-{{ $time->id }}" name="jogador_id" class="select" required>
                                        <option value="">Selecione...</option>
                                        @foreach ($time->jogadores as $jogador)
                                            <option value="{{ $jogador->id }}">{{ $jogador->apelido ?? $jogador->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-full">
                                    ⚽ Registrar gol
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Eventos da partida -->
        <section class="card">
            <h2 class="text-lg font-semibold mb-3">Eventos registrados</h2>
            @if ($partida->eventos->isEmpty())
                <p class="text-sm text-gray-700">Nenhum evento ainda. Inicie a partida para comecar.</p>
            @else
                <ul class="space-y-2" role="list">
                    @foreach ($partida->eventos->reverse() as $ev)
                        <li class="flex items-center gap-3 p-2 rounded-lg border anim-slide-down">
                            <span class="font-mono text-sm w-12 text-right">{{ $ev->minuto }}'</span>
                            <span class="text-xl">
                                @switch($ev->tipo)
                                    @case('gol') ⚽ @break
                                    @case('gol_contra') 😬 @break
                                    @case('inicio') ▶️ @break
                                    @case('pausa') ⏸️ @break
                                    @case('retomada') ▶️ @break
                                    @case('fim') 🏁 @break
                                @endswitch
                            </span>
                            <span class="flex-1 min-w-0 text-sm">
                                @if ($ev->jogador)
                                    <strong>{{ $ev->jogador->apelido ?? $ev->jogador->name }}</strong>
                                @else
                                    <strong>{{ ucfirst(str_replace('_', ' ', $ev->tipo)) }}</strong>
                                @endif
                                @if ($ev->time)
                                    <span class="text-gray-600">· {{ $ev->time->nome }}</span>
                                @endif
                            </span>
                            @if (in_array($ev->tipo, ['gol', 'gol_contra']))
                                <form method="POST" action="{{ route('partidas.eventos.destroy', [$partida, $ev]) }}"
                                      onsubmit="return confirm('Remover este gol?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 text-xs underline" aria-label="Remover gol de {{ $ev->jogador?->name }}">remover</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    @push('scripts')
    @endpush

    <script>
        const DADOS_INICIAIS_CTRL = {!! json_encode([
            'placar_a' => $partida->placar_a,
            'placar_b' => $partida->placar_b,
            'tempo_formatado' => $partida->tempoFormatado(),
            'em_andamento' => $partida->emAndamento(),
        ]) !!};
        function controle(partidaId) {
            return {
                dados: DADOS_INICIAIS_CTRL,
                flashA: false,
                flashB: false,
                iniciarPolling() {
                    setInterval(() => this.atualizar(), 4000);
                    setInterval(() => {
                        if (this.dados.em_andamento) {
                            const [m, s] = this.dados.tempo_formatado.split(':').map(Number);
                            let t = m * 60 + s + 1;
                            this.dados.tempo_formatado = String(Math.floor(t/60)).padStart(2,'0') + ':' + String(t%60).padStart(2,'0');
                        }
                    }, 1000);
                },
                async atualizar() {
                    try {
                        const r = await fetch(`/partidas/${partidaId}/dados`, {headers: {Accept: 'application/json'}});
                        if (!r.ok) return;
                        const n = await r.json();
                        if (n.placar_a > this.dados.placar_a) { this.flashA = true; setTimeout(() => this.flashA = false, 600); }
                        if (n.placar_b > this.dados.placar_b) { this.flashB = true; setTimeout(() => this.flashB = false, 600); }
                        this.dados = n;
                    } catch(e) {}
                },
            };
        }
    </script>
</x-app-layout>
