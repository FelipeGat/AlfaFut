<x-app-layout>
    <x-slot name="title">{{ $campo->nome }}</x-slot>
    <x-slot name="header">
        <h1 class="font-bold text-xl sm:text-2xl">{{ $campo->nome }}</h1>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @if ($campo->foto_url)
            <img src="{{ $campo->foto_url }}" alt="Foto de {{ $campo->nome }}" class="w-full h-64 object-cover rounded-lg">
        @endif

        <div class="card">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="text-sm text-gray-600">Cadastrado por</p>
                    <p class="font-medium">{{ $campo->dono->nomeExibicao() }}</p>
                </div>
                @if ($campo->valor_hora)
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Valor</p>
                        <p class="font-bold text-lg">R$ {{ number_format($campo->valor_hora, 2, ',', '.') }} <span class="text-sm text-gray-600 font-normal">/ hora</span></p>
                    </div>
                @endif
            </div>

            <hr class="my-4">

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                @if ($campo->endereco)
                    <div><dt class="text-gray-600">Endereco</dt><dd>{{ $campo->endereco }}</dd></div>
                @endif
                @if ($campo->cidade)
                    <div><dt class="text-gray-600">Cidade</dt><dd>{{ $campo->cidade }}{{ $campo->estado ? '/' . $campo->estado : '' }}</dd></div>
                @endif
                @if ($campo->cep)
                    <div><dt class="text-gray-600">CEP</dt><dd>{{ $campo->cep }}</dd></div>
                @endif
                @if ($campo->tipo_piso)
                    <div><dt class="text-gray-600">Piso</dt><dd>{{ \App\Models\Campo::tiposPiso()[$campo->tipo_piso] ?? $campo->tipo_piso }}</dd></div>
                @endif
            </dl>

            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                @if ($campo->coberto)<span class="px-2 py-1 rounded-full bg-blue-100 text-blue-900">Coberto</span>@endif
                @if ($campo->possui_vestiario)<span class="px-2 py-1 rounded-full bg-gray-100">Vestiario</span>@endif
                @if ($campo->possui_estacionamento)<span class="px-2 py-1 rounded-full bg-gray-100">Estacionamento</span>@endif
                @if ($campo->acessivel_cadeirante)<span class="px-2 py-1 rounded-full bg-green-100 text-green-900">Acessivel</span>@endif
            </div>

            @if ($campo->descricao)
                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-1">Descricao</p>
                    <p class="whitespace-pre-line">{{ $campo->descricao }}</p>
                </div>
            @endif

            @if ($campo->contato_whatsapp)
                <div class="mt-6 pt-4 border-t">
                    <a href="https://wa.me/55{{ preg_replace('/\D/', '', $campo->contato_whatsapp) }}"
                       target="_blank" rel="noopener"
                       class="btn btn-primary inline-flex items-center gap-2">
                        Falar no WhatsApp: {{ $campo->contato_whatsapp }}
                    </a>
                </div>
            @endif

            @if (auth()->id() === $campo->dono_id)
                <div class="mt-6 pt-4 border-t flex gap-2">
                    <a href="{{ route('meus-campos.edit', $campo) }}" class="btn btn-secondary">Editar</a>
                </div>
            @endif
        </div>

        <p>
            <a href="{{ route('campos.catalogo') }}" class="underline text-primary">&larr; Voltar ao catalogo</a>
        </p>
    </div>
</x-app-layout>
