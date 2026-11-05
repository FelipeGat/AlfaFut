<?php

namespace Tests\Feature\Seguranca;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChecarProducaoTest extends TestCase
{
    public function test_checar_producao_aceita_configuracao_correta(): void
    {
        $this->configurarProducaoCorreta();

        $this->artisan('alfafut:checar-producao')->assertSuccessful();
    }

    #[DataProvider('configuracoesInseguras')]
    public function test_checar_producao_recusa_e_diz_o_motivo(array $config, string $motivo): void
    {
        $this->configurarProducaoCorreta();
        config($config);

        $this->artisan('alfafut:checar-producao')
            ->expectsOutputToContain($motivo)
            ->assertFailed();
    }

    public static function configuracoesInseguras(): array
    {
        return [
            'ambiente local' => [['app.env' => 'local'], 'APP_ENV'],
            'depuração ligada' => [['app.debug' => true], 'APP_DEBUG'],
            'sem chave' => [['app.key' => ''], 'APP_KEY'],
            'sqlite' => [['database.default' => 'sqlite'], 'SQLite'],
            'e-mail no log' => [['mail.default' => 'log'], 'MAIL_MAILER'],
            'remetente padrão' => [['mail.from.address' => 'hello@example.com'], 'MAIL_FROM_ADDRESS'],
        ];
    }

    private function configurarProducaoCorreta(): void
    {
        config([
            'app.env' => 'production',
            'app.debug' => false,
            'app.key' => 'base64:chave-de-teste',
            'database.default' => 'mysql',
            'mail.default' => 'smtp',
            'mail.from.address' => 'nao-responda@alfafut.test',
        ]);
    }
}
