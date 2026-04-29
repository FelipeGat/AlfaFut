<x-guest-layout>
    <x-slot name="title">Confirmar senha</x-slot>

    <div class="text-center mb-6">
        <h1 class="font-bold text-2xl" style="color: var(--md-sys-color-primary);">Confirmar senha</h1>
        <p class="text-sm text-gray-700 mt-2">Esta e uma area protegida. Confirme sua senha para continuar.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf
        <div>
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" class="input" required autocomplete="current-password" autofocus>
            @error('password')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-full">Confirmar</button>
    </form>
</x-guest-layout>
