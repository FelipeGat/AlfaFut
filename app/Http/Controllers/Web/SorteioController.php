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
        $this->authorize('controlar', $partida);

        $sorteio->sortear($partida);

        return back()->with('status', 'Times sorteados! Confira a escalação abaixo.');
    }
}
