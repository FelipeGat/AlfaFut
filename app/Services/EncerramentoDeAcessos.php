<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Trocar a senha expulsa quem estava usando a conta, pela API ou por outro navegador.
 */
class EncerramentoDeAcessos
{
    /** @param string|null $sessaoAtual sessão que continua conectada; sem ela, todas caem */
    public function encerrar(User $user, ?string $sessaoAtual = null): void
    {
        $user->tokens()->delete();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->when($sessaoAtual, fn ($sessoes) => $sessoes->where('id', '!=', $sessaoAtual))
            ->delete();

        $user->forceFill(['remember_token' => Str::random(60)])->save();
    }
}
