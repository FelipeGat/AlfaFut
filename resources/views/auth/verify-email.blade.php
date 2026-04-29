<x-guest-layout>
    <x-slot name="title">Verificar e-mail</x-slot>

    <div class="text-center mb-6">
        <h1 class="font-bold text-2xl" style="color: var(--md-sys-color-primary);">Verifique seu e-mail</h1>
        <p class="text-sm text-gray-700 mt-2">
            Obrigado por se cadastrar! Antes de comecar, confirme seu endereco de e-mail clicando no link que enviamos.
            Se nao recebeu, podemos reenviar.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 rounded-lg bg-green-50 border-l-4 border-green-700 text-green-900 text-sm" role="status">
            Um novo link de verificacao foi enviado para o e-mail informado no cadastro.
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-full">Reenviar e-mail de verificacao</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline w-full">Sair</button>
        </form>
    </div>
</x-guest-layout>
