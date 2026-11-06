<x-guest-layout>
    <x-slot name="title">Sessão expirada</x-slot>

    <div class="text-center space-y-3">
        <p class="text-sm text-gray-700">Erro 419</p>
        <h1 class="text-2xl font-bold">Sessão expirada</h1>
        <p>Volte, atualize a página e tente de novo.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Ir para o início</a>
    </div>
</x-guest-layout>
