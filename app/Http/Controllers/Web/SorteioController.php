<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Partida;
use App\Services\SorteioTimes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SorteioController extends Controller
{
    public function __invoke(Request $request, Partida $partida, SorteioTimes $sorteio): RedirectResponse
    {
        $papel = $partida->patota->membrosAtivos()
            ->where('users.id', $request->user()->id)
            ->value('patota_membros.papel');

        abort_unless(in_array($papel, ['administrador', 'organizador']), 403);

        if ($partida->confirmados()->count() < 2) {
            return back()->with('status', 'E preciso ao menos 2 confirmados para sortear.');
        }

        $sorteio->sortear($partida);

        return back()->with('status', 'Times sorteados! Verifique a escalacao abaixo.');
    }
}
