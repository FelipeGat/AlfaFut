<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConfirmacaoResource;
use App\Models\Partida;
use App\Services\VagasDaPartida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfirmacaoController extends Controller
{
    public function confirmar(Request $request, Partida $partida, VagasDaPartida $vagas): JsonResponse
    {
        $this->authorize('participar', $partida);

        $dados = $request->validate([
            'observacao' => ['nullable', 'string', 'max:280'],
        ]);

        $confirmacao = $vagas->confirmar($partida, $request->user(), $dados['observacao'] ?? null);

        return response()->json([
            'mensagem' => $confirmacao->em_lista_espera
                ? 'Você está na lista de espera.'
                : 'Presença confirmada!',
            'confirmacao' => new ConfirmacaoResource($confirmacao),
        ]);
    }

    public function recusar(Request $request, Partida $partida, VagasDaPartida $vagas): JsonResponse
    {
        $this->authorize('participar', $partida);

        $vagas->recusar($partida, $request->user());

        return response()->json(['mensagem' => 'Presença recusada.']);
    }

    public function cancelar(Request $request, Partida $partida, VagasDaPartida $vagas): JsonResponse
    {
        $this->authorize('participar', $partida);

        $vagas->cancelar($partida, $request->user());

        return response()->json(['mensagem' => 'Confirmação cancelada.']);
    }
}
