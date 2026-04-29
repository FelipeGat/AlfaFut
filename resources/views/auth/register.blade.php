<x-guest-layout>
    <x-slot name="title">Criar conta</x-slot>

    <div class="text-center mb-6">
        <h1 class="font-bold text-2xl" style="color: var(--md-sys-color-primary);">Crie sua conta</h1>
        <p class="text-sm text-gray-700 mt-1">Comece gratis e organize sua turma em minutos.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name">Nome completo *</label>
            <input id="name"
                   name="name"
                   type="text"
                   class="input"
                   required
                   autofocus
                   autocomplete="name"
                   value="{{ old('name') }}"
                   @error('name') aria-invalid="true" aria-describedby="name-erro" @enderror>
            @error('name')<p id="name-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email">E-mail *</label>
            <input id="email"
                   name="email"
                   type="email"
                   class="input"
                   required
                   autocomplete="username"
                   value="{{ old('email') }}"
                   @error('email') aria-invalid="true" aria-describedby="email-erro" @enderror>
            @error('email')<p id="email-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password">Senha *</label>
            <input id="password"
                   name="password"
                   type="password"
                   class="input"
                   required
                   autocomplete="new-password"
                   minlength="8"
                   @error('password') aria-invalid="true" aria-describedby="password-erro" @enderror>
            <p class="text-xs text-gray-600 mt-1">Use pelo menos 8 caracteres.</p>
            @error('password')<p id="password-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation">Confirmar senha *</label>
            <input id="password_confirmation"
                   name="password_confirmation"
                   type="password"
                   class="input"
                   required
                   autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary w-full mt-2">Criar conta</button>

        <p class="text-center text-sm pt-2">
            Ja tem uma conta?
            <a href="{{ route('login') }}" class="underline" style="color: var(--md-sys-color-primary);">Fazer login</a>
        </p>
    </form>
</x-guest-layout>
