<?php

namespace App\Policies;

use App\Models\Patota;
use App\Models\User;

class PatotaPolicy extends PolicyDeTurma
{
    public function view(User $user, Patota $patota): bool
    {
        return $patota->criador_id === $user->id || $patota->temMembroAtivo($user);
    }

    public function participar(User $user, Patota $patota): bool
    {
        return $this->view($user, $patota);
    }

    public function organizar(User $user, Patota $patota): bool
    {
        return $patota->temOrganizador($user);
    }

    public function gerenciarMembros(User $user, Patota $patota): bool
    {
        return $patota->criador_id === $user->id
            || ($patota->responsavel_id === $user->id && $patota->temMembroAtivo($user));
    }

    public function update(User $user, Patota $patota): bool
    {
        return $patota->criador_id === $user->id;
    }

    public function delete(User $user, Patota $patota): bool
    {
        return $patota->criador_id === $user->id;
    }
}
