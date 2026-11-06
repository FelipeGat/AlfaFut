<x-guest-layout>
    <x-slot name="title">Página não encontrada</x-slot>

    <div class="text-center space-y-3">
        <p class="text-sm text-gray-700">Erro 404</p>
        <h1 class="text-2xl font-bold">Página não encontrada</h1>
        <p>O endereço pode estar errado ou a página pode ter saído do ar.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Ir para o início</a>
    </div>
</x-guest-layout>
