<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Http\Request;

class ExigeEmailConfirmado extends EnsureEmailIsVerified
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if ($request instanceof Request && $request->expectsJson() && $request->user()?->hasVerifiedEmail() === false) {
            return response()->json([
                'message' => 'Confirme o seu e-mail para continuar. Se não recebeu o link, peça o reenvio.',
            ], 403);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}
