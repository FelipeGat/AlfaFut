<?php

namespace App\Services;

use Illuminate\Support\Facades\Password;

use function Illuminate\Support\defer;

/**
 * Site e API respondem igual para quem tem conta, quem não tem e quem pediu há pouco,
 * para a recuperação não revelar quem usa o AlfaFut.
 */
class RecuperacaoDeSenha
{
    public const MENSAGEM = 'Se houver uma conta com este e-mail, enviamos um link para redefinir a senha.';

    /** O envio roda depois da resposta, para o tempo de resposta também não revelar a conta. */
    public function pedirLink(string $email): void
    {
        defer(fn () => rescue(fn () => Password::sendResetLink(['email' => mb_strtolower($email)])));
    }
}
