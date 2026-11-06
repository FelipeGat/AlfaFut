<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConfirmacaoController;
use App\Http\Controllers\Api\DespesaController;
use App\Http\Controllers\Api\MensagemController;
use App\Http\Controllers\Api\PartidaController;
use App\Http\Controllers\Api\PatotaController;
use App\Http\Controllers\Api\PlacarController;
use App\Http\Controllers\Api\RecuperacaoSenhaController;
use App\Http\Controllers\Api\SorteioController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/registrar', [AuthController::class, 'register'])->middleware('throttle:cadastro');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('auth/esqueci-senha', [RecuperacaoSenhaController::class, 'solicitar'])->middleware('throttle:recuperacao');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/eu', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/reenviar-confirmacao', [AuthController::class, 'reenviarConfirmacao'])->middleware('throttle:reenvio-confirmacao');

        Route::patch('perfil', [UsuarioController::class, 'atualizarPerfil']);
        Route::patch('perfil/acessibilidade', [UsuarioController::class, 'atualizarAcessibilidade']);
        Route::get('perfil/meus-dados', [UsuarioController::class, 'meusDados'])->middleware('throttle:meus-dados');

        Route::middleware('verified')->group(function () {
            Route::post('patotas/entrar', [PatotaController::class, 'entrar'])->middleware('throttle:entrar-codigo');
            Route::get('patotas/arquivadas', [PatotaController::class, 'arquivadas']);
            Route::apiResource('patotas', PatotaController::class);
            Route::get('patotas/{patota}/membros', [PatotaController::class, 'membros']);
            Route::post('patotas/{patota}/sair', [PatotaController::class, 'sair']);
            Route::post('patotas/{patota}/restaurar', [PatotaController::class, 'restaurar'])->withTrashed();

            Route::get('patotas/{patota}/partidas', [PartidaController::class, 'index']);
            Route::post('patotas/{patota}/partidas', [PartidaController::class, 'store']);
            Route::get('partidas/{partida}', [PartidaController::class, 'show']);
            Route::patch('partidas/{partida}', [PartidaController::class, 'update']);
            Route::delete('partidas/{partida}', [PartidaController::class, 'destroy']);

            Route::post('partidas/{partida}/confirmar', [ConfirmacaoController::class, 'confirmar']);
            Route::post('partidas/{partida}/recusar', [ConfirmacaoController::class, 'recusar']);
            Route::delete('partidas/{partida}/confirmacao', [ConfirmacaoController::class, 'cancelar']);

            // Placar ao vivo
            Route::get('partidas/{partida}/placar', [PlacarController::class, 'dados']);
            Route::post('partidas/{partida}/iniciar', [PlacarController::class, 'iniciar']);
            Route::post('partidas/{partida}/pausar', [PlacarController::class, 'pausar']);
            Route::post('partidas/{partida}/finalizar', [PlacarController::class, 'finalizar']);
            Route::post('partidas/{partida}/gol', [PlacarController::class, 'gol']);
            Route::post('partidas/{partida}/sortear', SorteioController::class);

            Route::get('patotas/{patota}/despesas', [DespesaController::class, 'index']);
            Route::post('patotas/{patota}/despesas', [DespesaController::class, 'store']);
            Route::get('despesas/{despesa}', [DespesaController::class, 'show']);
            Route::patch('despesas/{despesa}', [DespesaController::class, 'update']);
            Route::delete('despesas/{despesa}', [DespesaController::class, 'destroy']);
            Route::post('despesas/{despesa}/refazer-rateio', [DespesaController::class, 'refazerRateio']);
            Route::post('despesas/{despesa}/marcar-paga', [DespesaController::class, 'marcarPaga']);
            Route::post('despesas/{despesa}/cancelar', [DespesaController::class, 'cancelar']);
            Route::post('pagamentos/{pagamento}/quitar', [DespesaController::class, 'pagar']);

            Route::get('patotas/{patota}/mensagens', [MensagemController::class, 'index']);
            Route::post('patotas/{patota}/mensagens', [MensagemController::class, 'store']);
            Route::delete('mensagens/{mensagem}', [MensagemController::class, 'destroy']);

            // Painel administrativo (acesso global)
            Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
                Route::get('resumo', [AdminController::class, 'resumo']);
                Route::get('usuarios', [AdminController::class, 'usuarios']);
                Route::get('patotas', [AdminController::class, 'patotas']);
                Route::get('partidas', [AdminController::class, 'partidas']);
                Route::get('partidas-ativas', [AdminController::class, 'partidasAtivas']);
            });
        });
    });
});
