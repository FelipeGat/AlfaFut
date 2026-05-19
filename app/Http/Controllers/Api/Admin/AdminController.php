<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartidaResource;
use App\Http\Resources\PatotaResource;
use App\Http\Resources\UsuarioResource;
use App\Models\Despesa;
use App\Models\Partida;
use App\Models\Patota;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminController extends Controller
{
    public function resumo(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_usuarios' => User::count(),
                'total_admins' => User::where('role', 'admin')->count(),
                'total_patotas' => Patota::count(),
                'total_partidas' => Partida::count(),
                'partidas_agendadas' => Partida::where('status', 'agendada')->count(),
                'partidas_em_andamento' => Partida::where('status', 'em_andamento')->count(),
                'partidas_finalizadas' => Partida::where('status', 'finalizada')->count(),
                'total_despesas' => Despesa::count(),
                'despesas_abertas' => Despesa::where('status', 'aberta')->count(),
                'valor_total_despesas' => (float) Despesa::sum('valor_total'),
            ],
        ]);
    }

    public function usuarios(): AnonymousResourceCollection
    {
        $users = User::orderBy('name')->get();
        return UsuarioResource::collection($users);
    }

    public function patotas(): AnonymousResourceCollection
    {
        $patotas = Patota::query()
            ->withCount('membrosAtivos as total_membros')
            ->with('criador')
            ->orderBy('nome')
            ->get();
        return PatotaResource::collection($patotas);
    }

    public function partidas(): AnonymousResourceCollection
    {
        $partidas = Partida::query()
            ->with(['patota', 'local', 'organizador', 'confirmacoes'])
            ->orderByDesc('data_hora')
            ->limit(100)
            ->get();
        return PartidaResource::collection($partidas);
    }

    /**
     * Partidas em andamento ou agendadas para hoje/futuro.
     */
    public function partidasAtivas(): AnonymousResourceCollection
    {
        $partidas = Partida::query()
            ->where(function ($q) {
                $q->where('status', 'em_andamento')
                  ->orWhere(function ($w) {
                      $w->whereIn('status', ['agendada', 'confirmada'])
                        ->where('data_hora', '>=', now()->startOfDay());
                  });
            })
            ->with(['patota', 'local', 'organizador', 'confirmacoes', 'times'])
            ->orderByDesc('iniciada_em')
            ->orderBy('data_hora')
            ->get();
        return PartidaResource::collection($partidas);
    }
}
