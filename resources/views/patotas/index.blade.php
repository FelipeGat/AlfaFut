<x-app-layout>
    <x-slot name="title">Patotas</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-bold text-2xl">Minhas patotas</h1>
            <a href="{{ route('patotas.create') }}" class="btn btn-secondary" aria-label="Criar nova patota">+ Nova</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($patotas->isEmpty())
            <div class="card text-center">
                <p class="text-lg text-gray-700">Voce ainda nao participa de nenhuma patota.</p>
                <a href="{{ route('patotas.create') }}" class="btn btn-primary mt-4">Criar minha primeira patota</a>
            </div>
        @else
            <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" role="list">
                @foreach ($patotas as $patota)
                    <li class="card">
                        <h2 class="font-semibold text-lg">
                            <a href="{{ route('patotas.show', $patota) }}" class="text-primary underline">{{ $patota->nome }}</a>
                        </h2>
                        <p class="text-sm text-gray-700 mt-1">{{ $patota->cidade ?? 'Sem cidade' }} &middot; {{ $patota->total_membros }} membros</p>
                        <p class="text-sm mt-2">{{ Str::limit($patota->descricao, 90) ?: '—' }}</p>
                        <div class="mt-3 flex items-center gap-2 text-xs">
                            <span class="px-2 py-1 rounded-full bg-gray-100">{{ $patota->jogadores_por_time }}x{{ $patota->jogadores_por_time }}</span>
                            @if ($patota->publica)
                                <span class="px-2 py-1 rounded-full bg-green-100 text-green-900">publica</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
