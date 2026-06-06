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
        $this->autorizaDonoCampo($request);

        $campos = Campo::where('dono_id', $request->user()->id)
            ->orderBy('nome')
            ->get();

        return view('campos.meus_index', compact('campos'));
    }

    public function create(Request $request): View
    {
        $this->autorizaDonoCampo($request);

        return view('campos.create', [
            'tiposPiso' => Campo::tiposPiso(),
        ]);
    }

    private function autorizaDonoCampo(Request $request): void
    {
        abort_unless(
            in_array($request->user()->tipo_usuario, ['dono_campo', 'admin'])
                || $request->user()->isAdmin(),
            403,
            'Apenas donos de campo podem acessar esta area.'
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validar($request);
        $campo = Campo::create([
            ...$dados,
            'dono_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('meus-campos.index')
            ->with('status', 'Campo cadastrado! Ele ja aparece no catalogo publico.');
    }

    public function show(Request $request, Campo $campo): View
    {
        // Visivel pra qualquer logado (catalogo publico)
        return view('campos.show', compact('campo'));
    }

    public function edit(Request $request, Campo $campo): View
    {
        abort_unless($campo->dono_id === $request->user()->id, 403);
        return view('campos.edit', [
            'campo' => $campo,
            'tiposPiso' => Campo::tiposPiso(),
        ]);
    }

    public function update(Request $request, Campo $campo): RedirectResponse
    {
        abort_unless($campo->dono_id === $request->user()->id, 403);
        $campo->update($this->validar($request));

        return redirect()
            ->route('meus-campos.index')
            ->with('status', 'Campo atualizado.');
    }

    public function destroy(Request $request, Campo $campo): RedirectResponse
    {
        abort_unless($campo->dono_id === $request->user()->id, 403);
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
            ->when($busca, fn($q) => $q->where('nome', 'like', "%{$busca}%"))
            ->when($cidade, fn($q) => $q->where('cidade', $cidade))
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
        return $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'endereco' => ['nullable', 'string', 'max:200'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'string', 'max:9'],
            'tipo_piso' => ['nullable', 'in:' . implode(',', array_keys(Campo::tiposPiso()))],
            'coberto' => ['nullable', 'boolean'],
            'possui_vestiario' => ['nullable', 'boolean'],
            'possui_estacionamento' => ['nullable', 'boolean'],
            'acessivel_cadeirante' => ['nullable', 'boolean'],
            'valor_hora' => ['nullable', 'numeric', 'min:0'],
            'contato_whatsapp' => ['nullable', 'string', 'max:20'],
            'descricao' => ['nullable', 'string'],
            'foto_url' => ['nullable', 'url', 'max:255'],
            'ativo' => ['nullable', 'boolean'],
        ]);
    }
}
