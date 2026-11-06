<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecuperacaoDeSenha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecuperacaoSenhaController extends Controller
{
    /**
     * Envia o link de redefinição de senha.
     */
    public function solicitar(Request $request, RecuperacaoDeSenha $recuperacao): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $recuperacao->pedirLink($request->input('email'));

        return response()->json(['mensagem' => RecuperacaoDeSenha::MENSAGEM]);
    }
}
