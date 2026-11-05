<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DarBaixaRequest;
use App\Http\Requests\SalvarDespesaRequest;
use App\Http\Resources\DespesaResource;
use App\Models\Despesa;
use App\Models\Pagamento;
use App\Models\Patota;
use App\Services\FinanceiroDaTurma;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DespesaController extends Controller
{
    public function index(Request $request, Patota $patota): AnonymousResourceCollection
    {
        $this->authorize('view', $patota);

        return DespesaResource::collection(
            $patota->despesas()->orderByDesc('data_despesa')->get()
        );
    }

    public function store(SalvarDespesaRequest $request, Patota $patota, FinanceiroDaTurma $financeiro): JsonResponse
    {
        return response()->json(new DespesaResource($financeiro->criar($patota, $request->user(), $request->validated())), 201);
    }

    public function show(Despesa $despesa): JsonResponse
    {
        $this->authorize('view', $despesa);

        return response()->json([
            'despesa' => new DespesaResource($despesa),
            'pagamentos' => $despesa->pagamentos()->with('user')->get()->map(fn ($p) => [
                'id' => $p->id,
                'usuario' => $p->user->apelido ?: $p->user->name,
                'valor_devido' => (float) $p->valor_devido,
                'valor_pago' => (float) $p->valor_pago,
                'status' => $p->status,
                'data_pagamento' => $p->data_pagamento?->toDateString(),
            ]),
        ]);
    }

    public function update(SalvarDespesaRequest $request, Despesa $despesa, FinanceiroDaTurma $financeiro): DespesaResource
    {
        return new DespesaResource($financeiro->alterar($despesa, $request->user(), $request->validated()));
    }

    public function destroy(Request $request, Despesa $despesa, FinanceiroDaTurma $financeiro): JsonResponse
    {
        $this->authorize('delete', $despesa);

        $financeiro->remover($despesa, $request->user());

        return response()->json(['mensagem' => 'Despesa removida.']);
    }

    public function refazerRateio(Request $request, Despesa $despesa, FinanceiroDaTurma $financeiro): JsonResponse
    {
        $this->authorize('update', $despesa);

        $financeiro->refazerRateio($despesa, $request->user());

        return response()->json(['mensagem' => 'Rateio refeito com os confirmados atuais.']);
    }

    public function marcarPaga(Request $request, Despesa $despesa, FinanceiroDaTurma $financeiro): JsonResponse
    {
        $this->authorize('update', $despesa);

        $financeiro->marcarComoPaga($despesa, $request->user());

        return response()->json(['mensagem' => 'Despesa marcada como paga.']);
    }

    public function cancelar(Request $request, Despesa $despesa, FinanceiroDaTurma $financeiro): JsonResponse
    {
        $this->authorize('update', $despesa);

        $financeiro->cancelar($despesa, $request->user());

        return response()->json(['mensagem' => 'Despesa cancelada.']);
    }

    public function pagar(DarBaixaRequest $request, Pagamento $pagamento, FinanceiroDaTurma $financeiro): JsonResponse
    {
        $financeiro->darBaixa($pagamento, $request->user(), $request->validated('valor_pago'), $request->validated('forma_pagamento'));

        return response()->json(['mensagem' => 'Pagamento registrado.']);
    }
}
