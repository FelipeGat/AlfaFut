<x-app-layout>
    <x-slot name="title">Despesas - {{ $patota->nome }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="opacity-90 text-sm">{{ $patota->nome }}</p>
                <h1 class="font-bold text-2xl">Despesas</h1>
            </div>
            <a href="{{ route('patotas.despesas.create', $patota) }}" class="btn btn-secondary">+ Nova despesa</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($despesas->isEmpty())
            <div class="card text-center text-gray-700">Nenhuma despesa cadastrada.</div>
        @else
            <ul class="space-y-3" role="list">
                @foreach ($despesas as $d)
                    <li>
                        <a href="{{ route('patotas.despesas.show', [$patota, $d]) }}" class="card block hover:shadow-md focus:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <p class="font-medium text-lg">{{ $d->descricao }}</p>
                                    <p class="text-sm text-gray-700 capitalize">
                                        {{ $d->categoria }} &middot;
                                        <time datetime="{{ $d->data_despesa->toIso8601String() }}">
                                            {{ $d->data_despesa->translatedFormat('d/m/Y') }}
                                        </time>
                                        @if ($d->partida)
                                            &middot; <span>partida: {{ \Illuminate\Support\Str::limit($d->partida->titulo, 30) }}</span>
                                        @endif
                                    </p>
                                    @if ($d->rateada)
                                        <p class="text-xs text-gray-600 mt-1">
                                            Rateada entre {{ $d->pagamentos_count }} pessoas &middot;
                                            Pago: <strong>R$ {{ number_format($d->totalPago(), 2, ',', '.') }}</strong> de R$ {{ number_format($d->valor_total, 2, ',', '.') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold">R$ {{ number_format($d->valor_total, 2, ',', '.') }}</p>
                                    @php $aberto = $d->saldoAberto(); @endphp
                                    @if ($aberto > 0)
                                        <span class="inline-block text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-900">aberto: R$ {{ number_format($aberto, 2, ',', '.') }}</span>
                                    @else
                                        <span class="inline-block text-xs px-2 py-1 rounded-full bg-green-100 text-green-900">quitada</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
