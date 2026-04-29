<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartidaController extends Controller
{
    public function index(Request $request): View
    {
        $patotaIds = $request->user()->patotas()->pluck('patotas.id');
        $filtro = $request->query('filtro', 'proximas');

        $query = Partida::query()
            ->whereIn('patota_id', $patotaIds)
            ->with(['patota', 'local', 'confirmacoes']);

        $partidas = $filtro === 'passadas'
            ? $query->passadas()->limit(20)->get()
            : $query->proximas()->get();

        return view('partidas.index', compact('partidas', 'filtro'));
    }

    public function show(Request $request, Partida $partida): View
    {
        $this->authorizeMembro($request, $partida);
        $partida->load(['patota', 'local', 'organizador', 'confirmacoes.user', 'times.jogadores']);
        $minhaConfirmacao = $partida->confirmacoes->firstWhere('user_id', $request->user()->id);

        return view('partidas.show', compact('partida', 'minhaConfirmacao'));
    }

    public function confirmar(Request $request, Partida $partida): RedirectResponse
    {
        $this->authorizeMembro($request, $partida);

        $emListaEspera = $partida->isCheia();
        PartidaConfirmacao::updateOrCreate(
            ['partida_id' => $partida->id, 'user_id' => $request->user()->id],
            [
                'status' => 'confirmado',
                'em_lista_espera' => $emListaEspera,
                'posicao_lista_espera' => $emListaEspera
                    ? ($partida->listaEspera()->max('posicao_lista_espera') ?? 0) + 1
                    : null,
                'confirmado_em' => now(),
            ]
        );

        return back()->with('status', $emListaEspera
            ? 'Voce entrou na lista de espera.'
            : 'Presenca confirmada!');
    }

    public function recusar(Request $request, Partida $partida): RedirectResponse
    {
        $this->authorizeMembro($request, $partida);

        PartidaConfirmacao::updateOrCreate(
            ['partida_id' => $partida->id, 'user_id' => $request->user()->id],
            ['status' => 'recusado', 'em_lista_espera' => false]
        );

        return back()->with('status', 'Voce recusou esta partida.');
    }

    // Stubs minimos para satisfazer o resource controller.
    public function create(): View { abort(404); }
    public function store(Request $request): RedirectResponse { abort(404); }
    public function edit(Partida $partida): View { abort(404); }
    public function update(Request $request, Partida $partida): RedirectResponse { abort(404); }
    public function destroy(Partida $partida): RedirectResponse { abort(404); }

    private function authorizeMembro(Request $request, Partida $partida): void
    {
        $eMembro = $partida->patota
            ->membrosAtivos()
            ->where('users.id', $request->user()->id)
            ->exists();
        abort_unless($eMembro, 403);
    }
}
