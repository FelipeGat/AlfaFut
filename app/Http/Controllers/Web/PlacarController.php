<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrarGolRequest;
use App\Models\EventoPartida;
use App\Models\Partida;
use App\Services\PlacarDaPartida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlacarController extends Controller
{
    public function tv(Partida $partida): View
    {
        $this->authorize('view', $partida);

        $partida->load(['patota', 'times.jogadores', 'eventos.jogador', 'eventos.time']);

        return view('placar.tv', compact('partida'));
    }

    public function controle(Request $request, Partida $partida): View
    {
        $this->authorize('controlar', $partida);
        $partida->load(['patota', 'times.jogadores', 'eventos.jogador', 'eventos.time']);

        return view('placar.controle', compact('partida'));
    }

    public function resultado(Partida $partida): View
    {
        $this->authorize('view', $partida);

        abort_unless($partida->finalizada(), 404, 'A partida ainda não foi finalizada.');
        $partida->load(['patota', 'times.jogadores', 'eventos.jogador', 'eventos.time']);

        $artilheiros = $partida->gols()
            ->whereNotNull('jogador_id')
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
        $this->authorize('view', $partida);

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

    public function iniciar(Request $request, Partida $partida, PlacarDaPartida $placar): RedirectResponse
    {
        $this->authorize('controlar', $partida);

        return back()->with('status', $placar->iniciar($partida, $request->user()));
    }

    public function pausar(Request $request, Partida $partida, PlacarDaPartida $placar): RedirectResponse
    {
        $this->authorize('controlar', $partida);

        return back()->with('status', $placar->pausar($partida, $request->user()));
    }

    public function finalizar(Request $request, Partida $partida, PlacarDaPartida $placar): RedirectResponse
    {
        $this->authorize('controlar', $partida);

        return redirect()->route('partidas.resultado', $partida)
            ->with('status', $placar->finalizar($partida, $request->user()));
    }

    public function gol(RegistrarGolRequest $request, Partida $partida, PlacarDaPartida $placar): RedirectResponse
    {
        return back()->with('status', $placar->registrarGol($partida, $request->user(), $request->validated()));
    }

    public function removerEvento(Partida $partida, EventoPartida $evento, PlacarDaPartida $placar): RedirectResponse
    {
        $this->authorize('controlar', $partida);
        abort_unless($evento->partida_id === $partida->id, 404);

        return back()->with('status', $placar->removerGol($partida, $evento));
    }
}
