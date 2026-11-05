<x-app-layout>
    <x-slot name="title">Alterar {{ $partida->titulo }}</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">{{ $patota->nome }}</p>
        <h1 class="font-bold text-xl sm:text-2xl mt-1">Alterar {{ $partida->titulo }}</h1>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('partidas.update', $partida) }}" class="card space-y-6">
            @csrf
            @method('PUT')
            @include('partidas._form')

            <div class="flex flex-wrap items-center justify-end gap-2">
                <a href="{{ route('partidas.show', $partida) }}" class="btn btn-outline">Voltar</a>
                <button type="submit" class="btn btn-primary">Salvar alterações</button>
            </div>
        </form>
    </div>
</x-app-layout>
