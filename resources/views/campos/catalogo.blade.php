<x-app-layout>
    <x-slot name="title">Catalogo de campos</x-slot>
    <x-slot name="header">
        <h1 class="font-bold text-xl sm:text-2xl">Campos disponiveis</h1>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('campos.catalogo') }}" class="card mb-6 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="q" class="text-sm">Buscar por nome</label>
                <input id="q" name="q" type="text" class="input" value="{{ $busca }}" placeholder="Nome do campo">
            </div>
            <div class="w-full sm:w-auto">
                <label for="cidade" class="text-sm">Cidade</label>
                <select id="cidade" name="cidade" class="input">
                    <option value="">Todas</option>
                    @foreach ($cidades as $c)
                        <option value="{{ $c }}" {{ $cidade === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if ($busca || $cidade)
                <a href="{{ route('campos.catalogo') }}" class="btn btn-secondary">Limpar</a>
            @endif
        </form>

        @if ($campos->isEmpty())
            <div class="card text-center">
                <div class="text-5xl mb-3">🏟️</div>
                <p class="text-lg text-gray-700">Nenhum campo encontrado.</p>
                <p class="text-sm text-gray-600 mt-2">Tente outro filtro ou volte mais tarde.</p>
            </div>
        @else
            <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" role="list">
                @foreach ($campos as $campo)
                    <li class="card">
                        @if ($campo->foto_url)
                            <img src="{{ $campo->foto_url }}" alt="{{ $campo->nome }}" class="w-full h-32 object-cover rounded mb-3">
                        @endif
                        <h2 class="font-semibold text-lg">
                            <a href="{{ route('campos.show', $campo) }}" class="text-primary underline">{{ $campo->nome }}</a>
                        </h2>
                        <p class="text-sm text-gray-700 mt-1">
                            {{ $campo->cidade ?? 'Sem cidade' }}{{ $campo->estado ? '/' . $campo->estado : '' }}
                        </p>
                        @if ($campo->valor_hora)
                            <p class="text-sm mt-2 font-medium">R$ {{ number_format($campo->valor_hora, 2, ',', '.') }} <span class="text-gray-600 font-normal">/ hora</span></p>
                        @endif
                        <p class="text-xs text-gray-600 mt-2">Dono: {{ $campo->dono->nomeExibicao() }}</p>
                        <div class="mt-3 flex flex-wrap gap-1 text-xs">
                            @if ($campo->tipo_piso)
                                <span class="px-2 py-1 rounded-full bg-gray-100">{{ \App\Models\Campo::tiposPiso()[$campo->tipo_piso] ?? $campo->tipo_piso }}</span>
                            @endif
                            @if ($campo->coberto)<span class="px-2 py-1 rounded-full bg-blue-100 text-blue-900">coberto</span>@endif
                            @if ($campo->acessivel_cadeirante)<span class="px-2 py-1 rounded-full bg-green-100 text-green-900">acessivel</span>@endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">{{ $campos->links() }}</div>
        @endif
    </div>
</x-app-layout>
