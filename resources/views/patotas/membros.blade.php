<x-app-layout>
    <x-slot name="title">Membros de {{ $patota->nome }}</x-slot>
    <x-slot name="header">
        <p class="opacity-90 text-sm">
            <a href="{{ route('patotas.show', $patota) }}" class="underline">{{ $patota->nome }}</a>
        </p>
        <h1 class="font-bold text-xl sm:text-2xl mt-1">Membros ({{ $ativos->count() }})</h1>
    </x-slot>

    @php
        $podeGerenciar = auth()->user()->can('gerenciarMembros', $patota);
    @endphp

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        @unless ($podeGerenciar)
            <p class="text-sm text-gray-700">Só quem criou a turma e o responsável mudam papéis e removem membros.</p>
        @endunless

        <ul class="grid md:grid-cols-2 gap-4" role="list">
            @foreach ($ativos as $membro)
                @php
                    $nome = $membro->apelido ?? $membro->name;
                    $eCriador = $patota->criador_id === $membro->id;
                    $eResponsavel = $patota->responsavel_id === $membro->id;
                    $eOrganizador = in_array($membro->pivot->papel, ['administrador', 'organizador'], true);
                @endphp
                <li class="card">
                    <h2 class="font-semibold text-lg">{{ $nome }}</h2>
                    <p class="text-sm text-gray-700">{{ $membro->name }}</p>
                    <p class="mt-2 flex flex-wrap gap-1 text-xs">
                        @if ($eCriador)
                            <span class="px-2 py-1 rounded-full bg-gray-100">Criador</span>
                        @endif
                        @if ($eResponsavel)
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-900">Responsável</span>
                        @endif
                        <span class="px-2 py-1 rounded-full bg-gray-100">{{ $eOrganizador ? 'Organizador' : 'Membro' }}</span>
                    </p>
                    <dl class="mt-3 text-sm space-y-1">
                        <div class="flex flex-wrap gap-x-2">
                            <dt class="text-gray-700">E-mail:</dt>
                            <dd class="break-all">{{ $membro->email }}</dd>
                        </div>
                        <div class="flex flex-wrap gap-x-2">
                            <dt class="text-gray-700">Telefone:</dt>
                            <dd>{{ $membro->telefone ?? 'Não informado' }}</dd>
                        </div>
                    </dl>

                    @if ($podeGerenciar)
                        <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="Ações para {{ $nome }}">
                            @unless ($eCriador || $eResponsavel)
                                <form method="POST" action="{{ route('patotas.membros.papel', [$patota, $membro]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="papel" value="{{ $eOrganizador ? 'membro' : 'organizador' }}">
                                    <button type="submit" class="btn btn-outline" aria-label="{{ $eOrganizador ? 'Voltar '.$nome.' a membro' : 'Tornar '.$nome.' organizador' }}">
                                        {{ $eOrganizador ? 'Voltar a membro' : 'Tornar organizador' }}
                                    </button>
                                </form>
                            @endunless
                            @unless ($eResponsavel)
                                <form method="POST" action="{{ route('patotas.membros.responsavel', [$patota, $membro]) }}"
                                      onsubmit="return confirm('Passar a responsabilidade da turma para esta pessoa?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline" aria-label="Tornar {{ $nome }} responsável">Tornar responsável</button>
                                </form>
                            @endunless
                            @unless ($eCriador || $membro->id === auth()->id())
                                <form method="POST" action="{{ route('patotas.membros.destroy', [$patota, $membro]) }}"
                                      onsubmit="return confirm('Remover esta pessoa da turma? Ela só volta depois que a volta for liberada.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" aria-label="Remover {{ $nome }}">Remover</button>
                                </form>
                            @endunless
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($removidos->isNotEmpty())
            <section aria-labelledby="removidos-h" class="card">
                <h2 id="removidos-h">Removidos ({{ $removidos->count() }})</h2>
                <p class="text-sm text-gray-700 mt-1">Estas pessoas só entram com o código depois que a volta for liberada.</p>
                <ul class="mt-3 divide-y" role="list">
                    @foreach ($removidos as $membro)
                        <li class="py-3 flex flex-wrap items-center justify-between gap-2">
                            <span>{{ $membro->name }}</span>
                            @if ($podeGerenciar)
                                <form method="POST" action="{{ route('patotas.membros.liberar', [$patota, $membro]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline" aria-label="Liberar a volta de {{ $membro->name }}">Liberar volta</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-app-layout>
