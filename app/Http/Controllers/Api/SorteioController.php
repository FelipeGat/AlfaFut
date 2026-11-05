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
        $this->authorize('controlar', $partida);

        $sorteio->sortear($partida);

        return response()->json(['mensagem' => 'Times sorteados!']);
    }
}
