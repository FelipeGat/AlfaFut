<x-app-layout>
    <x-slot name="title">Partidas</x-slot>
    <x-slot name="header">
        <h1 class="font-bold text-2xl">Partidas</h1>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex gap-2" role="tablist" aria-label="Filtro de partidas">
            <a role="tab"
               aria-selected="{{ $filtro === 'proximas' ? 'true' : 'false' }}"
               href="{{ route('partidas.index', ['filtro' => 'proximas']) }}"
               class="px-4 py-2 rounded-full text-sm font-medium {{ $filtro === 'proximas' ? 'bg-primary text-on-primary' : 'bg-white border' }}">
                Proximas
            </a>
            <a role="tab"
               aria-selected="{{ $filtro === 'passadas' ? 'true' : 'false' }}"
               href="{{ route('partidas.index', ['filtro' => 'passadas']) }}"
               class="px-4 py-2 rounded-full text-sm font-medium {{ $filtro === 'passadas' ? 'bg-primary text-on-primary' : 'bg-white border' }}">
                Passadas
            </a>
        </div>

        @if ($partidas->isEmpty())
            <div class="card text-center text-gray-700">Nenhuma partida {{ $filtro === 'proximas' ? 'proxima' : 'passada' }}.</div>
        @else
            <ul class="space-y-3" role="list">
                @foreach ($partidas as $partida)
                    @php
                        $minha = $partida->confirmacoes->firstWhere('user_id', auth()->id());
                    @endphp
                    <li>
                        <a href="{{ route('partidas.show', $partida) }}" class="card block hover:shadow-md focus:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <p class="font-medium text-lg">{{ $partida->titulo }}</p>
                                    <p class="text-sm text-gray-700">
                                        {{ $partida->patota->nome }} &middot;
                                        <time datetime="{{ $partida->data_hora->toIso8601String() }}">
                                            {{ $partida->data_hora->translatedFormat('D, d \d\e M \a\s H:i') }}
                                        </time>
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $partida->local?->nome ?? 'Local a definir' }}</p>
                                </div>
                                <div class="text-right space-y-1">
                                    <span class="inline-block px-2 py-1 rounded-full bg-gray-100 text-xs">{{ $partida->vagasDisponiveis() }}/{{ $partida->vagas_total }} vagas</span>
                                    @if ($minha)
                                        @if ($minha->em_lista_espera)
                                            <span class="block text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-900">Lista de espera</span>
                                        @elseif ($minha->status === 'confirmado')
                                            <span class="block text-xs px-2 py-1 rounded-full bg-green-100 text-green-900">Voce confirmou</span>
                                        @elseif ($minha->status === 'recusado')
                                            <span class="block text-xs px-2 py-1 rounded-full bg-red-100 text-red-900">Voce recusou</span>
                                        @endif
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
