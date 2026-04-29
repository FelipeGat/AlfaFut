<x-app-layout>
    <x-slot name="title">{{ $despesa->descricao }}</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">{{ $patota->nome }} &middot; despesas</p>
        <h1 class="font-bold text-2xl">{{ $despesa->descricao }}</h1>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-3 gap-6">
        <section aria-labelledby="resumo-h" class="card lg:col-span-1">
            <h2 id="resumo-h">Resumo</h2>
            <dl class="mt-3 space-y-3 text-sm">
                <div>
                    <dt class="text-gray-600">Valor total</dt>
                    <dd class="text-3xl font-bold">R$ {{ number_format($despesa->valor_total, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-600">Total pago</dt>
                    <dd class="font-semibold text-green-700">R$ {{ number_format($despesa->totalPago(), 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-600">Saldo aberto</dt>
                    <dd class="font-semibold text-yellow-700">R$ {{ number_format($despesa->saldoAberto(), 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-600">Categoria</dt>
                    <dd class="capitalize">{{ $despesa->categoria }}</dd>
                </div>
                <div>
                    <dt class="text-gray-600">Data</dt>
                    <dd>
                        <time datetime="{{ $despesa->data_despesa->toIso8601String() }}">
                            {{ $despesa->data_despesa->translatedFormat('d/m/Y') }}
                        </time>
                    </dd>
                </div>
                @if ($despesa->partida)
                    <div>
                        <dt class="text-gray-600">Partida vinculada</dt>
                        <dd>
                            <a href="{{ route('partidas.show', $despesa->partida) }}" class="text-primary underline">
                                {{ $despesa->partida->titulo }}
                            </a>
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="text-gray-600">Cadastrada por</dt>
                    <dd>{{ $despesa->criadaPor->apelido ?? $despesa->criadaPor->name }}</dd>
                </div>
            </dl>

            @if ($patota->criador_id === auth()->id())
                <form method="POST" action="{{ route('patotas.despesas.destroy', [$patota, $despesa]) }}"
                      onsubmit="return confirm('Remover esta despesa? Esta acao nao pode ser desfeita.');"
                      class="mt-6 pt-4 border-t">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Remover despesa</button>
                </form>
            @endif
        </section>

        <section aria-labelledby="pagamentos-h" class="card lg:col-span-2">
            <h2 id="pagamentos-h">Pagamentos ({{ $pagamentos->count() }})</h2>

            @if ($pagamentos->isEmpty())
                <p class="mt-3 text-gray-700 text-sm">Esta despesa nao foi rateada. Para rateio automatico, vincule uma partida ao criar.</p>
            @else
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm" aria-describedby="pagamentos-h">
                        <thead class="text-left">
                            <tr class="border-b">
                                <th scope="col" class="py-2 pr-2">Jogador</th>
                                <th scope="col" class="py-2 pr-2 text-right">Devido</th>
                                <th scope="col" class="py-2 pr-2 text-right">Pago</th>
                                <th scope="col" class="py-2 pr-2">Status</th>
                                <th scope="col" class="py-2 pr-2">Acao</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($pagamentos as $p)
                                <tr>
                                    <td class="py-3 pr-2">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-primary text-on-primary text-xs font-bold" aria-hidden="true">
                                                {{ strtoupper(substr($p->user->apelido ?? $p->user->name, 0, 1)) }}
                                            </span>
                                            {{ $p->user->apelido ?? $p->user->name }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-2 text-right font-mono">R$ {{ number_format($p->valor_devido, 2, ',', '.') }}</td>
                                    <td class="py-3 pr-2 text-right font-mono">R$ {{ number_format($p->valor_pago, 2, ',', '.') }}</td>
                                    <td class="py-3 pr-2">
                                        @switch($p->status)
                                            @case('pago')
                                                <span class="inline-block text-xs px-2 py-1 rounded-full bg-green-100 text-green-900">Pago</span>
                                                @break
                                            @case('parcial')
                                                <span class="inline-block text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-900">Parcial</span>
                                                @break
                                            @default
                                                <span class="inline-block text-xs px-2 py-1 rounded-full bg-gray-100">Pendente</span>
                                        @endswitch
                                    </td>
                                    <td class="py-3 pr-2">
                                        @if ($p->user_id === auth()->id() && $p->status !== 'pago')
                                            <details class="inline-block">
                                                <summary class="btn btn-primary text-xs cursor-pointer" style="min-height:36px;padding:6px 12px">Pagar</summary>
                                                <form method="POST" action="{{ route('patotas.pagamentos.quitar', [$patota, $p]) }}" class="absolute mt-2 z-10 bg-white p-3 border rounded-lg shadow-lg w-72">
                                                    @csrf
                                                    <label for="vp-{{ $p->id }}" class="text-xs">Valor pago (R$)</label>
                                                    <input id="vp-{{ $p->id }}" name="valor_pago" type="number" step="0.01" min="0.01" required class="input mb-2" value="{{ $p->valor_devido }}" inputmode="decimal">
                                                    <label for="fp-{{ $p->id }}" class="text-xs">Forma</label>
                                                    <select id="fp-{{ $p->id }}" name="forma_pagamento" required class="select mb-2">
                                                        <option value="pix">PIX</option>
                                                        <option value="dinheiro">Dinheiro</option>
                                                        <option value="transferencia">Transferencia</option>
                                                        <option value="cartao">Cartao</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary w-full">Confirmar pagamento</button>
                                                </form>
                                            </details>
                                        @elseif ($p->status === 'pago')
                                            <span class="text-xs text-gray-600">
                                                {{ $p->forma_pagamento ?? '-' }} em
                                                @if ($p->data_pagamento)
                                                    <time datetime="{{ $p->data_pagamento->toIso8601String() }}">{{ $p->data_pagamento->translatedFormat('d/m') }}</time>
                                                @else - @endif
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-600">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
