<x-guest-layout>
    <x-slot name="title">Recuperar senha</x-slot>

    <div class="text-center mb-6">
        <h1 class="font-bold text-2xl" style="color: var(--md-sys-color-primary);">Esqueceu sua senha?</h1>
        <p class="text-sm text-gray-700 mt-2">Sem problema. Informe seu e-mail e enviaremos um link para criar uma nova senha.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border-l-4 border-green-700 text-green-900 text-sm" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" class="input" required autofocus value="{{ old('email') }}">
            @error('email')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-full">Enviar link de recuperacao</button>
        <p class="text-center text-sm pt-2">
            <a href="{{ route('login') }}" class="underline" style="color: var(--md-sys-color-primary);">Voltar para o login</a>
        </p>
    </form>
</x-guest-layout>
