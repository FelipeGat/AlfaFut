<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrarGolRequest;
use App\Models\Partida;
use App\Services\PlacarDaPartida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlacarController extends Controller
{
    public function dados(Partida $partida): JsonResponse
    {
        $this->authorize('view', $partida);

        $partida->loadMissing(['times.jogadores', 'eventos.jogador', 'eventos.time']);

        return response()->json([
            'data' => [
                'placar_a' => $partida->placar_a,
                'placar_b' => $partida->placar_b,
                'tempo_segundos' => $partida->tempoAtualSegundos(),
                'tempo_formatado' => $partida->tempoFormatado(),
                'em_andamento' => $partida->emAndamento(),
                'pausada' => $partida->pausada(),
                'finalizada' => $partida->finalizada(),
                'iniciada_em' => $partida->iniciada_em?->toIso8601String(),
                'times' => $partida->times->map(fn ($t) => [
                    'id' => $t->id,
                    'nome' => $t->nome,
                    'cor' => $t->cor,
                    'gols' => $t->gols,
                    'brasao' => $t->brasao,
                    'jogadores' => $t->jogadores->map(fn ($j) => [
                        'id' => $j->id,
                        'nome' => $j->apelido ?: $j->name,
                        'posicao' => $j->pivot->posicao,
                        'gols' => $j->pivot->gols,
                    ])->values(),
                ])->values(),
                'eventos' => $partida->eventos->map(fn ($e) => [
                    'id' => $e->id,
                    'tipo' => $e->tipo,
                    'minuto' => $e->minuto,
                    'time_id' => $e->time_id,
                    'time_nome' => $e->time?->nome,
                    'jogador_id' => $e->jogador_id,
                    'jogador_nome' => $e->jogador?->apelido ?? $e->jogador?->name,
                    'criado_em' => $e->created_at?->toIso8601String(),
                ])->values(),
            ],
        ]);
    }

    public function iniciar(Request $request, Partida $partida, PlacarDaPartida $placar): JsonResponse
    {
        $this->authorize('controlar', $partida);

        return response()->json(['mensagem' => $placar->iniciar($partida, $request->user())]);
    }

    public function pausar(Request $request, Partida $partida, PlacarDaPartida $placar): JsonResponse
    {
        $this->authorize('controlar', $partida);

        return response()->json(['mensagem' => $placar->pausar($partida, $request->user())]);
    }

    public function finalizar(Request $request, Partida $partida, PlacarDaPartida $placar): JsonResponse
    {
        $this->authorize('controlar', $partida);

        return response()->json(['mensagem' => $placar->finalizar($partida, $request->user())]);
    }

    public function gol(RegistrarGolRequest $request, Partida $partida, PlacarDaPartida $placar): JsonResponse
    {
        return response()->json(['mensagem' => $placar->registrarGol($partida, $request->user(), $request->validated())]);
    }
}
