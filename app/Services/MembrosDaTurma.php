<?php

namespace App\Services;

use App\Exceptions\RegraDaTurma;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Regras de quem entra, sai e cuida da turma, iguais no site e na API.
 * Quem pode pedir cada ação é decidido em PatotaPolicy.
 */
class MembrosDaTurma
{
    public function __construct(
        private readonly VagasDaPartida $vagas,
        private readonly FinanceiroDaTurma $financeiro,
    ) {}

    public function porCodigo(string $codigo): Patota
    {
        return Patota::where('codigo_convite', strtoupper(trim($codigo)))->first()
            ?? throw new RegraDaTurma('Código não encontrado. Confira as letras e os números com quem convidou você.', 404);
    }

    /** @return bool se a pessoa passou a fazer parte agora (false: já fazia) */
    public function entrar(User $user, Patota $patota): bool
    {
        $vinculo = PatotaMembro::firstOrCreate(
            ['patota_id' => $patota->id, 'user_id' => $user->id],
            ['papel' => 'membro', 'status' => 'ativo', 'entrou_em' => now()],
        );

        if ($vinculo->wasRecentlyCreated) {
            return true;
        }

        if ($vinculo->status === 'removido') {
            throw new RegraDaTurma('Sua entrada nesta turma não está liberada. Fale com quem organiza.');
        }

        if ($vinculo->status === 'ativo') {
            return false;
        }

        $vinculo->update(['papel' => 'membro', 'status' => 'ativo', 'entrou_em' => now(), 'saiu_em' => null]);

        return true;
    }

    /** @return int o que a pessoa deixou pendente na turma, em centavos */
    public function sair(User $user, Patota $patota): int
    {
        if ($patota->criador_id === $user->id) {
            throw new RegraDaTurma('Quem criou a turma não pode sair dela. Se a turma acabou, arquive pela tela de editar.');
        }

        if (! $patota->temMembroAtivo($user)) {
            throw new RegraDaTurma('Você não faz parte desta turma.', 404);
        }

        $this->desligar($patota, $user, 'inativo');

        return $this->financeiro->pendenteDe($patota, $user);
    }

    public function remover(Patota $patota, User $quemPediu, User $membro): void
    {
        if ($membro->id === $quemPediu->id) {
            throw new RegraDaTurma('Para deixar a turma, use a opção de sair.');
        }

        $this->exigirMembroAtivoQueNaoSejaCriador($patota, $membro, 'remover');
        $this->desligar($patota, $membro, 'removido');
    }

    public function liberarVolta(Patota $patota, User $membro): void
    {
        $vinculo = $this->vinculo($patota, $membro);

        if ($vinculo?->status !== 'removido') {
            throw new RegraDaTurma('Esta pessoa não está entre os removidos da turma.');
        }

        $vinculo->update(['status' => 'inativo']);
    }

    public function mudarPapel(Patota $patota, User $membro, string $papel): void
    {
        $this->exigirMembroAtivoQueNaoSejaCriador($patota, $membro, 'mudar o papel de');

        if ($patota->responsavel_id === $membro->id) {
            throw new RegraDaTurma('Passe a responsabilidade para outro membro antes de mudar o papel desta pessoa.');
        }

        $this->vinculo($patota, $membro)->update(['papel' => $papel]);
    }

    public function tornarResponsavel(Patota $patota, User $membro): void
    {
        if (! $patota->temMembroAtivo($membro)) {
            throw new RegraDaTurma('Só um membro ativo da turma pode ser responsável.');
        }

        $patota->update(['responsavel_id' => $membro->id]);
    }

    private function exigirMembroAtivoQueNaoSejaCriador(Patota $patota, User $membro, string $acao): void
    {
        if ($patota->criador_id === $membro->id) {
            throw new RegraDaTurma("Não é possível {$acao} quem criou a turma.");
        }

        if (! $patota->temMembroAtivo($membro)) {
            throw new RegraDaTurma('Esta pessoa não é membro ativo da turma.');
        }
    }

    private function desligar(Patota $patota, User $membro, string $status): void
    {
        DB::transaction(function () use ($patota, $membro, $status) {
            $this->vinculo($patota, $membro)->update(['status' => $status, 'saiu_em' => now()]);

            if ($patota->responsavel_id === $membro->id) {
                $patota->update(['responsavel_id' => $patota->criador_id]);
            }

            $this->vagas->liberarNaTurma($patota, $membro);
        });
    }

    private function vinculo(Patota $patota, User $membro): ?PatotaMembro
    {
        return PatotaMembro::where('patota_id', $patota->id)->where('user_id', $membro->id)->first();
    }
}
