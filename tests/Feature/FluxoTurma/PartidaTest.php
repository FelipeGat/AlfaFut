<?php

namespace Tests\Feature\FluxoTurma;

use App\Models\Campo;
use App\Models\Local;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PartidaTest extends TestCase
{
    use MontaTurma, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->montarTurma();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function campo(array $dados = []): Campo
    {
        return Campo::create([
            'dono_id' => $this->pessoas['fora']->id,
            'nome' => 'Arena do Bairro',
            'endereco' => 'Rua das Flores, 10',
            'acessivel_cadeirante' => true,
            'ativo' => true,
            ...$dados,
        ]);
    }

    public function test_formulario_sugere_as_vagas_da_turma(): void
    {
        $this->actingAs($this->pessoas['organizador'])
            ->get(route('partidas.create', $this->turma))
            ->assertOk()
            ->assertSee('name="vagas_total"', false)
            ->assertSee('value="10"', false);
    }

    public function test_organizador_marca_partida(): void
    {
        $this->actingAs($this->pessoas['organizador'])
            ->post(route('partidas.store', $this->turma), $this->dadosDePartida())
            ->assertRedirect();

        $partida = Partida::where('titulo', 'Pelada de sábado')->firstOrFail();
        $this->assertSame($this->turma->id, $partida->patota_id);
        $this->assertSame($this->pessoas['organizador']->id, $partida->organizador_id);
        $this->assertSame('agendada', $partida->status);

        $this->get(route('patotas.show', $this->turma))->assertSee('Pelada de sábado');
        $this->get(route('partidas.index'))->assertDontSee('Nenhuma partida');
    }

    public function test_vagas_vazias_vem_da_turma_e_valor_vazio_vira_zero(): void
    {
        $this->actingAs($this->pessoas['organizador'])
            ->post(route('partidas.store', $this->turma), $this->dadosDePartida([
                'vagas_total' => '',
                'valor_individual' => '',
                'duracao_minutos' => '',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $partida = Partida::latest('id')->firstOrFail();
        $this->assertSame(10, (int) $partida->vagas_total);
        $this->assertSame(90, (int) $partida->duracao_minutos);
        $this->assertSame('0.00', $partida->valor_individual);
    }

    public function test_horario_de_brasilia(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-05 14:30', 'America/Sao_Paulo'));

        $this->actingAs($this->pessoas['organizador'])
            ->post(route('partidas.store', $this->turma), $this->dadosDePartida(['data_hora' => '2026-11-05T15:30']))
            ->assertSessionHasNoErrors();

        $partida = Partida::latest('id')->firstOrFail();
        $this->assertSame('2026-11-05 15:30', $partida->data_hora->format('Y-m-d H:i'));

        $this->get(route('partidas.index'))->assertSee('Pelada de sábado')->assertSee('15:30');

        $this->get(route('partidas.show', $partida))->assertSee('quinta-feira')->assertSee('novembro');
    }

    public function test_data_no_passado_da_erro_em_portugues_e_guarda_o_que_foi_digitado(): void
    {
        $this->actingAs($this->pessoas['organizador'])
            ->from(route('partidas.create', $this->turma))
            ->post(route('partidas.store', $this->turma), $this->dadosDePartida(['data_hora' => '2020-01-01T10:00']))
            ->assertRedirect(route('partidas.create', $this->turma))
            ->assertSessionHasErrors(['data_hora' => 'A data e a hora precisam estar no futuro.']);

        $this->get(route('partidas.create', $this->turma))
            ->assertSee('Pelada de sábado')
            ->assertSee('role="alert"', false);
    }

    public function test_titulo_vazio_da_erro_em_portugues(): void
    {
        $this->actingAs($this->pessoas['organizador'])
            ->post(route('partidas.store', $this->turma), $this->dadosDePartida(['titulo' => '']))
            ->assertSessionHasErrors(['titulo' => 'O campo título é obrigatório.']);
    }

    public function test_prazo_depois_do_inicio_da_erro(): void
    {
        $this->actingAs($this->pessoas['organizador'])
            ->post(route('partidas.store', $this->turma), $this->dadosDePartida([
                'confirmacao_ate' => now()->addDays(5)->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasErrors('confirmacao_ate');
    }

    /** @return array<string, array{string, mixed}> */
    public static function limites(): array
    {
        return [
            'vagas acima do teto' => ['vagas_total', 201],
            'vagas abaixo do minimo' => ['vagas_total', 3],
            'valor acima do teto' => ['valor_individual', '100000'],
            'valor negativo' => ['valor_individual', '-1'],
            'duracao acima do teto' => ['duracao_minutos', 241],
            'duracao abaixo do minimo' => ['duracao_minutos', 10],
            'titulo longo' => ['titulo', str_repeat('a', 121)],
        ];
    }

    #[DataProvider('limites')]
    public function test_limites_viram_erro_de_validacao(string $campo, mixed $valor): void
    {
        $this->actingAs($this->pessoas['organizador'])
            ->post(route('partidas.store', $this->turma), $this->dadosDePartida([$campo => $valor]))
            ->assertSessionHasErrors($campo);
    }

    public function test_alterar_mantem_quem_confirmou(): void
    {
        $partida = $this->partida();
        PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $this->pessoas['membro']->id, 'status' => 'confirmado', 'em_lista_espera' => false]);

        $this->actingAs($this->pessoas['organizador'])
            ->put(route('partidas.update', $partida), $this->dadosDePartida(['titulo' => 'Novo título']))
            ->assertRedirect(route('partidas.show', $partida));

        $this->assertSame('Novo título', $partida->fresh()->titulo);
        $this->assertSame(1, $partida->confirmados()->count());
    }

    public function test_alterar_pode_manter_a_data_que_ja_passou(): void
    {
        $partida = $this->partida(['data_hora' => now()->subHour()]);

        $this->actingAs($this->pessoas['organizador'])
            ->put(route('partidas.update', $partida), $this->dadosDePartida([
                'titulo' => 'Outro título',
                'data_hora' => $partida->data_hora->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('Outro título', $partida->fresh()->titulo);
    }

    public function test_desmarcar_lista_de_espera(): void
    {
        $partida = $this->partida(['lista_espera_habilitada' => true]);

        $this->actingAs($this->pessoas['organizador'])
            ->put(route('partidas.update', $partida), $this->dadosDePartida(['lista_espera_habilitada' => 0]))
            ->assertSessionHasNoErrors();

        $this->assertFalse($partida->fresh()->lista_espera_habilitada);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function estadosTravados(): array
    {
        return [
            'em andamento' => [['status' => 'em_andamento', 'iniciada_em' => '2026-01-01 10:00:00']],
            'pausada' => [['status' => 'agendada', 'iniciada_em' => '2026-01-01 10:00:00', 'pausada_em' => '2026-01-01 10:10:00']],
            'finalizada' => [['status' => 'finalizada', 'finalizada_em' => '2026-01-01 11:00:00']],
            'cancelada' => [['status' => 'cancelada']],
        ];
    }

    #[DataProvider('estadosTravados')]
    public function test_partida_travada_nao_altera_nem_cancela(array $estado): void
    {
        $partida = $this->partida($estado);
        $this->actingAs($this->pessoas['organizador']);

        $this->get(route('partidas.show', $partida))
            ->assertDontSee(route('partidas.edit', $partida))
            ->assertDontSee(route('partidas.cancelar', $partida));

        $this->get(route('partidas.edit', $partida))
            ->assertRedirect(route('partidas.show', $partida))
            ->assertSessionHas('erro');
        $this->put(route('partidas.update', $partida), $this->dadosDePartida(['titulo' => 'Mudou']))->assertSessionHas('erro');
        $this->post(route('partidas.cancelar', $partida))->assertSessionHas('erro');

        $this->patchJson("/api/v1/partidas/{$partida->id}", ['titulo' => 'Mudou'])
            ->assertStatus(422)->assertJsonStructure(['mensagem']);
        $this->deleteJson("/api/v1/partidas/{$partida->id}")
            ->assertStatus(422)->assertJsonStructure(['mensagem']);

        $this->assertNotSame('Mudou', $partida->fresh()->titulo);
        $this->assertSame($estado['status'], $partida->fresh()->status);
    }

    public function test_vagas_nao_ficam_abaixo_dos_confirmados(): void
    {
        $partida = $this->partida(['vagas_total' => 10]);
        foreach (['membro', 'organizador', 'responsavel', 'criador', 'fora'] as $nome) {
            PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $this->pessoas[$nome]->id, 'status' => 'confirmado', 'em_lista_espera' => false]);
        }

        $this->actingAs($this->pessoas['organizador'])
            ->put(route('partidas.update', $partida), $this->dadosDePartida(['vagas_total' => 4]))
            ->assertSessionHasErrors(['vagas_total' => 'Há 5 confirmados. As vagas não podem ficar abaixo disso.']);

        $this->assertSame(10, (int) $partida->fresh()->vagas_total);
    }

    public function test_cancelar_tira_de_proximas_e_poe_em_passadas(): void
    {
        $partida = $this->partida(['titulo' => 'Pelada cancelável']);
        $this->actingAs($this->pessoas['organizador']);

        $this->post(route('partidas.cancelar', $partida))->assertRedirect(route('partidas.show', $partida));
        $this->assertSame('cancelada', $partida->fresh()->status);

        $this->get(route('partidas.index'))->assertDontSee('Pelada cancelável');
        $this->get(route('partidas.index', ['filtro' => 'passadas']))
            ->assertSee('Pelada cancelável')
            ->assertSee('Cancelada');
        $this->get(route('partidas.show', $partida))
            ->assertSee('Cancelada')
            ->assertDontSee('Confirmar presença')
            ->assertDontSee(route('partidas.sortear', $partida));
    }

    /** @return array<string, array{string}> */
    public static function semPermissao(): array
    {
        return ['fora' => ['fora'], 'ex-membro' => ['ex_membro'], 'removido' => ['removido'], 'membro' => ['membro']];
    }

    #[DataProvider('semPermissao')]
    public function test_quem_nao_organiza_e_recusado(string $pessoa): void
    {
        $partida = $this->partida(['titulo' => 'Intacta']);
        $this->actingAs($this->pessoas[$pessoa]);

        $this->get(route('partidas.create', $this->turma))->assertForbidden();
        $this->post(route('partidas.store', $this->turma), $this->dadosDePartida())->assertForbidden();
        $this->post(route('partidas.store', $this->turma), [])->assertForbidden();
        $this->get(route('partidas.edit', $partida))->assertForbidden();
        $this->put(route('partidas.update', $partida), $this->dadosDePartida())->assertForbidden();
        $this->post(route('partidas.cancelar', $partida))->assertForbidden();

        $this->postJson("/api/v1/patotas/{$this->turma->id}/partidas", [])->assertForbidden();
        $this->patchJson("/api/v1/partidas/{$partida->id}", [])->assertForbidden();

        $this->assertSame('Intacta', $partida->fresh()->titulo);
        $this->assertSame('agendada', $partida->fresh()->status);
    }

    public function test_opcoes_escondidas_do_membro_comum(): void
    {
        $partida = $this->partida();

        $this->actingAs($this->pessoas['membro']);
        $this->get(route('patotas.show', $this->turma))->assertDontSee('Nova partida');
        $this->get(route('partidas.show', $partida))
            ->assertDontSee(route('partidas.edit', $partida))
            ->assertDontSee(route('partidas.cancelar', $partida));

        $this->actingAs($this->pessoas['responsavel']);
        $this->get(route('patotas.show', $this->turma))->assertSee('Nova partida');
        $this->get(route('partidas.show', $partida))
            ->assertSee(route('partidas.edit', $partida))
            ->assertSee(route('partidas.cancelar', $partida));
    }

    public function test_local_de_outra_turma_e_recusado_no_site_e_na_api(): void
    {
        $outra = Patota::factory()->create(['criador_id' => $this->pessoas['fora']->id]);
        $local = Local::create(['patota_id' => $outra->id, 'nome' => 'Local alheio']);
        $this->actingAs($this->pessoas['organizador']);

        $this->post(route('partidas.store', $this->turma), $this->dadosDePartida(['lugar' => 'local:'.$local->id]))
            ->assertSessionHasErrors('local_id');

        $this->postJson("/api/v1/patotas/{$this->turma->id}/partidas", [
            ...$this->dadosDePartida(),
            'local_id' => $local->id,
        ])->assertStatus(422)->assertJsonValidationErrors('local_id');

        $partida = $this->partida();
        $this->patchJson("/api/v1/partidas/{$partida->id}", ['local_id' => $local->id])
            ->assertStatus(422)->assertJsonValidationErrors('local_id');
    }

    public function test_local_da_turma_aparece_na_partida(): void
    {
        $local = Local::create(['patota_id' => $this->turma->id, 'nome' => 'Quadra da Vila', 'endereco' => 'Rua A, 1', 'acessivel_cadeirante' => true]);

        $this->actingAs($this->pessoas['organizador'])
            ->post(route('partidas.store', $this->turma), $this->dadosDePartida(['lugar' => 'local:'.$local->id]))
            ->assertSessionHasNoErrors();

        $partida = Partida::latest('id')->firstOrFail();
        $this->assertSame($local->id, $partida->local_id);
        $this->assertNull($partida->campo_id);

        $this->get(route('partidas.show', $partida))
            ->assertSee('Quadra da Vila')
            ->assertSee('Rua A, 1')
            ->assertSee('Local acessível para cadeirantes');
    }

    public function test_campo_do_catalogo_aparece_na_partida_e_na_api(): void
    {
        $campo = $this->campo();

        $this->actingAs($this->pessoas['organizador'])
            ->post(route('partidas.store', $this->turma), $this->dadosDePartida(['lugar' => 'campo:'.$campo->id]))
            ->assertSessionHasNoErrors();

        $partida = Partida::latest('id')->firstOrFail();
        $this->assertSame($campo->id, $partida->campo_id);
        $this->assertNull($partida->local_id);

        $this->get(route('partidas.show', $partida))
            ->assertSee('Arena do Bairro')
            ->assertSee('Rua das Flores, 10')
            ->assertSee('Local acessível para cadeirantes');
        $this->get(route('partidas.index'))->assertSee('Arena do Bairro');
        $this->get(route('dashboard'))->assertSee('Arena do Bairro');

        $this->getJson("/api/v1/partidas/{$partida->id}")
            ->assertOk()
            ->assertJsonPath('data.campo.nome', 'Arena do Bairro')
            ->assertJsonPath('data.campo.acessivel_cadeirante', true);
    }

    public function test_envios_malformados_nao_viram_erro_de_servidor(): void
    {
        $this->actingAs($this->pessoas['organizador']);

        $this->post(route('partidas.store', $this->turma), $this->dadosDePartida(['lugar' => ['x']]))->assertRedirect();
        $this->post(route('partidas.store', $this->turma), $this->dadosDePartida(['lugar' => 'local:abc']))->assertSessionHasErrors('local_id');
        $this->post(route('partidas.store', $this->turma), $this->dadosDePartida(['data_hora' => ['x']]))->assertSessionHasErrors('data_hora');
        $this->post(route('partidas.store', $this->turma), $this->dadosDePartida(['data_hora' => 'amanha cedo']))->assertSessionHasErrors('data_hora');
        $this->post(route('partidas.store', $this->turma), $this->dadosDePartida(['vagas_total' => ['x']]))->assertSessionHasErrors('vagas_total');

        $this->postJson("/api/v1/patotas/{$this->turma->id}/partidas", ['titulo' => 'X', 'confirmacao_ate' => now()->addDay()->toIso8601String()])
            ->assertStatus(422);
        $this->post(route('patotas.entrar'), ['codigo_convite' => ['x']])->assertSessionHasErrors('codigo_convite');
    }

    public function test_local_e_campo_juntos_sao_recusados(): void
    {
        $campo = $this->campo();
        $local = Local::create(['patota_id' => $this->turma->id, 'nome' => 'Quadra']);

        $this->actingAs($this->pessoas['organizador'])
            ->postJson("/api/v1/patotas/{$this->turma->id}/partidas", [
                ...$this->dadosDePartida(),
                'local_id' => $local->id,
                'campo_id' => $campo->id,
            ])->assertStatus(422);
    }

    public function test_campo_inativo_so_vale_para_partida_que_ja_o_usa(): void
    {
        $inativo = $this->campo(['ativo' => false]);
        $this->actingAs($this->pessoas['organizador']);

        $this->post(route('partidas.store', $this->turma), $this->dadosDePartida(['lugar' => 'campo:'.$inativo->id]))
            ->assertSessionHasErrors('campo_id');

        $partida = $this->partida(['campo_id' => $inativo->id]);
        $this->put(route('partidas.update', $partida), $this->dadosDePartida(['lugar' => 'campo:'.$inativo->id]))
            ->assertSessionHasNoErrors();
        $this->assertSame($inativo->id, $partida->fresh()->campo_id);
    }

    public function test_tirar_o_lugar_deixa_a_definir(): void
    {
        $campo = $this->campo();
        $partida = $this->partida(['campo_id' => $campo->id]);

        $this->actingAs($this->pessoas['organizador'])
            ->put(route('partidas.update', $partida), $this->dadosDePartida(['lugar' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull($partida->fresh()->campo_id);
        $this->get(route('partidas.show', $partida))->assertSee('A definir');
    }

    public function test_api_aplica_os_limites_e_responde_no_horario_de_brasilia(): void
    {
        $this->actingAs($this->pessoas['organizador']);

        $this->postJson("/api/v1/patotas/{$this->turma->id}/partidas", $this->dadosDePartida(['vagas_total' => 201]))
            ->assertStatus(422)->assertJsonValidationErrors('vagas_total');

        $resposta = $this->postJson("/api/v1/patotas/{$this->turma->id}/partidas", $this->dadosDePartida([
            'data_hora' => now('UTC')->addDays(2)->setTime(23, 0)->toIso8601String(),
        ]))->assertCreated();

        $this->assertStringEndsWith('-03:00', $resposta->json('data.data_hora') ?? $resposta->json('data_hora'));
        $this->assertSame('20:00', Partida::latest('id')->first()->data_hora->format('H:i'));
    }
}
