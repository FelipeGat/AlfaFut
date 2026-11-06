<x-guest-layout>
    <x-slot name="title">Você não tem permissão</x-slot>

    <div class="text-center space-y-3">
        <p class="text-sm text-gray-700">Erro 403</p>
        <h1 class="text-2xl font-bold">Você não tem permissão</h1>
        <p>Sua conta não pode abrir esta página.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Ir para o início</a>
    </div>
</x-guest-layout>
