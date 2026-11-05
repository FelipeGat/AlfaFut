<?php

namespace App\Policies;

use App\Models\Pagamento;
use App\Models\User;

/**
 * Sem o before() de PolicyDeTurma: nem o admin global dá baixa no pagamento de outra pessoa.
 * Quem organiza só registra por quem não é mais membro ativo, que não tem como fazer isso sozinho.
 */
class PagamentoPolicy
{
    public function quitar(User $user, Pagamento $pagamento): bool
    {
        $turma = $pagamento->despesa?->patota;

        if ($turma === null) {
            return false;
        }

        if ($pagamento->user_id === $user->id) {
            return $user->can('view', $turma);
        }

        return $turma->temOrganizador($user) && ! $turma->temMembroAtivo($pagamento->user);
    }
}
