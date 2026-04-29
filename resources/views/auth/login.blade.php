<x-guest-layout>
    <x-slot name="title">Entrar</x-slot>

    <div class="text-center mb-6">
        <h1 class="font-bold text-2xl" style="color: var(--md-sys-color-primary);">Entrar</h1>
        <p class="text-sm text-gray-700 mt-1">Acesse sua turma e organize as proximas peladas.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border-l-4 border-green-700 text-green-900 text-sm" role="status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email">E-mail</label>
            <input id="email"
                   name="email"
                   type="email"
                   class="input"
                   required
                   autofocus
                   autocomplete="username"
                   value="{{ old('email') }}"
                   @error('email') aria-invalid="true" aria-describedby="email-erro" @enderror>
            @error('email')<p id="email-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password">Senha</label>
            <input id="password"
                   name="password"
                   type="password"
                   class="input"
                   required
                   autocomplete="current-password">
            @error('password')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-between flex-wrap gap-2">
            <label class="flex items-center gap-2 mb-0 text-sm">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4">
                Lembrar de mim
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm underline" style="color: var(--md-sys-color-primary);">Esqueci minha senha</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-full">Entrar</button>

        <p class="text-center text-sm pt-2">
            Ainda nao tem conta?
            <a href="{{ route('register') }}" class="underline" style="color: var(--md-sys-color-primary);">Criar conta</a>
        </p>
    </form>
</x-guest-layout>
