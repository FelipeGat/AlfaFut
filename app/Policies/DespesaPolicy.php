<?php

namespace App\Policies;

use App\Models\Despesa;
use App\Models\User;

class DespesaPolicy extends PolicyDeTurma
{
    public function view(User $user, Despesa $despesa): bool
    {
        return $despesa->patota !== null && $user->can('view', $despesa->patota);
    }

    public function update(User $user, Despesa $despesa): bool
    {
        return $despesa->patota !== null
            && ! $despesa->patota->trashed()
            && $despesa->patota->temOrganizador($user);
    }

    public function delete(User $user, Despesa $despesa): bool
    {
        return $this->update($user, $despesa);
    }
}
