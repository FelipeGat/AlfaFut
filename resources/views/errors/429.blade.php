<x-guest-layout>
    <x-slot name="title">Muitas tentativas</x-slot>

    <div class="text-center space-y-3">
        <p class="text-sm text-gray-700">Erro 429</p>
        <h1 class="text-2xl font-bold">Muitas tentativas</h1>
        <p>Aguarde um pouco e tente de novo.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Ir para o início</a>
    </div>
</x-guest-layout>
