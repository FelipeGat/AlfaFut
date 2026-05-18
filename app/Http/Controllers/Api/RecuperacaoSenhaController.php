<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class RecuperacaoSenhaController extends Controller
{
    /**
     * Envia o link de redefinicao de senha.
     */
    public function solicitar(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::ResetLinkSent) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        return response()->json([
            'mensagem' => 'Enviamos um link de recuperacao para seu e-mail.',
        ]);
    }
}
