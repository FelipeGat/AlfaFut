<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalvarPartidaRequest;
use App\Http\Resources\PartidaResource;
use App\Models\Partida;
use App\Models\Patota;
use App\Services\VagasDaPartida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PartidaController extends Controller
{
    public function index(Request $request, Patota $patota): AnonymousResourceCollection
    {
        $this->authorize('view', $patota);

        $filtro = $request->query('filtro', 'proximas');
        $query = $patota->partidas()->with(['local', 'campo', 'organizador', 'confirmacoes']);

        if ($filtro === 'passadas') {
            $query->passadas();
        } else {
            $query->proximas();
        }

        return PartidaResource::collection($query->get());
    }

    public function store(SalvarPartidaRequest $request, Patota $patota): JsonResponse
    {
        $partida = $patota->marcarPartida($request->user(), $request->dados());

        return response()->json(
            new PartidaResource($partida->load(['local', 'campo', 'organizador'])),
            201
        );
    }

    public function show(Request $request, Partida $partida): PartidaResource
    {
        $this->authorize('view', $partida);

        return new PartidaResource(
            $partida->load(['local', 'campo', 'organizador', 'confirmacoes.user', 'times.jogadores'])
        );
    }

    public function update(SalvarPartidaRequest $request, Partida $partida, VagasDaPartida $vagas): PartidaResource
    {
        $partida->exigirAlteravel();

        $partida->update([
            ...$request->dados(),
            ...$request->validate([
                'status' => ['sometimes', 'in:agendada,confirmada,cancelada'],
            ]),
        ]);
        $vagas->reorganizar($partida);

        return new PartidaResource($partida->fresh(['local', 'campo', 'organizador']));
    }

    public function destroy(Request $request, Partida $partida): JsonResponse
    {
        $this->authorize('controlar', $partida);
        $partida->exigirAlteravel();

        $partida->update(['status' => 'cancelada']);

        return response()->json(['mensagem' => 'Partida cancelada.']);
    }
}
