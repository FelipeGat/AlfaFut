<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartidaResource;
use App\Models\Partida;
use App\Models\Patota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PartidaController extends Controller
{
    public function index(Request $request, Patota $patota): AnonymousResourceCollection
    {
        $this->authorizeMembro($request, $patota);

        $filtro = $request->query('filtro', 'proximas');
        $query = $patota->partidas()->with(['local', 'organizador', 'confirmacoes']);

        if ($filtro === 'passadas') {
            $query->passadas();
        } else {
            $query->proximas();
        }

        return PartidaResource::collection($query->get());
    }

    public function store(Request $request, Patota $patota): JsonResponse
    {
        $this->authorizeOrganizador($request, $patota);

        $dados = $request->validate([
            'local_id' => ['nullable', 'exists:locais,id'],
            'titulo' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'data_hora' => ['required', 'date', 'after:now'],
            'duracao_minutos' => ['nullable', 'integer', 'min:30', 'max:240'],
            'vagas_total' => ['nullable', 'integer', 'min:4'],
            'valor_individual' => ['nullable', 'numeric', 'min:0'],
            'confirmacao_ate' => ['nullable', 'date', 'before:data_hora'],
            'lista_espera_habilitada' => ['nullable', 'boolean'],
        ]);

        $partida = $patota->partidas()->create([
            ...$dados,
            'organizador_id' => $request->user()->id,
            'vagas_total' => $dados['vagas_total'] ?? $patota->vagasPorPartida(),
            'duracao_minutos' => $dados['duracao_minutos'] ?? 90,
            'status' => 'agendada',
        ]);

        return response()->json(
            new PartidaResource($partida->load(['local', 'organizador'])),
            201
        );
    }

    public function show(Request $request, Partida $partida): PartidaResource
    {
        $this->authorizeMembro($request, $partida->patota);

        return new PartidaResource(
            $partida->load(['local', 'organizador', 'confirmacoes.user', 'times.jogadores'])
        );
    }

    public function update(Request $request, Partida $partida): PartidaResource
    {
        $this->authorizeOrganizador($request, $partida->patota);

        $dados = $request->validate([
            'local_id' => ['nullable', 'exists:locais,id'],
            'titulo' => ['sometimes', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'data_hora' => ['sometimes', 'date'],
            'duracao_minutos' => ['nullable', 'integer', 'min:30', 'max:240'],
            'vagas_total' => ['sometimes', 'integer', 'min:4'],
            'valor_individual' => ['nullable', 'numeric', 'min:0'],
            'confirmacao_ate' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:agendada,confirmada,em_andamento,finalizada,cancelada'],
        ]);

        $partida->update($dados);

        return new PartidaResource($partida->fresh(['local', 'organizador']));
    }

    public function destroy(Request $request, Partida $partida): JsonResponse
    {
        $this->authorizeOrganizador($request, $partida->patota);

        $partida->update(['status' => 'cancelada']);

        return response()->json(['mensagem' => 'Partida cancelada.']);
    }

    private function authorizeMembro(Request $request, Patota $patota): void
    {
        if ($request->user()->isAdmin()) return;
        $eMembro = $patota->membrosAtivos()->where('users.id', $request->user()->id)->exists();
        abort_unless($eMembro, 403);
    }

    private function authorizeOrganizador(Request $request, Patota $patota): void
    {
        if ($request->user()->isAdmin()) return;
        $papel = $patota->membrosAtivos()
            ->where('users.id', $request->user()->id)
            ->value('patota_membros.papel');
        abort_unless(in_array($papel, ['administrador', 'organizador']), 403);
    }
}
