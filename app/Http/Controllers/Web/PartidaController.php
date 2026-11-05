<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalvarPartidaRequest;
use App\Models\Campo;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PartidaController extends Controller
{
    public function index(Request $request): View
    {
        $patotaIds = $request->user()->patotasAtivas()->pluck('patotas.id');
        $filtro = $request->query('filtro', 'proximas');

        $query = Partida::query()
            ->whereIn('patota_id', $patotaIds)
            ->with(['patota', 'local', 'campo', 'confirmacoes']);

        $partidas = $filtro === 'passadas'
            ? $query->passadas()->limit(20)->get()
            : $query->proximas()->get();

        return view('partidas.index', compact('partidas', 'filtro'));
    }

    public function show(Request $request, Partida $partida): View
    {
        $this->authorize('view', $partida);
        $partida->load(['patota', 'local', 'campo', 'organizador', 'confirmacoes.user', 'times.jogadores']);
        $minhaConfirmacao = $partida->confirmacoes->firstWhere('user_id', $request->user()->id);

        return view('partidas.show', compact('partida', 'minhaConfirmacao'));
    }

    public function confirmar(Request $request, Partida $partida): RedirectResponse
    {
        $this->authorize('participar', $partida);

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
            ? 'Você entrou na lista de espera.'
            : 'Presença confirmada!');
    }

    public function recusar(Request $request, Partida $partida): RedirectResponse
    {
        $this->authorize('participar', $partida);

        PartidaConfirmacao::updateOrCreate(
            ['partida_id' => $partida->id, 'user_id' => $request->user()->id],
            ['status' => 'recusado', 'em_lista_espera' => false]
        );

        return back()->with('status', 'Você recusou esta partida.');
    }

    public function create(Patota $patota): View
    {
        $this->authorize('organizar', $patota);

        return view('partidas.create', [
            'patota' => $patota,
            'partida' => null,
            ...$this->lugares($patota),
        ]);
    }

    public function store(SalvarPartidaRequest $request, Patota $patota): RedirectResponse
    {
        $partida = $patota->marcarPartida($request->user(), $request->dados());

        return redirect()->route('partidas.show', $partida)->with('status', 'Partida marcada.');
    }

    public function edit(Partida $partida): View|RedirectResponse
    {
        $this->authorize('controlar', $partida);

        if (! $partida->podeSerAlterada()) {
            return redirect()->route('partidas.show', $partida)
                ->with('erro', 'Esta partida não pode mais ser alterada, porque já começou, terminou ou foi cancelada.');
        }

        return view('partidas.edit', [
            'patota' => $partida->patota,
            'partida' => $partida,
            ...$this->lugares($partida->patota, $partida),
        ]);
    }

    public function update(SalvarPartidaRequest $request, Partida $partida): RedirectResponse
    {
        $partida->exigirAlteravel();
        $partida->update($request->dados());

        return redirect()->route('partidas.show', $partida)->with('status', 'Partida alterada.');
    }

    public function cancelar(Partida $partida): RedirectResponse
    {
        $this->authorize('controlar', $partida);
        $partida->exigirAlteravel();

        $partida->update(['status' => 'cancelada']);

        return redirect()->route('partidas.show', $partida)->with('status', 'Partida cancelada.');
    }

    /** @return array{locais: Collection, campos: Collection} */
    private function lugares(Patota $patota, ?Partida $partida = null): array
    {
        return [
            'locais' => $patota->locais()->orderBy('nome')->get(),
            'campos' => Campo::query()
                ->where(fn ($q) => $q->where('ativo', true)->orWhere('id', $partida?->campo_id))
                ->orderBy('cidade')
                ->orderBy('nome')
                ->get(),
        ];
    }
}
