<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\RegraDaTurma;
use App\Http\Controllers\Controller;
use App\Models\Campo;
use App\Models\Local;
use App\Models\Patota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LocalController extends Controller
{
    public function index(Patota $patota): View
    {
        $this->authorize('organizar', $patota);

        $locais = $patota->locais()->orderBy('nome')->get();

        return view('locais.index', compact('patota', 'locais'));
    }

    public function create(Patota $patota): View
    {
        $this->authorize('organizar', $patota);

        return view('locais.create', [
            'patota' => $patota,
            'local' => null,
            'tiposPiso' => Campo::tiposPiso(),
        ]);
    }

    public function store(Request $request, Patota $patota): RedirectResponse
    {
        $this->authorize('organizar', $patota);

        $patota->locais()->create($this->validar($request));

        return redirect()->route('patotas.locais.index', $patota)->with('status', 'Local cadastrado.');
    }

    public function edit(Patota $patota, Local $local): View
    {
        $this->autorizar($patota, $local);

        return view('locais.edit', [
            'patota' => $patota,
            'local' => $local,
            'tiposPiso' => Campo::tiposPiso(),
        ]);
    }

    public function update(Request $request, Patota $patota, Local $local): RedirectResponse
    {
        $this->autorizar($patota, $local);

        $local->update($this->validar($request));

        return redirect()->route('patotas.locais.index', $patota)->with('status', 'Local alterado.');
    }

    public function destroy(Patota $patota, Local $local): RedirectResponse
    {
        $this->autorizar($patota, $local);

        $emUso = $local->partidas()->proximas()->whereNot('status', 'cancelada')->count();

        if ($emUso > 0) {
            throw new RegraDaTurma("Este local está em {$emUso} partida(s) marcada(s). Troque o local dessas partidas antes de remover.");
        }

        $local->delete();

        return redirect()->route('patotas.locais.index', $patota)->with('status', 'Local removido.');
    }

    private function autorizar(Patota $patota, Local $local): void
    {
        $this->authorize('organizar', $patota);
        abort_unless($local->patota_id === $patota->id, 404);
    }

    private function validar(Request $request): array
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'endereco' => ['nullable', 'string', 'max:200'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:10'],
            'tipo_piso' => ['nullable', Rule::in(array_keys(Campo::tiposPiso()))],
            'valor_locacao' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'contato' => ['nullable', 'string', 'max:120'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        // Caixa desmarcada não vem no formulário.
        foreach (['coberto', 'possui_vestiario', 'possui_estacionamento', 'acessivel_cadeirante'] as $caixa) {
            $dados[$caixa] = $request->boolean($caixa);
        }

        return $dados;
    }
}
