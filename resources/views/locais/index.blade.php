<x-app-layout>
    <x-slot name="title">Locais de {{ $patota->nome }}</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">
            <a href="{{ route('patotas.show', $patota) }}" class="underline">{{ $patota->nome }}</a>
        </p>
        <div class="mt-1 flex flex-wrap items-center justify-between gap-2">
            <h1 class="font-bold text-xl sm:text-2xl">Locais da turma</h1>
            <a href="{{ route('patotas.locais.create', $patota) }}" class="btn btn-secondary">+ Novo local</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($locais->isEmpty())
            <div class="card text-center">
                <p class="text-lg text-gray-700">A turma ainda não tem local cadastrado.</p>
                <p class="text-sm text-gray-700 mt-2">Cadastre os lugares onde vocês costumam jogar, ou use um campo do catálogo ao marcar a partida.</p>
                <a href="{{ route('patotas.locais.create', $patota) }}" class="btn btn-primary mt-4">Cadastrar o primeiro local</a>
            </div>
        @else
            <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" role="list">
                @foreach ($locais as $local)
                    <li class="card">
                        <h2 class="font-semibold text-lg">{{ $local->nome }}</h2>
                        @if ($local->endereco)
                            <p class="text-sm text-gray-700 mt-1">{{ $local->endereco }}</p>
                        @endif
                        <p class="text-sm text-gray-700">{{ $local->cidade }}{{ $local->estado ? ' / '.$local->estado : '' }}</p>
                        @if ($local->acessivel_cadeirante)
                            <p class="mt-2">
                                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-900">Acessível para cadeirantes</span>
                            </p>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('patotas.locais.edit', [$patota, $local]) }}" class="btn btn-outline" aria-label="Alterar {{ $local->nome }}">Alterar</a>
                            <form method="POST" action="{{ route('patotas.locais.destroy', [$patota, $local]) }}"
                                  onsubmit="return confirm('Remover este local?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" aria-label="Remover {{ $local->nome }}">Remover</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
