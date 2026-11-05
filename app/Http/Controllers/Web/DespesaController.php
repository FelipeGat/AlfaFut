<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DarBaixaRequest;
use App\Http\Requests\SalvarDespesaRequest;
use App\Models\Despesa;
use App\Models\Pagamento;
use App\Models\Patota;
use App\Services\FinanceiroDaTurma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(SalvarDespesaRequest $request, Patota $patota, FinanceiroDaTurma $financeiro): RedirectResponse
    {
        $despesa = $financeiro->criar($patota, $request->user(), $request->validated());

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
        $podeGerir = $request->user()->can('update', $despesa);
        $movimentos = $podeGerir ? $despesa->movimentos()->with('user')->latest('id')->get() : collect();

        return view('despesas.show', compact('patota', 'despesa', 'pagamentos', 'podeGerir', 'movimentos'));
    }

    public function destroy(Request $request, Patota $patota, Despesa $despesa, FinanceiroDaTurma $financeiro): RedirectResponse
    {
        $this->autorizarGestao($patota, $despesa);

        $financeiro->remover($despesa, $request->user());

        return redirect()->route('patotas.despesas.index', $patota)->with('status', 'Despesa removida.');
    }

    public function refazerRateio(Request $request, Patota $patota, Despesa $despesa, FinanceiroDaTurma $financeiro): RedirectResponse
    {
        $this->autorizarGestao($patota, $despesa);

        $financeiro->refazerRateio($despesa, $request->user());

        return back()->with('status', 'Rateio refeito com os confirmados atuais.');
    }

    public function marcarPaga(Request $request, Patota $patota, Despesa $despesa, FinanceiroDaTurma $financeiro): RedirectResponse
    {
        $this->autorizarGestao($patota, $despesa);

        $financeiro->marcarComoPaga($despesa, $request->user());

        return back()->with('status', 'Despesa marcada como paga.');
    }

    public function cancelar(Request $request, Patota $patota, Despesa $despesa, FinanceiroDaTurma $financeiro): RedirectResponse
    {
        $this->autorizarGestao($patota, $despesa);

        $financeiro->cancelar($despesa, $request->user());

        return back()->with('status', 'Despesa cancelada.');
    }

    public function pagar(DarBaixaRequest $request, Patota $patota, Pagamento $pagamento, FinanceiroDaTurma $financeiro): RedirectResponse
    {
        abort_unless($pagamento->despesa->patota_id === $patota->id, 404);

        $financeiro->darBaixa($pagamento, $request->user(), $request->validated('valor_pago'), $request->validated('forma_pagamento'));

        return back()->with('status', 'Pagamento registrado.');
    }

    private function autorizarGestao(Patota $patota, Despesa $despesa): void
    {
        $this->authorize('organizar', $patota);
        abort_unless($despesa->patota_id === $patota->id, 404);
    }
}
