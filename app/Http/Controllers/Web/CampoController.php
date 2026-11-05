<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Campo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampoController extends Controller
{
    public function meusIndex(Request $request): View
    {
        $this->authorize('create', Campo::class);

        $campos = Campo::where('dono_id', $request->user()->id)
            ->orderBy('nome')
            ->get();

        return view('campos.meus_index', compact('campos'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Campo::class);

        return view('campos.create', [
            'tiposPiso' => Campo::tiposPiso(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Campo::class);

        Campo::create([
            ...$this->validar($request),
            'ativo' => true,
            'dono_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('meus-campos.index')
            ->with('status', 'Campo cadastrado. Ele já aparece no catálogo.');
    }

    public function show(Request $request, Campo $campo): View
    {
        $this->authorize('view', $campo);

        return view('campos.show', compact('campo'));
    }

    public function edit(Request $request, Campo $campo): View
    {
        $this->authorize('update', $campo);

        return view('campos.edit', [
            'campo' => $campo,
            'tiposPiso' => Campo::tiposPiso(),
        ]);
    }

    public function update(Request $request, Campo $campo): RedirectResponse
    {
        $this->authorize('update', $campo);

        $campo->update([
            ...$this->validar($request),
            'ativo' => $request->boolean('ativo'),
        ]);

        return redirect()
            ->route('meus-campos.index')
            ->with('status', 'Campo atualizado.');
    }

    public function destroy(Request $request, Campo $campo): RedirectResponse
    {
        $this->authorize('delete', $campo);

        $campo->delete();

        return redirect()
            ->route('meus-campos.index')
            ->with('status', 'Campo removido.');
    }

    public function catalogo(Request $request): View
    {
        $busca = $request->query('q');
        $cidade = $request->query('cidade');

        $campos = Campo::ativos()
            ->when($busca, fn ($q) => $q->where('nome', 'like', "%{$busca}%"))
            ->when($cidade, fn ($q) => $q->where('cidade', $cidade))
            ->with('dono:id,name')
            ->orderBy('cidade')
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        $cidades = Campo::ativos()
            ->whereNotNull('cidade')
            ->distinct()
            ->orderBy('cidade')
            ->pluck('cidade');

        return view('campos.catalogo', compact('campos', 'cidades', 'busca', 'cidade'));
    }

    private function validar(Request $request): array
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'endereco' => ['nullable', 'string', 'max:200'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:9'],
            'tipo_piso' => ['nullable', 'in:'.implode(',', array_keys(Campo::tiposPiso()))],
            'valor_hora' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'contato_whatsapp' => ['nullable', 'string', 'max:20'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'foto_url' => ['nullable', 'url', 'max:255'],
        ]);

        // Caixa desmarcada não vem no formulário.
        foreach (['coberto', 'possui_vestiario', 'possui_estacionamento', 'acessivel_cadeirante'] as $caixa) {
            $dados[$caixa] = $request->boolean($caixa);
        }

        return $dados;
    }
}
