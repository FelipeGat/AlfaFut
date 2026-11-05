{{-- Usa $local (ou null) e $tiposPiso. --}}
<div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label for="nome">Nome *</label>
        <input id="nome" name="nome" type="text" class="input" required maxlength="120"
               value="{{ old('nome', $local?->nome) }}" placeholder="Quadra da Vila"
               @error('nome') aria-invalid="true" aria-describedby="nome-erro" @enderror>
        @error('nome')<p id="nome-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="endereco">Endereço</label>
        <input id="endereco" name="endereco" type="text" class="input" maxlength="200"
               value="{{ old('endereco', $local?->endereco) }}"
               @error('endereco') aria-invalid="true" aria-describedby="endereco-erro" @enderror>
        @error('endereco')<p id="endereco-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="cidade">Cidade</label>
        <input id="cidade" name="cidade" type="text" class="input" maxlength="80"
               value="{{ old('cidade', $local?->cidade) }}"
               @error('cidade') aria-invalid="true" aria-describedby="cidade-erro" @enderror>
        @error('cidade')<p id="cidade-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="estado">Estado (UF)</label>
        <input id="estado" name="estado" type="text" class="input uppercase" maxlength="2"
               value="{{ old('estado', $local?->estado) }}"
               @error('estado') aria-invalid="true" aria-describedby="estado-erro" @enderror>
        @error('estado')<p id="estado-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="cep">CEP</label>
        <input id="cep" name="cep" type="text" class="input" maxlength="10" inputmode="numeric"
               value="{{ old('cep', $local?->cep) }}"
               @error('cep') aria-invalid="true" aria-describedby="cep-erro" @enderror>
        @error('cep')<p id="cep-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="tipo_piso">Tipo de piso</label>
        <select id="tipo_piso" name="tipo_piso" class="select"
                @error('tipo_piso') aria-invalid="true" aria-describedby="tipo_piso-erro" @enderror>
            <option value="">Não informado</option>
            @foreach ($tiposPiso as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(old('tipo_piso', $local?->tipo_piso) === $valor)>{{ $rotulo }}</option>
            @endforeach
        </select>
        @error('tipo_piso')<p id="tipo_piso-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="valor_locacao">Valor da locação (R$)</label>
        <input id="valor_locacao" name="valor_locacao" type="number" class="input" step="0.01" min="0" inputmode="decimal"
               value="{{ old('valor_locacao', $local?->valor_locacao) }}"
               @error('valor_locacao') aria-invalid="true" aria-describedby="valor_locacao-erro" @enderror>
        @error('valor_locacao')<p id="valor_locacao-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="contato">Contato</label>
        <input id="contato" name="contato" type="text" class="input" maxlength="120"
               value="{{ old('contato', $local?->contato) }}"
               @error('contato') aria-invalid="true" aria-describedby="contato-erro" @enderror>
        @error('contato')<p id="contato-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <fieldset class="sm:col-span-2 border rounded-lg p-4">
        <legend class="px-2 font-medium">O local tem</legend>
        <div class="grid sm:grid-cols-2 gap-2 mt-1">
            @foreach ([
                'coberto' => 'Cobertura',
                'possui_vestiario' => 'Vestiário',
                'possui_estacionamento' => 'Estacionamento',
                'acessivel_cadeirante' => 'Acesso para cadeirantes',
            ] as $caixa => $rotulo)
                <div class="flex items-center">
                    <input id="{{ $caixa }}" name="{{ $caixa }}" type="checkbox" value="1" class="h-5 w-5"
                           @checked($errors->any() ? old($caixa) : $local?->{$caixa})>
                    <label for="{{ $caixa }}" class="ms-2 mb-0">{{ $rotulo }}</label>
                </div>
            @endforeach
        </div>
    </fieldset>

    <div class="sm:col-span-2">
        <label for="observacoes">Observações</label>
        <textarea id="observacoes" name="observacoes" class="textarea" rows="3" maxlength="2000"
                  @error('observacoes') aria-invalid="true" aria-describedby="observacoes-erro" @enderror>{{ old('observacoes', $local?->observacoes) }}</textarea>
        @error('observacoes')<p id="observacoes-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>
</div>
