<?php

namespace Tests\Unit;

use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use App\Models\User;
use App\Services\SorteioTimes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SorteioTimesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sortear_distribui_jogadores_entre_dois_times(): void
    {
        $admin = User::factory()->create();
        $patota = Patota::factory()->create([
            'criador_id' => $admin->id,
            'jogadores_por_time' => 5,
            'quantidade_times' => 2,
        ]);
        $partida = Partida::factory()->create([
            'patota_id' => $patota->id,
            'organizador_id' => $admin->id,
            'vagas_total' => 10,
        ]);

        $jogadores = User::factory()->count(10)->sequence(
            ['nivel_habilidade' => 'iniciante'],
            ['nivel_habilidade' => 'intermediario'],
            ['nivel_habilidade' => 'avancado'],
        )->create();

        foreach ($jogadores as $j) {
            PartidaConfirmacao::create([
                'partida_id' => $partida->id,
                'user_id' => $j->id,
                'status' => 'confirmado',
                'em_lista_espera' => false,
            ]);
        }

        $times = (new SorteioTimes())->sortear($partida);

        $this->assertCount(2, $times);
        $total = $times->sum(fn ($t) => $t->jogadores->count());
        $this->assertSame(10, $total);
    }

    public function test_goleiros_sao_distribuidos_um_por_time(): void
    {
        $admin = User::factory()->create();
        $patota = Patota::factory()->create([
            'criador_id' => $admin->id,
            'jogadores_por_time' => 5,
            'quantidade_times' => 2,
        ]);
        $partida = Partida::factory()->create([
            'patota_id' => $patota->id,
            'organizador_id' => $admin->id,
            'vagas_total' => 10,
        ]);

        // 2 goleiros + 8 de linha
        $goleiros = User::factory()->count(2)->state(['posicao_preferida' => 'goleiro'])->create();
        $linha = User::factory()->count(8)->state(['posicao_preferida' => 'meia'])->create();

        foreach ($goleiros->merge($linha) as $j) {
            PartidaConfirmacao::create([
                'partida_id' => $partida->id,
                'user_id' => $j->id,
                'status' => 'confirmado',
                'em_lista_espera' => false,
            ]);
        }

        $times = (new SorteioTimes())->sortear($partida);

        foreach ($times as $time) {
            $goleirosNoTime = $time->jogadores->where('pivot.posicao', 'goleiro')->count();
            $this->assertSame(1, $goleirosNoTime, "Time {$time->nome} deveria ter 1 goleiro");
        }
    }
}
