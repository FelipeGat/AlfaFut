<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MensagemResource;
use App\Models\Mensagem;
use App\Models\Patota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class MensagemController extends Controller
{
    public function index(Request $request, Patota $patota): AnonymousResourceCollection
    {
        $this->authorize('view', $patota);

        $mensagens = $patota->mensagens()
            ->with('autor')
            ->when($request->query('partida_id'), fn ($q, $id) => $q->where('partida_id', $id))
            ->orderByDesc('fixada')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return MensagemResource::collection($mensagens);
    }

    public function store(Request $request, Patota $patota): JsonResponse
    {
        $this->authorize('participar', $patota);

        $dados = $request->validate([
            'partida_id' => ['nullable', Rule::exists('partidas', 'id')->where('patota_id', $patota->id)],
            'conteudo' => ['required', 'string', 'max:1000'],
            'tipo' => ['nullable', 'in:texto,aviso,enquete'],
            'fixada' => ['nullable', 'boolean'],
        ]);

        if (! empty($dados['fixada']) && $request->user()->cannot('organizar', $patota)) {
            $dados['fixada'] = false;
        }

        $mensagem = $patota->mensagens()->create([
            ...$dados,
            'autor_id' => $request->user()->id,
        ]);

        return response()->json(
            new MensagemResource($mensagem->load('autor')),
            201
        );
    }

    public function destroy(Request $request, Mensagem $mensagem): JsonResponse
    {
        $this->authorize('delete', $mensagem);

        $mensagem->delete();

        return response()->json(['mensagem' => 'Removida.']);
    }
}
