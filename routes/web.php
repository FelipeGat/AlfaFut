<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AcessibilidadeController;
use App\Http\Controllers\Web\CampoController;
use App\Http\Controllers\Web\ComoUsarController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DespesaController;
use App\Http\Controllers\Web\LocalController;
use App\Http\Controllers\Web\MembroController;
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
Route::view('/offline', 'offline')->name('offline');

Route::middleware(['auth', 'verified', 'cache.headers:no_store;private'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('patotas/entrar', [PatotaController::class, 'entrar'])->middleware('throttle:entrar-codigo')->name('patotas.entrar');
    Route::resource('patotas', PatotaController::class);
    Route::post('patotas/{patota}/sair', [PatotaController::class, 'sair'])->name('patotas.sair');

    // Gestão de membros
    Route::get('patotas/{patota}/membros', [MembroController::class, 'index'])->name('patotas.membros.index');
    Route::patch('patotas/{patota}/membros/{membro}/papel', [MembroController::class, 'papel'])->name('patotas.membros.papel');
    Route::post('patotas/{patota}/membros/{membro}/responsavel', [MembroController::class, 'responsavel'])->name('patotas.membros.responsavel');
    Route::post('patotas/{patota}/membros/{membro}/liberar', [MembroController::class, 'liberar'])->name('patotas.membros.liberar');
    Route::delete('patotas/{patota}/membros/{membro}', [MembroController::class, 'destroy'])->name('patotas.membros.destroy');

    // Locais da turma
    Route::resource('patotas.locais', LocalController::class)
        ->parameters(['locais' => 'local'])
        ->except('show');

    // Catalogo publico de campos (todos os logados podem ver)
    Route::get('campos', [CampoController::class, 'catalogo'])->name('campos.catalogo');
    Route::get('campos/{campo}', [CampoController::class, 'show'])->name('campos.show');

    // CRUD dos meus campos (dono do campo)
    Route::get('meus-campos', [CampoController::class, 'meusIndex'])->name('meus-campos.index');
    Route::get('meus-campos/novo', [CampoController::class, 'create'])->name('meus-campos.create');
    Route::post('meus-campos', [CampoController::class, 'store'])->name('meus-campos.store');
    Route::get('meus-campos/{campo}/editar', [CampoController::class, 'edit'])->name('meus-campos.edit');
    Route::put('meus-campos/{campo}', [CampoController::class, 'update'])->name('meus-campos.update');
    Route::delete('meus-campos/{campo}', [CampoController::class, 'destroy'])->name('meus-campos.destroy');

    Route::get('partidas', [PartidaController::class, 'index'])->name('partidas.index');
    Route::get('patotas/{patota}/partidas/nova', [PartidaController::class, 'create'])->name('partidas.create');
    Route::post('patotas/{patota}/partidas', [PartidaController::class, 'store'])->name('partidas.store');
    Route::get('partidas/{partida}', [PartidaController::class, 'show'])->name('partidas.show');
    Route::get('partidas/{partida}/editar', [PartidaController::class, 'edit'])->name('partidas.edit');
    Route::put('partidas/{partida}', [PartidaController::class, 'update'])->name('partidas.update');
    Route::post('partidas/{partida}/cancelar', [PartidaController::class, 'cancelar'])->name('partidas.cancelar');
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

Route::middleware(['auth', 'cache.headers:no_store;private'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
