<x-app-layout>
    <x-slot name="title">Editar campo</x-slot>
    <x-slot name="header">
        <h1 class="font-bold text-xl sm:text-2xl">Editar campo</h1>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="card">
            <form method="POST" action="{{ route('meus-campos.update', $campo) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('campos._form', ['campo' => $campo, 'tiposPiso' => $tiposPiso])

                <div class="flex items-center justify-end gap-2 pt-2 border-t mt-4">
                    <a href="{{ route('meus-campos.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar alteracoes</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
