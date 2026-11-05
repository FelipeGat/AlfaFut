<?php

namespace Tests\Feature\VagaDinheiroPlacar;

use App\Models\Campo;
use App\Models\Despesa;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\FluxoTurma\MontaTurma;
use Tests\TestCase;

class ExcluirContaTest extends TestCase
{
    use MontaTurma, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->montarTurma();
    }

    private function excluir(User $user)
    {
        return $this->actingAs($user, 'web')->from('/profile')->delete('/profile', ['password' => 'password']);
    }

    private function confirmar(Partida $partida, User $user, bool $fila = false, ?int $posicao = null): void
    {
        PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $user->id, 'status' => 'confirmado', 'em_lista_espera' => $fila, 'posicao_lista_espera' => $posicao, 'confirmado_em' => now()]);
    }

    public function test_dados_pessoais_somem_e_o_acesso_termina(): void
    {
        $user = $this->novoMembro();
        $user->update(['name' => 'Fulano Sicrano', 'apelido' => 'Fulaninho', 'telefone' => '27999990000', 'avatar_url' => 'https://exemplo.test/f.png', 'data_nascimento' => '1990-01-02', 'posicao_preferida' => 'meia', 'nivel_habilidade' => 'avancado', 'alto_contraste' => true, 'necessidades_acessibilidade' => ['visual']]);
        $email = $user->email;
        $token = $user->createToken('app')->plainTextToken;

        $this->excluir($user)->assertRedirect('/');
        $this->assertGuest();

        $registro = $user->fresh();
        $this->assertNotNull($registro->excluida_em);
        $this->assertSame('Conta excluída', $registro->name);
        $this->assertStringEndsWith('@alfafut.invalid', $registro->email);
        foreach (['apelido', 'telefone', 'avatar_url', 'data_nascimento', 'posicao_preferida', 'necessidades_acessibilidade', 'email_verified_at'] as $campo) {
            $this->assertNull($registro->{$campo}, $campo);
        }
        $this->assertSame('intermediario', $registro->nivel_habilidade);
        $this->assertFalse((bool) $registro->alto_contraste);
        $this->assertFalse(Hash::check('password', $registro->password));
        $this->assertSame(0, $registro->tokens()->count());
        $this->assertStringNotContainsString($email, json_encode($registro->getAttributes()));
        $this->assertStringNotContainsString('Fulan', json_encode($registro->getAttributes()));

        $this->post('/login', ['email' => $email, 'password' => 'password']);
        $this->assertGuest();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/v1/patotas', ['Authorization' => "Bearer {$token}"])->assertUnauthorized();
    }

    public function test_senha_errada_nao_exclui(): void
    {
        $user = $this->novoMembro();

        $this->actingAs($user)->from('/profile')->delete('/profile', ['password' => 'errada'])->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertNull($user->fresh()->excluida_em);
    }

    public function test_turma_e_rateio_dos_outros_continuam_certos(): void
    {
        $partida = $this->partida(['vagas_total' => 10]);
        $sai = $this->novoMembro();
        $fica = $this->novoMembro();
        foreach ([$sai, $fica] as $j) {
            $this->confirmar($partida, $j);
        }
        $this->actingAs($this->pessoas['criador'], 'sanctum')->postJson("/api/v1/patotas/{$this->turma->id}/despesas", [
            'partida_id' => $partida->id, 'descricao' => 'Campo', 'categoria' => 'locacao', 'valor_total' => '100.00', 'data_despesa' => now()->toDateString(), 'rateada' => true,
        ])->assertCreated();
        $despesa = Despesa::first();

        $this->excluir($sai)->assertRedirect('/');

        $this->assertSame('100.00', number_format($despesa->pagamentos()->get()->sum(fn ($p) => (float) $p->valor_devido), 2, '.', ''));
        $this->assertNotNull($this->turma->fresh());
        $this->actingAs($this->pessoas['criador'])->get("/patotas/{$this->turma->id}/despesas/{$despesa->id}")->assertOk()->assertSee('Conta excluída');
        $this->assertSame('inativo', $this->turma->membros()->where('users.id', $sai->id)->first()->pivot->status);
    }

    public function test_quem_criou_partida_ou_despesa_exclui_sem_erro(): void
    {
        $organizador = $this->pessoas['organizador'];
        $partida = $this->partida(['organizador_id' => $organizador->id]);
        $this->actingAs($organizador, 'sanctum')->postJson("/api/v1/patotas/{$this->turma->id}/despesas", [
            'descricao' => 'Bola', 'categoria' => 'material', 'valor_total' => '50.00', 'data_despesa' => now()->toDateString(),
        ])->assertCreated();

        $this->excluir($organizador)->assertRedirect('/');

        $this->actingAs($this->pessoas['criador'], 'web')->get("/partidas/{$partida->id}")->assertOk();
        $this->assertSame('Conta excluída', $partida->fresh()->organizador->name);
    }

    public function test_vaga_e_liberada_e_a_fila_anda(): void
    {
        $partida = $this->partida(['vagas_total' => 1]);
        $sai = $this->novoMembro();
        $espera = $this->novoMembro();
        $this->confirmar($partida, $sai);
        $this->confirmar($partida, $espera, true, 1);

        $this->excluir($sai)->assertRedirect('/');

        $this->assertSame(0, $partida->confirmacoes()->where('user_id', $sai->id)->count());
        $this->assertFalse((bool) $partida->confirmacoes()->where('user_id', $espera->id)->value('em_lista_espera'));
    }

    public function test_criador_de_turma_com_outros_membros_e_recusado(): void
    {
        $this->excluir($this->pessoas['criador'])
            ->assertRedirect('/profile')
            ->assertSessionHas('erro', fn ($m) => str_contains($m, $this->turma->nome) && str_contains($m, 'rquiv'));

        $this->assertNull($this->pessoas['criador']->fresh()->excluida_em);
        $this->assertAuthenticated();
    }

    public function test_criador_sozinho_tem_a_turma_arquivada_junto(): void
    {
        $user = User::factory()->create();
        $turma = Patota::factory()->create(['criador_id' => $user->id]);
        PatotaMembro::create(['patota_id' => $turma->id, 'user_id' => $user->id, 'papel' => 'administrador', 'status' => 'ativo']);

        $this->excluir($user)->assertRedirect('/');

        $this->assertTrue($turma->fresh()->trashed());
    }

    public function test_criador_que_arquivou_a_turma_consegue_excluir(): void
    {
        $this->turma->delete();

        $this->excluir($this->pessoas['criador'])->assertRedirect('/');

        $this->assertNotNull($this->pessoas['criador']->fresh()->excluida_em);
    }

    public function test_campos_saem_do_catalogo(): void
    {
        $dono = User::factory()->create(['tipo_usuario' => 'dono_campo']);
        $campo = Campo::create(['dono_id' => $dono->id, 'nome' => 'Arena', 'ativo' => true, 'contato_whatsapp' => '27999990000']);

        $this->excluir($dono)->assertRedirect('/');

        $campo->refresh();
        $this->assertFalse((bool) $campo->ativo);
        $this->assertNull($campo->contato_whatsapp);
    }

    public function test_email_fica_livre_para_novo_cadastro(): void
    {
        $user = $this->novoMembro();
        $email = $user->email;

        $this->excluir($user)->assertRedirect('/');
        $this->app['auth']->forgetGuards();

        $this->post('/register', ['name' => 'Pessoa Nova', 'email' => $email, 'tipo_usuario' => 'jogador', 'password' => 'SenhaForte!2026x', 'password_confirmation' => 'SenhaForte!2026x'])->assertRedirect();

        $nova = User::where('email', $email)->first();
        $this->assertNotNull($nova);
        $this->assertNotSame($user->id, $nova->id);
        $this->assertSame(0, $nova->patotasAtivas()->count());
    }
}
