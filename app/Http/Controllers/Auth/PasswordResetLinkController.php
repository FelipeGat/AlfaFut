<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\RecuperacaoDeSenha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, RecuperacaoDeSenha $recuperacao): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $recuperacao->pedirLink($request->input('email'));

        return back()->with('status', RecuperacaoDeSenha::MENSAGEM);
    }
}
