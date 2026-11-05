<?php

namespace App\Services;

use App\Exceptions\RegraDaTurma;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use App\Models\TimeJogador;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Regras de quem tem vaga, iguais no site e na API.
 * Cada pedido trava a linha da partida, para simultâneos não passarem das vagas.
 */
class VagasDaPartida
{
    public function confirmar(Partida $partida, User $user, ?string $observacao = null): PartidaConfirmacao
    {
        return DB::transaction(function () use ($partida, $user, $observacao) {
            $travada = $this->travar($partida);

            if ($motivo = $travada->motivoParaNaoConfirmar()) {
                throw new RegraDaTurma($motivo);
            }

            $atual = $travada->confirmacoes()->where('user_id', $user->id)->first();

            if ($atual?->status === 'confirmado') {
                if ($observacao !== null) {
                    $atual->update(['observacao' => $observacao]);
                }

                return $atual;
            }

            $naFila = $travada->isCheia();

            if ($naFila && ! $travada->lista_espera_habilitada) {
                throw new RegraDaTurma('Esta partida está lotada e não tem lista de espera.');
            }

            return PartidaConfirmacao::updateOrCreate(
                ['partida_id' => $travada->id, 'user_id' => $user->id],
                [
                    'status' => 'confirmado',
                    'em_lista_espera' => $naFila,
                    'posicao_lista_espera' => $naFila ? $travada->listaEspera()->reorder()->max('posicao_lista_espera') + 1 : null,
                    'observacao' => $observacao,
                    'confirmado_em' => now(),
                ]
            );
        });
    }

    public function recusar(Partida $partida, User $user): void
    {
        DB::transaction(function () use ($partida, $user) {
            $travada = $this->travar($partida);

            if ($motivo = $travada->motivoParaNaoRecusar()) {
                throw new RegraDaTurma($motivo);
            }

            PartidaConfirmacao::updateOrCreate(
                ['partida_id' => $travada->id, 'user_id' => $user->id],
                ['status' => 'recusado', 'em_lista_espera' => false, 'posicao_lista_espera' => null]
            );

            $this->tirarDoTime($travada, $user);
            $this->andarFila($travada);
        });
    }

    public function cancelar(Partida $partida, User $user): void
    {
        DB::transaction(function () use ($partida, $user) {
            $travada = $this->travar($partida);

            if ($motivo = $travada->motivoParaNaoRecusar()) {
                throw new RegraDaTurma($motivo);
            }

            $travada->confirmacoes()->where('user_id', $user->id)->firstOrFail()->delete();

            $this->tirarDoTime($travada, $user);
            $this->andarFila($travada);
        });
    }

    /** Quem deixa a turma ou a conta perde a vaga nas partidas que ainda vão acontecer. */
    public function liberarNaTurma(Patota $patota, User $user): void
    {
        $partidas = $patota->partidas()
            ->whereIn('status', ['agendada', 'confirmada'])
            ->whereNull('iniciada_em')
            ->where('data_hora', '>=', now())
            ->whereHas('confirmacoes', fn ($q) => $q->where('user_id', $user->id))
            ->get();

        foreach ($partidas as $partida) {
            DB::transaction(function () use ($partida, $user) {
                $travada = $this->travar($partida);
                $travada->confirmacoes()->where('user_id', $user->id)->delete();
                $this->tirarDoTime($travada, $user);
                $this->andarFila($travada);
            });
        }
    }

    public function reorganizar(Partida $partida): void
    {
        DB::transaction(fn () => $this->andarFila($this->travar($partida)));
    }

    private function travar(Partida $partida): Partida
    {
        return Partida::whereKey($partida->getKey())->lockForUpdate()->firstOrFail();
    }

    private function tirarDoTime(Partida $partida, User $user): void
    {
        TimeJogador::whereIn('time_id', $partida->times()->select('id'))
            ->where('user_id', $user->id)
            ->delete();
    }

    /** Promove os primeiros enquanto houver vaga e renumera quem continua na fila. */
    private function andarFila(Partida $partida): void
    {
        $vagas = $partida->vagasDisponiveis();
        $posicao = 0;

        foreach ($partida->listaEspera()->orderBy('id')->get() as $confirmacao) {
            if ($vagas > 0) {
                $confirmacao->update(['em_lista_espera' => false, 'posicao_lista_espera' => null]);
                $vagas--;

                continue;
            }

            $confirmacao->update(['posicao_lista_espera' => ++$posicao]);
        }
    }
}
