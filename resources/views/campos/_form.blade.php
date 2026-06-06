{{-- Form partial. Usa $campo (instancia ou null) e $tiposPiso (array). --}}
@php $c = $campo ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label for="nome">Nome do campo *</label>
        <input id="nome" name="nome" type="text" class="input" required maxlength="120"
               value="{{ old('nome', $c?->nome) }}" placeholder="Ex: Campo do Bairro">
        @error('nome')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label for="endereco">Endereco</label>
        <input id="endereco" name="endereco" type="text" class="input" maxlength="200"
               value="{{ old('endereco', $c?->endereco) }}" placeholder="Rua, numero, bairro">
    </div>

    <div>
        <label for="cidade">Cidade</label>
        <input id="cidade" name="cidade" type="text" class="input" maxlength="80"
               value="{{ old('cidade', $c?->cidade) }}">
    </div>

    <div>
        <label for="estado">Estado (UF)</label>
        <input id="estado" name="estado" type="text" class="input" maxlength="2"
               value="{{ old('estado', $c?->estado) }}" placeholder="SP" style="text-transform:uppercase">
    </div>

    <div>
        <label for="cep">CEP</label>
        <input id="cep" name="cep" type="text" class="input" maxlength="9"
               value="{{ old('cep', $c?->cep) }}" placeholder="00000-000">
    </div>

    <div>
        <label for="tipo_piso">Tipo de piso</label>
        <select id="tipo_piso" name="tipo_piso" class="input">
            <option value="">— escolher —</option>
            @foreach ($tiposPiso as $valor => $label)
                <option value="{{ $valor }}" {{ old('tipo_piso', $c?->tipo_piso) === $valor ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="valor_hora">Valor por hora (R$)</label>
        <input id="valor_hora" name="valor_hora" type="number" step="0.01" min="0" class="input"
               value="{{ old('valor_hora', $c?->valor_hora) }}" placeholder="80,00">
    </div>

    <div>
        <label for="contato_whatsapp">WhatsApp para contato</label>
        <input id="contato_whatsapp" name="contato_whatsapp" type="text" class="input" maxlength="20"
               value="{{ old('contato_whatsapp', $c?->contato_whatsapp) }}" placeholder="(11) 99999-9999">
    </div>

    <div class="md:col-span-2">
        <label for="foto_url">URL de uma foto do campo (opcional)</label>
        <input id="foto_url" name="foto_url" type="url" class="input" maxlength="255"
               value="{{ old('foto_url', $c?->foto_url) }}" placeholder="https://...">
    </div>

    <div class="md:col-span-2">
        <label for="descricao">Descricao</label>
        <textarea id="descricao" name="descricao" rows="3" class="input"
                  placeholder="Detalhes sobre o campo, regras, horarios...">{{ old('descricao', $c?->descricao) }}</textarea>
    </div>

    <fieldset class="md:col-span-2 border rounded p-3">
        <legend class="px-2 font-medium text-sm">Estrutura</legend>
        <div class="grid grid-cols-2 gap-2 text-sm">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="coberto" value="1" {{ old('coberto', $c?->coberto) ? 'checked' : '' }}> Coberto
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="possui_vestiario" value="1" {{ old('possui_vestiario', $c?->possui_vestiario) ? 'checked' : '' }}> Vestiario
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="possui_estacionamento" value="1" {{ old('possui_estacionamento', $c?->possui_estacionamento) ? 'checked' : '' }}> Estacionamento
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="acessivel_cadeirante" value="1" {{ old('acessivel_cadeirante', $c?->acessivel_cadeirante) ? 'checked' : '' }}> Acessivel a cadeirantes
            </label>
        </div>
    </fieldset>

    @if ($c)
        <label class="flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="ativo" value="1" {{ old('ativo', $c->ativo) ? 'checked' : '' }}>
            Campo ativo (aparece no catalogo publico)
        </label>
    @endif
</div>
