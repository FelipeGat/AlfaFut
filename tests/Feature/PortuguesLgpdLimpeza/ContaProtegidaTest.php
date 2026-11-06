<?php

namespace Tests\Feature\PortuguesLgpdLimpeza;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ContaProtegidaTest extends TestCase
{
    use RefreshDatabase;

    private const SENHA_NOVA = 'senha-nova-12345';

    public function test_recuperacao_no_site_responde_igual_com_e_sem_conta(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $comConta = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);
        $semConta = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'ninguem@alfafut.test']);

        $comConta->assertRedirect('/forgot-password')->assertSessionHasNoErrors();
        $semConta->assertRedirect('/forgot-password')->assertSessionHasNoErrors();
        $this->assertSame(session('status'), $this->mensagemNoSite($user->email));
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_recuperacao_na_api_responde_igual_com_e_sem_conta(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $comConta = $this->postJson('/api/v1/auth/esqueci-senha', ['email' => $user->email]);
        $semConta = $this->postJson('/api/v1/auth/esqueci-senha', ['email' => 'ninguem@alfafut.test']);

        $comConta->assertOk();
        $semConta->assertOk();
        $this->assertSame($comConta->json(), $semConta->json());
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_pedido_repetido_nao_revela_o_bloqueio_por_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $primeiro = $this->postJson('/api/v1/auth/esqueci-senha', ['email' => $user->email]);
        $segundo = $this->postJson('/api/v1/auth/esqueci-senha', ['email' => $user->email]);

        $segundo->assertOk();
        $this->assertSame($primeiro->json(), $segundo->json());
    }

    public function test_email_com_maiusculas_recebe_o_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'jogador@alfafut.test']);

        $this->postJson('/api/v1/auth/esqueci-senha', ['email' => 'JOGADOR@alfafut.test'])->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_conta_excluida_nao_recebe_nada_e_a_resposta_e_a_mesma(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'conta-excluida-1@alfafut.invalid', 'excluida_em' => now()]);

        $this->postJson('/api/v1/auth/esqueci-senha', ['email' => 'antigo@alfafut.test'])->assertOk();

        Notification::assertNothingSent();
    }

    public function test_tela_de_nova_senha_nao_diferencia_token_errado_de_conta_inexistente(): void
    {
        $user = User::factory()->create();

        $tentar = fn (string $email) => $this->from('/reset-password/x')->post('/reset-password', [
            'token' => 'token-errado',
            'email' => $email,
            'password' => self::SENHA_NOVA,
            'password_confirmation' => self::SENHA_NOVA,
        ])->baseResponse->getSession()->get('errors')->first('email');

        $this->assertSame($tentar($user->email), $tentar('ninguem@alfafut.test'));
    }

    public function test_acesso_pela_api_vence_em_60_dias(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('app');

        $this->travel(59)->days();
        $this->withToken($token->plainTextToken)->getJson('/api/v1/auth/eu')->assertOk();

        $this->app['auth']->forgetGuards();
        $this->travel(2)->days();
        $this->withToken($token->plainTextToken)->getJson('/api/v1/auth/eu')->assertUnauthorized();
    }

    public function test_trocar_a_senha_no_perfil_encerra_tokens_e_outras_sessoes(): void
    {
        $user = User::factory()->create(['password' => Hash::make('senha-atual-12345'), 'remember_token' => 'antigo']);
        $token = $user->createToken('app')->plainTextToken;
        DB::table('sessions')->insert(['id' => 'outro-navegador', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

        $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'senha-atual-12345',
            'password' => self::SENHA_NOVA,
            'password_confirmation' => self::SENHA_NOVA,
        ])->assertRedirect('/profile');

        $this->assertTrue(Hash::check(self::SENHA_NOVA, $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseMissing('sessions', ['id' => 'outro-navegador']);
        $this->assertNotSame('antigo', $user->fresh()->remember_token);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/auth/eu')->assertUnauthorized();
    }

    public function test_redefinir_pelo_link_encerra_todos_os_acessos(): void
    {
        $user = User::factory()->create();
        $user->createToken('app');
        DB::table('sessions')->insert(['id' => 'navegador', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

        $this->post('/reset-password', [
            'token' => Password::createToken($user),
            'email' => $user->email,
            'password' => self::SENHA_NOVA,
            'password_confirmation' => self::SENHA_NOVA,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseMissing('sessions', ['id' => 'navegador']);
    }

    private function mensagemNoSite(string $email): ?string
    {
        return $this->from('/forgot-password')->post('/forgot-password', ['email' => $email])
            ->baseResponse->getSession()->get('status');
    }
}
