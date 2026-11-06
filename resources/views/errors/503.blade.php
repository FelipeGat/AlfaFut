<x-guest-layout>
    <x-slot name="title">Em manutenção</x-slot>

    <div class="text-center space-y-3">
        <p class="text-sm text-gray-700">Erro 503</p>
        <h1 class="text-2xl font-bold">Em manutenção</h1>
        <p>O AlfaFut volta em instantes.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Ir para o início</a>
    </div>
</x-guest-layout>
