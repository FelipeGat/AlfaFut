<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AcessibilidadeController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DespesaController;
use App\Http\Controllers\Web\MensagemController;
use App\Http\Controllers\Web\PartidaController;
use App\Http\Controllers\Web\PatotaController;
use App\Http\Controllers\Web\SorteioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('patotas', PatotaController::class);

    Route::get('partidas', [PartidaController::class, 'index'])->name('partidas.index');
    Route::get('partidas/{partida}', [PartidaController::class, 'show'])->name('partidas.show');
    Route::post('partidas/{partida}/confirmar', [PartidaController::class, 'confirmar'])->name('partidas.confirmar');
    Route::post('partidas/{partida}/recusar', [PartidaController::class, 'recusar'])->name('partidas.recusar');
    Route::post('partidas/{partida}/sortear', SorteioController::class)->name('partidas.sortear');

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
