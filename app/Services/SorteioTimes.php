<?php

namespace App\Services;

use App\Exceptions\RegraDaTurma;
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
            $partida = Partida::whereKey($partida->getKey())->lockForUpdate()->firstOrFail();
            $this->exigirPodeSortear($partida);

            $confirmados = $partida->confirmados()->with('user')->get();

            if ($confirmados->count() < 2) {
                throw new RegraDaTurma('É preciso ao menos 2 confirmados para sortear.');
            }

            $partida->times()->delete();

            // Nunca mais times que jogadores: nenhum time fica vazio
            $quantidade = min($partida->patota->quantidade_times, $confirmados->count());

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

            $tamanhoPorTime = $times->mapWithKeys(fn ($t) => [$t->id => 0])->all();
            $somaPorTime = $tamanhoPorTime;

            // 1. Distribuir goleiros (um por time, se houver)
            foreach ($goleiros as $i => $goleiro) {
                $time = $times[$i % $times->count()];
                TimeJogador::create([
                    'time_id' => $time->id,
                    'user_id' => $goleiro->id,
                    'posicao' => 'goleiro',
                ]);
                $tamanhoPorTime[$time->id]++;
                $somaPorTime[$time->id] += self::PESO_NIVEL[$goleiro->nivel_habilidade] ?? 2;
            }

            // 2. Demais jogadores, do maior nivel ao menor: primeiro o time com menos gente, depois o de menor soma
            foreach ($linha as $jogador) {
                $timeMenor = $times
                    ->sortBy([
                        fn ($a, $b) => $tamanhoPorTime[$a->id] <=> $tamanhoPorTime[$b->id],
                        fn ($a, $b) => $somaPorTime[$a->id] <=> $somaPorTime[$b->id],
                    ])
                    ->first();
                TimeJogador::create([
                    'time_id' => $timeMenor->id,
                    'user_id' => $jogador->id,
                    'posicao' => $jogador->posicao_preferida,
                ]);
                $tamanhoPorTime[$timeMenor->id]++;
                $somaPorTime[$timeMenor->id] += self::PESO_NIVEL[$jogador->nivel_habilidade] ?? 2;
            }

            return $times->map(fn ($t) => $t->load('jogadores'));
        });
    }

    private function exigirPodeSortear(Partida $partida): void
    {
        if ($partida->turmaArquivada()) {
            throw new RegraDaTurma('Esta turma está arquivada.');
        }

        if ($partida->status === 'cancelada') {
            throw new RegraDaTurma('Esta partida foi cancelada.');
        }

        if ($partida->comecou()) {
            throw new RegraDaTurma('Esta partida já começou. Os times não podem mais ser sorteados.');
        }
    }

    private function corPorIndice(int $i): string
    {
        return ['branco', 'preto', 'azul', 'vermelho', 'amarelo', 'verde'][$i] ?? 'cinza';
    }
}
