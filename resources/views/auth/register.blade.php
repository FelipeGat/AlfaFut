<x-guest-layout>
    <x-slot name="title">Criar conta</x-slot>

    <div class="text-center mb-6">
        <h1 class="font-bold text-2xl" style="color: var(--md-sys-color-primary);">Crie sua conta</h1>
        <p class="text-sm text-gray-700 mt-1">Comece gratis e organize sua turma em minutos.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-2 font-medium">Eu quero me cadastrar como *</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2" role="radiogroup" aria-label="Tipo de usuario">
                @php
                    $tipoSelecionado = old('tipo_usuario', 'jogador');
                    $tipos = [
                        'jogador' => ['icone' => '⚽', 'titulo' => 'Jogador', 'desc' => 'Vou participar de peladas'],
                        'dono_pelada' => ['icone' => '👥', 'titulo' => 'Dono da pelada', 'desc' => 'Vou criar e organizar uma turma'],
                        'dono_campo' => ['icone' => '🏟️', 'titulo' => 'Dono do campo', 'desc' => 'Vou cadastrar meu campo pra alugar'],
                    ];
                @endphp
                @foreach ($tipos as $valor => $info)
                    <label class="cursor-pointer border-2 rounded-lg p-3 text-center transition hover:shadow-md"
                           style="border-color: {{ $tipoSelecionado === $valor ? 'var(--md-sys-color-primary)' : '#d1d5db' }};
                                  background-color: {{ $tipoSelecionado === $valor ? 'rgba(0, 100, 60, 0.05)' : 'white' }};">
                        <input type="radio"
                               name="tipo_usuario"
                               value="{{ $valor }}"
                               class="sr-only"
                               {{ $tipoSelecionado === $valor ? 'checked' : '' }}
                               required>
                        <div class="text-3xl mb-1">{{ $info['icone'] }}</div>
                        <div class="font-semibold text-sm">{{ $info['titulo'] }}</div>
                        <div class="text-xs text-gray-600 mt-1">{{ $info['desc'] }}</div>
                    </label>
                @endforeach
            </div>
            @error('tipo_usuario')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        </div>

        <script>
            // Realca o cartao selecionado
            document.querySelectorAll('input[name="tipo_usuario"]').forEach((r) => {
                r.addEventListener('change', () => {
                    document.querySelectorAll('input[name="tipo_usuario"]').forEach((x) => {
                        const lbl = x.closest('label');
                        if (x.checked) {
                            lbl.style.borderColor = 'var(--md-sys-color-primary)';
                            lbl.style.backgroundColor = 'rgba(0, 100, 60, 0.05)';
                        } else {
                            lbl.style.borderColor = '#d1d5db';
                            lbl.style.backgroundColor = 'white';
                        }
                    });
                });
            });
        </script>

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
