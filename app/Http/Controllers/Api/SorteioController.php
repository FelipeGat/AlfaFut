<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partida;
use App\Services\SorteioTimes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SorteioController extends Controller
{
    public function __invoke(Request $request, Partida $partida, SorteioTimes $sorteio): JsonResponse
    {
        $papel = $partida->patota->membrosAtivos()
            ->where('users.id', $request->user()->id)
            ->value('patota_membros.papel');

        $eAdmin = $partida->patota->criador_id === $request->user()->id
            || $partida->patota->responsavel_id === $request->user()->id
            || in_array($papel, ['administrador', 'organizador']);

        abort_unless($eAdmin, 403, 'Apenas o responsavel pode sortear os times.');

        if ($partida->confirmados()->count() < 2) {
            return response()->json(['mensagem' => 'E preciso ao menos 2 confirmados.'], 422);
        }

        $sorteio->sortear($partida);

        return response()->json(['mensagem' => 'Times sorteados!']);
    }
}
