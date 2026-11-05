<x-guest-layout>
    <x-slot name="title">Confirmar e-mail</x-slot>

    <div class="text-center mb-6">
        <h1 class="font-bold text-2xl" style="color: var(--md-sys-color-primary);">Confirme o seu e-mail</h1>
        <p class="text-sm text-gray-700 mt-2">
            Obrigado por se cadastrar! Antes de começar, confirme o seu e-mail pelo link que enviamos.
            Se não recebeu, olhe a caixa de spam ou peça o reenvio.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 rounded-lg bg-green-50 border-l-4 border-green-700 text-green-900 text-sm" role="status" aria-live="polite">
            Enviamos um novo link para o e-mail informado no cadastro.
        </div>
    @endif

    @error('email')
        <div class="mb-4 p-3 rounded-lg bg-red-50 border-l-4 border-red-700 text-red-900 text-sm" role="alert" aria-live="assertive">
            {{ $message }}
        </div>
    @enderror

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary w-full">Reenviar o e-mail de confirmação</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline w-full">Sair</button>
        </form>
    </div>
</x-guest-layout>
