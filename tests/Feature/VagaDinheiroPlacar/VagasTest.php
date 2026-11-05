<?php

namespace Tests\Feature\VagaDinheiroPlacar;

use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Time;
use App\Models\TimeJogador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FluxoTurma\MontaTurma;
use Tests\TestCase;

class VagasTest extends TestCase
{
    use MontaTurma, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->montarTurma();
    }

    public static function vias(): array
    {
        return ['site' => ['site'], 'api' => ['api']];
    }

    private function pedir(string $via, User $quem, string $acao, Partida $partida)
    {
        return $via === 'api'
            ? $this->actingAs($quem, 'sanctum')->postJson("/api/v1/partidas/{$partida->id}/{$acao}")
            : $this->actingAs($quem)->from("/partidas/{$partida->id}")->post("/partidas/{$partida->id}/{$acao}");
    }

    private function assertRecusado(string $via, $resposta): void
    {
        if ($via === 'api') {
            $resposta->assertStatus(422)->assertJsonStructure(['mensagem']);

            return;
        }

        $resposta->assertRedirect()->assertSessionHas('erro');
    }

    private function titular(Partida $partida, User $user): PartidaConfirmacao
    {
        return PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $user->id, 'status' => 'confirmado', 'em_lista_espera' => false, 'confirmado_em' => now()]);
    }

    private function naFila(Partida $partida, User $user, int $posicao): PartidaConfirmacao
    {
        return PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $user->id, 'status' => 'confirmado', 'em_lista_espera' => true, 'posicao_lista_espera' => $posicao, 'confirmado_em' => now()]);
    }

    /** @return array{Partida, User, User, User} partida cheia de 1 vaga, titular e dois na fila */
    private function partidaComFila(array $dados = []): array
    {
        $partida = $this->partida(['vagas_total' => 1, ...$dados]);
        $titular = $this->novoMembro();
        $primeiro = $this->novoMembro();
        $segundo = $this->novoMembro();
        $this->titular($partida, $titular);
        $this->naFila($partida, $primeiro, 1);
        $this->naFila($partida, $segundo, 2);

        return [$partida, $titular, $primeiro, $segundo];
    }

    #[DataProvider('vias')]
    public function test_titular_que_repete_continua_titular_com_a_partida_cheia(string $via): void
    {
        [$partida, $titular] = $this->partidaComFila();

        $this->pedir($via, $titular, 'confirmar', $partida);

        $this->assertSame(1, $partida->confirmados()->count());
        $this->assertFalse((bool) $partida->confirmacoes()->where('user_id', $titular->id)->value('em_lista_espera'));
        $this->assertSame(2, $partida->listaEspera()->count());
    }

    #[DataProvider('vias')]
    public function test_quem_esta_na_fila_mantem_a_posicao_ao_repetir(string $via): void
    {
        [$partida, , $primeiro] = $this->partidaComFila();

        $this->pedir($via, $primeiro, 'confirmar', $partida);

        $this->assertSame(1, $partida->confirmacoes()->where('user_id', $primeiro->id)->value('posicao_lista_espera'));
    }

    public static function partidasFechadas(): array
    {
        return [
            'cancelada' => [fn () => ['status' => 'cancelada'], 'cancelada'],
            'prazo vencido' => [fn () => ['confirmacao_ate' => now()->subHour()], 'prazo'],
            'horário passado' => [fn () => ['data_hora' => now()->subHour()], 'horário'],
            'iniciada' => [fn () => ['status' => 'em_andamento', 'iniciada_em' => now()], 'começou'],
            'finalizada' => [fn () => ['status' => 'finalizada', 'iniciada_em' => now()->subHour(), 'finalizada_em' => now()], 'começou'],
        ];
    }

    #[DataProvider('partidasFechadas')]
    public function test_confirmacao_recusada_em_partida_fechada(callable $dados, string $motivo): void
    {
        $partida = $this->partida($dados());

        $resposta = $this->pedir('api', $this->pessoas['membro'], 'confirmar', $partida);
        $resposta->assertStatus(422);
        $this->assertStringContainsString($motivo, $resposta->json('mensagem'));
        $this->assertSame(0, $partida->confirmacoes()->count());

        $this->assertRecusado('site', $this->pedir('site', $this->pessoas['membro'], 'confirmar', $partida));
        $this->assertSame(0, $partida->confirmacoes()->count());
    }

    #[DataProvider('vias')]
    public function test_partida_cheia_sem_lista_recusa_em_vez_de_pôr_na_fila(string $via): void
    {
        $partida = $this->partida(['vagas_total' => 1, 'lista_espera_habilitada' => false]);
        $this->titular($partida, $this->novoMembro());

        $this->assertRecusado($via, $this->pedir($via, $this->pessoas['membro'], 'confirmar', $partida));
        $this->assertSame(0, $partida->confirmacoes()->where('user_id', $this->pessoas['membro']->id)->count());
    }

    #[DataProvider('vias')]
    public function test_partida_cheia_com_lista_poe_na_fila_ao_fim(string $via): void
    {
        [$partida] = $this->partidaComFila();

        $this->pedir($via, $this->pessoas['membro'], 'confirmar', $partida);

        $this->assertSame(3, $partida->confirmacoes()->where('user_id', $this->pessoas['membro']->id)->value('posicao_lista_espera'));
    }

    #[DataProvider('vias')]
    public function test_recusa_de_titular_promove_o_primeiro_e_renumera_a_fila(string $via): void
    {
        [$partida, $titular, $primeiro, $segundo] = $this->partidaComFila();

        $this->pedir($via, $titular, 'recusar', $partida);

        $this->assertFalse((bool) $partida->confirmacoes()->where('user_id', $primeiro->id)->value('em_lista_espera'));
        $this->assertSame(1, $partida->confirmacoes()->where('user_id', $segundo->id)->value('posicao_lista_espera'));
        $this->assertSame(1, $partida->confirmados()->count());
    }

    #[DataProvider('vias')]
    public function test_recusa_de_quem_esta_na_fila_faz_os_outros_subirem(string $via): void
    {
        [$partida, $titular, $primeiro, $segundo] = $this->partidaComFila();

        $this->pedir($via, $primeiro, 'recusar', $partida);

        $this->assertSame(1, $partida->confirmacoes()->where('user_id', $segundo->id)->value('posicao_lista_espera'));
        $this->assertFalse((bool) $partida->confirmacoes()->where('user_id', $titular->id)->value('em_lista_espera'));
    }

    #[DataProvider('vias')]
    public function test_recusa_vale_depois_do_prazo_de_confirmar_e_a_fila_anda(string $via): void
    {
        [$partida, $titular, $primeiro] = $this->partidaComFila(['confirmacao_ate' => now()->subHour()]);

        $this->pedir($via, $titular, 'recusar', $partida);

        $this->assertSame('recusado', $partida->confirmacoes()->where('user_id', $titular->id)->value('status'));
        $this->assertFalse((bool) $partida->confirmacoes()->where('user_id', $primeiro->id)->value('em_lista_espera'));
    }

    public function test_recusa_em_partida_iniciada_e_recusada(): void
    {
        [$partida, $titular] = $this->partidaComFila(['status' => 'em_andamento', 'iniciada_em' => now()]);

        $this->assertRecusado('api', $this->pedir('api', $titular, 'recusar', $partida));
        $this->assertSame('confirmado', $partida->confirmacoes()->where('user_id', $titular->id)->value('status'));
    }

    public function test_cancelar_pela_api_promove_e_renumera(): void
    {
        [$partida, $titular, $primeiro, $segundo] = $this->partidaComFila();

        $this->actingAs($titular, 'sanctum')->deleteJson("/api/v1/partidas/{$partida->id}/confirmacao")->assertOk();

        $this->assertSame(0, $partida->confirmacoes()->where('user_id', $titular->id)->count());
        $this->assertFalse((bool) $partida->confirmacoes()->where('user_id', $primeiro->id)->value('em_lista_espera'));
        $this->assertSame(1, $partida->confirmacoes()->where('user_id', $segundo->id)->value('posicao_lista_espera'));
    }

    public function test_quem_sai_da_turma_libera_a_vaga_so_nas_partidas_que_nao_comecaram(): void
    {
        [$futura, $titular, $primeiro] = $this->partidaComFila();
        $iniciada = $this->partida(['status' => 'em_andamento', 'iniciada_em' => now()]);
        $this->titular($iniciada, $titular);

        $this->actingAs($titular)->post("/patotas/{$this->turma->id}/sair")->assertRedirect();

        $this->assertSame(0, $futura->confirmacoes()->where('user_id', $titular->id)->count());
        $this->assertFalse((bool) $futura->confirmacoes()->where('user_id', $primeiro->id)->value('em_lista_espera'));
        $this->assertSame(1, $iniciada->confirmacoes()->where('user_id', $titular->id)->count());
    }

    public function test_quem_e_removido_libera_a_vaga(): void
    {
        [$partida, $titular, $primeiro] = $this->partidaComFila();

        $this->actingAs($this->pessoas['criador'])->delete("/patotas/{$this->turma->id}/membros/{$titular->id}")->assertRedirect();

        $this->assertSame(0, $partida->confirmacoes()->where('user_id', $titular->id)->count());
        $this->assertFalse((bool) $partida->confirmacoes()->where('user_id', $primeiro->id)->value('em_lista_espera'));
    }

    public function test_aumentar_as_vagas_promove_da_fila_no_site_e_na_api(): void
    {
        [$partida, , $primeiro, $segundo] = $this->partidaComFila(['vagas_total' => 4]);
        $partida->update(['vagas_total' => 1]);

        $this->actingAs($this->pessoas['criador'], 'sanctum')
            ->patchJson("/api/v1/partidas/{$partida->id}", ['vagas_total' => 4])
            ->assertOk();

        $this->assertSame(3, $partida->confirmados()->count());
        $this->assertSame(0, $partida->listaEspera()->count());
        $this->assertFalse((bool) $partida->confirmacoes()->where('user_id', $segundo->id)->value('em_lista_espera'));

        [$outra, , $terceiro] = $this->partidaComFila();
        $this->actingAs($this->pessoas['criador'])
            ->put("/partidas/{$outra->id}", $this->dadosDePartida(['vagas_total' => 4]))
            ->assertRedirect();
        $this->assertFalse((bool) $outra->confirmacoes()->where('user_id', $terceiro->id)->value('em_lista_espera'));
    }

    #[DataProvider('vias')]
    public function test_quem_recusa_sai_do_time_sorteado(string $via): void
    {
        $partida = $this->partida();
        $jogador = $this->novoMembro();
        $this->titular($partida, $jogador);
        $time = Time::create(['partida_id' => $partida->id, 'nome' => 'Time 1']);
        TimeJogador::create(['time_id' => $time->id, 'user_id' => $jogador->id]);

        $this->pedir($via, $jogador, 'recusar', $partida);

        $this->assertSame(0, $time->escalacao()->count());
    }

    public function test_tela_avisa_o_motivo_e_nao_oferece_confirmar_com_prazo_vencido(): void
    {
        $partida = $this->partida(['confirmacao_ate' => now()->subHour()]);

        $this->actingAs($this->pessoas['membro'])->get("/partidas/{$partida->id}")
            ->assertOk()
            ->assertSee('O prazo para confirmar presença')
            ->assertDontSee(route('partidas.confirmar', $partida));
    }
}
