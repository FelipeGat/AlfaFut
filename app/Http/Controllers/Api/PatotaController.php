<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatotaResource;
use App\Http\Resources\UsuarioResource;
use App\Models\Patota;
use App\Models\PatotaMembro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatotaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $patotas = $request->user()
            ->patotas()
            ->withCount('membrosAtivos as total_membros')
            ->with('criador')
            ->orderBy('nome')
            ->get();

        return PatotaResource::collection($patotas);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'string', 'size:2'],
            'jogadores_por_time' => ['required', 'integer', 'min:3', 'max:11'],
            'quantidade_times' => ['required', 'integer', 'min:2', 'max:6'],
            'valor_mensalidade' => ['nullable', 'numeric', 'min:0'],
            'publica' => ['nullable', 'boolean'],
        ]);

        $patota = Patota::create([
            ...$dados,
            'criador_id' => $request->user()->id,
        ]);

        PatotaMembro::create([
            'patota_id' => $patota->id,
            'user_id' => $request->user()->id,
            'papel' => 'administrador',
            'status' => 'ativo',
        ]);

        return response()->json(new PatotaResource($patota->load('criador')), 201);
    }

    public function show(Request $request, Patota $patota): PatotaResource
    {
        $this->authorizeMembro($request, $patota);

        return new PatotaResource(
            $patota->load('criador')->loadCount('membrosAtivos as total_membros')
        );
    }

    public function update(Request $request, Patota $patota): PatotaResource
    {
        abort_unless($patota->criador_id === $request->user()->id, 403, 'Apenas o criador pode editar a patota.');

        $dados = $request->validate([
            'nome' => ['sometimes', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'string', 'size:2'],
            'jogadores_por_time' => ['sometimes', 'integer', 'min:3', 'max:11'],
            'quantidade_times' => ['sometimes', 'integer', 'min:2', 'max:6'],
            'valor_mensalidade' => ['nullable', 'numeric', 'min:0'],
            'publica' => ['nullable', 'boolean'],
        ]);

        $patota->update($dados);

        return new PatotaResource($patota->fresh('criador'));
    }

    public function destroy(Request $request, Patota $patota): JsonResponse
    {
        abort_unless($patota->criador_id === $request->user()->id, 403);

        $patota->delete();

        return response()->json(['mensagem' => 'Patota arquivada.']);
    }

    public function entrar(Request $request): JsonResponse
    {
        $request->validate([
            'codigo_convite' => ['required', 'string', 'size:8'],
        ]);

        $patota = Patota::where('codigo_convite', strtoupper($request->codigo_convite))->firstOrFail();

        PatotaMembro::firstOrCreate(
            ['patota_id' => $patota->id, 'user_id' => $request->user()->id],
            ['papel' => 'membro', 'status' => 'ativo']
        );

        return response()->json([
            'mensagem' => 'Voce entrou na ' . $patota->nome,
            'patota' => new PatotaResource($patota),
        ]);
    }

    public function membros(Request $request, Patota $patota): AnonymousResourceCollection
    {
        $this->authorizeMembro($request, $patota);

        return UsuarioResource::collection($patota->membrosAtivos()->orderBy('name')->get());
    }

    public function sair(Request $request, Patota $patota): JsonResponse
    {
        $userId = $request->user()->id;

        if ($patota->criador_id === $userId) {
            return response()->json([
                'mensagem' => 'O criador nao pode sair da turma. Transfira a responsabilidade antes.',
            ], 422);
        }

        $vinculo = $patota->membrosAtivos()
            ->where('users.id', $userId)
            ->first();

        if (! $vinculo) {
            return response()->json(['mensagem' => 'Voce nao faz parte desta turma.'], 404);
        }

        \App\Models\PatotaMembro::where('patota_id', $patota->id)
            ->where('user_id', $userId)
            ->update(['status' => 'inativo', 'saiu_em' => now()]);

        return response()->json(['mensagem' => 'Voce saiu da turma.']);
    }

    private function authorizeMembro(Request $request, Patota $patota): void
    {
        $eMembro = $patota->membrosAtivos()->where('users.id', $request->user()->id)->exists();
        abort_unless($eMembro, 403, 'Voce nao faz parte desta patota.');
    }
}
