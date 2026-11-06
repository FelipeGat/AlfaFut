<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'tipo_usuario' => ['required', 'in:jogador,dono_pelada,dono_campo'],
            'aceite_termos' => ['accepted'],
        ], ['aceite_termos.accepted' => 'Para criar a conta, é preciso aceitar os termos de uso e a política de privacidade.']);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->concluirCadastro($request->tipo_usuario);

        event(new Registered($user));

        Auth::login($user);

        return redirect(match ($user->tipo_usuario) {
            'dono_campo' => route('meus-campos.create'),
            'dono_pelada' => route('patotas.create'),
            default => route('dashboard'),
        });
    }
}
