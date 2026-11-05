<?php

namespace App\Policies;

use App\Models\Campo;
use App\Models\User;

class CampoPolicy extends PolicyDeTurma
{
    public function view(User $user, Campo $campo): bool
    {
        return $campo->ativo || $campo->dono_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->tipo_usuario === 'dono_campo';
    }

    public function update(User $user, Campo $campo): bool
    {
        return $campo->dono_id === $user->id;
    }

    public function delete(User $user, Campo $campo): bool
    {
        return $campo->dono_id === $user->id;
    }
}
