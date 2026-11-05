<?php

namespace App\Policies;

use App\Models\Mensagem;
use App\Models\User;

class MensagemPolicy extends PolicyDeTurma
{
    public function delete(User $user, Mensagem $mensagem): bool
    {
        $patota = $mensagem->patota;

        if ($patota === null) {
            return false;
        }

        return $patota->temOrganizador($user)
            || ($mensagem->autor_id === $user->id && $patota->temMembroAtivo($user));
    }
}
