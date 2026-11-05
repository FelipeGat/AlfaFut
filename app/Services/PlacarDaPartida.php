<?php

namespace App\Services;

use App\Exceptions\RegraDaTurma;
use App\Models\EventoPartida;
use App\Models\Partida;
use App\Models\TimeJogador;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Ordem da partida e placar, iguais no site e na API.
 * Repetir um pedido não muda nada; gols de time e jogador vêm sempre dos eventos.
 */
class PlacarDaPartida
{
    /** Limite das colunas de gols. */
    private const LIMITE_GOLS = 255;

    public function iniciar(Partida $partida, User $quem): string
    {
        return $this->comTrava($partida, function (Partida $travada) use ($quem) {
            $this->exigirNaoEncerrada($travada);

            if ($travada->emAndamento()) {
                return 'A partida já está em andamento.';
            }

            if ($travada->pausada()) {
                $travada->update(['iniciada_em' => now(), 'pausada_em' => null]);
                $this->registrar($travada, $quem, EventoPartida::TIPO_RETOMADA);

                return 'Partida retomada.';
            }

            if ($travada->times()->count() < 2) {
                throw new RegraDaTurma('Sorteie os times antes de iniciar a partida.');
            }

            $travada->update([
                'iniciada_em' => now(),
                'tempo_acumulado_segundos' => 0,
                'placar_a' => 0,
                'placar_b' => 0,
                'status' => 'em_andamento',
            ]);
            $this->registrar($travada, $quem, EventoPartida::TIPO_INICIO);

            return 'Partida iniciada!';
        });
    }

    public function pausar(Partida $partida, User $quem): string
    {
        return $this->comTrava($partida, function (Partida $travada) use ($quem) {
            $this->exigirNaoEncerrada($travada);

            if ($travada->pausada()) {
                return 'A partida já está pausada.';
            }

            if (! $travada->emAndamento()) {
                throw new RegraDaTurma('A partida ainda não começou.');
            }

            $travada->update([
                'pausada_em' => now(),
                'tempo_acumulado_segundos' => $travada->tempoAtualSegundos(),
            ]);
            $this->registrar($travada, $quem, EventoPartida::TIPO_PAUSA);

            return 'Partida pausada.';
        });
    }

    public function finalizar(Partida $partida, User $quem): string
    {
        return $this->comTrava($partida, function (Partida $travada) use ($quem) {
            $this->exigirAtiva($travada);

            if ($travada->finalizada()) {
                return 'Esta partida já foi encerrada.';
            }

            if (! $travada->iniciada_em) {
                throw new RegraDaTurma('A partida ainda não começou.');
            }

            $travada->update([
                'tempo_acumulado_segundos' => $travada->tempoAtualSegundos(),
                'finalizada_em' => now(),
                'status' => 'finalizada',
            ]);
            $this->registrar($travada, $quem, EventoPartida::TIPO_FIM);

            return 'Partida encerrada!';
        });
    }

    /** @param array{time_id: int|string, jogador_id?: int|string|null, tipo?: string|null, observacao?: string|null} $dados */
    public function registrarGol(Partida $partida, User $quem, array $dados): string
    {
        return $this->comTrava($partida, function (Partida $travada) use ($quem, $dados) {
            $this->exigirEmJogo($travada);

            $time = $travada->times()->findOrFail($dados['time_id']);
            $tipo = $dados['tipo'] ?? EventoPartida::TIPO_GOL;
            $jogadorId = $dados['jogador_id'] ?? null;

            if ($jogadorId !== null) {
                $this->exigirJogadorDoLance($travada, $time->id, (int) $jogadorId, $tipo);
            }

            if ($time->gols >= self::LIMITE_GOLS) {
                throw new RegraDaTurma('Este time chegou ao limite de gols do placar.');
            }

            EventoPartida::create([
                'partida_id' => $travada->id,
                'time_id' => $time->id,
                'jogador_id' => $jogadorId,
                'tipo' => $tipo,
                'minuto' => intdiv($travada->tempoAtualSegundos(), 60),
                'observacao' => $dados['observacao'] ?? null,
                'registrado_por_id' => $quem->id,
            ]);

            $this->recalcular($travada);

            return 'Gol registrado!';
        });
    }

    public function removerGol(Partida $partida, EventoPartida $evento): string
    {
        return $this->comTrava($partida, function (Partida $travada) use ($evento) {
            $this->exigirEmJogo($travada);

            if (! in_array($evento->tipo, [EventoPartida::TIPO_GOL, EventoPartida::TIPO_GOL_CONTRA], true)) {
                throw new RegraDaTurma('Só gols podem ser removidos.');
            }

            $evento->delete();
            $this->recalcular($travada);

            return 'Gol removido.';
        });
    }

    private function comTrava(Partida $partida, Closure $acao): string
    {
        return DB::transaction(fn () => $acao(Partida::whereKey($partida->getKey())->lockForUpdate()->firstOrFail()));
    }

    private function exigirAtiva(Partida $partida): void
    {
        if ($partida->turmaArquivada()) {
            throw new RegraDaTurma('Esta turma está arquivada.');
        }

        if ($partida->status === 'cancelada') {
            throw new RegraDaTurma('Esta partida foi cancelada.');
        }
    }

    private function exigirNaoEncerrada(Partida $partida): void
    {
        $this->exigirAtiva($partida);

        if ($partida->finalizada()) {
            throw new RegraDaTurma('Esta partida já foi encerrada.');
        }
    }

    private function exigirEmJogo(Partida $partida): void
    {
        $this->exigirNaoEncerrada($partida);

        if (! $partida->iniciada_em) {
            throw new RegraDaTurma('A partida ainda não começou.');
        }
    }

    private function exigirJogadorDoLance(Partida $partida, int $timeId, int $jogadorId, string $tipo): void
    {
        $time = TimeJogador::whereIn('time_id', $partida->times()->select('id'))
            ->where('user_id', $jogadorId)
            ->value('time_id');

        if ($time === null) {
            throw new RegraDaTurma('Este jogador não está em nenhum time desta partida.');
        }

        if ($tipo === EventoPartida::TIPO_GOL && $time !== $timeId) {
            throw new RegraDaTurma('Este jogador não é deste time.');
        }

        if ($tipo === EventoPartida::TIPO_GOL_CONTRA && $time === $timeId) {
            throw new RegraDaTurma('O gol contra é do jogador do outro time.');
        }
    }

    private function recalcular(Partida $partida): void
    {
        $eventos = $partida->eventos()->whereIn('tipo', [EventoPartida::TIPO_GOL, EventoPartida::TIPO_GOL_CONTRA])->get();
        $times = $partida->times()->orderBy('id')->get();

        foreach ($times as $time) {
            $doTime = $eventos->where('time_id', $time->id);
            $time->update(['gols' => $doTime->count()]);

            foreach ($time->escalacao as $vaga) {
                $vaga->update(['gols' => $doTime->where('tipo', EventoPartida::TIPO_GOL)->where('jogador_id', $vaga->user_id)->count()]);
            }
        }

        $partida->update([
            'placar_a' => $times->get(0)?->gols ?? 0,
            'placar_b' => $times->get(1)?->gols ?? 0,
        ]);
    }

    private function registrar(Partida $partida, User $quem, string $tipo): void
    {
        EventoPartida::create([
            'partida_id' => $partida->id,
            'tipo' => $tipo,
            'minuto' => intdiv($partida->tempoAtualSegundos(), 60),
            'registrado_por_id' => $quem->id,
        ]);
    }
}
