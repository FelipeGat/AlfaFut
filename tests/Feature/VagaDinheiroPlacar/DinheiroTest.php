<?php

namespace Tests\Feature\VagaDinheiroPlacar;

use App\Models\Despesa;
use App\Models\Pagamento;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FluxoTurma\MontaTurma;
use Tests\TestCase;

class DinheiroTest extends TestCase
{
    use MontaTurma, RefreshDatabase;

    private User $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->montarTurma();
        $this->org = $this->pessoas['criador'];
    }

    public static function vias(): array
    {
        return ['site' => ['site'], 'api' => ['api']];
    }

    private function confirmar(Partida $partida, ?User $user = null): User
    {
        $user ??= $this->novoMembro();
        PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $user->id, 'status' => 'confirmado', 'em_lista_espera' => false, 'confirmado_em' => now()->addSeconds($partida->confirmacoes()->count())]);

        return $user;
    }

    private function dados(array $dados = []): array
    {
        return [
            'descricao' => 'Aluguel do campo',
            'categoria' => 'locacao',
            'valor_total' => '100.00',
            'data_despesa' => now()->toDateString(),
            'rateada' => 1,
            ...$dados,
        ];
    }

    private function criar(string $via, array $dados)
    {
        return $via === 'api'
            ? $this->actingAs($this->org, 'sanctum')->postJson("/api/v1/patotas/{$this->turma->id}/despesas", $dados)
            : $this->actingAs($this->org)->from('/x')->post("/patotas/{$this->turma->id}/despesas", $dados);
    }

    /** @return array{Despesa, Partida, array<int, User>} */
    private function rateada(int $pessoas = 3, string $valor = '100.00'): array
    {
        $partida = $this->partida();
        $jogadores = [];
        foreach (range(1, $pessoas) as $i) {
            $jogadores[] = $this->confirmar($partida);
        }

        $this->criar('api', $this->dados(['partida_id' => $partida->id, 'valor_total' => $valor]))->assertCreated();

        return [Despesa::latest('id')->first(), $partida, $jogadores];
    }

    private function parcela(Despesa $despesa, User $user): Pagamento
    {
        return $despesa->pagamentos()->where('user_id', $user->id)->first();
    }

    private function baixa(User $quem, Pagamento $parcela, string $valor)
    {
        return $this->actingAs($quem, 'sanctum')->postJson("/api/v1/pagamentos/{$parcela->id}/quitar", ['valor_pago' => $valor, 'forma_pagamento' => 'pix']);
    }

    private function somaCentavos(Despesa $despesa): int
    {
        return $despesa->pagamentos()->get()->sum(fn ($p) => (int) str_replace('.', '', $p->valor_devido));
    }

    #[DataProvider('vias')]
    public function test_rateio_de_cem_entre_tres_fecha_ao_centavo(string $via): void
    {
        $partida = $this->partida();
        $primeiro = $this->confirmar($partida);
        $this->confirmar($partida);
        $this->confirmar($partida);

        $this->criar($via, $this->dados(['partida_id' => $partida->id]));

        $despesa = Despesa::latest('id')->first();
        $this->assertSame(10000, $this->somaCentavos($despesa));
        $this->assertSame('33.34', $this->parcela($despesa, $primeiro)->valor_devido);
        $this->assertSame(['33.33', '33.33', '33.34'], $despesa->pagamentos()->orderBy('valor_devido')->pluck('valor_devido')->all());
    }

    public function test_rateio_fecha_com_valores_que_nao_dividem(): void
    {
        foreach (['0.01', '0.05', '10.00', '33.33', '99.99', '1234.57', '999999.99'] as $valor) {
            $partida = $this->partida();
            foreach (range(1, 7) as $i) {
                $this->confirmar($partida);
            }
            $this->criar('api', $this->dados(['partida_id' => $partida->id, 'valor_total' => $valor]))->assertCreated();
            $despesa = Despesa::latest('id')->first();
            $this->assertSame((int) str_replace('.', '', $valor), $this->somaCentavos($despesa), $valor);
        }
    }

    #[DataProvider('vias')]
    public function test_rateada_sem_partida_ou_sem_confirmados_e_recusada(string $via): void
    {
        $resposta = $this->criar($via, $this->dados());
        $via === 'api' ? $resposta->assertStatus(422) : $resposta->assertSessionHas('erro');

        $resposta = $this->criar($via, $this->dados(['partida_id' => $this->partida()->id]));
        $via === 'api' ? $resposta->assertStatus(422) : $resposta->assertSessionHas('erro');

        $this->assertSame(0, Despesa::count());
    }

    public function test_api_sem_rateada_vale_nao_rateada(): void
    {
        $dados = $this->dados();
        unset($dados['rateada']);

        $this->criar('api', $dados)->assertCreated()->assertJsonPath('rateada', false);
        $this->assertFalse(Despesa::first()->fresh()->rateada);
    }

    public function test_tela_avisa_que_os_confirmados_mudaram_e_refazer_mantem_o_que_foi_pago(): void
    {
        [$despesa, $partida, [$pagou, $saiu, $ficou]] = $this->rateada();
        $this->baixa($pagou, $this->parcela($despesa, $pagou), '33.34')->assertOk();
        $partida->confirmacoes()->where('user_id', $saiu->id)->delete();
        $entrou = $this->confirmar($partida);

        $url = "/patotas/{$this->turma->id}/despesas/{$despesa->id}";
        $this->actingAs($this->org)->get($url)->assertOk()->assertSee('confirmados mudaram');
        $this->actingAs($ficou)->get($url)->assertOk()->assertDontSee('Refazer rateio');

        $this->actingAs($this->org)->post("{$url}/refazer-rateio")->assertRedirect();

        $despesa->refresh();
        $this->assertSame(10000, $this->somaCentavos($despesa));
        $this->assertNull($despesa->pagamentos()->where('user_id', $saiu->id)->first());
        $this->assertSame('33.34', $this->parcela($despesa, $pagou)->valor_pago);
        $this->assertSame('33.33', $this->parcela($despesa, $entrou)->valor_devido);
        $this->actingAs($this->org)->get($url)->assertDontSee('confirmados mudaram');
    }

    public function test_refazer_com_quem_pagou_e_saiu_ou_pagou_acima_da_nova_cota(): void
    {
        [$despesa, $partida, [$a, $b]] = $this->rateada(2);
        $this->baixa($a, $this->parcela($despesa, $a), '50.00')->assertOk();
        $this->baixa($b, $this->parcela($despesa, $b), '20.00')->assertOk();
        $partida->confirmacoes()->where('user_id', $a->id)->delete();
        foreach (range(1, 3) as $i) {
            $this->confirmar($partida);
        }

        $this->actingAs($this->org, 'sanctum')->postJson("/api/v1/despesas/{$despesa->id}/refazer-rateio")->assertOk();

        $despesa->refresh();
        $this->assertSame(10000, $this->somaCentavos($despesa));
        $this->assertSame('50.00', $this->parcela($despesa, $a)->valor_devido);
        $this->assertSame('pago', $this->parcela($despesa, $a)->status);
        $this->assertSame('20.00', $this->parcela($despesa, $b)->valor_pago);
        $this->assertSame('parcial', $this->parcela($despesa, $b)->status === 'pago' ? 'parcial' : $this->parcela($despesa, $b)->status);
        $this->assertCount(5, $despesa->pagamentos()->get());
    }

    public function test_refazer_sem_confirmados_e_recusado_sem_apagar_parcelas(): void
    {
        [$despesa, $partida] = $this->rateada();
        $partida->confirmacoes()->delete();

        $this->actingAs($this->org, 'sanctum')->postJson("/api/v1/despesas/{$despesa->id}/refazer-rateio")->assertStatus(422);
        $this->assertSame(3, $despesa->pagamentos()->count());
    }

    public function test_alterar_o_valor_refaz_o_rateio_e_nao_aceita_menos_que_o_pago(): void
    {
        [$despesa, , [$a]] = $this->rateada();
        $this->baixa($a, $this->parcela($despesa, $a), '30.00')->assertOk();

        $this->actingAs($this->org, 'sanctum')->patchJson("/api/v1/despesas/{$despesa->id}", ['valor_total' => '200.00'])->assertOk();
        $this->assertSame(20000, $this->somaCentavos($despesa->fresh()));

        $this->actingAs($this->org, 'sanctum')->patchJson("/api/v1/despesas/{$despesa->id}", ['valor_total' => '29.99'])->assertStatus(422);
        $this->assertSame('200.00', $despesa->fresh()->valor_total);
    }

    public function test_patch_nao_aceita_mudar_a_situacao(): void
    {
        [$despesa] = $this->rateada();

        $this->actingAs($this->org, 'sanctum')->patchJson("/api/v1/despesas/{$despesa->id}", ['status' => 'fechada'])->assertStatus(422);
        $this->assertSame('aberta', $despesa->fresh()->status);
    }

    public function test_baixas_somam_e_a_situacao_fica_parcial(): void
    {
        [$despesa, , [$a]] = $this->rateada();
        $parcela = $this->parcela($despesa, $a);

        $this->baixa($a, $parcela, '10.00')->assertOk();
        $this->baixa($a, $parcela, '5.00')->assertOk();

        $parcela->refresh();
        $this->assertSame('15.00', $parcela->valor_pago);
        $this->assertSame('parcial', $parcela->status);
        $this->assertSame(15.0, $despesa->fresh()->totalPago());
        $this->assertSame(85.0, $despesa->fresh()->saldoAberto());
    }

    public function test_baixa_acima_do_saldo_e_recusada_e_informa_o_saldo(): void
    {
        [$despesa, , [$a]] = $this->rateada();
        $parcela = $this->parcela($despesa, $a);
        $this->baixa($a, $parcela, '30.00')->assertOk();

        $this->baixa($a, $parcela, '6.00')->assertStatus(422)->assertJsonFragment(['mensagem' => 'O saldo desta parcela é de R$ 3,34.']);
        $this->assertSame('30.00', $parcela->fresh()->valor_pago);
    }

    public function test_baixa_com_mais_de_duas_casas_ou_sem_valor_e_recusada(): void
    {
        [$despesa, , [$a]] = $this->rateada();

        $this->baixa($a, $this->parcela($despesa, $a), '1.999')->assertStatus(422);
        $this->baixa($a, $this->parcela($despesa, $a), '0')->assertStatus(422);
    }

    public function test_despesa_fecha_sozinha_e_nao_aceita_baixa_depois(): void
    {
        [$despesa, , [$a, $b]] = $this->rateada(2);
        $this->baixa($a, $this->parcela($despesa, $a), '50.00')->assertOk();
        $this->assertSame('aberta', $despesa->fresh()->status);
        $this->baixa($b, $this->parcela($despesa, $b), '50.00')->assertOk();

        $this->assertSame('fechada', $despesa->fresh()->status);
        $this->assertSame(0.0, $despesa->fresh()->saldoAberto());
        $this->baixa($b, $this->parcela($despesa, $b), '1.00')->assertStatus(422);
    }

    #[DataProvider('vias')]
    public function test_nao_rateada_pode_ser_marcada_como_paga(string $via): void
    {
        $this->criar('api', $this->dados(['rateada' => 0]))->assertCreated();
        $despesa = Despesa::first();

        $via === 'api'
            ? $this->actingAs($this->org, 'sanctum')->postJson("/api/v1/despesas/{$despesa->id}/marcar-paga")->assertOk()
            : $this->actingAs($this->org)->post("/patotas/{$this->turma->id}/despesas/{$despesa->id}/marcar-paga")->assertRedirect();

        $this->assertSame('fechada', $despesa->fresh()->status);
        $this->assertSame(0.0, $despesa->fresh()->saldoAberto());
    }

    public function test_rateada_nao_pode_ser_marcada_como_paga(): void
    {
        [$despesa] = $this->rateada();

        $this->actingAs($this->org, 'sanctum')->postJson("/api/v1/despesas/{$despesa->id}/marcar-paga")->assertStatus(422);
    }

    #[DataProvider('vias')]
    public function test_despesa_com_pagamento_nao_e_removida_e_cancelar_mantem_o_pago(string $via): void
    {
        [$despesa, , [$a, $b]] = $this->rateada();
        $this->baixa($a, $this->parcela($despesa, $a), '10.00')->assertOk();

        if ($via === 'api') {
            $this->actingAs($this->org, 'sanctum')->deleteJson("/api/v1/despesas/{$despesa->id}")->assertStatus(422);
            $this->actingAs($this->org, 'sanctum')->postJson("/api/v1/despesas/{$despesa->id}/cancelar")->assertOk();
        } else {
            $this->actingAs($this->org)->from('/x')->delete("/patotas/{$this->turma->id}/despesas/{$despesa->id}")->assertSessionHas('erro');
            $this->actingAs($this->org)->post("/patotas/{$this->turma->id}/despesas/{$despesa->id}/cancelar")->assertRedirect();
        }

        $despesa->refresh();
        $this->assertSame('cancelada', $despesa->status);
        $this->assertSame('10.00', $this->parcela($despesa, $a)->valor_pago);
        $this->assertSame('cancelado', $this->parcela($despesa, $b)->status);
        $this->assertSame(0.0, $despesa->saldoAberto());
        $this->baixa($b, $this->parcela($despesa, $b), '1.00')->assertStatus(422);
    }

    public function test_despesa_sem_pagamento_e_removida_e_o_historico_fica(): void
    {
        [$despesa] = $this->rateada();

        $this->actingAs($this->org, 'sanctum')->deleteJson("/api/v1/despesas/{$despesa->id}")->assertOk();

        $this->assertNull($despesa->fresh());
        $this->assertDatabaseHas('movimentos_financeiros', ['patota_id' => $this->turma->id, 'acao' => 'removeu', 'user_id' => $this->org->id]);
    }

    public function test_partida_cancelada_avisa_na_despesa(): void
    {
        [$despesa, $partida] = $this->rateada();
        $partida->update(['status' => 'cancelada']);

        $this->actingAs($this->org)->get("/patotas/{$this->turma->id}/despesas/{$despesa->id}")
            ->assertOk()->assertSee('A partida desta despesa foi cancelada');
    }

    public function test_historico_registra_quem_fez_e_aparece_so_para_quem_organiza(): void
    {
        [$despesa, , [$a]] = $this->rateada();
        $this->baixa($a, $this->parcela($despesa, $a), '10.00')->assertOk();

        $this->assertDatabaseHas('movimentos_financeiros', ['despesa_id' => $despesa->id, 'acao' => 'criou', 'user_id' => $this->org->id]);
        $this->assertDatabaseHas('movimentos_financeiros', ['despesa_id' => $despesa->id, 'acao' => 'baixa', 'user_id' => $a->id, 'valor_centavos' => 1000]);

        $url = "/patotas/{$this->turma->id}/despesas/{$despesa->id}";
        $this->actingAs($this->org)->get($url)->assertOk()->assertSee('Histórico')->assertSee('R$ 10,00');
        $this->actingAs($a)->get($url)->assertOk()->assertDontSee('Histórico');
    }

    public function test_organizador_da_baixa_so_de_quem_nao_e_mais_membro_ativo(): void
    {
        [$despesa, , [$a, $b]] = $this->rateada();

        $this->baixa($this->pessoas['organizador'], $this->parcela($despesa, $a), '10.00')->assertForbidden();

        $this->actingAs($b)->post("/patotas/{$this->turma->id}/sair")->assertRedirect()->assertSessionHas('status', fn ($m) => str_contains($m, 'R$ 33,33'));

        $this->baixa($this->pessoas['organizador'], $this->parcela($despesa, $b), '10.00')->assertOk();
        $this->assertSame('10.00', $this->parcela($despesa, $b)->valor_pago);
        $this->assertDatabaseHas('movimentos_financeiros', ['despesa_id' => $despesa->id, 'acao' => 'baixa', 'user_id' => $this->pessoas['organizador']->id]);
        $this->baixa($b, $this->parcela($despesa, $b), '1.00')->assertForbidden();
    }

    public function test_teto_de_valor_da_despesa_e_recusado_sem_erro_500(): void
    {
        $this->criar('api', $this->dados(['rateada' => 0, 'valor_total' => '1000000.00']))->assertStatus(422);
        $this->criar('api', $this->dados(['rateada' => 0, 'valor_total' => '99999999999']))->assertStatus(422);
        $this->assertSame(0, Despesa::count());
    }
}
