<?php

namespace Tests\Feature\FluxoTurma;

use App\Models\Patota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MembrosTest extends TestCase
{
    use MontaTurma, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->montarTurma();
    }

    public function test_quem_organiza_ve_papel_e_contato(): void
    {
        $this->pessoas['membro']->update(['telefone' => '27999990000']);

        foreach (['organizador', 'responsavel', 'criador', 'admin'] as $nome) {
            $this->actingAs($this->pessoas[$nome])
                ->get(route('patotas.membros.index', $this->turma))
                ->assertOk()
                ->assertSee($this->pessoas['membro']->email)
                ->assertSee('27999990000')
                ->assertSee('Organizador');
        }
    }

    /** @return array<string, array{string}> */
    public static function foraDaGestao(): array
    {
        return ['fora' => ['fora'], 'ex-membro' => ['ex_membro'], 'removido' => ['removido'], 'membro' => ['membro']];
    }

    #[DataProvider('foraDaGestao')]
    public function test_quem_nao_organiza_nao_abre_a_gestao(string $pessoa): void
    {
        $this->actingAs($this->pessoas[$pessoa])
            ->get(route('patotas.membros.index', $this->turma))
            ->assertForbidden();
    }

    public function test_membro_comum_nao_ve_contato_nem_codigo_na_turma(): void
    {
        $this->actingAs($this->pessoas['membro'])
            ->get(route('patotas.show', $this->turma))
            ->assertOk()
            ->assertDontSee($this->pessoas['organizador']->email)
            ->assertDontSee('ABCD1234')
            ->assertDontSee(route('patotas.membros.index', $this->turma));
    }

    public function test_quem_organiza_ve_o_codigo_e_os_atalhos(): void
    {
        foreach (['organizador', 'responsavel', 'criador'] as $nome) {
            $this->actingAs($this->pessoas[$nome])
                ->get(route('patotas.show', $this->turma))
                ->assertSee('ABCD1234')
                ->assertSee(route('patotas.membros.index', $this->turma))
                ->assertSee(route('patotas.locais.index', $this->turma));
        }
    }

    /** @return array<string, array{string}> */
    public static function quemGerencia(): array
    {
        return ['criador' => ['criador'], 'responsavel' => ['responsavel'], 'admin' => ['admin']];
    }

    #[DataProvider('quemGerencia')]
    public function test_promove_e_rebaixa(string $quem): void
    {
        $alvo = $this->pessoas['membro'];
        $this->actingAs($this->pessoas[$quem]);

        $this->patch(route('patotas.membros.papel', [$this->turma, $alvo]), ['papel' => 'organizador'])
            ->assertRedirect(route('patotas.membros.index', $this->turma));
        $this->assertSame('organizador', $this->vinculo('membro')->papel);

        $this->actingAs($alvo)->get(route('partidas.create', $this->turma))->assertOk();

        $this->actingAs($this->pessoas[$quem])
            ->patch(route('patotas.membros.papel', [$this->turma, $alvo]), ['papel' => 'membro']);
        $this->assertSame('membro', $this->vinculo('membro')->papel);

        $this->actingAs($alvo)->get(route('partidas.create', $this->turma))->assertForbidden();
    }

    public function test_papel_fora_da_lista_e_recusado(): void
    {
        $this->actingAs($this->pessoas['criador'])
            ->patch(route('patotas.membros.papel', [$this->turma, $this->pessoas['membro']]), ['papel' => 'administrador'])
            ->assertSessionHasErrors('papel');

        $this->assertSame('membro', $this->vinculo('membro')->papel);
    }

    /** @return array<string, array{string}> */
    public static function naoGerencia(): array
    {
        return ['fora' => ['fora'], 'ex-membro' => ['ex_membro'], 'removido' => ['removido'], 'membro' => ['membro'], 'organizador' => ['organizador']];
    }

    #[DataProvider('naoGerencia')]
    public function test_quem_nao_gerencia_e_recusado(string $pessoa): void
    {
        $alvo = $this->pessoas['ex_membro'];
        $turma = $this->turma;
        $this->actingAs($this->pessoas[$pessoa]);

        $this->patch(route('patotas.membros.papel', [$turma, $this->pessoas['membro']]), ['papel' => 'organizador'])->assertForbidden();
        $this->patch(route('patotas.membros.papel', [$turma, $this->pessoas['membro']]), [])->assertForbidden();
        $this->post(route('patotas.membros.responsavel', [$turma, $this->pessoas['membro']]))->assertForbidden();
        $this->post(route('patotas.membros.liberar', [$turma, $this->pessoas['removido']]))->assertForbidden();
        $this->delete(route('patotas.membros.destroy', [$turma, $this->pessoas['responsavel']]))->assertForbidden();

        $this->assertSame('removido', $this->vinculo('removido')->status);
        $this->assertSame('ativo', $this->vinculo('responsavel')->status);
        $this->assertSame($this->pessoas['responsavel']->id, $turma->fresh()->responsavel_id);
    }

    public function test_criador_nao_e_rebaixado_nem_removido(): void
    {
        $criador = $this->pessoas['criador'];
        $this->actingAs($this->pessoas['responsavel']);

        $this->patch(route('patotas.membros.papel', [$this->turma, $criador]), ['papel' => 'membro'])->assertSessionHas('erro');
        $this->delete(route('patotas.membros.destroy', [$this->turma, $criador]))->assertSessionHas('erro');

        $this->assertSame('administrador', $this->vinculo('criador')->papel);
        $this->assertSame('ativo', $this->vinculo('criador')->status);
    }

    public function test_ninguem_remove_a_si_mesmo(): void
    {
        $this->actingAs($this->pessoas['responsavel'])
            ->delete(route('patotas.membros.destroy', [$this->turma, $this->pessoas['responsavel']]))
            ->assertSessionHas('erro');

        $this->assertSame('ativo', $this->vinculo('responsavel')->status);
    }

    public function test_responsavel_nao_muda_de_papel_sem_passar_a_responsabilidade(): void
    {
        $this->actingAs($this->pessoas['criador'])
            ->patch(route('patotas.membros.papel', [$this->turma, $this->pessoas['responsavel']]), ['papel' => 'organizador'])
            ->assertSessionHas('erro');

        $this->assertSame('membro', $this->vinculo('responsavel')->papel);
    }

    public function test_remover_tira_o_acesso_e_a_volta_so_com_liberacao(): void
    {
        $alvo = $this->pessoas['membro'];

        $this->actingAs($this->pessoas['criador'])
            ->delete(route('patotas.membros.destroy', [$this->turma, $alvo]))
            ->assertRedirect(route('patotas.membros.index', $this->turma));
        $this->assertSame('removido', $this->vinculo('membro')->status);
        $this->assertNotNull($this->vinculo('membro')->saiu_em);

        $this->get(route('patotas.membros.index', $this->turma))->assertSee('Removidos')->assertSee($alvo->name);

        $this->actingAs($alvo)->get(route('patotas.show', $this->turma))->assertForbidden();
        $this->post(route('patotas.entrar'), ['codigo_convite' => 'ABCD1234'])->assertSessionHas('erro');
        $this->assertSame('removido', $this->vinculo('membro')->status);

        $this->actingAs($this->pessoas['criador'])
            ->post(route('patotas.membros.liberar', [$this->turma, $alvo]))
            ->assertRedirect(route('patotas.membros.index', $this->turma));
        $this->assertSame('inativo', $this->vinculo('membro')->status);

        $this->actingAs($alvo)->post(route('patotas.entrar'), ['codigo_convite' => 'ABCD1234'])
            ->assertRedirect(route('patotas.show', $this->turma));
        $this->assertSame('ativo', $this->vinculo('membro')->status);
    }

    public function test_liberar_quem_nao_foi_removido_e_recusado(): void
    {
        $this->actingAs($this->pessoas['criador'])
            ->post(route('patotas.membros.liberar', [$this->turma, $this->pessoas['membro']]))
            ->assertSessionHas('erro');

        $this->assertSame('ativo', $this->vinculo('membro')->status);
    }

    public function test_remover_o_responsavel_devolve_ao_criador(): void
    {
        $this->actingAs($this->pessoas['criador'])
            ->delete(route('patotas.membros.destroy', [$this->turma, $this->pessoas['responsavel']]))
            ->assertRedirect(route('patotas.membros.index', $this->turma));

        $this->assertSame($this->pessoas['criador']->id, $this->turma->fresh()->responsavel_id);
    }

    public function test_alvo_de_fora_da_turma_e_recusado_sem_erro_de_servidor(): void
    {
        $fora = $this->pessoas['fora'];
        $this->actingAs($this->pessoas['criador']);

        $this->patch(route('patotas.membros.papel', [$this->turma, $fora]), ['papel' => 'organizador'])->assertSessionHas('erro');
        $this->delete(route('patotas.membros.destroy', [$this->turma, $fora]))->assertSessionHas('erro');
        $this->post(route('patotas.membros.responsavel', [$this->turma, $fora]))->assertSessionHas('erro');
        $this->post(route('patotas.membros.responsavel', [$this->turma, $this->pessoas['ex_membro']]))->assertSessionHas('erro');

        $this->assertNull($this->vinculo('fora'));
        $this->assertSame($this->pessoas['responsavel']->id, $this->turma->fresh()->responsavel_id);
    }

    public function test_criador_passa_a_responsabilidade(): void
    {
        $this->actingAs($this->pessoas['criador'])
            ->post(route('patotas.membros.responsavel', [$this->turma, $this->pessoas['membro']]))
            ->assertRedirect(route('patotas.membros.index', $this->turma));

        $this->assertSame($this->pessoas['membro']->id, $this->turma->fresh()->responsavel_id);
        $this->get(route('patotas.show', $this->turma))->assertSee($this->pessoas['membro']->apelido ?? $this->pessoas['membro']->name);
    }

    public function test_responsavel_que_passa_adiante_volta_para_a_turma_sem_erro(): void
    {
        $this->actingAs($this->pessoas['responsavel'])
            ->post(route('patotas.membros.responsavel', [$this->turma, $this->pessoas['membro']]))
            ->assertRedirect(route('patotas.show', $this->turma));

        $this->get(route('patotas.membros.index', $this->turma))->assertForbidden();
    }

    public function test_editar_turma_recusa_responsavel_que_nao_e_membro_ativo(): void
    {
        $dados = ['nome' => 'Turma', 'jogadores_por_time' => 5, 'quantidade_times' => 2];
        $this->actingAs($this->pessoas['criador']);

        foreach (['fora', 'ex_membro', 'removido'] as $nome) {
            $this->put(route('patotas.update', $this->turma), [...$dados, 'responsavel_id' => $this->pessoas[$nome]->id])
                ->assertSessionHasErrors('responsavel_id');
        }

        $this->put(route('patotas.update', $this->turma), [...$dados, 'responsavel_id' => $this->pessoas['membro']->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($this->pessoas['membro']->id, $this->turma->fresh()->responsavel_id);
    }

    public function test_brasao_da_galeria_fica_salvo_e_outro_endereco_e_recusado(): void
    {
        $dados = ['nome' => 'Turma', 'jogadores_por_time' => 5, 'quantidade_times' => 2];
        $this->actingAs($this->pessoas['criador']);

        $this->put(route('patotas.update', $this->turma), [...$dados, 'brasao' => 'images/brasoes/18-fenix-fc.svg'])
            ->assertSessionHasNoErrors();
        $this->assertSame('images/brasoes/18-fenix-fc.svg', $this->turma->fresh()->brasao);
        $this->get(route('patotas.show', $this->turma))->assertSee('images/brasoes/18-fenix-fc.svg');

        $this->put(route('patotas.update', $this->turma), [...$dados, 'brasao' => 'https://exemplo.test/x.png'])
            ->assertSessionHasErrors('brasao');
    }

    public function test_tela_nao_oferece_remover_a_si_mesmo_nem_o_criador(): void
    {
        $resposta = $this->actingAs($this->pessoas['responsavel'])
            ->get(route('patotas.membros.index', $this->turma));

        $acao = fn (string $nome) => 'action="'.route('patotas.membros.destroy', [$this->turma, $this->pessoas[$nome]]).'"';
        $resposta->assertDontSee($acao('responsavel'), false);
        $resposta->assertDontSee($acao('criador'), false);
        $resposta->assertSee($acao('membro'), false);

        $this->actingAs($this->pessoas['organizador'])
            ->get(route('patotas.membros.index', $this->turma))
            ->assertDontSee($acao('membro'), false);
    }

    public function test_turma_de_outra_pessoa_nao_vaza_pela_gestao(): void
    {
        $outra = Patota::factory()->create(['criador_id' => $this->pessoas['fora']->id]);

        $this->actingAs($this->pessoas['criador'])
            ->get(route('patotas.membros.index', $outra))
            ->assertForbidden();
    }
}
