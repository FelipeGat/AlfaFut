<x-app-layout>
    <x-slot name="title">Novo local</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">{{ $patota->nome }}</p>
        <h1 class="font-bold text-xl sm:text-2xl mt-1">Novo local</h1>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('patotas.locais.store', $patota) }}" class="card space-y-6">
            @csrf
            @include('locais._form')

            <div class="flex flex-wrap items-center justify-end gap-2">
                <a href="{{ route('patotas.locais.index', $patota) }}" class="btn btn-outline">Voltar</a>
                <button type="submit" class="btn btn-primary">Cadastrar local</button>
            </div>
        </form>
    </div>
</x-app-layout>
