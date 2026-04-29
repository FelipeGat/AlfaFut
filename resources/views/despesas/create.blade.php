<x-app-layout>
    <x-slot name="title">Nova despesa</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">{{ $patota->nome }}</p>
        <h1 class="font-bold text-2xl">Nova despesa</h1>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('patotas.despesas.store', $patota) }}" class="card space-y-6" aria-describedby="form-help">
            @csrf
            <p id="form-help" class="text-sm text-gray-700">Os campos marcados com asterisco (*) sao obrigatorios.</p>

            <div>
                <label for="descricao">Descricao *</label>
                <input id="descricao" name="descricao" type="text" class="input" required maxlength="160" value="{{ old('descricao') }}">
                @error('descricao')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="categoria">Categoria *</label>
                    <select id="categoria" name="categoria" class="select" required>
                        <option value="locacao" @selected(old('categoria') === 'locacao')>Locacao do campo</option>
                        <option value="arbitragem" @selected(old('categoria') === 'arbitragem')>Arbitragem</option>
                        <option value="material" @selected(old('categoria') === 'material')>Material (bolas, coletes)</option>
                        <option value="alimentacao" @selected(old('categoria') === 'alimentacao')>Alimentacao</option>
                        <option value="outro" @selected(old('categoria') === 'outro')>Outro</option>
                    </select>
                </div>
                <div>
                    <label for="data_despesa">Data *</label>
                    <input id="data_despesa" name="data_despesa" type="date" class="input" required value="{{ old('data_despesa', now()->toDateString()) }}">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="valor_total">Valor total (R$) *</label>
                    <input id="valor_total" name="valor_total" type="number" class="input" required step="0.01" min="0.01" inputmode="decimal" value="{{ old('valor_total') }}">
                </div>
                <div>
                    <label for="partida_id">Partida (opcional)</label>
                    <select id="partida_id" name="partida_id" class="select">
                        <option value="">Sem partida vinculada</option>
                        @foreach ($partidas as $p)
                            <option value="{{ $p->id }}" @selected(old('partida_id') == $p->id)>
                                {{ $p->titulo }} ({{ $p->data_hora->translatedFormat('d/m H:i') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <fieldset class="border rounded-lg p-4">
                <legend class="px-2 font-medium">Rateio</legend>
                <div class="flex items-start gap-3">
                    <input id="rateada" name="rateada" type="checkbox" value="1" class="h-5 w-5 mt-1" {{ old('rateada') ? 'checked' : '' }}>
                    <label for="rateada" class="mb-0">
                        Ratear entre os confirmados da partida
                        <span class="block text-sm text-gray-600 font-normal mt-1">
                            O sistema cria automaticamente um pagamento individual para cada confirmado, dividindo o valor.
                        </span>
                    </label>
                </div>
            </fieldset>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('patotas.despesas.index', $patota) }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Cadastrar despesa</button>
            </div>
        </form>
    </div>
</x-app-layout>
