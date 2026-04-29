<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AcessibilidadeController;
use App\Http\Controllers\Web\ComoUsarController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DespesaController;
use App\Http\Controllers\Web\MensagemController;
use App\Http\Controllers\Web\PartidaController;
use App\Http\Controllers\Web\PatotaController;
use App\Http\Controllers\Web\PlacarController;
use App\Http\Controllers\Web\SorteioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/como-usar', ComoUsarController::class)->name('como-usar');

// Rota temporaria para capturas de tela em ambiente local
if (app()->environment('local')) {
    Route::get('/_demo-login', function () {
        $u = \App\Models\User::where('email', 'admin@alfafut.test')->firstOrFail();
        auth()->login($u);
        return redirect('/dashboard');
    });
}

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('patotas', PatotaController::class);

    Route::get('partidas', [PartidaController::class, 'index'])->name('partidas.index');
    Route::get('partidas/{partida}', [PartidaController::class, 'show'])->name('partidas.show');
    Route::post('partidas/{partida}/confirmar', [PartidaController::class, 'confirmar'])->name('partidas.confirmar');
    Route::post('partidas/{partida}/recusar', [PartidaController::class, 'recusar'])->name('partidas.recusar');
    Route::post('partidas/{partida}/sortear', SorteioController::class)->name('partidas.sortear');

    // Placar ao vivo / TV mode
    Route::get('partidas/{partida}/tv', [PlacarController::class, 'tv'])->name('partidas.tv');
    Route::get('partidas/{partida}/controle', [PlacarController::class, 'controle'])->name('partidas.controle');
    Route::get('partidas/{partida}/resultado', [PlacarController::class, 'resultado'])->name('partidas.resultado');
    Route::get('partidas/{partida}/dados', [PlacarController::class, 'dados'])->name('partidas.dados');
    Route::post('partidas/{partida}/iniciar', [PlacarController::class, 'iniciar'])->name('partidas.iniciar');
    Route::post('partidas/{partida}/pausar', [PlacarController::class, 'pausar'])->name('partidas.pausar');
    Route::post('partidas/{partida}/finalizar', [PlacarController::class, 'finalizar'])->name('partidas.finalizar');
    Route::post('partidas/{partida}/gol', [PlacarController::class, 'gol'])->name('partidas.gol');
    Route::delete('partidas/{partida}/eventos/{evento}', [PlacarController::class, 'removerEvento'])->name('partidas.eventos.destroy');

    // Despesas (escopo por patota)
    Route::get('patotas/{patota}/despesas', [DespesaController::class, 'index'])->name('patotas.despesas.index');
    Route::get('patotas/{patota}/despesas/criar', [DespesaController::class, 'create'])->name('patotas.despesas.create');
    Route::post('patotas/{patota}/despesas', [DespesaController::class, 'store'])->name('patotas.despesas.store');
    Route::get('patotas/{patota}/despesas/{despesa}', [DespesaController::class, 'show'])->name('patotas.despesas.show');
    Route::delete('patotas/{patota}/despesas/{despesa}', [DespesaController::class, 'destroy'])->name('patotas.despesas.destroy');
    Route::post('patotas/{patota}/pagamentos/{pagamento}/quitar', [DespesaController::class, 'pagar'])->name('patotas.pagamentos.quitar');

    // Mural / mensagens
    Route::get('patotas/{patota}/mural', [MensagemController::class, 'index'])->name('patotas.mensagens.index');
    Route::post('patotas/{patota}/mural', [MensagemController::class, 'store'])->name('patotas.mensagens.store');
    Route::delete('patotas/{patota}/mural/{mensagem}', [MensagemController::class, 'destroy'])->name('patotas.mensagens.destroy');

    Route::get('acessibilidade', [AcessibilidadeController::class, 'edit'])->name('acessibilidade.edit');
    Route::patch('acessibilidade', [AcessibilidadeController::class, 'update'])->name('acessibilidade.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
