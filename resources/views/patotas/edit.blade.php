<x-app-layout>
    <x-slot name="title">Editar turma</x-slot>
    <x-slot name="header">
        <h1 class="font-bold text-2xl">Editar {{ $patota->nome }}</h1>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        <form method="POST" action="{{ route('patotas.update', $patota) }}" class="card space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="nome">Nome *</label>
                <input id="nome" name="nome" type="text" class="input" required maxlength="120" value="{{ old('nome', $patota->nome) }}">
            </div>

            <div>
                <label for="descricao">Descricao</label>
                <textarea id="descricao" name="descricao" class="textarea" rows="3" maxlength="500">{{ old('descricao', $patota->descricao) }}</textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="cidade">Cidade</label>
                    <input id="cidade" name="cidade" type="text" class="input" maxlength="80" value="{{ old('cidade', $patota->cidade) }}">
                </div>
                <div>
                    <label for="estado">Estado (UF)</label>
                    <input id="estado" name="estado" type="text" class="input" maxlength="2" minlength="2" value="{{ old('estado', $patota->estado) }}">
                </div>
            </div>

            <fieldset x-data="{ sel: '{{ old('brasao', $patota->brasao) }}' }">
                <legend class="font-medium block mb-2">Brasao da turma</legend>
                <input type="hidden" name="brasao" :value="sel">
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                    @foreach ($clubes as $clube)
                        <button type="button"
                                @click="sel = '{{ $clube['brasao'] }}'"
                                :class="sel === '{{ $clube['brasao'] }}' ? 'ring-4 ring-offset-2 scale-105' : ''"
                                style="--tw-ring-color: var(--md-sys-color-primary);"
                                class="bg-white border rounded-lg p-2 transition-all hover:shadow-lg focus:outline-none"
                                :aria-pressed="sel === '{{ $clube['brasao'] }}' ? 'true' : 'false'"
                                aria-label="Escolher brasao {{ $clube['nome'] }}">
                            <img src="{{ asset($clube['brasao']) }}" alt="{{ $clube['nome'] }}" class="w-full h-auto" loading="lazy">
                            <span class="block text-xs mt-1 text-center text-gray-700 truncate">{{ $clube['nome'] }}</span>
                        </button>
                    @endforeach
                </div>
            </fieldset>

            <div>
                <label for="responsavel_id">Responsavel pela turma</label>
                <select id="responsavel_id" name="responsavel_id" class="select" required>
                    @foreach ($membros as $m)
                        <option value="{{ $m->id }}" @selected(old('responsavel_id', $patota->responsavel_id) == $m->id)>
                            {{ $m->apelido ?? $m->name }}@if ($m->id === $patota->criador_id) (criador)@endif
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-600 mt-1">Apenas o responsavel (e o criador) pode controlar o cronometro e marcar gols ao vivo.</p>
            </div>

            <fieldset class="border rounded-lg p-4">
                <legend class="px-2 font-medium">Configuracao</legend>
                <div class="grid sm:grid-cols-2 gap-4 mt-2">
                    <div>
                        <label for="jogadores_por_time">Jogadores por time *</label>
                        <input id="jogadores_por_time" name="jogadores_por_time" type="number" class="input" required min="3" max="11" value="{{ old('jogadores_por_time', $patota->jogadores_por_time) }}">
                    </div>
                    <div>
                        <label for="quantidade_times">Quantidade de times *</label>
                        <input id="quantidade_times" name="quantidade_times" type="number" class="input" required min="2" max="6" value="{{ old('quantidade_times', $patota->quantidade_times) }}">
                    </div>
                    <div>
                        <label for="valor_mensalidade">Mensalidade (R$)</label>
                        <input id="valor_mensalidade" name="valor_mensalidade" type="number" class="input" step="0.01" min="0" value="{{ old('valor_mensalidade', $patota->valor_mensalidade) }}" inputmode="decimal">
                    </div>
                    <div class="flex items-center mt-7">
                        <input id="publica" name="publica" type="checkbox" value="1" class="h-5 w-5" {{ old('publica', $patota->publica) ? 'checked' : '' }}>
                        <label for="publica" class="ms-2 mb-0">Turma publica</label>
                    </div>
                </div>
            </fieldset>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('patotas.show', $patota) }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar alteracoes</button>
            </div>
        </form>

        <form method="POST" action="{{ route('patotas.destroy', $patota) }}" onsubmit="return confirm('Tem certeza que deseja arquivar esta turma?');" class="card">
            @csrf
            @method('DELETE')
            <h2 class="text-red-700">Zona perigosa</h2>
            <p class="text-sm text-gray-700 mt-2">Arquivar a turma oculta ela e suspende as partidas. Esta acao podera ser revertida pelo administrador.</p>
            <button type="submit" class="btn btn-danger mt-4">Arquivar turma</button>
        </form>
    </div>
</x-app-layout>
