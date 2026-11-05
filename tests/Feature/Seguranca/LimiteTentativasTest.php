<?php

namespace Tests\Feature\Seguranca;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LimiteTentativasTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TrustProxies::flushState();

        parent::tearDown();
    }

    #[DataProvider('rotasDeLogin')]
    public function test_sexta_senha_errada_seguida_e_barrada(string $rota): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $tentativa) {
            $this->postJson($rota, ['email' => $user->email, 'password' => 'errada'])->assertStatus(422);
        }

        $this->postJson($rota, ['email' => $user->email, 'password' => 'errada'])
            ->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertJsonPath('message', fn (string $m) => str_starts_with($m, 'Muitas tentativas. Tente de novo em'));
    }

    public static function rotasDeLogin(): array
    {
        return ['api' => ['/api/v1/auth/login'], 'web' => ['/login']];
    }

    public function test_formulario_de_login_mostra_o_limite_em_portugues(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $tentativa) {
            $this->post('/login', ['email' => $user->email, 'password' => 'errada']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'errada'])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertStringStartsWith('Muitas tentativas', session('errors')->first('email'));
    }

    public function test_outra_pessoa_na_mesma_rede_continua_entrando(): void
    {
        $bloqueado = User::factory()->create();
        $colega = User::factory()->create();

        foreach (range(1, 6) as $tentativa) {
            $this->postJson('/api/v1/auth/login', ['email' => $bloqueado->email, 'password' => 'errada']);
        }

        $this->postJson('/api/v1/auth/login', ['email' => $colega->email, 'password' => 'password'])->assertOk();
    }

    #[DataProvider('rotasDeCadastro')]
    public function test_cadastro_limitado_por_origem(string $rota): void
    {
        foreach (range(1, 30) as $tentativa) {
            $this->postJson($rota, [])->assertStatus(422);
        }

        $this->postJson($rota, [])->assertStatus(429);
    }

    public static function rotasDeCadastro(): array
    {
        return ['api' => ['/api/v1/auth/registrar'], 'web' => ['/register']];
    }

    #[DataProvider('rotasDeRecuperacao')]
    public function test_recuperacao_limitada_sem_enviar_email(string $rota): void
    {
        Notification::fake();
        $user = User::factory()->create();

        foreach (range(1, 3) as $tentativa) {
            $this->postJson($rota, ['email' => $user->email]);
        }

        $this->postJson($rota, ['email' => $user->email])->assertStatus(429);

        Notification::assertCount(1);
    }

    public static function rotasDeRecuperacao(): array
    {
        return ['api' => ['/api/v1/auth/esqueci-senha'], 'web' => ['/forgot-password']];
    }

    public function test_entrar_por_codigo_limitado_por_usuario(): void
    {
        $user = User::factory()->create();
        $colega = User::factory()->create();

        foreach (range(1, 10) as $tentativa) {
            $this->actingAs($user, 'sanctum')->postJson('/api/v1/patotas/entrar', ['codigo_convite' => 'NAOEXIST'])->assertNotFound();
        }

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/patotas/entrar', ['codigo_convite' => 'NAOEXIST'])->assertStatus(429);
        $this->actingAs($colega, 'sanctum')->postJson('/api/v1/patotas/entrar', ['codigo_convite' => 'NAOEXIST'])->assertNotFound();
    }

    public function test_cabecalho_forjado_nao_escapa_do_limite(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $tentativa) {
            $this->withHeader('X-Forwarded-For', "198.51.100.{$tentativa}")
                ->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'errada'])
                ->assertStatus(422);
        }

        $this->withHeader('X-Forwarded-For', '198.51.100.99')
            ->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'errada'])
            ->assertStatus(429);
    }

    public function test_atras_do_proxy_configurado_conta_a_origem_real(): void
    {
        config(['app.proxies_confiaveis' => '127.0.0.1']);
        $this->app->getProvider(AppServiceProvider::class)->boot();
        $user = User::factory()->create();

        foreach (range(1, 6) as $tentativa) {
            $this->withHeader('X-Forwarded-For', '198.51.100.1')
                ->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'errada']);
        }

        $this->withHeader('X-Forwarded-For', '198.51.100.1')
            ->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'errada'])
            ->assertStatus(429);

        $this->withHeader('X-Forwarded-For', '198.51.100.2')
            ->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk();
    }

    public function test_limite_com_campo_email_em_formato_estranho_nao_derruba(): void
    {
        $this->postJson('/api/v1/auth/login', ['email' => ['a', 'b'], 'password' => 'x'])->assertStatus(422);
    }
}
