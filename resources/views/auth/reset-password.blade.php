<x-guest-layout>
    <x-slot name="title">Nova senha</x-slot>

    <div class="text-center mb-6">
        <h1 class="font-bold text-2xl" style="color: var(--md-sys-color-primary);">Definir nova senha</h1>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" class="input" required autofocus autocomplete="username" value="{{ old('email', $request->email) }}">
            @error('email')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password">Nova senha</label>
            <input id="password" name="password" type="password" class="input" required autocomplete="new-password" minlength="8">
            @error('password')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation">Confirmar nova senha</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="input" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary w-full">Salvar nova senha</button>
    </form>
</x-guest-layout>
