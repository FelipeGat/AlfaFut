<?php

namespace Tests\Feature\PortuguesLgpdLimpeza;

use App\Models\Patota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class ApiPrevisivelTest extends TestCase
{
    use RefreshDatabase;

    public function test_sem_login_e_sem_pedir_json_recebe_401_em_json(): void
    {
        $this->confereJson($this->get('/api/v1/patotas'), 401);
    }

    public function test_validacao_sem_pedir_json_recebe_422_em_json(): void
    {
        $this->confereJson($this->post('/api/v1/auth/login', []), 422);
    }

    public function test_permissao_sem_pedir_json_recebe_403_em_json(): void
    {
        $turma = Patota::factory()->create(['criador_id' => User::factory()->create()->id]);
        Sanctum::actingAs(User::factory()->create());

        $this->confereJson($this->get("/api/v1/patotas/{$turma->id}"), 403);
    }

    public function test_registro_inexistente_recebe_404_sem_nome_de_classe(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $resposta = $this->get('/api/v1/patotas/999999');

        $this->confereJson($resposta, 404);
        $this->assertStringNotContainsString('App\\Models', $resposta->getContent());
        $this->assertStringNotContainsString('No query results', $resposta->getContent());
    }

    public function test_rota_inexistente_recebe_404_em_json(): void
    {
        $this->confereJson($this->get('/api/v1/nao-existe'), 404);
    }

    public function test_metodo_errado_recebe_405_em_json(): void
    {
        $this->confereJson($this->post('/api/v1/auth/eu'), 405);
    }

    public function test_limite_sem_pedir_json_recebe_429_em_json(): void
    {
        foreach (range(1, 5) as $tentativa) {
            $this->post('/api/v1/auth/login', ['email' => 'a@alfafut.test', 'password' => 'errada']);
        }

        $this->confereJson($this->post('/api/v1/auth/login', ['email' => 'a@alfafut.test', 'password' => 'errada']), 429);
    }

    public function test_erro_interno_nao_revela_detalhes(): void
    {
        config(['app.debug' => false]);
        Route::get('/api/v1/teste-de-erro', fn () => throw new RuntimeException('detalhe interno'));

        $resposta = $this->get('/api/v1/teste-de-erro');

        $this->confereJson($resposta, 500);
        $this->assertStringNotContainsString('detalhe interno', $resposta->getContent());
    }

    public function test_mensagens_da_recusa_em_portugues(): void
    {
        $this->assertSame('Faça login para continuar.', $this->get('/api/v1/patotas')->json('message'));
        $this->assertSame('Não encontrado.', $this->get('/api/v1/nao-existe')->json('message'));
    }

    public function test_turma_traz_total_de_membros_e_criador_nas_tres_respostas(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $criada = $this->postJson('/api/v1/patotas', ['nome' => 'Turma', 'jogadores_por_time' => 5, 'quantidade_times' => 2])
            ->assertCreated();
        $this->assertSame(1, $criada->json('total_membros'));
        $this->assertSame($user->id, $criada->json('criador.id'));

        $id = $criada->json('id');
        $this->getJson("/api/v1/patotas/{$id}")->assertJsonPath('data.total_membros', 1)->assertJsonPath('data.criador.id', $user->id);
        $this->getJson('/api/v1/patotas')->assertJsonPath('data.0.total_membros', 1)->assertJsonPath('data.0.criador.id', $user->id);
    }

    public function test_perfil_proprio_traz_tipo_usuario(): void
    {
        Sanctum::actingAs(User::factory()->create(['tipo_usuario' => 'dono_campo']));

        $this->getJson('/api/v1/auth/eu')->assertJsonPath('data.tipo_usuario', 'dono_campo');
    }

    public function test_cadastro_na_api_aceita_perfil_publico_e_recusa_os_demais(): void
    {
        Notification::fake();
        $dados = ['name' => 'Dono', 'password' => 'senha-forte-12345', 'aceite_termos' => true];

        $this->postJson('/api/v1/auth/registrar', [...$dados, 'email' => 'dono@alfafut.test', 'tipo_usuario' => 'dono_campo'])->assertCreated();
        $this->assertSame('dono_campo', User::where('email', 'dono@alfafut.test')->value('tipo_usuario'));

        $this->postJson('/api/v1/auth/registrar', [...$dados, 'email' => 'sem@alfafut.test'])->assertCreated();
        $this->assertSame('jogador', User::where('email', 'sem@alfafut.test')->value('tipo_usuario'));

        $this->postJson('/api/v1/auth/registrar', [...$dados, 'email' => 'x@alfafut.test', 'tipo_usuario' => 'admin'])
            ->assertUnprocessable()->assertJsonValidationErrors('tipo_usuario');
    }

    public function test_role_enviado_e_ignorado_no_cadastro_e_no_perfil(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/auth/registrar', [
            'name' => 'Pessoa',
            'email' => 'pessoa@alfafut.test',
            'password' => 'senha-forte-12345',
            'aceite_termos' => true,
            'role' => 'admin',
        ])->assertCreated();
        $this->assertNotSame('admin', User::where('email', 'pessoa@alfafut.test')->value('role'));

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->patchJson('/api/v1/perfil', ['role' => 'admin', 'tipo_usuario' => 'dono_campo']);
        $this->assertNotSame('admin', $user->fresh()->role);
        $this->assertSame('jogador', $user->fresh()->tipo_usuario);

        $this->actingAs($user)->patch('/profile', ['name' => 'Novo', 'email' => $user->email, 'role' => 'admin', 'tipo_usuario' => 'dono_campo']);
        $this->assertNotSame('admin', $user->fresh()->role);
        $this->assertSame('jogador', $user->fresh()->tipo_usuario);
    }

    public function test_fillable_nao_tem_campos_de_privilegio(): void
    {
        $this->assertNotContains('role', (new User)->getFillable());
        $this->assertNotContains('tipo_usuario', (new User)->getFillable());
    }

    private function confereJson(TestResponse $resposta, int $status): void
    {
        $resposta->assertStatus($status);
        $this->assertStringContainsString('application/json', (string) $resposta->headers->get('Content-Type'));
        $this->assertIsArray($resposta->json());
    }
}
