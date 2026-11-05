{{-- Usa $patota, $partida (ou null), $locais e $campos. --}}
@php
    $lugarAtual = $partida?->local_id
        ? 'local:'.$partida->local_id
        : ($partida?->campo_id ? 'campo:'.$partida->campo_id : '');
    $lugarEscolhido = old('lugar', $lugarAtual);
@endphp

<div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label for="titulo">Título *</label>
        <input id="titulo" name="titulo" type="text" class="input" required maxlength="120"
               value="{{ old('titulo', $partida?->titulo) }}" placeholder="Pelada de sábado"
               @error('titulo') aria-invalid="true" aria-describedby="titulo-erro" @enderror>
        @error('titulo')<p id="titulo-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="data_hora">Data e hora *</label>
        <input id="data_hora" name="data_hora" type="datetime-local" class="input" required
               value="{{ old('data_hora', $partida?->data_hora?->format('Y-m-d\TH:i')) }}"
               @error('data_hora') aria-invalid="true" aria-describedby="data_hora-erro" @enderror>
        @error('data_hora')<p id="data_hora-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="duracao_minutos">Duração (minutos)</label>
        <input id="duracao_minutos" name="duracao_minutos" type="number" class="input" min="30" max="240" inputmode="numeric"
               value="{{ old('duracao_minutos', $partida?->duracao_minutos ?? 90) }}"
               @error('duracao_minutos') aria-invalid="true" aria-describedby="duracao_minutos-erro" @enderror>
        @error('duracao_minutos')<p id="duracao_minutos-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="vagas_total">Vagas</label>
        <input id="vagas_total" name="vagas_total" type="number" class="input" min="4" max="200" inputmode="numeric"
               value="{{ old('vagas_total', $partida?->vagas_total ?? $patota->vagasPorPartida()) }}"
               aria-describedby="vagas_total-ajuda @error('vagas_total') vagas_total-erro @enderror"
               @error('vagas_total') aria-invalid="true" @enderror>
        <p id="vagas_total-ajuda" class="text-xs text-gray-600 mt-1">Sugestão pela turma: {{ $patota->jogadores_por_time }} jogadores × {{ $patota->quantidade_times }} times.</p>
        @error('vagas_total')<p id="vagas_total-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="valor_individual">Valor por jogador (R$)</label>
        <input id="valor_individual" name="valor_individual" type="number" class="input" step="0.01" min="0" max="99999.99" inputmode="decimal"
               value="{{ old('valor_individual', $partida?->valor_individual) }}"
               @error('valor_individual') aria-invalid="true" aria-describedby="valor_individual-erro" @enderror>
        @error('valor_individual')<p id="valor_individual-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="confirmacao_ate">Confirmar até</label>
        <input id="confirmacao_ate" name="confirmacao_ate" type="datetime-local" class="input"
               value="{{ old('confirmacao_ate', $partida?->confirmacao_ate?->format('Y-m-d\TH:i')) }}"
               @error('confirmacao_ate') aria-invalid="true" aria-describedby="confirmacao_ate-erro" @enderror>
        @error('confirmacao_ate')<p id="confirmacao_ate-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="lugar">Lugar</label>
        <select id="lugar" name="lugar" class="select"
                aria-describedby="lugar-ajuda @error('local_id') lugar-erro @enderror @error('campo_id') lugar-erro @enderror">
            <option value="">A definir</option>
            @if ($locais->isNotEmpty())
                <optgroup label="Locais da turma">
                    @foreach ($locais as $local)
                        <option value="local:{{ $local->id }}" @selected($lugarEscolhido === 'local:'.$local->id)>{{ $local->nome }}</option>
                    @endforeach
                </optgroup>
            @endif
            @if ($campos->isNotEmpty())
                <optgroup label="Campos do catálogo">
                    @foreach ($campos as $campo)
                        <option value="campo:{{ $campo->id }}" @selected($lugarEscolhido === 'campo:'.$campo->id)>{{ $campo->nome }}{{ $campo->cidade ? ' ('.$campo->cidade.')' : '' }}</option>
                    @endforeach
                </optgroup>
            @endif
        </select>
        <p id="lugar-ajuda" class="text-xs text-gray-600 mt-1">
            Não achou? <a href="{{ route('patotas.locais.create', $patota) }}" class="text-primary underline">Cadastre um local da turma</a> e depois volte aqui.
        </p>
        @error('local_id')<p id="lugar-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
        @error('campo_id')<p id="lugar-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" class="textarea" rows="3" maxlength="2000"
                  @error('descricao') aria-invalid="true" aria-describedby="descricao-erro" @enderror>{{ old('descricao', $partida?->descricao) }}</textarea>
        @error('descricao')<p id="descricao-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2 flex items-center">
        <input type="hidden" name="lista_espera_habilitada" value="0">
        <input id="lista_espera_habilitada" name="lista_espera_habilitada" type="checkbox" value="1" class="h-5 w-5"
               @checked(old('lista_espera_habilitada', $partida?->lista_espera_habilitada ?? true))>
        <label for="lista_espera_habilitada" class="ms-2 mb-0">Aceitar lista de espera quando as vagas acabarem</label>
    </div>
</div>
