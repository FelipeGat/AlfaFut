<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <h1 class="font-bold text-xl sm:text-2xl">Olá, {{ auth()->user()->apelido ?? auth()->user()->name }}!</h1>
        <p class="opacity-90 text-sm mt-1">Pronto para a próxima pelada?</p>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <section aria-labelledby="resumo-heading">
            <h2 id="resumo-heading" class="sr-only">Resumo</h2>
            <div class="grid sm:grid-cols-3 gap-4">
                <div class="card">
                    <p class="text-sm text-gray-700">Suas turmas</p>
                    <p class="text-3xl font-bold mt-1">{{ $patotas->count() }}</p>
                </div>
                <div class="card">
                    <p class="text-sm text-gray-700">Próximas partidas</p>
                    <p class="text-3xl font-bold mt-1">{{ $proximasPartidas->count() }}</p>
                </div>
                <div class="card">
                    <p class="text-sm text-gray-700">Confirmadas por você</p>
                    <p class="text-3xl font-bold mt-1">{{ $totalConfirmadas }}</p>
                </div>
            </div>
        </section>

        <section aria-labelledby="entrar-heading" class="card">
            <h2 id="entrar-heading" class="mb-3">Recebeu um convite?</h2>
            @include('partials.entrar-codigo')
        </section>

        <section aria-labelledby="patotas-heading" class="card">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h2 id="patotas-heading">Minhas turmas</h2>
                <a href="{{ route('patotas.create') }}" class="btn btn-primary">+ Nova</a>
            </div>
            @if ($patotas->isEmpty())
                <p class="text-gray-700">Você ainda não participa de nenhuma turma. Entre com um código de convite ou <a href="{{ route('patotas.create') }}" class="text-primary underline">crie a primeira</a>.</p>
            @else
                <ul class="divide-y" role="list">
                    @foreach ($patotas as $patota)
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <a href="{{ route('patotas.show', $patota) }}" class="font-medium text-primary underline">{{ $patota->nome }}</a>
                                <p class="text-sm text-gray-700">
                                    {{ $patota->total_membros }} membros &middot; {{ $patota->cidade ?? 'sem cidade' }}
                                </p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100">
                                {{ $patota->jogadores_por_time }}x{{ $patota->jogadores_por_time }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section aria-labelledby="proximas-heading" class="card">
            <h2 id="proximas-heading" class="mb-4">Próximas partidas</h2>
            @if ($proximasPartidas->isEmpty())
                <p class="text-gray-700">Nenhuma partida agendada.</p>
            @else
                <ul class="divide-y" role="list">
                    @foreach ($proximasPartidas as $partida)
                        <li class="py-3">
                            <a href="{{ route('partidas.show', $partida) }}" class="block focus:bg-gray-50 hover:bg-gray-50 rounded-lg p-2 -m-2">
                                <p class="font-medium">{{ $partida->titulo }}</p>
                                <p class="text-sm text-gray-700">
                                    {{ $partida->patota->nome }} &middot;
                                    <time datetime="{{ $partida->data_hora->toIso8601String() }}">
                                        {{ $partida->data_hora->translatedFormat('D, d \d\e M') }} às {{ $partida->data_hora->format('H:i') }}
                                    </time>
                                </p>
                                <p class="text-xs text-gray-600 mt-1">
                                    {{ $partida->lugar()?->nome ?? 'Local a definir' }} &middot;
                                    {{ $partida->vagasDisponiveis() }} {{ $partida->vagasDisponiveis() === 1 ? 'vaga livre' : 'vagas livres' }}
                                </p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

    </div>
</x-app-layout>
