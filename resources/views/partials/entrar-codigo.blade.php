<form method="POST" action="{{ route('patotas.entrar') }}" class="flex flex-wrap items-end gap-3">
    @csrf
    <div class="flex-1 min-w-[12rem]">
        <label for="codigo_convite">Código de convite</label>
        <input id="codigo_convite" name="codigo_convite" type="text" class="input font-mono uppercase" required maxlength="20"
               autocomplete="off" autocapitalize="characters" value="{{ old('codigo_convite') }}"
               @error('codigo_convite') aria-invalid="true" aria-describedby="codigo_convite-erro" @enderror>
        @error('codigo_convite')<p id="codigo_convite-erro" class="text-red-700 text-sm mt-1" role="alert">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="btn btn-primary">Entrar com código</button>
</form>
