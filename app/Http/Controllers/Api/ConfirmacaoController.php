<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConfirmacaoResource;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfirmacaoController extends Controller
{
    public function confirmar(Request $request, Partida $partida): JsonResponse
    {
        $this->authorizeMembro($request, $partida);

        $dados = $request->validate([
            'observacao' => ['nullable', 'string', 'max:280'],
        ]);

        $emListaEspera = $partida->isCheia();

        $confirmacao = PartidaConfirmacao::updateOrCreate(
            [
                'partida_id' => $partida->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status' => 'confirmado',
                'em_lista_espera' => $emListaEspera,
                'posicao_lista_espera' => $emListaEspera
                    ? ($partida->listaEspera()->max('posicao_lista_espera') ?? 0) + 1
                    : null,
                'observacao' => $dados['observacao'] ?? null,
                'confirmado_em' => now(),
            ]
        );

        return response()->json([
            'mensagem' => $emListaEspera
                ? 'Voce entrou na lista de espera.'
                : 'Presenca confirmada!',
            'confirmacao' => new ConfirmacaoResource($confirmacao),
        ]);
    }

    public function recusar(Request $request, Partida $partida): JsonResponse
    {
        $this->authorizeMembro($request, $partida);

        PartidaConfirmacao::updateOrCreate(
            ['partida_id' => $partida->id, 'user_id' => $request->user()->id],
            ['status' => 'recusado', 'em_lista_espera' => false]
        );

        $this->promoverDaListaEspera($partida);

        return response()->json(['mensagem' => 'Presenca recusada.']);
    }

    public function cancelar(Request $request, Partida $partida): JsonResponse
    {
        $confirmacao = PartidaConfirmacao::where('partida_id', $partida->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $confirmacao->delete();

        $this->promoverDaListaEspera($partida);

        return response()->json(['mensagem' => 'Confirmacao cancelada.']);
    }

    private function promoverDaListaEspera(Partida $partida): void
    {
        if ($partida->vagasDisponiveis() === 0) {
            return;
        }

        $proximo = $partida->listaEspera()->orderBy('posicao_lista_espera')->first();
        $proximo?->update(['em_lista_espera' => false, 'posicao_lista_espera' => null]);
    }

    private function authorizeMembro(Request $request, Partida $partida): void
    {
        $eMembro = $partida->patota
            ->membrosAtivos()
            ->where('users.id', $request->user()->id)
            ->exists();
        abort_unless($eMembro, 403);
    }
}
