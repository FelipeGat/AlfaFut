<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DespesaResource;
use App\Models\Despesa;
use App\Models\Pagamento;
use App\Models\Partida;
use App\Models\Patota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DespesaController extends Controller
{
    public function index(Request $request, Patota $patota): AnonymousResourceCollection
    {
        return DespesaResource::collection(
            $patota->despesas()->orderByDesc('data_despesa')->get()
        );
    }

    public function store(Request $request, Patota $patota): JsonResponse
    {
        $dados = $request->validate([
            'partida_id' => ['nullable', 'exists:partidas,id'],
            'descricao' => ['required', 'string', 'max:160'],
            'categoria' => ['required', 'in:locacao,arbitragem,material,alimentacao,outro'],
            'valor_total' => ['required', 'numeric', 'min:0.01'],
            'data_despesa' => ['required', 'date'],
            'rateada' => ['nullable', 'boolean'],
        ]);

        $despesa = $patota->despesas()->create([
            ...$dados,
            'criada_por_id' => $request->user()->id,
            'status' => 'aberta',
        ]);

        if ($despesa->rateada && $despesa->partida_id) {
            $partida = Partida::with('confirmados')->find($despesa->partida_id);
            $confirmados = $partida->confirmados;
            $rateio = $confirmados->count() > 0
                ? round($despesa->valor_total / $confirmados->count(), 2)
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

        return response()->json(new DespesaResource($despesa), 201);
    }

    public function show(Despesa $despesa): JsonResponse
    {
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

    public function update(Request $request, Despesa $despesa): DespesaResource
    {
        $despesa->update($request->validate([
            'descricao' => ['sometimes', 'string', 'max:160'],
            'categoria' => ['sometimes', 'in:locacao,arbitragem,material,alimentacao,outro'],
            'valor_total' => ['sometimes', 'numeric', 'min:0.01'],
            'status' => ['sometimes', 'in:aberta,fechada,cancelada'],
        ]));

        return new DespesaResource($despesa);
    }

    public function destroy(Despesa $despesa): JsonResponse
    {
        $despesa->delete();

        return response()->json(['mensagem' => 'Despesa removida.']);
    }

    public function pagar(Request $request, Pagamento $pagamento): JsonResponse
    {
        abort_unless($pagamento->user_id === $request->user()->id, 403);

        $dados = $request->validate([
            'valor_pago' => ['required', 'numeric', 'min:0.01'],
            'forma_pagamento' => ['required', 'in:pix,dinheiro,transferencia,cartao'],
        ]);

        $pagamento->quitar((float) $dados['valor_pago'], $dados['forma_pagamento']);

        return response()->json(['mensagem' => 'Pagamento registrado.']);
    }
}
