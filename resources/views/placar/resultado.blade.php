<x-app-layout>
    <x-slot name="title">Resultado - {{ $partida->titulo }}</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">{{ $partida->patota->nome }}</p>
        <h1 class="font-bold text-xl sm:text-2xl">🏆 Resultado final</h1>
    </x-slot>

    @php
        $totalGols = (int) $partida->placar_a + (int) $partida->placar_b;
        $timeA = $partida->times[0] ?? null;
        $timeB = $partida->times[1] ?? null;
        $vencedor = null;
        if ($timeA && $timeB) {
            if ($partida->placar_a > $partida->placar_b) $vencedor = $timeA;
            elseif ($partida->placar_b > $partida->placar_a) $vencedor = $timeB;
        }
    @endphp

    <!-- Confetti -->
    @if ($vencedor)
        <div aria-hidden="true" class="pointer-events-none fixed inset-0 z-0 overflow-hidden" id="confetti-host"></div>
    @endif

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 relative z-10">

        <!-- Placar final -->
        <section class="card text-center anim-fade-in">
            <p class="text-xs uppercase tracking-widest text-gray-700 mb-3">Placar final</p>
            <div class="grid grid-cols-3 items-center gap-2">
                <div>
                    @if ($timeA?->brasao)
                        <img src="{{ asset($timeA->brasao) }}" alt="Brasao {{ $timeA->nome }}" class="h-20 sm:h-32 w-auto mx-auto anim-fade-in" loading="lazy">
                    @endif
                    <p class="text-sm sm:text-lg font-semibold uppercase mt-2 truncate">{{ $timeA?->nome ?? 'Time A' }}</p>
                    <p class="text-5xl sm:text-8xl font-black mt-2">{{ $partida->placar_a }}</p>
                </div>
                <div class="text-2xl sm:text-4xl text-gray-400">×</div>
                <div>
                    @if ($timeB?->brasao)
                        <img src="{{ asset($timeB->brasao) }}" alt="Brasao {{ $timeB->nome }}" class="h-20 sm:h-32 w-auto mx-auto anim-fade-in" loading="lazy">
                    @endif
                    <p class="text-sm sm:text-lg font-semibold uppercase mt-2 truncate">{{ $timeB?->nome ?? 'Time B' }}</p>
                    <p class="text-5xl sm:text-8xl font-black mt-2">{{ $partida->placar_b }}</p>
                </div>
            </div>

            @if ($vencedor)
                <p class="mt-6 text-lg">
                    🏆 <strong style="color: var(--md-sys-color-primary);">{{ $vencedor->nome }}</strong> venceu!
                </p>
            @else
                <p class="mt-6 text-lg">🤝 Empate</p>
            @endif

            <p class="mt-2 text-sm text-gray-700">
                Tempo total: <strong>{{ $partida->tempoFormatado() }}</strong> &middot;
                Total de gols: <strong>{{ $totalGols }}</strong>
            </p>
        </section>

        <!-- Artilharia -->
        <section class="card anim-slide-up">
            <h2 class="text-lg font-semibold mb-4">⚽ Artilharia da partida</h2>
            @if ($artilheiros->isEmpty())
                <p class="text-gray-700 text-sm">Nenhum gol registrado.</p>
            @else
                <ol class="space-y-2" role="list">
                    @foreach ($artilheiros as $i => $a)
                        <li class="flex items-center gap-3 p-3 rounded-lg @if($i === 0) bg-yellow-50 border border-yellow-200 @else border @endif">
                            <span class="text-2xl w-8 text-center">
                                @if ($i === 0) 🥇
                                @elseif ($i === 1) 🥈
                                @elseif ($i === 2) 🥉
                                @else &nbsp;&nbsp;
                                @endif
                            </span>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary text-on-primary font-bold" aria-hidden="true">
                                {{ strtoupper(substr($a['jogador']?->apelido ?? $a['jogador']?->name ?? '?', 0, 1)) }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <strong>{{ $a['jogador']?->apelido ?? $a['jogador']?->name ?? 'Desconhecido' }}</strong>
                                <span class="text-sm text-gray-600 block">{{ $a['time']?->nome ?? '' }}</span>
                            </div>
                            <span class="text-2xl font-bold">{{ $a['gols'] }}</span>
                            <span class="text-sm text-gray-700">{{ $a['gols'] === 1 ? 'gol' : 'gols' }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <!-- Linha do tempo de eventos -->
        <section class="card anim-slide-up">
            <h2 class="text-lg font-semibold mb-3">📜 Linha do tempo</h2>
            @php $eventosImportantes = $partida->eventos->whereIn('tipo', ['inicio', 'gol', 'gol_contra', 'fim']); @endphp
            @if ($eventosImportantes->isEmpty())
                <p class="text-gray-700 text-sm">Sem eventos registrados.</p>
            @else
                <ul class="space-y-2" role="list">
                    @foreach ($eventosImportantes as $ev)
                        <li class="flex items-center gap-3 text-sm">
                            <span class="font-mono w-12 text-right">{{ $ev->minuto }}'</span>
                            <span class="text-xl">
                                @switch($ev->tipo)
                                    @case('gol') ⚽ @break
                                    @case('gol_contra') 😬 @break
                                    @case('inicio') ▶️ @break
                                    @case('fim') 🏁 @break
                                @endswitch
                            </span>
                            <span class="flex-1">
                                @if ($ev->jogador)
                                    <strong>{{ $ev->jogador->apelido ?? $ev->jogador->name }}</strong>
                                @else
                                    <strong>{{ ucfirst(str_replace('_', ' ', $ev->tipo)) }}</strong>
                                @endif
                                @if ($ev->time)
                                    <span class="text-gray-600">- {{ $ev->time->nome }}</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="flex flex-wrap gap-2 justify-center">
            <a href="{{ route('partidas.show', $partida) }}" class="btn btn-outline">Detalhes da partida</a>
            <a href="{{ route('patotas.show', $partida->patota) }}" class="btn btn-primary">Voltar para a turma</a>
        </div>
    </div>

    @if ($vencedor)
        <script>
            (function() {
                if (document.documentElement.dataset.reduzirMovimento === '1') return;
                const host = document.getElementById('confetti-host');
                if (!host) return;
                const cores = ['#1b5e20', '#f57c00', '#fbc02d', '#1976d2', '#d32f2f', '#7b1fa2'];
                for (let i = 0; i < 80; i++) {
                    const c = document.createElement('span');
                    c.style.cssText = `
                        position: absolute;
                        top: -20px;
                        left: ${Math.random() * 100}%;
                        width: ${6 + Math.random() * 8}px;
                        height: ${10 + Math.random() * 14}px;
                        background: ${cores[Math.floor(Math.random() * cores.length)]};
                        border-radius: 2px;
                        animation: confete-cair ${3 + Math.random() * 3}s linear ${Math.random() * 2}s forwards;
                    `;
                    host.appendChild(c);
                }
                setTimeout(() => host.remove(), 7000);
            })();
        </script>
    @endif
</x-app-layout>
