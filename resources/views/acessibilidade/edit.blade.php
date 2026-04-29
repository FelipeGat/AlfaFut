<x-app-layout>
    <x-slot name="title">Acessibilidade</x-slot>
    <x-slot name="header">
        <h1 class="font-bold text-2xl">Preferencias de acessibilidade</h1>
        <p class="opacity-90 text-sm">Suas escolhas se aplicam imediatamente em toda a aplicacao.</p>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('acessibilidade.update') }}" class="card space-y-8">
            @csrf
            @method('PATCH')

            <fieldset>
                <legend class="font-semibold text-lg">Visualizacao</legend>
                <p class="text-sm text-gray-700 mt-1">Configure contraste e tamanho de fonte (WCAG 1.4.4 / 1.4.6).</p>

                <div class="mt-4 flex items-center gap-3">
                    <input id="alto_contraste" name="alto_contraste" type="checkbox" value="1" class="h-5 w-5"
                           {{ $user->alto_contraste ? 'checked' : '' }}>
                    <label for="alto_contraste" class="mb-0">Ativar tema de alto contraste</label>
                </div>

                <div class="mt-4">
                    <label for="tamanho_fonte">Tamanho de fonte</label>
                    <select id="tamanho_fonte" name="tamanho_fonte" class="select" required>
                        @foreach (['pequena' => 'Pequena (14px)', 'media' => 'Media (16px) - padrao', 'grande' => 'Grande (18px)', 'extra_grande' => 'Extra grande (22px)'] as $valor => $rotulo)
                            <option value="{{ $valor }}" @selected($user->tamanho_fonte === $valor)>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                </div>
            </fieldset>

            <fieldset>
                <legend class="font-semibold text-lg">Movimento e leitor de tela</legend>

                <div class="mt-4 flex items-center gap-3">
                    <input id="reduzir_movimento" name="reduzir_movimento" type="checkbox" value="1" class="h-5 w-5"
                           {{ $user->reduzir_movimento ? 'checked' : '' }}>
                    <label for="reduzir_movimento" class="mb-0">Reduzir animacoes e transicoes (WCAG 2.3.3)</label>
                </div>

                <div class="mt-3 flex items-center gap-3">
                    <input id="leitor_tela_otimizado" name="leitor_tela_otimizado" type="checkbox" value="1" class="h-5 w-5"
                           {{ $user->leitor_tela_otimizado ? 'checked' : '' }}>
                    <label for="leitor_tela_otimizado" class="mb-0">Otimizar para leitor de tela (descricoes detalhadas)</label>
                </div>
            </fieldset>

            <fieldset>
                <legend class="font-semibold text-lg">Necessidades especificas</legend>
                <p class="text-sm text-gray-700 mt-1">Marque o que se aplica para que o organizador possa adaptar a partida.</p>

                @php
                    $necessidades = $user->necessidades_acessibilidade ?? [];
                    $opcoes = [
                        'cadeirante' => 'Cadeirante / mobilidade reduzida',
                        'visual_baixa' => 'Baixa visao',
                        'visual_total' => 'Cego',
                        'auditiva' => 'Surdo / deficiencia auditiva',
                        'libras' => 'Comunicacao em Libras',
                        'cognitiva' => 'Deficiencia cognitiva',
                        'tea' => 'Transtorno do espectro autista',
                    ];
                @endphp
                <div class="mt-3 grid sm:grid-cols-2 gap-2">
                    @foreach ($opcoes as $valor => $rotulo)
                        <label class="flex items-center gap-2 mb-0">
                            <input type="checkbox" name="necessidades_acessibilidade[]" value="{{ $valor }}" class="h-5 w-5"
                                   {{ in_array($valor, $necessidades) ? 'checked' : '' }}>
                            <span>{{ $rotulo }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Salvar preferencias</button>
            </div>
        </form>
    </div>
</x-app-layout>
