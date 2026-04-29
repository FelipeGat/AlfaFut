<x-app-layout>
    <x-slot name="title">{{ $partida->titulo }}</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">{{ $partida->patota->nome }}</p>
        <h1 class="font-bold text-2xl mt-1">{{ $partida->titulo }}</h1>
        <p class="opacity-95 mt-1">
            <time datetime="{{ $partida->data_hora->toIso8601String() }}">
                {{ $partida->data_hora->translatedFormat('l, d \d\e F \d\e Y \a\s H:i') }}
            </time>
        </p>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-3 gap-6">

        <section aria-labelledby="info-h" class="card lg:col-span-2">
            <h2 id="info-h">Detalhes</h2>
            @if ($partida->descricao)
                <p class="mt-2 text-gray-700">{{ $partida->descricao }}</p>
            @endif
            <dl class="grid sm:grid-cols-2 gap-4 mt-4 text-sm">
                <div>
                    <dt class="text-gray-600">Local</dt>
                    <dd class="font-medium">{{ $partida->local?->nome ?? 'A definir' }}</dd>
                    @if ($partida->local?->endereco)
                        <dd class="text-gray-700">{{ $partida->local->endereco }}</dd>
                    @endif
                    @if ($partida->local?->acessivel_cadeirante)
                        <dd class="mt-1">
                            <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-900" role="img" aria-label="Local acessivel para cadeirantes">Acessivel</span>
                        </dd>
                    @endif
                </div>
                <div>
                    <dt class="text-gray-600">Duracao</dt>
                    <dd class="font-medium">{{ $partida->duracao_minutos }} min</dd>
                </div>
                <div>
                    <dt class="text-gray-600">Valor por jogador</dt>
                    <dd class="font-medium">R$ {{ number_format($partida->valor_individual, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-600">Status</dt>
                    <dd class="font-medium uppercase text-xs">{{ $partida->status }}</dd>
                </div>
                @if ($partida->confirmacao_ate)
                    <div class="sm:col-span-2">
                        <dt class="text-gray-600">Confirmar ate</dt>
                        <dd class="font-medium">
                            <time datetime="{{ $partida->confirmacao_ate->toIso8601String() }}">
                                {{ $partida->confirmacao_ate->translatedFormat('d/m H:i') }}
                            </time>
                        </dd>
                    </div>
                @endif
            </dl>

            <div class="mt-6 flex gap-2 flex-wrap" role="group" aria-label="Acoes da partida">
                @if ($partida->status === 'agendada' || $partida->status === 'confirmada')
                    <form method="POST" action="{{ route('partidas.confirmar', $partida) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary"
                                aria-label="Confirmar minha presenca nesta partida">
                            @if ($minhaConfirmacao && $minhaConfirmacao->status === 'confirmado')
                                Atualizar confirmacao
                            @else
                                Confirmar presenca
                            @endif
                        </button>
                    </form>
                    <form method="POST" action="{{ route('partidas.recusar', $partida) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline">Nao vou</button>
                    </form>
                @endif
            </div>

            @php
                $papelUsuario = $partida->patota->membrosAtivos()
                    ->where('users.id', auth()->id())
                    ->value('patota_membros.papel');
                $eAdmin = in_array($papelUsuario, ['administrador', 'organizador']);
            @endphp
            @if ($eAdmin && $partida->confirmados->count() >= 2)
                <form method="POST" action="{{ route('partidas.sortear', $partida) }}" class="mt-3"
                      onsubmit="return confirm('Sortear/re-sortear os times agora? Times atuais serao apagados.');">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        🎲 {{ $partida->times->count() ? 'Re-sortear times' : 'Sortear times' }}
                    </button>
                </form>
            @endif

            @if ($minhaConfirmacao)
                <p class="mt-3 text-sm" role="status" aria-live="polite">
                    @if ($minhaConfirmacao->em_lista_espera)
                        Voce esta na <strong>posicao {{ $minhaConfirmacao->posicao_lista_espera }}</strong> da lista de espera.
                    @elseif ($minhaConfirmacao->status === 'confirmado')
                        Voce confirmou presenca em
                        <time datetime="{{ $minhaConfirmacao->confirmado_em?->toIso8601String() }}">
                            {{ $minhaConfirmacao->confirmado_em?->translatedFormat('d/m H:i') }}
                        </time>.
                    @elseif ($minhaConfirmacao->status === 'recusado')
                        Voce informou que nao podera comparecer.
                    @endif
                </p>
            @endif
        </section>

        @if ($partida->times->count() > 0)
            <section aria-labelledby="times-h" class="card lg:col-span-2">
                <h2 id="times-h">Times sorteados</h2>
                <div class="mt-3 grid sm:grid-cols-2 gap-4">
                    @foreach ($partida->times as $time)
                        <div class="border rounded-lg p-3" aria-labelledby="time-{{ $time->id }}">
                            <h3 id="time-{{ $time->id }}" class="font-semibold">
                                {{ $time->nome }}
                                @if ($time->cor)
                                    <span class="text-xs text-gray-600 font-normal">({{ $time->cor }})</span>
                                @endif
                            </h3>
                            <ul class="mt-2 space-y-1" role="list">
                                @forelse ($time->jogadores as $jogador)
                                    <li class="text-sm flex items-center gap-2">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary text-on-primary text-xs font-bold" aria-hidden="true">
                                            {{ strtoupper(substr($jogador->apelido ?? $jogador->name, 0, 1)) }}
                                        </span>
                                        <span>{{ $jogador->apelido ?? $jogador->name }}</span>
                                        @if ($jogador->pivot->posicao)
                                            <span class="ms-auto text-xs text-gray-600">{{ $jogador->pivot->posicao }}</span>
                                        @endif
                                    </li>
                                @empty
                                    <li class="text-sm text-gray-700">Sem jogadores.</li>
                                @endforelse
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section aria-labelledby="conf-h" class="card">
            <h2 id="conf-h">Confirmados ({{ $partida->confirmados->count() }}/{{ $partida->vagas_total }})</h2>
            <ul class="mt-3 space-y-2" role="list">
                @forelse ($partida->confirmados as $c)
                    <li class="flex items-center gap-2 text-sm">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-primary text-on-primary text-xs font-bold" aria-hidden="true">
                            {{ strtoupper(substr($c->user->apelido ?? $c->user->name, 0, 1)) }}
                        </span>
                        <span>{{ $c->user->apelido ?? $c->user->name }}</span>
                        @if ($c->user->posicao_preferida)
                            <span class="ms-auto text-xs text-gray-700">{{ $c->user->posicao_preferida }}</span>
                        @endif
                    </li>
                @empty
                    <li class="text-sm text-gray-700">Ninguem confirmou ainda.</li>
                @endforelse
            </ul>

            @if ($partida->listaEspera->count())
                <h3 class="mt-4 text-sm font-semibold">Lista de espera</h3>
                <ol class="mt-2 space-y-1 text-sm" role="list">
                    @foreach ($partida->listaEspera as $c)
                        <li class="text-gray-700">
                            <span aria-hidden="true">{{ $c->posicao_lista_espera }}.</span>
                            <span class="sr-only">posicao {{ $c->posicao_lista_espera }}:</span>
                            {{ $c->user->apelido ?? $c->user->name }}
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
</x-app-layout>
