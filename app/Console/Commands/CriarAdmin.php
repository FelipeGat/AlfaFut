<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CriarAdmin extends Command
{
    protected $signature = 'alfafut:criar-admin {email : E-mail da conta} {--nome=Administrador : Nome da pessoa}';

    protected $description = 'Cria uma conta de administrador da plataforma, pedindo a senha no ato';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $validacao = Validator::make(['email' => $email], ['email' => ['required', 'email', 'unique:users,email']]);

        if ($validacao->fails()) {
            $this->error('E-mail inválido ou já cadastrado.');

            return self::FAILURE;
        }

        $senha = (string) $this->secret('Senha');

        if (mb_strlen($senha) < 12) {
            $this->error('A senha precisa ter ao menos 12 caracteres.');

            return self::FAILURE;
        }

        if ($senha !== (string) $this->secret('Repita a senha')) {
            $this->error('As senhas não conferem.');

            return self::FAILURE;
        }

        (new User)->forceFill([
            'name' => $this->option('nome'),
            'email' => $email,
            'password' => $senha,
            'role' => 'admin',
            'email_verified_at' => now(),
        ])->save();

        $this->info("Administrador {$email} criado.");

        return self::SUCCESS;
    }
}
