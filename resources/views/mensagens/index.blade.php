<x-app-layout>
    <x-slot name="title">Mural - {{ $patota->nome }}</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">{{ $patota->nome }}</p>
        <h1 class="font-bold text-xl sm:text-2xl">Mural da turma</h1>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <section aria-labelledby="nova-mensagem-h" class="card">
            <h2 id="nova-mensagem-h" class="text-lg">Nova mensagem</h2>
            <form method="POST" action="{{ route('patotas.mensagens.store', $patota) }}" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label for="conteudo" class="sr-only">Conteudo da mensagem</label>
                    <textarea id="conteudo"
                              name="conteudo"
                              class="textarea"
                              rows="3"
                              maxlength="1000"
                              required
                              placeholder="Escreva uma mensagem para o time..."
                              aria-required="true">{{ old('conteudo') }}</textarea>
                    @error('conteudo')<p class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <label for="tipo" class="mb-0 text-sm">Tipo:</label>
                        <select id="tipo" name="tipo" class="select" style="width:auto">
                            <option value="texto">Mensagem</option>
                            <option value="aviso">Aviso</option>
                            <option value="enquete">Enquete</option>
                        </select>
                        @if ($patota->criador_id === auth()->id())
                            <label class="ms-3 mb-0 text-sm flex items-center gap-1">
                                <input type="checkbox" name="fixada" value="1" class="h-4 w-4">
                                Fixar no topo
                            </label>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">Publicar</button>
                </div>
            </form>
        </section>

        <section aria-labelledby="mural-h">
            <h2 id="mural-h" class="sr-only">Lista de mensagens</h2>
            @if ($mensagens->isEmpty())
                <div class="card text-center text-gray-700">Nenhuma mensagem ainda. Seja o primeiro a postar!</div>
            @else
                <ul class="space-y-3" role="list">
                    @foreach ($mensagens as $m)
                        <li>
                            <article class="card {{ $m->fixada ? 'border-l-4 border-yellow-500' : '' }}" aria-labelledby="msg-{{ $m->id }}">
                                <header class="flex items-start justify-between gap-3 mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary text-on-primary font-bold" aria-hidden="true">
                                            {{ strtoupper(substr($m->autor->apelido ?? $m->autor->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <p id="msg-{{ $m->id }}" class="font-medium text-sm">{{ $m->autor->apelido ?? $m->autor->name }}</p>
                                            <p class="text-xs text-gray-600">
                                                <time datetime="{{ $m->created_at->toIso8601String() }}">{{ $m->created_at->diffForHumans() }}</time>
                                                @if ($m->fixada)
                                                    &middot; <span class="text-yellow-700" aria-label="Mensagem fixada no topo">📌 fixada</span>
                                                @endif
                                                @if ($m->tipo !== 'texto')
                                                    &middot; <span class="capitalize">{{ $m->tipo }}</span>
                                                @endif
                                                @if ($m->partida)
                                                    &middot; <a href="{{ route('partidas.show', $m->partida) }}" class="text-primary underline">{{ $m->partida->titulo }}</a>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @if ($m->autor_id === auth()->id() || $patota->criador_id === auth()->id())
                                        <form method="POST" action="{{ route('patotas.mensagens.destroy', [$patota, $m]) }}"
                                              onsubmit="return confirm('Remover esta mensagem?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-700 text-sm" aria-label="Remover mensagem de {{ $m->autor->apelido ?? $m->autor->name }}">Remover</button>
                                        </form>
                                    @endif
                                </header>
                                <p class="text-base whitespace-pre-wrap">{{ $m->conteudo }}</p>
                            </article>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-app-layout>
