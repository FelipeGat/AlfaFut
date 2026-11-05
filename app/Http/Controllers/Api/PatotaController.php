<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\RegraDaTurma;
use App\Http\Controllers\Controller;
use App\Http\Resources\MembroResource;
use App\Http\Resources\PatotaResource;
use App\Http\Resources\UsuarioResource;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Services\MembrosDaTurma;
use App\Support\Dinheiro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatotaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            // Admin global ve todas as turmas
            $patotas = Patota::query()
                ->withCount('membrosAtivos as total_membros')
                ->with('criador')
                ->orderBy('nome')
                ->get();
        } else {
            $patotas = $user
                ->patotasAtivas()
                ->withCount('membrosAtivos as total_membros')
                ->with('criador')
                ->orderBy('nome')
                ->get();
        }

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
            'valor_mensalidade' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
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
        $this->authorize('view', $patota);

        return new PatotaResource(
            $patota->load('criador')->loadCount('membrosAtivos as total_membros')
        );
    }

    public function update(Request $request, Patota $patota): PatotaResource
    {
        $this->authorize('update', $patota);

        $dados = $request->validate([
            'nome' => ['sometimes', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'string', 'size:2'],
            'jogadores_por_time' => ['sometimes', 'integer', 'min:3', 'max:11'],
            'quantidade_times' => ['sometimes', 'integer', 'min:2', 'max:6'],
            'valor_mensalidade' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
            'publica' => ['nullable', 'boolean'],
        ]);

        $patota->update($dados);

        return new PatotaResource($patota->fresh('criador'));
    }

    public function destroy(Request $request, Patota $patota): JsonResponse
    {
        $this->authorize('delete', $patota);

        $patota->delete();

        return response()->json(['mensagem' => 'Patota arquivada.']);
    }

    public function entrar(Request $request, MembrosDaTurma $membros): JsonResponse
    {
        $request->validate([
            'codigo_convite' => ['required', 'string', 'size:8'],
        ]);

        $patota = $membros->porCodigo($request->codigo_convite);
        $membros->entrar($request->user(), $patota);

        return response()->json([
            'mensagem' => 'Você entrou na '.$patota->nome,
            'patota' => new PatotaResource($patota),
        ]);
    }

    public function membros(Request $request, Patota $patota): AnonymousResourceCollection
    {
        $this->authorize('view', $patota);

        $membros = $patota->membrosAtivos()->orderBy('name')->get();

        return $request->user()->can('organizar', $patota)
            ? MembroResource::collection($membros)
            : UsuarioResource::collection($membros);
    }

    public function sair(Request $request, Patota $patota, MembrosDaTurma $membros): JsonResponse
    {
        $pendente = $membros->sair($request->user(), $patota);

        return response()->json([
            'mensagem' => 'Você saiu da turma.',
            'pendente' => Dinheiro::reais($pendente),
        ]);
    }

    public function arquivadas(Request $request): AnonymousResourceCollection
    {
        return PatotaResource::collection(
            Patota::onlyTrashed()->where('criador_id', $request->user()->id)->orderBy('nome')->get()
        );
    }

    public function restaurar(Patota $patota): PatotaResource
    {
        $this->authorize('restore', $patota);

        if (! $patota->trashed()) {
            throw new RegraDaTurma('Esta turma não está arquivada.');
        }

        $patota->restore();

        return new PatotaResource($patota->fresh('criador'));
    }
}
