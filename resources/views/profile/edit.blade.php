<x-app-layout>
    <x-slot name="title">Meu perfil</x-slot>
    <x-slot name="header">
        <h1 class="font-bold text-xl sm:text-2xl">Meu perfil</h1>
        <p class="opacity-90 text-sm">Atualize seus dados, preferências de jogo e senha.</p>
    </x-slot>

    <div class="py-6 sm:py-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if (session('status') === 'profile-updated')
            <div role="status" aria-live="polite" class="bg-green-50 border-l-4 border-green-700 text-green-900 p-4 rounded">
                ✓ Perfil atualizado.
            </div>
        @endif
        @if (session('status') === 'password-updated')
            <div role="status" aria-live="polite" class="bg-green-50 border-l-4 border-green-700 text-green-900 p-4 rounded">
                ✓ Senha alterada.
            </div>
        @endif
        @if (session('status') === 'verification-link-sent')
            <div role="status" aria-live="polite" class="bg-blue-50 border-l-4 border-blue-700 text-blue-900 p-4 rounded">
                Um novo link de verificação foi enviado para seu e-mail.
            </div>
        @endif

        {{-- Card 1: Dados pessoais --}}
        <section class="card" aria-labelledby="ident-h">
            <header class="mb-4">
                <h2 id="ident-h" class="text-lg font-semibold">Dados pessoais</h2>
                <p class="text-sm text-gray-700">Como você aparece para os colegas da turma.</p>
            </header>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name">Nome completo *</label>
                    <input id="name" name="name" type="text" class="input" required maxlength="120" autocomplete="name"
                           value="{{ old('name', $user->name) }}"
                           @error('name') aria-invalid="true" @enderror>
                    @error('name')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="apelido">Apelido</label>
                    <input id="apelido" name="apelido" type="text" class="input" maxlength="60" autocomplete="nickname"
                           value="{{ old('apelido', $user->apelido) }}"
                           @error('apelido') aria-invalid="true" @enderror>
                    @error('apelido')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="posicao_preferida">Posição preferida</label>
                        <select id="posicao_preferida" name="posicao_preferida" class="select" aria-describedby="sorteio-ajuda"
                                @error('posicao_preferida') aria-invalid="true" @enderror>
                            <option value="">Não informar</option>
                            @foreach (['goleiro' => 'Goleiro', 'zagueiro' => 'Zagueiro', 'lateral' => 'Lateral', 'meia' => 'Meia', 'atacante' => 'Atacante'] as $valor => $rotulo)
                                <option value="{{ $valor }}" @selected(old('posicao_preferida', $user->posicao_preferida) === $valor)>{{ $rotulo }}</option>
                            @endforeach
                        </select>
                        @error('posicao_preferida')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="nivel_habilidade">Nível</label>
                        <select id="nivel_habilidade" name="nivel_habilidade" class="select" aria-describedby="sorteio-ajuda"
                                @error('nivel_habilidade') aria-invalid="true" @enderror>
                            @foreach (['iniciante' => 'Iniciante', 'intermediario' => 'Intermediário', 'avancado' => 'Avançado'] as $valor => $rotulo)
                                <option value="{{ $valor }}" @selected(old('nivel_habilidade', $user->nivel_habilidade) === $valor)>{{ $rotulo }}</option>
                            @endforeach
                        </select>
                        @error('nivel_habilidade')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>
                <p id="sorteio-ajuda" class="text-sm text-gray-700">O sorteio de times usa a posição e o nível.</p>

                <div>
                    <label for="email">E-mail *</label>
                    <input id="email" name="email" type="email" class="input" required autocomplete="email"
                           value="{{ old('email', $user->email) }}"
                           @error('email') aria-invalid="true" @enderror>
                    @error('email')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-2 p-3 rounded bg-yellow-50 border-l-4 border-yellow-500 text-yellow-900 text-sm">
                            Seu e-mail ainda não foi confirmado.
                            <button type="submit" form="reenviar-confirmacao" class="underline font-medium">Reenviar link de confirmação</button>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn btn-primary">Salvar dados</button>
                </div>
            </form>

            <form id="reenviar-confirmacao" method="POST" action="{{ route('verification.send') }}" class="hidden">
                @csrf
            </form>
        </section>

        {{-- Card 2: Senha --}}
        <section class="card" aria-labelledby="senha-h">
            <header class="mb-4">
                <h2 id="senha-h" class="text-lg font-semibold">Alterar senha</h2>
                <p class="text-sm text-gray-700">Escolha uma senha forte e única para esta conta.</p>
            </header>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password">Senha atual *</label>
                    <input id="current_password" name="current_password" type="password" class="input" required autocomplete="current-password"
                           @error('current_password', 'updatePassword') aria-invalid="true" @enderror>
                    @error('current_password', 'updatePassword')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password">Nova senha *</label>
                    <input id="password" name="password" type="password" class="input" required autocomplete="new-password" minlength="8"
                           @error('password', 'updatePassword') aria-invalid="true" @enderror>
                    <p class="text-xs text-gray-600 mt-1">Use pelo menos 8 caracteres.</p>
                    @error('password', 'updatePassword')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation">Confirmar nova senha *</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input" required autocomplete="new-password">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn btn-primary">Alterar senha</button>
                </div>
            </form>
        </section>

        {{-- Card 3: Seus dados --}}
        <section class="card" aria-labelledby="dados-h">
            <header class="mb-4">
                <h2 id="dados-h" class="text-lg font-semibold">Seus dados</h2>
                <p class="text-sm text-gray-700">Baixe uma cópia do que o AlfaFut guarda sobre você: cadastro, acessibilidade, turmas, presenças, parcelas e mensagens. Veja como tratamos seus dados na <a href="{{ route('privacidade') }}" class="underline">política de privacidade</a>.</p>
            </header>

            <a href="{{ route('profile.dados') }}" class="btn btn-secondary">Baixar meus dados</a>
        </section>

        {{-- Card 4: Zona perigosa --}}
        <section class="card border-2 border-red-200" aria-labelledby="perigo-h" x-data="{ confirmando: false }">
            <header class="mb-4">
                <h2 id="perigo-h" class="text-lg font-semibold text-red-700">Zona perigosa</h2>
                <p class="text-sm text-gray-700">Seu nome, e-mail, telefone, posição, nível e preferências são apagados, e você perde o acesso. Em partidas, placares e despesas antigas da turma, seu nome passa a aparecer como "Conta excluída", para os totais dos outros continuarem certos. Se você criou uma turma com outros membros, arquive a turma antes.</p>
            </header>

            <button type="button" class="btn btn-danger" @click="confirmando = true" x-show="!confirmando">
                Excluir minha conta
            </button>

            <form method="POST" action="{{ route('profile.destroy') }}" x-show="confirmando" x-cloak class="space-y-4">
                @csrf
                @method('DELETE')

                <p class="text-sm text-red-800 font-semibold">Tem certeza? Digite sua senha para confirmar:</p>

                <div>
                    <label for="password_delete">Senha</label>
                    <input id="password_delete" name="password" type="password" class="input" required autocomplete="current-password"
                           @error('password', 'userDeletion') aria-invalid="true" @enderror>
                    @error('password', 'userDeletion')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-wrap gap-2 justify-end">
                    <button type="button" class="btn btn-outline" @click="confirmando = false">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar exclusão</button>
                </div>
            </form>
        </section>

        <div class="text-center">
            <a href="{{ route('dashboard') }}" class="text-primary underline">← Voltar para o painel</a>
        </div>
    </div>
</x-app-layout>
