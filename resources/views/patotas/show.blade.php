<x-app-layout>
    <x-slot name="title">{{ $patota->nome }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1 flex items-start gap-3">
                @if ($patota->brasao)
                    <img src="{{ asset($patota->brasao) }}" alt="Brasao {{ $patota->nome }}" class="h-16 sm:h-20 w-auto shrink-0 anim-fade-in" loading="lazy">
                @endif
                <div class="min-w-0">
                    <h1 class="font-bold text-xl sm:text-2xl">{{ $patota->nome }}</h1>
                    <p class="opacity-90 text-sm">{{ $patota->cidade ?? '' }}{{ $patota->estado ? ' / '.$patota->estado : '' }}</p>
                </div>
            </div>
            @if ($patota->criador_id === auth()->id())
                <a href="{{ route('patotas.edit', $patota) }}" class="btn btn-outline bg-white shrink-0">Editar</a>
            @endif
        </div>
        <nav aria-label="Areas da turma" class="mt-4 flex gap-2 flex-wrap">
            <a href="{{ route('patotas.mensagens.index', $patota) }}" class="px-3 py-2 rounded-full bg-white/15 text-on-primary text-sm font-medium">💬 Mural</a>
            <a href="{{ route('patotas.despesas.index', $patota) }}" class="px-3 py-2 rounded-full bg-white/15 text-on-primary text-sm font-medium">💰 Despesas</a>
        </nav>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-3 gap-6">

        <section aria-labelledby="info-heading" class="card lg:col-span-2">
            <h2 id="info-heading">Informacoes</h2>
            <p class="mt-2 text-gray-700">{{ $patota->descricao ?? 'Sem descricao.' }}</p>
            <dl class="grid grid-cols-2 gap-4 mt-4 text-sm">
                <div>
                    <dt class="text-gray-600">Jogadores por time</dt>
                    <dd class="font-medium">{{ $patota->jogadores_por_time }}</dd>
                </div>
                <div>
                    <dt class="text-gray-600">Quantidade de times</dt>
                    <dd class="font-medium">{{ $patota->quantidade_times }}</dd>
                </div>
                <div>
                    <dt class="text-gray-600">Vagas por partida</dt>
                    <dd class="font-medium">{{ $patota->vagasPorPartida() }}</dd>
                </div>
                <div>
                    <dt class="text-gray-600">Mensalidade</dt>
                    <dd class="font-medium">R$ {{ number_format($patota->valor_mensalidade, 2, ',', '.') }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-gray-600">Responsavel</dt>
                    <dd class="font-medium">{{ $patota->responsavel?->apelido ?? $patota->responsavel?->name ?? '-' }}</dd>
                </div>
                @if ($patota->criador_id === auth()->id())
                    <div class="col-span-2">
                        <dt class="text-gray-600">Codigo de convite</dt>
                        <dd class="font-mono font-bold text-lg">{{ $patota->codigo_convite }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section aria-labelledby="proximas-h" class="card">
            <h2 id="proximas-h">Proximas partidas</h2>
            @if ($proximasPartidas->isEmpty())
                <p class="mt-2 text-gray-700 text-sm">Nenhuma partida agendada.</p>
            @else
                <ul class="mt-2 space-y-2" role="list">
                    @foreach ($proximasPartidas as $partida)
                        <li>
                            <a href="{{ route('partidas.show', $partida) }}" class="block p-2 -m-2 rounded hover:bg-gray-50 focus:bg-gray-50">
                                <p class="font-medium">{{ $partida->titulo }}</p>
                                <p class="text-sm text-gray-700">
                                    <time datetime="{{ $partida->data_hora->toIso8601String() }}">
                                        {{ $partida->data_hora->translatedFormat('D, d/m H:i') }}
                                    </time>
                                </p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section aria-labelledby="membros-h" class="card lg:col-span-3">
            <h2 id="membros-h">Membros ativos ({{ $membros->count() }})</h2>
            <ul class="mt-3 grid sm:grid-cols-2 lg:grid-cols-3 gap-3" role="list">
                @foreach ($membros as $membro)
                    <li class="flex items-center gap-3 p-2 rounded border border-gray-100">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary text-on-primary font-bold" aria-hidden="true">
                            {{ strtoupper(substr($membro->apelido ?? $membro->name, 0, 1)) }}
                        </span>
                        <div>
                            <p class="font-medium">{{ $membro->apelido ?? $membro->name }}</p>
                            <p class="text-xs text-gray-700">
                                {{ $membro->posicao_preferida ?? 'qualquer posicao' }} &middot; {{ $membro->nivel_habilidade }}
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-app-layout>
