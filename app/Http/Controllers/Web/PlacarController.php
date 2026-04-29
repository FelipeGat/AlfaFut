<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EventoPartida;
use App\Models\Partida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlacarController extends Controller
{
    public function tv(Partida $partida): View
    {
        $partida->load(['patota', 'times.jogadores', 'eventos.jogador', 'eventos.time']);
        return view('placar.tv', compact('partida'));
    }

    public function controle(Request $request, Partida $partida): View
    {
        $this->authorizeResponsavel($request, $partida);
        $partida->load(['patota', 'times.jogadores', 'eventos.jogador', 'eventos.time']);
        return view('placar.controle', compact('partida'));
    }

    public function resultado(Partida $partida): View
    {
        abort_unless($partida->finalizada(), 404, 'A partida ainda nao foi finalizada.');
        $partida->load(['patota', 'times.jogadores', 'eventos.jogador', 'eventos.time']);

        $artilheiros = $partida->gols()
            ->with('jogador', 'time')
            ->get()
            ->groupBy('jogador_id')
            ->map(fn ($gols) => [
                'jogador' => $gols->first()->jogador,
                'time' => $gols->first()->time,
                'gols' => $gols->count(),
            ])
            ->sortByDesc('gols')
            ->values();

        return view('placar.resultado', compact('partida', 'artilheiros'));
    }

    public function dados(Partida $partida): JsonResponse
    {
        $partida->loadMissing(['times', 'eventos.jogador', 'eventos.time']);

        return response()->json([
            'placar_a' => $partida->placar_a,
            'placar_b' => $partida->placar_b,
            'tempo_segundos' => $partida->tempoAtualSegundos(),
            'tempo_formatado' => $partida->tempoFormatado(),
            'em_andamento' => $partida->emAndamento(),
            'pausada' => $partida->pausada(),
            'finalizada' => $partida->finalizada(),
            'times' => $partida->times->map(fn ($t) => [
                'id' => $t->id,
                'nome' => $t->nome,
                'cor' => $t->cor,
                'gols' => $t->gols,
                'brasao' => $t->brasao,
            ]),
            'ultimos_eventos' => $partida->eventos->take(-10)->reverse()->values()->map(fn ($e) => [
                'tipo' => $e->tipo,
                'minuto' => $e->minuto,
                'time' => $e->time?->nome,
                'jogador' => $e->jogador?->apelido ?? $e->jogador?->name,
                'criado_em' => $e->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function iniciar(Request $request, Partida $partida): RedirectResponse
    {
        $this->authorizeResponsavel($request, $partida);

        if ($partida->finalizada()) {
            return back()->with('status', 'Partida ja foi finalizada.');
        }

        // Se estava pausada, retoma
        if ($partida->pausada()) {
            $partida->update([
                'iniciada_em' => now(),
                'pausada_em' => null,
            ]);
            $this->logEvento($partida, $request, EventoPartida::TIPO_RETOMADA);
            return back()->with('status', 'Partida retomada.');
        }

        // Caso primeira vez
        $partida->update([
            'iniciada_em' => now(),
            'tempo_acumulado_segundos' => 0,
            'placar_a' => 0,
            'placar_b' => 0,
            'status' => 'em_andamento',
        ]);
        $this->logEvento($partida, $request, EventoPartida::TIPO_INICIO);

        return back()->with('status', 'Partida iniciada!');
    }

    public function pausar(Request $request, Partida $partida): RedirectResponse
    {
        $this->authorizeResponsavel($request, $partida);

        if (! $partida->emAndamento()) {
            return back()->with('status', 'Partida nao esta em andamento.');
        }

        $segundosDecorridos = (int) abs($partida->iniciada_em->diffInSeconds(now()));
        $partida->update([
            'pausada_em' => now(),
            'tempo_acumulado_segundos' => $partida->tempo_acumulado_segundos + $segundosDecorridos,
        ]);
        $this->logEvento($partida, $request, EventoPartida::TIPO_PAUSA);

        return back()->with('status', 'Partida pausada.');
    }

    public function finalizar(Request $request, Partida $partida): RedirectResponse
    {
        $this->authorizeResponsavel($request, $partida);

        $tempoFinal = $partida->tempoAtualSegundos();
        $partida->update([
            'finalizada_em' => now(),
            'tempo_acumulado_segundos' => $tempoFinal,
            'status' => 'finalizada',
        ]);
        $this->logEvento($partida, $request, EventoPartida::TIPO_FIM);

        return redirect()->route('partidas.resultado', $partida)
            ->with('status', 'Partida encerrada!');
    }

    public function gol(Request $request, Partida $partida): RedirectResponse
    {
        $this->authorizeResponsavel($request, $partida);

        $dados = $request->validate([
            'time_id' => ['required', 'exists:times,id'],
            'jogador_id' => ['nullable', 'exists:users,id'],
            'tipo' => ['nullable', 'in:gol,gol_contra'],
            'observacao' => ['nullable', 'string', 'max:120'],
        ]);

        $tipo = $dados['tipo'] ?? EventoPartida::TIPO_GOL;
        $time = $partida->times()->findOrFail($dados['time_id']);

        EventoPartida::create([
            'partida_id' => $partida->id,
            'time_id' => $time->id,
            'jogador_id' => $dados['jogador_id'] ?? null,
            'tipo' => $tipo,
            'minuto' => intdiv($partida->tempoAtualSegundos(), 60),
            'observacao' => $dados['observacao'] ?? null,
            'registrado_por_id' => $request->user()->id,
        ]);

        // Atualiza placar
        $time->increment('gols');
        $times = $partida->times()->orderBy('id')->get();
        $partida->update([
            'placar_a' => $times[0]?->gols ?? 0,
            'placar_b' => $times[1]?->gols ?? 0,
        ]);

        // Atualiza estatisticas individuais
        if (! empty($dados['jogador_id'])) {
            $partida->times()->where('id', $time->id)
                ->first()
                ?->jogadores()
                ->updateExistingPivot($dados['jogador_id'], [
                    'gols' => DB::raw('gols + 1'),
                ]);
        }

        return back()->with('status', '⚽ Gol registrado!');
    }

    public function removerEvento(Request $request, Partida $partida, EventoPartida $evento): RedirectResponse
    {
        $this->authorizeResponsavel($request, $partida);
        abort_unless($evento->partida_id === $partida->id, 404);

        // Se for gol, decrementa
        if (in_array($evento->tipo, [EventoPartida::TIPO_GOL, EventoPartida::TIPO_GOL_CONTRA])) {
            $time = $evento->time;
            if ($time && $time->gols > 0) {
                $time->decrement('gols');
            }
            $times = $partida->times()->orderBy('id')->get();
            $partida->update([
                'placar_a' => $times[0]?->gols ?? 0,
                'placar_b' => $times[1]?->gols ?? 0,
            ]);
        }

        $evento->delete();

        return back()->with('status', 'Evento removido.');
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
