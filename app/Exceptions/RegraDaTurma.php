<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/** Ação recusada por regra da turma: `{mensagem}` na API e aviso de erro no site. */
class RegraDaTurma extends RuntimeException
{
    public function __construct(string $mensagem, private readonly int $statusNaApi = 422)
    {
        parent::__construct($mensagem);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json(['mensagem' => $this->getMessage()], $this->statusNaApi);
        }

        return back()->with('erro', $this->getMessage());
    }
}
