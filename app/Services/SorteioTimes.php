<?php

namespace App\Services;

use App\Models\Partida;
use App\Models\Time;
use App\Models\TimeJogador;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SorteioTimes
{
    private const PESO_NIVEL = [
        'iniciante' => 1,
        'intermediario' => 2,
        'avancado' => 3,
    ];

    public function sortear(Partida $partida): Collection
    {
        return DB::transaction(function () use ($partida) {
            $partida->times()->delete();

            $confirmados = $partida->confirmados()->with('user')->get();
            $patota = $partida->patota;
            $quantidade = $patota->quantidade_times;

            // Sorteia clubes ficticios distintos para cada time
            $clubesDisponiveis = collect(config('clubes.clubes'))->shuffle()->take($quantidade);

            $times = collect();
            foreach (range(0, $quantidade - 1) as $i) {
                $clube = $clubesDisponiveis[$i];
                $times->push(Time::create([
                    'partida_id' => $partida->id,
                    'nome' => $clube['nome'],
                    'cor' => $clube['cor'],
                    'brasao' => $clube['brasao'],
                    'clube_codigo' => $clube['codigo'],
                ]));
            }

            $jogadores = $confirmados
                ->map(fn ($c) => $c->user)
                ->shuffle()
                ->sortByDesc(fn ($u) => self::PESO_NIVEL[$u->nivel_habilidade] ?? 2)
                ->values();

            $goleiros = $jogadores->where('posicao_preferida', 'goleiro')->values();
            $linha = $jogadores->where('posicao_preferida', '!=', 'goleiro')->values();

            $somaPorTime = $times->mapWithKeys(fn ($t) => [$t->id => 0]);

            // 1. Distribuir goleiros (um por time, se houver)
            foreach ($goleiros as $i => $goleiro) {
                $time = $times[$i % $times->count()];
                TimeJogador::create([
                    'time_id' => $time->id,
                    'user_id' => $goleiro->id,
                    'posicao' => 'goleiro',
                ]);
                $somaPorTime[$time->id] += self::PESO_NIVEL[$goleiro->nivel_habilidade] ?? 2;
            }

            // 2. Snake draft pelos demais (jogadores ja ordenados desc por nivel)
            foreach ($linha as $jogador) {
                $timeMenorSoma = $times->sortBy(fn ($t) => $somaPorTime[$t->id])->first();
                TimeJogador::create([
                    'time_id' => $timeMenorSoma->id,
                    'user_id' => $jogador->id,
                    'posicao' => $jogador->posicao_preferida,
                ]);
                $somaPorTime[$timeMenorSoma->id] += self::PESO_NIVEL[$jogador->nivel_habilidade] ?? 2;
            }

            return $times->map(fn ($t) => $t->load('jogadores'));
        });
    }

    private function corPorIndice(int $i): string
    {
        return ['branco', 'preto', 'azul', 'vermelho', 'amarelo', 'verde'][$i] ?? 'cinza';
    }
}
