<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Despesa;
use App\Models\Pagamento;
use App\Models\Patota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DespesaController extends Controller
{
    public function index(Request $request, Patota $patota): View
    {
        $this->authorize('view', $patota);

        $despesas = $patota->despesas()
            ->with('partida', 'criadaPor')
            ->withCount('pagamentos')
            ->orderByDesc('data_despesa')
            ->get();

        return view('despesas.index', compact('patota', 'despesas'));
    }

    public function create(Request $request, Patota $patota): View
    {
        $this->authorize('organizar', $patota);

        $partidas = $patota->partidas()->orderByDesc('data_hora')->limit(20)->get();

        return view('despesas.create', compact('patota', 'partidas'));
    }

    public function store(Request $request, Patota $patota): RedirectResponse
    {
        $this->authorize('organizar', $patota);

        $dados = $request->validate([
            'partida_id' => ['nullable', Rule::exists('partidas', 'id')->where('patota_id', $patota->id)],
            'descricao' => ['required', 'string', 'max:160'],
            'categoria' => ['required', 'in:locacao,arbitragem,material,alimentacao,outro'],
            'valor_total' => ['required', 'numeric', 'min:0.01'],
            'data_despesa' => ['required', 'date'],
            'rateada' => ['nullable', 'boolean'],
        ]);

        $despesa = $patota->despesas()->create([
            ...$dados,
            'criada_por_id' => $request->user()->id,
            'rateada' => (bool) ($dados['rateada'] ?? false),
            'status' => 'aberta',
        ]);

        if ($despesa->rateada && $despesa->partida_id) {
            $partida = $patota->partidas()->with('confirmados')->find($despesa->partida_id);
            $confirmados = $partida->confirmados;
            $rateio = $confirmados->count() > 0
                ? round((float) $despesa->valor_total / $confirmados->count(), 2)
                : 0;

            foreach ($confirmados as $c) {
                Pagamento::create([
                    'despesa_id' => $despesa->id,
                    'user_id' => $c->user_id,
                    'valor_devido' => $rateio,
                    'data_vencimento' => $despesa->data_despesa,
                    'status' => 'pendente',
                ]);
            }
        }

        return redirect()
            ->route('patotas.despesas.show', [$patota, $despesa])
            ->with('status', 'Despesa cadastrada'.($despesa->rateada ? ' e rateada entre os confirmados.' : '.'));
    }

    public function show(Request $request, Patota $patota, Despesa $despesa): View
    {
        $this->authorize('view', $patota);
        abort_unless($despesa->patota_id === $patota->id, 404);

        $despesa->load(['partida', 'criadaPor']);
        $pagamentos = $despesa->pagamentos()->with('user')->get();

        return view('despesas.show', compact('patota', 'despesa', 'pagamentos'));
    }

    public function destroy(Request $request, Patota $patota, Despesa $despesa): RedirectResponse
    {
        $this->authorize('organizar', $patota);
        abort_unless($despesa->patota_id === $patota->id, 404);

        $despesa->delete();

        return redirect()->route('patotas.despesas.index', $patota)->with('status', 'Despesa removida.');
    }

    public function pagar(Request $request, Patota $patota, Pagamento $pagamento): RedirectResponse
    {
        $this->authorize('quitar', $pagamento);
        abort_unless($pagamento->despesa->patota_id === $patota->id, 404);

        $dados = $request->validate([
            'valor_pago' => ['required', 'numeric', 'min:0.01'],
            'forma_pagamento' => ['required', 'in:pix,dinheiro,transferencia,cartao'],
        ]);

        $pagamento->quitar((float) $dados['valor_pago'], $dados['forma_pagamento']);

        return back()->with('status', 'Pagamento registrado.');
    }
}
