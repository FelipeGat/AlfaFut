<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeuUsuarioResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'apelido' => ['nullable', 'string', 'max:60'],
            'email' => ['required', 'email', 'unique:users,email'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'posicao_preferida' => ['nullable', 'in:goleiro,zagueiro,lateral,meia,atacante'],
            'nivel_habilidade' => ['nullable', 'in:iniciante,intermediario,avancado'],
            'tipo_usuario' => ['nullable', 'in:jogador,dono_pelada,dono_campo'],
            'aceite_termos' => ['accepted'],
        ], ['aceite_termos.accepted' => 'Para criar a conta, é preciso aceitar os termos de uso e a política de privacidade.']);

        $user = User::create([
            ...$dados,
            'password' => Hash::make($dados['password']),
        ]);
        $user->concluirCadastro($dados['tipo_usuario'] ?? 'jogador');

        event(new Registered($user));

        $token = $user->createToken('app-mobile')->plainTextToken;

        return response()->json([
            'usuario' => new MeuUsuarioResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['E-mail ou senha incorretos.'],
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'app-mobile')->plainTextToken;

        return response()->json([
            'usuario' => new MeuUsuarioResource($user),
            'token' => $token,
        ]);
    }

    public function reenviarConfirmacao(Request $request): JsonResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return response()->json(['mensagem' => 'Se o e-mail ainda não foi confirmado, enviamos um novo link.']);
    }

    public function me(Request $request): MeuUsuarioResource
    {
        return new MeuUsuarioResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensagem' => 'Sessão encerrada.']);
    }
}
