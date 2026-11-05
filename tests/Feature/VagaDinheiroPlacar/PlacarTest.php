<?php

namespace Tests\Feature\VagaDinheiroPlacar;

use App\Models\Partida;
use App\Models\Time;
use App\Models\TimeJogador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FluxoTurma\MontaTurma;
use Tests\TestCase;

class PlacarTest extends TestCase
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

    private function pedir(string $via, string $acao, Partida $partida, array $dados = [])
    {
        return $via === 'api'
            ? $this->actingAs($this->org, 'sanctum')->postJson("/api/v1/partidas/{$partida->id}/{$acao}", $dados)
            : $this->actingAs($this->org)->from("/partidas/{$partida->id}/controle")->post("/partidas/{$partida->id}/{$acao}", $dados);
    }

    private function assertRecusado(string $via, $resposta): void
    {
        if ($via === 'api') {
            $resposta->assertStatus(422)->assertJsonStructure(['mensagem']);

            return;
        }

        $resposta->assertRedirect()->assertSessionHas('erro');
    }

    /** @return array{Partida, array<int, Time>, array<int, User>} partida com times de 2 jogadores cada */
    private function comTimes(int $quantidade = 2, array $dados = []): array
    {
        $partida = $this->partida($dados);
        $times = [];
        $jogadores = [];

        foreach (range(1, $quantidade) as $i) {
            $time = Time::create(['partida_id' => $partida->id, 'nome' => "Time {$i}"]);
            $times[] = $time;
            foreach (range(1, 2) as $j) {
                $jogador = $this->novoMembro();
                TimeJogador::create(['time_id' => $time->id, 'user_id' => $jogador->id]);
                $jogadores[$time->id][] = $jogador;
            }
        }

        return [$partida, $times, $jogadores];
    }

    #[DataProvider('vias')]
    public function test_segundo_toque_em_iniciar_nao_zera_o_placar(string $via): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();
        $this->pedir($via, 'iniciar', $partida);
        $this->pedir($via, 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $jogadores[$times[0]->id][0]->id]);
        $eventos = $partida->eventos()->count();
        $iniciadaEm = $partida->fresh()->iniciada_em;

        $this->travel(5)->minutes();
        $this->pedir($via, 'iniciar', $partida)->assertSessionHasNoErrors();

        $partida->refresh();
        $this->assertSame(1, (int) $partida->placar_a);
        $this->assertSame($eventos, $partida->eventos()->count());
        $this->assertTrue($iniciadaEm->equalTo($partida->iniciada_em));
    }

    #[DataProvider('vias')]
    public function test_retomar_mantem_placar_e_tempo(string $via): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();
        $this->pedir($via, 'iniciar', $partida);
        $this->pedir($via, 'gol', $partida, ['time_id' => $times[1]->id, 'jogador_id' => $jogadores[$times[1]->id][0]->id]);
        $this->travel(2)->minutes();
        $this->pedir($via, 'pausar', $partida);
        $acumulado = (int) $partida->fresh()->tempo_acumulado_segundos;
        $this->pedir($via, 'pausar', $partida);
        $this->assertSame($acumulado, (int) $partida->fresh()->tempo_acumulado_segundos);

        $this->travel(10)->minutes();
        $this->pedir($via, 'iniciar', $partida);

        $partida->refresh();
        $this->assertSame(1, (int) $partida->placar_b);
        $this->assertTrue($partida->emAndamento());
        $this->assertGreaterThanOrEqual(120, $partida->tempoAtualSegundos());
        $this->assertLessThan(130, $partida->tempoAtualSegundos());
    }

    public static function acoes(): array
    {
        return ['iniciar' => ['iniciar'], 'pausar' => ['pausar'], 'finalizar' => ['finalizar'], 'gol' => ['gol']];
    }

    #[DataProvider('acoes')]
    public function test_partida_cancelada_recusa_o_controle(string $acao): void
    {
        [$partida, $times, $jogadores] = $this->comTimes(2, ['status' => 'cancelada']);

        foreach (['api', 'site'] as $via) {
            $this->assertRecusado($via, $this->pedir($via, $acao, $partida, ['time_id' => $times[0]->id, 'jogador_id' => $jogadores[$times[0]->id][0]->id]));
        }

        $this->assertSame('cancelada', $partida->fresh()->status);
        $this->assertSame(0, $partida->eventos()->count());
    }

    #[DataProvider('vias')]
    public function test_iniciar_sem_times_pede_para_sortear(string $via): void
    {
        $partida = $this->partida();

        $this->assertRecusado($via, $this->pedir($via, 'iniciar', $partida));
        $this->assertNull($partida->fresh()->iniciada_em);
    }

    #[DataProvider('vias')]
    public function test_finalizar_de_novo_nao_muda_a_hora_nem_o_tempo(string $via): void
    {
        [$partida] = $this->comTimes();
        $this->pedir($via, 'iniciar', $partida);
        $this->travel(3)->minutes();
        $this->pedir($via, 'finalizar', $partida);
        $primeira = $partida->fresh();

        $this->travel(30)->minutes();
        $this->pedir($via, 'finalizar', $partida);

        $segunda = $partida->fresh();
        $this->assertTrue($primeira->finalizada_em->equalTo($segunda->finalizada_em));
        $this->assertSame((int) $primeira->tempo_acumulado_segundos, (int) $segunda->tempo_acumulado_segundos);
        $this->assertSame(1, $partida->eventos()->where('tipo', 'fim')->count());
    }

    #[DataProvider('vias')]
    public function test_partida_que_nao_comecou_nao_finaliza_nem_recebe_gol(string $via): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();

        $this->assertRecusado($via, $this->pedir($via, 'finalizar', $partida));
        $this->assertRecusado($via, $this->pedir($via, 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $jogadores[$times[0]->id][0]->id]));
        $this->assertNull($partida->fresh()->finalizada_em);
        $this->assertSame(0, (int) $times[0]->fresh()->gols);
    }

    #[DataProvider('vias')]
    public function test_gol_depois_do_fim_e_recusado(string $via): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();
        $this->pedir($via, 'iniciar', $partida);
        $this->pedir($via, 'finalizar', $partida);

        $this->assertRecusado($via, $this->pedir($via, 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $jogadores[$times[0]->id][0]->id]));
        $this->assertSame(0, (int) $partida->fresh()->placar_a);
    }

    #[DataProvider('vias')]
    public function test_gol_aceito_durante_a_pausa(string $via): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();
        $this->pedir($via, 'iniciar', $partida);
        $this->pedir($via, 'pausar', $partida);

        $this->pedir($via, 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $jogadores[$times[0]->id][0]->id]);

        $this->assertSame(1, (int) $partida->fresh()->placar_a);
    }

    #[DataProvider('vias')]
    public function test_gol_de_jogador_de_outro_time_ou_de_fora_e_recusado(string $via): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();
        $this->pedir($via, 'iniciar', $partida);

        $this->assertRecusado($via, $this->pedir($via, 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $jogadores[$times[1]->id][0]->id]));
        $this->assertRecusado($via, $this->pedir($via, 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $this->pessoas['fora']->id]));
        $this->assertSame(0, (int) $times[0]->fresh()->gols);
    }

    public function test_gol_contra_conta_para_o_time_e_nao_entra_na_artilharia(): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();
        $this->pedir('api', 'iniciar', $partida);
        $autor = $jogadores[$times[1]->id][0];

        $this->pedir('api', 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $autor->id, 'tipo' => 'gol_contra'])->assertOk();
        $this->assertRecusado('api', $this->pedir('api', 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $jogadores[$times[0]->id][0]->id, 'tipo' => 'gol_contra']));

        $this->assertSame(1, (int) $times[0]->fresh()->gols);
        $this->assertSame(0, (int) TimeJogador::where('time_id', $times[1]->id)->where('user_id', $autor->id)->value('gols'));
    }

    public function test_remover_gol_corrige_placar_time_e_artilharia(): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();
        $autor = $jogadores[$times[0]->id][0];
        $this->pedir('site', 'iniciar', $partida);
        $this->pedir('site', 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $autor->id]);
        $this->pedir('site', 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $autor->id]);
        $this->assertSame(2, (int) TimeJogador::where('time_id', $times[0]->id)->where('user_id', $autor->id)->value('gols'));

        $evento = $partida->gols()->first();
        $this->actingAs($this->org)->delete("/partidas/{$partida->id}/eventos/{$evento->id}")->assertRedirect();

        $this->assertSame(1, (int) $partida->fresh()->placar_a);
        $this->assertSame(1, (int) $times[0]->fresh()->gols);
        $this->assertSame(1, (int) TimeJogador::where('time_id', $times[0]->id)->where('user_id', $autor->id)->value('gols'));
    }

    public function test_remover_gol_depois_do_fim_e_recusado(): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();
        $this->pedir('site', 'iniciar', $partida);
        $this->pedir('site', 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $jogadores[$times[0]->id][0]->id]);
        $this->pedir('site', 'finalizar', $partida);
        $evento = $partida->gols()->first();

        $this->actingAs($this->org)->from('/x')->delete("/partidas/{$partida->id}/eventos/{$evento->id}")
            ->assertSessionHas('erro');

        $this->assertSame(1, (int) $partida->fresh()->placar_a);
    }

    public function test_tres_times_aparecem_no_controle_na_tv_e_no_resultado(): void
    {
        [$partida, $times, $jogadores] = $this->comTimes(3);
        $this->pedir('site', 'iniciar', $partida);
        $this->pedir('site', 'gol', $partida, ['time_id' => $times[2]->id, 'jogador_id' => $jogadores[$times[2]->id][0]->id]);
        $this->pedir('site', 'gol', $partida, ['time_id' => $times[2]->id, 'jogador_id' => $jogadores[$times[2]->id][1]->id]);

        $this->actingAs($this->org)->get("/partidas/{$partida->id}/controle")->assertOk()
            ->assertSee('data-placar-time="'.$times[2]->id.'"', false);
        $this->actingAs($this->org)->get("/partidas/{$partida->id}/tv")->assertOk()->assertSee('dados.times', false)->assertSee('Time 3');
        $this->actingAs($this->org)->getJson("/partidas/{$partida->id}/dados")->assertOk()->assertJsonPath('times.2.gols', 2);

        $this->pedir('site', 'finalizar', $partida);
        $this->actingAs($this->org)->get("/partidas/{$partida->id}/resultado")->assertOk()
            ->assertSee('data-placar-time="'.$times[2]->id.'"', false)
            ->assertSeeInOrder(['Time 3', 'venceu']);
    }

    public function test_resultado_com_tres_times_aponta_empate_na_lideranca(): void
    {
        [$partida, $times, $jogadores] = $this->comTimes(3);
        $this->pedir('site', 'iniciar', $partida);
        $this->pedir('site', 'gol', $partida, ['time_id' => $times[1]->id, 'jogador_id' => $jogadores[$times[1]->id][0]->id]);
        $this->pedir('site', 'gol', $partida, ['time_id' => $times[2]->id, 'jogador_id' => $jogadores[$times[2]->id][0]->id]);
        $this->pedir('site', 'finalizar', $partida);

        $this->actingAs($this->org)->get("/partidas/{$partida->id}/resultado")->assertOk()
            ->assertSee('Empate entre Time 2 e Time 3')
            ->assertDontSee('venceu');
    }

    public function test_resultado_nao_quebra_com_gol_sem_jogador(): void
    {
        [$partida, $times] = $this->comTimes();
        $this->pedir('api', 'iniciar', $partida);
        $this->pedir('api', 'gol', $partida, ['time_id' => $times[0]->id])->assertOk();
        $this->pedir('api', 'finalizar', $partida);

        $this->actingAs($this->org)->get("/partidas/{$partida->id}/resultado")->assertOk();
    }

    public function test_api_nao_muda_a_partida_para_em_andamento_ou_finalizada_pela_edicao(): void
    {
        [$partida] = $this->comTimes();

        foreach (['em_andamento', 'finalizada'] as $status) {
            $this->actingAs($this->org, 'sanctum')
                ->patchJson("/api/v1/partidas/{$partida->id}", ['status' => $status])
                ->assertStatus(422);
        }

        $this->assertSame('agendada', $partida->fresh()->status);
    }

    public function test_time_com_limite_de_gols_recusa_sem_erro_500(): void
    {
        [$partida, $times, $jogadores] = $this->comTimes();
        $this->pedir('api', 'iniciar', $partida);
        $times[0]->update(['gols' => 255]);

        $this->assertRecusado('api', $this->pedir('api', 'gol', $partida, ['time_id' => $times[0]->id, 'jogador_id' => $jogadores[$times[0]->id][0]->id]));
    }
}
