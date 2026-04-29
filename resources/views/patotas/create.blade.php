<x-app-layout>
    <x-slot name="title">Nova patota</x-slot>
    <x-slot name="header">
        <h1 class="font-bold text-2xl">Criar nova patota</h1>
        <p class="text-sm opacity-90">Passo 1: voce sera o administrador, depois compartilhe o codigo de convite.</p>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('patotas.store') }}" class="card space-y-6" aria-describedby="form-help">
            @csrf
            <p id="form-help" class="text-sm text-gray-700">Os campos marcados com asterisco (*) sao obrigatorios.</p>

            <div>
                <label for="nome">Nome da patota *</label>
                <input id="nome" name="nome" type="text" class="input" required maxlength="120"
                       value="{{ old('nome') }}" aria-required="true"
                       @error('nome') aria-invalid="true" aria-describedby="nome-erro" @enderror>
                @error('nome')<p id="nome-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="descricao">Descricao</label>
                <textarea id="descricao" name="descricao" class="textarea" rows="3" maxlength="500">{{ old('descricao') }}</textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="cidade">Cidade</label>
                    <input id="cidade" name="cidade" type="text" class="input" maxlength="80" value="{{ old('cidade') }}">
                </div>
                <div>
                    <label for="estado">Estado (UF)</label>
                    <input id="estado" name="estado" type="text" class="input" maxlength="2" minlength="2" value="{{ old('estado') }}">
                </div>
            </div>

            <fieldset class="border rounded-lg p-4">
                <legend class="px-2 font-medium">Configuracao do jogo</legend>
                <div class="grid sm:grid-cols-2 gap-4 mt-2">
                    <div>
                        <label for="jogadores_por_time">Jogadores por time *</label>
                        <input id="jogadores_por_time" name="jogadores_por_time" type="number" class="input" required min="3" max="11" value="{{ old('jogadores_por_time', 5) }}">
                    </div>
                    <div>
                        <label for="quantidade_times">Quantidade de times *</label>
                        <input id="quantidade_times" name="quantidade_times" type="number" class="input" required min="2" max="6" value="{{ old('quantidade_times', 2) }}">
                    </div>
                    <div>
                        <label for="valor_mensalidade">Mensalidade (R$)</label>
                        <input id="valor_mensalidade" name="valor_mensalidade" type="number" class="input" step="0.01" min="0" value="{{ old('valor_mensalidade', 0) }}" inputmode="decimal">
                    </div>
                    <div class="flex items-center mt-7">
                        <input id="publica" name="publica" type="checkbox" value="1" class="h-5 w-5" {{ old('publica') ? 'checked' : '' }}>
                        <label for="publica" class="ms-2 mb-0">Patota publica (aparece em busca)</label>
                    </div>
                </div>
            </fieldset>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('patotas.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Criar patota</button>
            </div>
        </form>
    </div>
</x-app-layout>
