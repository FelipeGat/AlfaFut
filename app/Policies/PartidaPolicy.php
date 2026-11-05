<?php

namespace App\Policies;

use App\Models\Partida;
use App\Models\User;

class PartidaPolicy extends PolicyDeTurma
{
    public function view(User $user, Partida $partida): bool
    {
        return $partida->patota !== null && $user->can('view', $partida->patota);
    }

    public function participar(User $user, Partida $partida): bool
    {
        return $this->view($user, $partida);
    }

    public function controlar(User $user, Partida $partida): bool
    {
        return $partida->patota?->temOrganizador($user) ?? false;
    }
}
