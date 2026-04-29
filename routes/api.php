<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConfirmacaoController;
use App\Http\Controllers\Api\DespesaController;
use App\Http\Controllers\Api\MensagemController;
use App\Http\Controllers\Api\PartidaController;
use App\Http\Controllers\Api\PatotaController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/registrar', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/eu', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::patch('perfil', [UsuarioController::class, 'atualizarPerfil']);
        Route::patch('perfil/acessibilidade', [UsuarioController::class, 'atualizarAcessibilidade']);

        Route::post('patotas/entrar', [PatotaController::class, 'entrar']);
        Route::apiResource('patotas', PatotaController::class);
        Route::get('patotas/{patota}/membros', [PatotaController::class, 'membros']);

        Route::get('patotas/{patota}/partidas', [PartidaController::class, 'index']);
        Route::post('patotas/{patota}/partidas', [PartidaController::class, 'store']);
        Route::get('partidas/{partida}', [PartidaController::class, 'show']);
        Route::patch('partidas/{partida}', [PartidaController::class, 'update']);
        Route::delete('partidas/{partida}', [PartidaController::class, 'destroy']);

        Route::post('partidas/{partida}/confirmar', [ConfirmacaoController::class, 'confirmar']);
        Route::post('partidas/{partida}/recusar', [ConfirmacaoController::class, 'recusar']);
        Route::delete('partidas/{partida}/confirmacao', [ConfirmacaoController::class, 'cancelar']);

        Route::get('patotas/{patota}/despesas', [DespesaController::class, 'index']);
        Route::post('patotas/{patota}/despesas', [DespesaController::class, 'store']);
        Route::get('despesas/{despesa}', [DespesaController::class, 'show']);
        Route::patch('despesas/{despesa}', [DespesaController::class, 'update']);
        Route::delete('despesas/{despesa}', [DespesaController::class, 'destroy']);
        Route::post('pagamentos/{pagamento}/quitar', [DespesaController::class, 'pagar']);

        Route::get('patotas/{patota}/mensagens', [MensagemController::class, 'index']);
        Route::post('patotas/{patota}/mensagens', [MensagemController::class, 'store']);
        Route::delete('mensagens/{mensagem}', [MensagemController::class, 'destroy']);
    });
});
