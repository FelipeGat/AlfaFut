<?php

namespace App\Services;

use App\Exceptions\RegraDaTurma;
use App\Models\Despesa;
use App\Models\MovimentoFinanceiro;
use App\Models\Pagamento;
use App\Models\Partida;
use App\Models\Patota;
use App\Models\User;
use App\Support\Dinheiro;
use Illuminate\Support\Facades\DB;

/**
 * Despesa, rateio e baixa, iguais no site e na API.
 * Calcula em centavos inteiros e registra quem fez o quê na mesma transação.
 */
class FinanceiroDaTurma
{
    /** @param array<string, mixed> $dados */
    public function criar(Patota $patota, User $quem, array $dados): Despesa
    {
        return DB::transaction(function () use ($patota, $quem, $dados) {
            $rateada = (bool) ($dados['rateada'] ?? false);
            $confirmados = [];

            if ($rateada) {
                $partida = $patota->partidas()->find($dados['partida_id'] ?? null)
                    ?? throw new RegraDaTurma('Para dividir entre os confirmados, escolha a partida da despesa.');
                $confirmados = $this->confirmadosDe($partida);

                if ($confirmados === []) {
                    throw new RegraDaTurma('Esta partida ainda não tem confirmados para dividir a despesa.');
                }
            }

            $despesa = $patota->despesas()->create([
                ...$dados,
                'criada_por_id' => $quem->id,
                'rateada' => $rateada,
                'status' => 'aberta',
            ]);

            if ($rateada) {
                $this->ratear($despesa, $confirmados);
            }

            $valor = Dinheiro::centavos($despesa->valor_total);
            $this->registrar($despesa, $quem, 'criou', 'Lançou a despesa de '.Dinheiro::formatar($valor).'.', $valor);

            return $despesa;
        });
    }

    /** @param array<string, mixed> $dados */
    public function alterar(Despesa $despesa, User $quem, array $dados): Despesa
    {
        return DB::transaction(function () use ($despesa, $quem, $dados) {
            $travada = $this->travar($despesa);
            $this->exigirAberta($travada);

            $antes = Dinheiro::centavos($travada->valor_total);
            $depois = array_key_exists('valor_total', $dados) ? Dinheiro::centavos($dados['valor_total']) : $antes;
            $pago = $travada->centavosPagos();

            if ($depois < $pago) {
                throw new RegraDaTurma('O valor não pode ficar abaixo do que já foi pago ('.Dinheiro::formatar($pago).').');
            }

            $travada->update($dados);

            if ($depois !== $antes && $travada->pagamentos()->exists()) {
                $confirmados = $travada->partida ? $this->confirmadosDe($travada->partida) : [];
                $this->ratear($travada, $confirmados ?: $travada->pagamentos()->orderBy('id')->pluck('user_id')->all());
            }

            $texto = $depois !== $antes
                ? 'Alterou o valor de '.Dinheiro::formatar($antes).' para '.Dinheiro::formatar($depois).'.'
                : 'Alterou os dados da despesa.';
            $this->registrar($travada, $quem, 'alterou', $texto, $depois);

            return $travada;
        });
    }

    public function refazerRateio(Despesa $despesa, User $quem): void
    {
        DB::transaction(function () use ($despesa, $quem) {
            $travada = $this->travar($despesa);
            $this->exigirAberta($travada);

            if (! $travada->rateada || ! $travada->partida) {
                throw new RegraDaTurma('Esta despesa não é dividida entre os confirmados de uma partida.');
            }

            $confirmados = $this->confirmadosDe($travada->partida);

            if ($confirmados === []) {
                throw new RegraDaTurma('A partida não tem confirmados. O rateio atual foi mantido.');
            }

            $this->ratear($travada, $confirmados);
            $this->registrar($travada, $quem, 'refez_rateio', 'Refez o rateio entre '.count($confirmados).' confirmados.');
        });
    }

    public function darBaixa(Pagamento $pagamento, User $quem, string|int|float $valor, string $forma): void
    {
        DB::transaction(function () use ($pagamento, $quem, $valor, $forma) {
            $parcela = Pagamento::whereKey($pagamento->getKey())->lockForUpdate()->firstOrFail();
            $despesa = $this->travar($parcela->despesa);
            $this->exigirAberta($despesa);

            $devido = Dinheiro::centavos($parcela->valor_devido);
            $pago = Dinheiro::centavos($parcela->valor_pago);
            $recebido = Dinheiro::centavos($valor);
            $saldo = $devido - $pago;

            if ($parcela->status === 'cancelado' || $saldo <= 0) {
                throw new RegraDaTurma('Esta parcela não tem saldo em aberto.');
            }

            if ($recebido > $saldo) {
                throw new RegraDaTurma('O saldo desta parcela é de '.Dinheiro::formatar($saldo).'.');
            }

            $parcela->update([
                'valor_pago' => Dinheiro::decimal($pago + $recebido),
                'data_pagamento' => now()->toDateString(),
                'forma_pagamento' => $forma,
                'status' => $this->situacaoDaParcela($devido, $pago + $recebido),
            ]);

            $nome = $parcela->user->nomeExibicao();
            $this->registrar($despesa, $quem, 'baixa', 'Registrou '.Dinheiro::formatar($recebido)." na parcela de {$nome}.", $recebido, $parcela);
            $this->fecharSeQuitada($despesa);
        });
    }

    public function marcarComoPaga(Despesa $despesa, User $quem): void
    {
        DB::transaction(function () use ($despesa, $quem) {
            $travada = $this->travar($despesa);
            $this->exigirAberta($travada);

            if ($travada->pagamentos()->exists()) {
                throw new RegraDaTurma('Despesa rateada fecha sozinha quando todas as parcelas são pagas.');
            }

            $travada->update(['status' => 'fechada']);
            $this->registrar($travada, $quem, 'marcou_paga', 'Marcou a despesa como paga.');
        });
    }

    public function cancelar(Despesa $despesa, User $quem): void
    {
        DB::transaction(function () use ($despesa, $quem) {
            $travada = $this->travar($despesa);
            $this->exigirAberta($travada);

            $travada->pagamentos()->whereIn('status', ['pendente', 'parcial'])->update(['status' => 'cancelado']);
            $travada->update(['status' => 'cancelada']);
            $this->registrar($travada, $quem, 'cancelou', 'Cancelou a despesa.');
        });
    }

    public function remover(Despesa $despesa, User $quem): void
    {
        DB::transaction(function () use ($despesa, $quem) {
            $travada = $this->travar($despesa);

            if ($travada->patota->trashed()) {
                throw new RegraDaTurma('Esta turma está arquivada.');
            }

            if ($travada->centavosPagos() > 0) {
                throw new RegraDaTurma('Esta despesa já tem pagamento registrado. Cancele em vez de remover.');
            }

            $this->registrar($travada, $quem, 'removeu', "Removeu a despesa \"{$travada->descricao}\".", Dinheiro::centavos($travada->valor_total));
            $travada->delete();
        });
    }

    /** O que a pessoa ainda deve nas despesas abertas da turma, em centavos. */
    public function pendenteDe(Patota $patota, User $user): int
    {
        return Pagamento::where('user_id', $user->id)
            ->whereIn('status', ['pendente', 'parcial'])
            ->whereHas('despesa', fn ($q) => $q->where('patota_id', $patota->id)->where('status', 'aberta'))
            ->get()
            ->sum(fn (Pagamento $p) => Dinheiro::centavos($p->valor_devido) - Dinheiro::centavos($p->valor_pago));
    }

    /**
     * Divide o total entre as pessoas informadas, mantendo o que já foi pago.
     * Quem pagou e saiu, ou pagou acima da cota, fica com o que pagou; o restante vai para os demais.
     *
     * @param  list<int>  $pessoas  na ordem em que confirmaram
     */
    private function ratear(Despesa $despesa, array $pessoas): void
    {
        $parcelas = $despesa->pagamentos()->get()->keyBy('user_id');
        $restante = Dinheiro::centavos($despesa->valor_total);

        foreach ($parcelas as $userId => $parcela) {
            if (in_array($userId, $pessoas)) {
                continue;
            }

            $pago = Dinheiro::centavos($parcela->valor_pago);

            if ($pago === 0) {
                $parcela->delete();

                continue;
            }

            $this->ajustar($parcela, $pago);
            $restante -= $pago;
        }

        $abertos = [];
        foreach ($pessoas as $userId) {
            $abertos[$userId] = Dinheiro::centavos($parcelas->get($userId)?->valor_pago);
        }

        do {
            $cota = $abertos === [] ? 0 : intdiv($restante, count($abertos));
            $acima = array_filter($abertos, fn (int $pago) => $pago > $cota);

            foreach ($acima as $userId => $pago) {
                $this->ajustar($parcelas[$userId], $pago);
                $restante -= $pago;
                unset($abertos[$userId]);
            }
        } while ($acima !== []);

        $valores = Dinheiro::dividir($restante, count($abertos));

        foreach (array_keys($abertos) as $i => $userId) {
            $parcela = $parcelas->get($userId);

            if ($valores[$i] === 0) {
                $parcela?->delete();

                continue;
            }

            if ($parcela) {
                $this->ajustar($parcela, $valores[$i]);

                continue;
            }

            Pagamento::create([
                'despesa_id' => $despesa->id,
                'user_id' => $userId,
                'valor_devido' => Dinheiro::decimal($valores[$i]),
                'data_vencimento' => $despesa->data_despesa,
                'status' => 'pendente',
            ]);
        }

        $this->fecharSeQuitada($despesa);
    }

    private function ajustar(Pagamento $parcela, int $devido): void
    {
        $parcela->update([
            'valor_devido' => Dinheiro::decimal($devido),
            'status' => $this->situacaoDaParcela($devido, Dinheiro::centavos($parcela->valor_pago)),
        ]);
    }

    private function situacaoDaParcela(int $devido, int $pago): string
    {
        return match (true) {
            $pago >= $devido => 'pago',
            $pago > 0 => 'parcial',
            default => 'pendente',
        };
    }

    private function fecharSeQuitada(Despesa $despesa): void
    {
        $emAberto = $despesa->pagamentos()->whereIn('status', ['pendente', 'parcial'])->exists();

        if (! $emAberto && $despesa->pagamentos()->exists()) {
            $despesa->update(['status' => 'fechada']);
        }
    }

    /** @return list<int> na ordem em que confirmaram */
    private function confirmadosDe(Partida $partida): array
    {
        return $partida->confirmados()->orderBy('confirmado_em')->orderBy('id')->pluck('user_id')->all();
    }

    private function travar(Despesa $despesa): Despesa
    {
        return Despesa::whereKey($despesa->getKey())->lockForUpdate()->firstOrFail();
    }

    private function exigirAberta(Despesa $despesa): void
    {
        if ($despesa->patota->trashed()) {
            throw new RegraDaTurma('Esta turma está arquivada.');
        }

        if ($despesa->status === 'cancelada') {
            throw new RegraDaTurma('Esta despesa foi cancelada.');
        }

        if ($despesa->status === 'fechada') {
            throw new RegraDaTurma('Esta despesa já está fechada.');
        }
    }

    private function registrar(Despesa $despesa, User $quem, string $acao, string $descricao, ?int $centavos = null, ?Pagamento $parcela = null): void
    {
        MovimentoFinanceiro::create([
            'patota_id' => $despesa->patota_id,
            'despesa_id' => $despesa->id,
            'pagamento_id' => $parcela?->id,
            'user_id' => $quem->id,
            'acao' => $acao,
            'descricao' => $descricao,
            'valor_centavos' => $centavos,
        ]);
    }
}
