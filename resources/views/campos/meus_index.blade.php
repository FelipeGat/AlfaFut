<x-app-layout>
    <x-slot name="title">Meus campos</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h1 class="font-bold text-xl sm:text-2xl">Meus campos</h1>
            <a href="{{ route('meus-campos.create') }}" class="btn btn-secondary" aria-label="Cadastrar novo campo">+ Novo campo</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="bg-green-100 border border-green-300 text-green-900 px-4 py-3 rounded mb-4" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($campos->isEmpty())
            <div class="card text-center">
                <div class="text-5xl mb-3">🏟️</div>
                <p class="text-lg text-gray-700">Voce ainda nao cadastrou nenhum campo.</p>
                <p class="text-sm text-gray-600 mt-2">Cadastre seu campo para que organizadores de pelada possam encontrar voce.</p>
                <a href="{{ route('meus-campos.create') }}" class="btn btn-primary mt-4">Cadastrar meu primeiro campo</a>
            </div>
        @else
            <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4" role="list">
                @foreach ($campos as $campo)
                    <li class="card">
                        @if ($campo->foto_url)
                            <img src="{{ $campo->foto_url }}" alt="{{ $campo->nome }}" class="w-full h-32 object-cover rounded mb-3">
                        @endif
                        <div class="flex items-start justify-between gap-2">
                            <h2 class="font-semibold text-lg">
                                <a href="{{ route('campos.show', $campo) }}" class="text-primary underline">{{ $campo->nome }}</a>
                            </h2>
                            @if (! $campo->ativo)
                                <span class="px-2 py-1 rounded-full bg-gray-200 text-gray-700 text-xs">inativo</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-700 mt-1">
                            {{ $campo->cidade ?? 'Sem cidade' }}{{ $campo->estado ? '/' . $campo->estado : '' }}
                        </p>
                        @if ($campo->valor_hora)
                            <p class="text-sm mt-2 font-medium">R$ {{ number_format($campo->valor_hora, 2, ',', '.') }} <span class="text-gray-600 font-normal">/ hora</span></p>
                        @endif
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                            @if ($campo->tipo_piso)
                                <span class="px-2 py-1 rounded-full bg-gray-100">{{ \App\Models\Campo::tiposPiso()[$campo->tipo_piso] ?? $campo->tipo_piso }}</span>
                            @endif
                            @if ($campo->coberto)
                                <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-900">coberto</span>
                            @endif
                            @if ($campo->acessivel_cadeirante)
                                <span class="px-2 py-1 rounded-full bg-green-100 text-green-900">acessivel</span>
                            @endif
                        </div>
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('meus-campos.edit', $campo) }}" class="text-sm underline text-primary">Editar</a>
                            <form method="POST" action="{{ route('meus-campos.destroy', $campo) }}" class="inline" onsubmit="return confirm('Remover este campo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm underline text-red-700">Remover</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
