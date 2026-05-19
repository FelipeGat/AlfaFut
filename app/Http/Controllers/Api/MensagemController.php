<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MensagemResource;
use App\Models\Mensagem;
use App\Models\Patota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MensagemController extends Controller
{
    public function index(Request $request, Patota $patota): AnonymousResourceCollection
    {
        $this->authorizeMembro($request, $patota);

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
        $this->authorizeMembro($request, $patota);

        $dados = $request->validate([
            'partida_id' => ['nullable', 'exists:partidas,id'],
            'conteudo' => ['required', 'string', 'max:1000'],
            'tipo' => ['nullable', 'in:texto,aviso,enquete'],
            'fixada' => ['nullable', 'boolean'],
        ]);

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
        abort_unless(
            $mensagem->autor_id === $request->user()->id
                || $mensagem->patota->criador_id === $request->user()->id,
            403
        );

        $mensagem->delete();

        return response()->json(['mensagem' => 'Removida.']);
    }

    private function authorizeMembro(Request $request, Patota $patota): void
    {
        if ($request->user()->isAdmin()) return;
        $eMembro = $patota->membrosAtivos()
            ->where('users.id', $request->user()->id)
            ->exists();
        abort_unless($eMembro, 403);
    }
}
