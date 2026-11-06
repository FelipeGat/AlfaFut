<x-guest-layout>
    <x-slot name="title">Algo deu errado</x-slot>

    <div class="text-center space-y-3">
        <p class="text-sm text-gray-700">Erro 500</p>
        <h1 class="text-2xl font-bold">Algo deu errado</h1>
        <p>Tivemos um erro interno. Tente de novo em instantes.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Ir para o início</a>
    </div>
</x-guest-layout>
