<?php

namespace Tests\Feature\Seguranca;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class SeedEProducaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_de_demonstracao_e_recusado_em_producao(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(RuntimeException::class);
        (new DatabaseSeeder)->setContainer($this->app)->run();
    }

    public function test_producao_nao_cria_usuario_ao_migrar(): void
    {
        $this->assertSame(0, User::count());
    }

    public function test_seed_local_so_tem_contas_ficticias_e_ja_confirmadas(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, User::count());
        $this->assertSame(0, User::where('email', 'not like', '%@alfafut.test')->count());
        $this->assertSame(0, User::whereNull('email_verified_at')->count());
        $this->assertSame(1, User::where('role', 'admin')->count());
    }

    public function test_seed_gera_senha_nova_a_cada_execucao(): void
    {
        $this->seed(DatabaseSeeder::class);
        $antes = User::where('email', 'admin@alfafut.test')->value('password');

        $this->seed(DatabaseSeeder::class);

        $this->assertNotSame($antes, User::where('email', 'admin@alfafut.test')->value('password'));
    }

    public function test_arquivos_versionados_nao_tem_dado_pessoal_nem_atalho_de_login(): void
    {
        $this->assertStringNotContainsString('_demo-login', file_get_contents(base_path('routes/web.php')));

        foreach (['database/seeders/PatotaSeeder.php', 'README.md', 'docs/09-api.md'] as $arquivo) {
            $conteudo = file_get_contents(base_path($arquivo));

            $this->assertStringNotContainsString('@gmail.com', $conteudo, $arquivo);
            $this->assertDoesNotMatchRegularExpression("/Hash::make\('/", $conteudo, $arquivo);
        }
    }

    public function test_criar_admin_com_senha_escolhida_no_ato(): void
    {
        $this->artisan('alfafut:criar-admin', ['email' => 'operador@alfafut.test', '--nome' => 'Operador'])
            ->expectsQuestion('Senha', 'senha-bem-grande-mesmo')
            ->expectsQuestion('Repita a senha', 'senha-bem-grande-mesmo')
            ->assertSuccessful();

        $admin = User::where('email', 'operador@alfafut.test')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertTrue(Hash::check('senha-bem-grande-mesmo', $admin->password));
    }

    public function test_criar_admin_recusa_senha_curta_diferente_ou_email_repetido(): void
    {
        $this->artisan('alfafut:criar-admin', ['email' => 'a@alfafut.test'])
            ->expectsQuestion('Senha', 'curta')
            ->assertFailed();

        $this->artisan('alfafut:criar-admin', ['email' => 'a@alfafut.test'])
            ->expectsQuestion('Senha', 'senha-bem-grande-mesmo')
            ->expectsQuestion('Repita a senha', 'outra-senha-bem-grande')
            ->assertFailed();

        User::factory()->create(['email' => 'existe@alfafut.test']);
        $this->artisan('alfafut:criar-admin', ['email' => 'existe@alfafut.test'])->assertFailed();

        $this->assertSame(0, User::where('role', 'admin')->count());
    }
}
