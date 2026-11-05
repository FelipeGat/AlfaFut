<x-app-layout>
    <x-slot name="title">Alterar {{ $local->nome }}</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">{{ $patota->nome }}</p>
        <h1 class="font-bold text-xl sm:text-2xl mt-1">Alterar {{ $local->nome }}</h1>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('patotas.locais.update', [$patota, $local]) }}" class="card space-y-6">
            @csrf
            @method('PUT')
            @include('locais._form')

            <div class="flex flex-wrap items-center justify-end gap-2">
                <a href="{{ route('patotas.locais.index', $patota) }}" class="btn btn-outline">Voltar</a>
                <button type="submit" class="btn btn-primary">Salvar alterações</button>
            </div>
        </form>
    </div>
</x-app-layout>
