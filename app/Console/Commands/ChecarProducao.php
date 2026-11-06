<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ChecarProducao extends Command
{
    protected $signature = 'alfafut:checar-producao';

    protected $description = 'Recusa subir em produção com configuração insegura; roda no script de entrada da imagem';

    public function handle(): int
    {
        $problemas = array_filter([
            config('app.env') !== 'production' ? 'APP_ENV precisa ser production.' : null,
            config('app.debug') ? 'APP_DEBUG precisa estar desligado.' : null,
            blank(config('app.key')) ? 'APP_KEY não foi definida.' : null,
            config('database.default') === 'sqlite' ? 'O banco de produção não pode ser SQLite: defina DB_CONNECTION=mysql.' : null,
            in_array(config('mail.default'), ['log', 'array'], true) ? 'Defina um envio real de e-mail em MAIL_MAILER: com log, os links ficariam no registro do servidor.' : null,
            config('mail.from.address') === 'hello@example.com' ? 'Defina MAIL_FROM_ADDRESS.' : null,
            blank(config('alfafut.responsavel.nome')) ? 'Defina ALFAFUT_RESPONSAVEL_NOME: a política de privacidade precisa dizer quem responde pelos dados.' : null,
            blank(config('alfafut.responsavel.contato_privacidade')) ? 'Defina ALFAFUT_CONTATO_PRIVACIDADE: a política de privacidade precisa de um contato.' : null,
        ]);

        foreach ($problemas as $problema) {
            $this->error($problema);
        }

        if ($problemas !== []) {
            return self::FAILURE;
        }

        $this->info('Configuração de produção conferida.');

        return self::SUCCESS;
    }
}
