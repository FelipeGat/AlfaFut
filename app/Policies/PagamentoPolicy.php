<?php

namespace App\Policies;

use App\Models\Pagamento;
use App\Models\User;

/**
 * Sem o before() de PolicyDeTurma: nem o admin global dá baixa no pagamento de outra pessoa.
 */
class PagamentoPolicy
{
    public function quitar(User $user, Pagamento $pagamento): bool
    {
        return $pagamento->user_id === $user->id
            && $pagamento->despesa?->patota !== null
            && $user->can('view', $pagamento->despesa->patota);
    }
}
