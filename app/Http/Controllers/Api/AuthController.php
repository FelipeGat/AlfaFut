<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Models\User;
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
        ]);

        $user = User::create([
            ...$dados,
            'password' => Hash::make($dados['password']),
        ]);

        $token = $user->createToken('app-mobile')->plainTextToken;

        return response()->json([
            'usuario' => new UsuarioResource($user),
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
                'email' => ['Credenciais invalidas.'],
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'app-mobile')->plainTextToken;

        return response()->json([
            'usuario' => new UsuarioResource($user),
            'token' => $token,
        ]);
    }

    public function me(Request $request): UsuarioResource
    {
        return new UsuarioResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensagem' => 'Sessao encerrada.']);
    }
}
