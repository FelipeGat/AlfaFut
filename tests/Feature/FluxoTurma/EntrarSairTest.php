<?php

namespace Tests\Feature\FluxoTurma;

use App\Models\PatotaMembro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntrarSairTest extends TestCase
{
    use MontaTurma, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->montarTurma();
    }

    public function test_entra_com_codigo_em_minusculas_e_com_espacos(): void
    {
        $this->actingAs($this->pessoas['fora'])
            ->post(route('patotas.entrar'), ['codigo_convite' => '  abcd1234 '])
            ->assertRedirect(route('patotas.show', $this->turma));

        $this->assertSame('ativo', $this->vinculo('fora')->status);
        $this->assertSame('membro', $this->vinculo('fora')->papel);
    }

    public function test_codigo_invalido_e_turma_arquivada_tem_a_mesma_resposta(): void
    {
        $invalido = $this->actingAs($this->pessoas['fora'])
            ->post(route('patotas.entrar'), ['codigo_convite' => 'ZZZZ9999']);
        $mensagem = session('erro');

        $this->turma->delete();
        $arquivada = $this->actingAs($this->pessoas['fora'])
            ->post(route('patotas.entrar'), ['codigo_convite' => 'ABCD1234']);

        $invalido->assertRedirect();
        $arquivada->assertRedirect();
        $this->assertNotEmpty($mensagem);
        $this->assertSame($mensagem, session('erro'));
        $this->assertNull($this->vinculo('fora'));
    }

    public function test_quem_ja_e_membro_recebe_aviso_sem_duplicar(): void
    {
        $this->actingAs($this->pessoas['membro'])
            ->post(route('patotas.entrar'), ['codigo_convite' => 'ABCD1234'])
            ->assertRedirect(route('patotas.show', $this->turma))
            ->assertSessionHas('status');

        $this->assertSame(1, PatotaMembro::where('user_id', $this->pessoas['membro']->id)->count());
    }

    public function test_quem_saiu_volta_como_membro_comum(): void
    {
        $this->actingAs($this->pessoas['ex_membro'])
            ->post(route('patotas.entrar'), ['codigo_convite' => 'ABCD1234'])
            ->assertRedirect(route('patotas.show', $this->turma));

        $this->assertSame('ativo', $this->vinculo('ex_membro')->status);
        $this->assertSame('membro', $this->vinculo('ex_membro')->papel);
        $this->assertNull($this->vinculo('ex_membro')->saiu_em);
    }

    public function test_removido_e_recusado_no_site_e_na_api(): void
    {
        $this->actingAs($this->pessoas['removido'])
            ->post(route('patotas.entrar'), ['codigo_convite' => 'ABCD1234'])
            ->assertSessionHas('erro');

        $this->actingAs($this->pessoas['removido'])
            ->postJson('/api/v1/patotas/entrar', ['codigo_convite' => 'ABCD1234'])
            ->assertStatus(422)
            ->assertJsonStructure(['mensagem']);

        $this->assertSame('removido', $this->vinculo('removido')->status);
    }

    public function test_api_reativa_quem_saiu_e_responde_404_para_codigo_invalido(): void
    {
        $this->actingAs($this->pessoas['ex_membro'])
            ->postJson('/api/v1/patotas/entrar', ['codigo_convite' => 'ABCD1234'])
            ->assertOk();
        $this->assertSame('ativo', $this->vinculo('ex_membro')->status);

        $this->actingAs($this->pessoas['fora'])
            ->postJson('/api/v1/patotas/entrar', ['codigo_convite' => 'ZZZZ9999'])
            ->assertNotFound();
    }

    public function test_limite_de_tentativas_no_site(): void
    {
        $this->actingAs($this->pessoas['fora']);

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('patotas.entrar'), ['codigo_convite' => 'ZZZZ9999']);
        }

        $this->from(route('dashboard'))
            ->post(route('patotas.entrar'), ['codigo_convite' => 'ABCD1234'])
            ->assertRedirect(route('dashboard'));
        $this->assertStringContainsString('Muitas tentativas', session('erro'));
        $this->assertNull($this->vinculo('fora'));

        $this->get(route('dashboard'))->assertSee('Muitas tentativas');
    }

    public function test_membro_sai_e_perde_o_acesso(): void
    {
        $this->actingAs($this->pessoas['membro'])
            ->post(route('patotas.sair', $this->turma))
            ->assertRedirect(route('patotas.index'));

        $this->assertSame('inativo', $this->vinculo('membro')->status);
        $this->assertNotNull($this->vinculo('membro')->saiu_em);

        $this->get(route('patotas.show', $this->turma))->assertForbidden();
        $this->get(route('patotas.index'))->assertDontSee($this->turma->nome);
    }

    public function test_criador_nao_sai_no_site_nem_na_api(): void
    {
        $this->actingAs($this->pessoas['criador'])
            ->post(route('patotas.sair', $this->turma))
            ->assertSessionHas('erro');

        $this->actingAs($this->pessoas['criador'])
            ->postJson("/api/v1/patotas/{$this->turma->id}/sair")
            ->assertStatus(422)
            ->assertJsonStructure(['mensagem']);

        $this->assertSame('ativo', $this->vinculo('criador')->status);
    }

    public function test_responsavel_que_sai_devolve_a_responsabilidade_ao_criador(): void
    {
        $this->actingAs($this->pessoas['responsavel'])
            ->post(route('patotas.sair', $this->turma))
            ->assertRedirect(route('patotas.index'));

        $this->assertSame($this->pessoas['criador']->id, $this->turma->fresh()->responsavel_id);
    }

    public function test_api_sair_tem_a_mesma_regra_do_responsavel(): void
    {
        $this->actingAs($this->pessoas['responsavel'])
            ->postJson("/api/v1/patotas/{$this->turma->id}/sair")
            ->assertOk();

        $this->assertSame($this->pessoas['criador']->id, $this->turma->fresh()->responsavel_id);
    }

    public function test_quem_nao_e_membro_nao_sai(): void
    {
        $this->actingAs($this->pessoas['fora'])
            ->postJson("/api/v1/patotas/{$this->turma->id}/sair")
            ->assertNotFound();

        $this->actingAs($this->pessoas['ex_membro'])
            ->post(route('patotas.sair', $this->turma))
            ->assertSessionHas('erro');
    }

    public function test_tela_da_turma_so_oferece_sair_a_quem_pode(): void
    {
        $this->actingAs($this->pessoas['membro'])
            ->get(route('patotas.show', $this->turma))
            ->assertSee('Sair da turma');

        $this->actingAs($this->pessoas['criador'])
            ->get(route('patotas.show', $this->turma))
            ->assertDontSee('Sair da turma');
    }

    public function test_painel_e_turmas_tem_o_campo_do_codigo(): void
    {
        $this->actingAs($this->pessoas['fora']);

        $this->get(route('dashboard'))->assertSee('codigo_convite', false)->assertSee('Entrar com código');
        $this->get(route('patotas.index'))->assertSee('codigo_convite', false)->assertSee('Entrar com código');
    }
}
