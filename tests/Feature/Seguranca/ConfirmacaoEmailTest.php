<?php

namespace Tests\Feature\Seguranca;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ConfirmacaoEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_conta_nova_pelo_site_recebe_o_link_e_para_na_tela_de_confirmacao(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Pessoa Nova',
            'email' => 'nova@alfafut.test',
            'tipo_usuario' => 'jogador',
            'password' => 'senha-bem-grande',
            'password_confirmation' => 'senha-bem-grande',
        ]);

        $user = User::where('email', 'nova@alfafut.test')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get('/patotas')->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get('/verify-email')
            ->assertOk()
            ->assertSee('Confirme o seu e-mail')
            ->assertSee('Reenviar o e-mail de confirmação');
    }

    public function test_conta_nova_pela_api_precisa_confirmar_para_usar_a_turma(): void
    {
        Notification::fake();

        $resposta = $this->postJson('/api/v1/auth/registrar', [
            'name' => 'Pessoa Nova',
            'email' => 'nova@alfafut.test',
            'password' => 'senha-bem-grande',
        ])->assertCreated()->assertJsonPath('usuario.email_confirmado', false);

        $user = User::where('email', 'nova@alfafut.test')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);

        $token = ['Authorization' => 'Bearer '.$resposta->json('token')];

        $this->getJson('/api/v1/patotas', $token)
            ->assertForbidden()
            ->assertJsonPath('message', 'Confirme o seu e-mail para continuar. Se não recebeu o link, peça o reenvio.');

        $this->getJson('/api/v1/auth/eu', $token)->assertOk();
        $this->postJson('/api/v1/auth/reenviar-confirmacao', [], $token)->assertOk();
        Notification::assertSentToTimes($user, VerifyEmail::class, 2);
        $this->postJson('/api/v1/auth/logout', [], $token)->assertOk();
    }

    public function test_link_valido_libera_a_conta(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect();

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->actingAs($user->fresh())->get('/dashboard')->assertOk();
        $this->actingAs($user->fresh(), 'sanctum')->getJson('/api/v1/patotas')->assertOk();
    }

    public function test_conta_ja_confirmada_nao_passa_por_nada(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/auth/reenviar-confirmacao')->assertOk();

        Notification::assertNothingSent();
    }

    public function test_sem_confirmar_ainda_cuida_da_propria_conta(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/profile')->assertOk();
        $this->actingAs($user, 'sanctum')->patchJson('/api/v1/perfil', ['apelido' => 'Novo'])->assertOk();
        $this->actingAs($user, 'web')->delete('/profile', ['password' => 'password'])->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_reenvio_pelo_site_tem_limite_em_portugues(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        foreach (range(1, 6) as $tentativa) {
            $this->actingAs($user)->post('/email/verification-notification');
        }

        $this->actingAs($user)->from('/verify-email')->post('/email/verification-notification')->assertRedirect('/verify-email');
        $this->assertStringStartsWith('Muitas tentativas', session('errors')->first('email'));
        Notification::assertSentToTimes($user, VerifyEmail::class, 6);
    }

    public function test_emails_saem_em_portugues(): void
    {
        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();
        $user->sendPasswordResetNotification('token-de-teste');

        $mensagens = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(2, $mensagens);

        $confirmacao = $mensagens[0]->getOriginalMessage();
        $redefinicao = $mensagens[1]->getOriginalMessage();

        $this->assertSame('Confirme seu e-mail', $confirmacao->getSubject());
        $this->assertSame('Redefinição de senha', $redefinicao->getSubject());

        foreach ([$confirmacao, $redefinicao] as $mensagem) {
            $corpo = html_entity_decode($mensagem->getHtmlBody());

            $this->assertStringContainsString('Olá!', $corpo);
            $this->assertStringContainsString('Todos os direitos reservados', $corpo);
            $this->assertStringNotContainsString('All rights reserved', $corpo);
            $this->assertStringNotContainsString('having trouble', $corpo);
        }
    }
}
