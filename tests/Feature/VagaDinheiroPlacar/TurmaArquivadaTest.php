<?php

namespace Tests\Feature\VagaDinheiroPlacar;

use App\Models\Patota;
use App\Models\Time;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\FluxoTurma\MontaTurma;
use Tests\TestCase;

class TurmaArquivadaTest extends TestCase
{
    use MontaTurma, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->montarTurma();
    }

    public function test_membro_ve_a_partida_so_para_leitura(): void
    {
        $partida = $this->partida();
        $this->turma->delete();

        foreach (['membro', 'criador', 'admin'] as $nome) {
            $this->actingAs($this->pessoas[$nome])->get("/partidas/{$partida->id}")
                ->assertOk()
                ->assertSee('Esta turma está arquivada')
                ->assertDontSee(route('partidas.confirmar', $partida))
                ->assertDontSee(route('partidas.edit', $partida));
        }
    }

    public function test_tv_e_resultado_nao_derrubam_a_tela(): void
    {
        $partida = $this->partida(['status' => 'finalizada', 'iniciada_em' => now()->subHour(), 'finalizada_em' => now()]);
        Time::create(['partida_id' => $partida->id, 'nome' => 'Time 1']);
        Time::create(['partida_id' => $partida->id, 'nome' => 'Time 2']);
        $this->turma->delete();

        $this->actingAs($this->pessoas['admin'])->get("/partidas/{$partida->id}/tv")->assertOk();
        $this->actingAs($this->pessoas['membro'])->get("/partidas/{$partida->id}/resultado")->assertOk();
    }

    public function test_acoes_sao_recusadas_para_todos_inclusive_administrador(): void
    {
        $partida = $this->partida();
        $time = Time::create(['partida_id' => $partida->id, 'nome' => 'Time 1']);
        Time::create(['partida_id' => $partida->id, 'nome' => 'Time 2']);
        $this->turma->delete();

        foreach (['confirmar', 'recusar', 'sortear', 'iniciar', 'pausar', 'finalizar', 'gol'] as $acao) {
            foreach (['membro', 'criador', 'admin'] as $nome) {
                $resposta = $this->actingAs($this->pessoas[$nome], 'sanctum')
                    ->postJson("/api/v1/partidas/{$partida->id}/{$acao}", ['time_id' => $time->id]);
                $this->assertContains($resposta->status(), [403, 422], "{$acao} por {$nome}: {$resposta->status()}");
            }
        }

        $this->assertNull($partida->fresh()->iniciada_em);
        $this->assertSame(0, $partida->confirmacoes()->count());
    }

    public function test_alterar_partida_de_turma_arquivada_e_recusado_ao_administrador(): void
    {
        $partida = $this->partida();
        $this->turma->delete();

        $this->actingAs($this->pessoas['admin'], 'sanctum')
            ->patchJson("/api/v1/partidas/{$partida->id}", ['titulo' => 'Novo'])
            ->assertStatus(422);
        $this->assertNotSame('Novo', $partida->fresh()->titulo);
    }

    public function test_criador_ve_as_arquivadas_e_restaura_no_site(): void
    {
        $this->turma->delete();

        $this->actingAs($this->pessoas['criador'])->get('/patotas')->assertOk()->assertSee('Turmas arquivadas')->assertSee($this->turma->nome);
        $this->actingAs($this->pessoas['membro'])->get('/patotas')->assertOk()->assertDontSee('Turmas arquivadas');

        $this->actingAs($this->pessoas['criador'])->post("/patotas/{$this->turma->id}/restaurar")->assertRedirect();

        $this->assertFalse($this->turma->fresh()->trashed());
    }

    public function test_restaurar_pela_api_e_negado_a_quem_nao_criou(): void
    {
        $this->turma->delete();

        foreach (['membro', 'organizador', 'responsavel', 'fora'] as $nome) {
            $this->actingAs($this->pessoas[$nome], 'sanctum')->postJson("/api/v1/patotas/{$this->turma->id}/restaurar")->assertForbidden();
            $this->actingAs($this->pessoas[$nome])->post("/patotas/{$this->turma->id}/restaurar")->assertForbidden();
        }
        $this->assertTrue($this->turma->fresh()->trashed());

        $this->actingAs($this->pessoas['criador'], 'sanctum')->getJson('/api/v1/patotas/arquivadas')->assertOk()->assertJsonPath('data.0.id', $this->turma->id);
        $this->actingAs($this->pessoas['membro'], 'sanctum')->getJson('/api/v1/patotas/arquivadas')->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($this->pessoas['criador'], 'sanctum')->postJson("/api/v1/patotas/{$this->turma->id}/restaurar")->assertOk();
        $this->assertFalse($this->turma->fresh()->trashed());
    }

    public function test_restaurar_turma_que_nao_esta_arquivada_nao_quebra(): void
    {
        $this->actingAs($this->pessoas['criador'], 'sanctum')->postJson("/api/v1/patotas/{$this->turma->id}/restaurar")->assertStatus(422);
    }

    public function test_partida_volta_como_estava_ao_restaurar(): void
    {
        $partida = $this->partida();
        $this->turma->delete();
        $this->turma->restore();

        $this->actingAs($this->pessoas['membro'], 'sanctum')->postJson("/api/v1/partidas/{$partida->id}/confirmar")->assertOk();
        $this->assertSame('agendada', $partida->fresh()->status);
    }

    public function test_mensagem_com_partida_de_outra_turma_e_recusada(): void
    {
        $outraTurma = Patota::factory()->create(['criador_id' => $this->pessoas['fora']->id]);
        $alheia = $this->partida(['patota_id' => $outraTurma->id]);
        $daTurma = $this->partida();
        $membro = $this->pessoas['membro'];

        $this->actingAs($membro, 'sanctum')->postJson("/api/v1/patotas/{$this->turma->id}/mensagens", ['conteudo' => 'Oi', 'partida_id' => $alheia->id])->assertStatus(422);
        $this->actingAs($membro)->from('/x')->post("/patotas/{$this->turma->id}/mural", ['conteudo' => 'Oi', 'partida_id' => $alheia->id])->assertSessionHasErrors('partida_id');
        $this->assertSame(0, $this->turma->mensagens()->count());

        $this->actingAs($membro, 'sanctum')->postJson("/api/v1/patotas/{$this->turma->id}/mensagens", ['conteudo' => 'Oi', 'partida_id' => $daTurma->id])->assertCreated();
    }

    public function test_mensalidade_e_publica_em_branco_nao_dao_erro(): void
    {
        $dados = ['nome' => 'Turma Nova', 'jogadores_por_time' => 5, 'quantidade_times' => 2, 'valor_mensalidade' => null, 'publica' => null];

        $criada = $this->actingAs($this->pessoas['membro'], 'sanctum')->postJson('/api/v1/patotas', $dados)->assertCreated();
        $turma = Patota::find($criada->json('id'));
        $this->assertSame('0.00', $turma->valor_mensalidade);

        $this->actingAs($this->pessoas['membro'], 'sanctum')->patchJson("/api/v1/patotas/{$turma->id}", ['valor_mensalidade' => null, 'publica' => null])->assertOk();

        $this->actingAs($this->pessoas['membro'])->post('/patotas', $dados)->assertRedirect()->assertSessionHasNoErrors();
    }

    public function test_mensalidade_acima_do_teto_e_recusada(): void
    {
        $dados = ['nome' => 'Turma Nova', 'jogadores_por_time' => 5, 'quantidade_times' => 2, 'valor_mensalidade' => '100000.00'];

        $this->actingAs($this->pessoas['membro'], 'sanctum')->postJson('/api/v1/patotas', $dados)->assertStatus(422);
        $this->actingAs($this->pessoas['membro'])->from('/x')->post('/patotas', $dados)->assertSessionHasErrors('valor_mensalidade');
    }
}
