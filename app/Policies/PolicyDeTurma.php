<?php

namespace App\Policies;

use App\Models\User;

abstract class PolicyDeTurma
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }
}
