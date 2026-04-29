<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventoPartida;
use App\Models\Partida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlacarController extends Controller
{
    public function dados(Partida $partida): JsonResponse
    {
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

    public function iniciar(Request $request, Partida $partida): JsonResponse
    {
        $this->authorizeResponsavel($request, $partida);

        if ($partida->finalizada()) {
            return response()->json(['mensagem' => 'Partida ja foi finalizada.'], 422);
        }

        if ($partida->pausada()) {
            $partida->update(['iniciada_em' => now(), 'pausada_em' => null]);
            $this->logEvento($partida, $request, EventoPartida::TIPO_RETOMADA);
            return response()->json(['mensagem' => 'Partida retomada.']);
        }

        $partida->update([
            'iniciada_em' => now(),
            'tempo_acumulado_segundos' => 0,
            'placar_a' => 0,
            'placar_b' => 0,
            'status' => 'em_andamento',
        ]);
        $this->logEvento($partida, $request, EventoPartida::TIPO_INICIO);

        return response()->json(['mensagem' => 'Partida iniciada!']);
    }

    public function pausar(Request $request, Partida $partida): JsonResponse
    {
        $this->authorizeResponsavel($request, $partida);

        if (! $partida->emAndamento()) {
            return response()->json(['mensagem' => 'Partida nao esta em andamento.'], 422);
        }

        $segundos = (int) abs($partida->iniciada_em->diffInSeconds(now()));
        $partida->update([
            'pausada_em' => now(),
            'tempo_acumulado_segundos' => $partida->tempo_acumulado_segundos + $segundos,
        ]);
        $this->logEvento($partida, $request, EventoPartida::TIPO_PAUSA);

        return response()->json(['mensagem' => 'Partida pausada.']);
    }

    public function finalizar(Request $request, Partida $partida): JsonResponse
    {
        $this->authorizeResponsavel($request, $partida);

        $partida->update([
            'finalizada_em' => now(),
            'tempo_acumulado_segundos' => $partida->tempoAtualSegundos(),
            'status' => 'finalizada',
        ]);
        $this->logEvento($partida, $request, EventoPartida::TIPO_FIM);

        return response()->json(['mensagem' => 'Partida encerrada!']);
    }

    public function gol(Request $request, Partida $partida): JsonResponse
    {
        $this->authorizeResponsavel($request, $partida);

        $dados = $request->validate([
            'time_id' => ['required', 'exists:times,id'],
            'jogador_id' => ['nullable', 'exists:users,id'],
            'tipo' => ['nullable', 'in:gol,gol_contra'],
        ]);

        $tipo = $dados['tipo'] ?? EventoPartida::TIPO_GOL;
        $time = $partida->times()->findOrFail($dados['time_id']);

        EventoPartida::create([
            'partida_id' => $partida->id,
            'time_id' => $time->id,
            'jogador_id' => $dados['jogador_id'] ?? null,
            'tipo' => $tipo,
            'minuto' => intdiv($partida->tempoAtualSegundos(), 60),
            'registrado_por_id' => $request->user()->id,
        ]);

        $time->increment('gols');
        $times = $partida->times()->orderBy('id')->get();
        $partida->update([
            'placar_a' => $times[0]?->gols ?? 0,
            'placar_b' => $times[1]?->gols ?? 0,
        ]);

        if (! empty($dados['jogador_id'])) {
            $time->jogadores()->updateExistingPivot($dados['jogador_id'], [
                'gols' => DB::raw('gols + 1'),
            ]);
        }

        return response()->json(['mensagem' => 'Gol registrado!']);
    }

    private function authorizeResponsavel(Request $request, Partida $partida): void
    {
        $userId = $request->user()->id;
        $patota = $partida->patota;

        $eResponsavel = $patota->responsavel_id === $userId
            || $patota->criador_id === $userId;

        if (! $eResponsavel) {
            $papel = $patota->membrosAtivos()
                ->where('users.id', $userId)
                ->value('patota_membros.papel');
            $eResponsavel = in_array($papel, ['administrador', 'organizador']);
        }

        abort_unless($eResponsavel, 403, 'Apenas o responsavel pode controlar a partida.');
    }

    private function logEvento(Partida $partida, Request $request, string $tipo): void
    {
        EventoPartida::create([
            'partida_id' => $partida->id,
            'tipo' => $tipo,
            'minuto' => intdiv($partida->tempoAtualSegundos(), 60),
            'registrado_por_id' => $request->user()->id,
        ]);
    }
}
