<?php

namespace Tests\Feature\VagaDinheiroPlacar;

use App\Models\PartidaConfirmacao;
use App\Models\Time;
use App\Models\User;
use App\Services\SorteioTimes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\FluxoTurma\MontaTurma;
use Tests\TestCase;

class SorteioTest extends TestCase
{
    use MontaTurma, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->montarTurma();
    }

    private function confirmar($partida, User $user): void
    {
        PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $user->id, 'status' => 'confirmado', 'em_lista_espera' => false, 'confirmado_em' => now()]);
    }

    public function test_sorteio_recusado_em_partida_iniciada_finalizada_ou_cancelada(): void
    {
        foreach ([
            ['status' => 'em_andamento', 'iniciada_em' => now()],
            ['status' => 'finalizada', 'iniciada_em' => now()->subHour(), 'finalizada_em' => now()],
            ['status' => 'cancelada'],
        ] as $dados) {
            $partida = $this->partida($dados);
            $time = Time::create(['partida_id' => $partida->id, 'nome' => 'Time 1']);
            $this->confirmar($partida, $this->novoMembro());
            $this->confirmar($partida, $this->novoMembro());

            $this->actingAs($this->pessoas['criador'], 'sanctum')->postJson("/api/v1/partidas/{$partida->id}/sortear")->assertStatus(422);
            $this->actingAs($this->pessoas['criador'])->from('/x')->post("/partidas/{$partida->id}/sortear")->assertSessionHas('erro');

            $this->assertNotNull($time->fresh());
        }
    }

    public function test_cem_sorteios_ficam_com_diferenca_de_no_maximo_um_jogador_e_sem_time_vazio(): void
    {
        $pool = collect(range(1, 24))->map(fn () => $this->novoMembro());
        $niveis = ['iniciante', 'intermediario', 'avancado', null];
        $posicoes = ['goleiro', 'zagueiro', 'meia', 'atacante', null];

        foreach (range(1, 100) as $rodada) {
            mt_srand($rodada);
            $this->turma->update(['quantidade_times' => mt_rand(2, 6)]);
            $partida = $this->partida(['vagas_total' => 30]);
            $escolhidos = $pool->shuffle()->take(mt_rand(2, 24));

            foreach ($escolhidos as $jogador) {
                $jogador->update([
                    'nivel_habilidade' => $niveis[array_rand($niveis)],
                    'posicao_preferida' => $posicoes[array_rand($posicoes)],
                ]);
                $this->confirmar($partida, $jogador);
            }

            $times = app(SorteioTimes::class)->sortear($partida->fresh());
            $tamanhos = $times->map(fn ($t) => $t->jogadores->count());

            $this->assertGreaterThan(0, $tamanhos->min(), "rodada {$rodada}: time vazio");
            $this->assertLessThanOrEqual(1, $tamanhos->max() - $tamanhos->min(), "rodada {$rodada}: {$tamanhos->implode(',')}");
            $this->assertSame($escolhidos->count(), $tamanhos->sum());
        }
    }

    public function test_soma_de_nivel_fica_equilibrada(): void
    {
        $partida = $this->partida(['vagas_total' => 12]);
        foreach (range(1, 6) as $i) {
            foreach (['avancado', 'iniciante'] as $nivel) {
                $jogador = $this->novoMembro();
                $jogador->update(['nivel_habilidade' => $nivel, 'posicao_preferida' => 'meia']);
                $this->confirmar($partida, $jogador);
            }
        }

        $times = app(SorteioTimes::class)->sortear($partida->fresh());
        $peso = ['iniciante' => 1, 'intermediario' => 2, 'avancado' => 3];

        $this->assertSame(
            $times[0]->jogadores->sum(fn ($j) => $peso[$j->nivel_habilidade]),
            $times[1]->jogadores->sum(fn ($j) => $peso[$j->nivel_habilidade]),
        );
    }

    public function test_menos_confirmados_que_times_nao_cria_time_vazio(): void
    {
        $this->turma->update(['quantidade_times' => 6]);
        $partida = $this->partida();
        foreach (range(1, 3) as $i) {
            $this->confirmar($partida, $this->novoMembro());
        }

        $times = app(SorteioTimes::class)->sortear($partida->fresh());

        $this->assertCount(3, $times);
        $this->assertSame([1, 1, 1], $times->map(fn ($t) => $t->jogadores->count())->values()->all());
    }

    public function test_jogador_sem_nivel_nem_posicao_entra_sem_erro(): void
    {
        $partida = $this->partida();
        foreach (range(1, 4) as $i) {
            $jogador = $this->novoMembro();
            $jogador->update(['nivel_habilidade' => null, 'posicao_preferida' => null]);
            $this->confirmar($partida, $jogador);
        }

        $times = app(SorteioTimes::class)->sortear($partida->fresh());

        $this->assertSame(4, $times->sum(fn ($t) => $t->jogadores->count()));
    }

    public function test_perfil_no_site_informa_apelido_posicao_e_nivel(): void
    {
        $user = $this->pessoas['membro'];

        $this->actingAs($user)->get('/profile')->assertOk()
            ->assertSee('name="apelido"', false)
            ->assertSee('name="posicao_preferida"', false)
            ->assertSee('name="nivel_habilidade"', false);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'apelido' => 'Marquinhos',
            'posicao_preferida' => 'goleiro',
            'nivel_habilidade' => 'avancado',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Marquinhos', $user->apelido);
        $this->assertSame('goleiro', $user->posicao_preferida);
        $this->assertSame('avancado', $user->nivel_habilidade);

        $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'posicao_preferida' => 'presidente',
            'nivel_habilidade' => 'lenda',
        ])->assertSessionHasErrors(['posicao_preferida', 'nivel_habilidade']);
    }
}
