<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $patotas = $user->patotas()
            ->withCount('membrosAtivos as total_membros')
            ->orderBy('nome')
            ->get();

        $proximasPartidas = Partida::query()
            ->whereIn('patota_id', $patotas->pluck('id'))
            ->proximas()
            ->with(['patota', 'local', 'confirmacoes'])
            ->limit(5)
            ->get();

        $totalConfirmadas = $proximasPartidas
            ->filter(fn ($p) => $p->confirmacoes
                ->where('user_id', $user->id)
                ->where('status', 'confirmado')
                ->count() > 0)
            ->count();

        return view('dashboard', [
            'patotas' => $patotas,
            'proximasPartidas' => $proximasPartidas,
            'totalConfirmadas' => $totalConfirmadas,
        ]);
    }
}
