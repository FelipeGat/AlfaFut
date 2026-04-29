<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPartidaTest extends TestCase
{
    use RefreshDatabase;

    private function patotaCom(int $vagas = 4): array
    {
        $admin = User::factory()->create();
        $patota = Patota::factory()->create([
            'criador_id' => $admin->id,
            'jogadores_por_time' => (int) ($vagas / 2),
            'quantidade_times' => 2,
        ]);
        PatotaMembro::create(['patota_id' => $patota->id, 'user_id' => $admin->id, 'papel' => 'administrador', 'status' => 'ativo']);
        return [$admin, $patota];
    }

    public function test_admin_cria_partida(): void
    {
        [$admin, $patota] = $this->patotaCom();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/patotas/{$patota->id}/partidas", [
                'titulo' => 'Pelada de teste',
                'data_hora' => now()->addDays(2)->toIso8601String(),
                'duracao_minutos' => 90,
            ])
            ->assertCreated()
            ->assertJsonPath('titulo', 'Pelada de teste');
    }

    public function test_membro_confirma_presenca(): void
    {
        [$admin, $patota] = $this->patotaCom();
        $jogador = User::factory()->create();
        PatotaMembro::create(['patota_id' => $patota->id, 'user_id' => $jogador->id, 'papel' => 'membro', 'status' => 'ativo']);

        $partida = Partida::factory()->create([
            'patota_id' => $patota->id,
            'organizador_id' => $admin->id,
            'vagas_total' => 4,
        ]);

        $this->actingAs($jogador, 'sanctum')
            ->postJson("/api/v1/partidas/{$partida->id}/confirmar")
            ->assertOk();

        $this->assertDatabaseHas('partida_confirmacoes', [
            'partida_id' => $partida->id,
            'user_id' => $jogador->id,
            'status' => 'confirmado',
            'em_lista_espera' => false,
        ]);
    }

    public function test_partida_cheia_envia_para_lista_de_espera(): void
    {
        [$admin, $patota] = $this->patotaCom();
        $partida = Partida::factory()->create([
            'patota_id' => $patota->id,
            'organizador_id' => $admin->id,
            'vagas_total' => 1,
        ]);

        // Primeiro entra como confirmado
        $primeiro = User::factory()->create();
        PatotaMembro::create(['patota_id' => $patota->id, 'user_id' => $primeiro->id, 'papel' => 'membro', 'status' => 'ativo']);
        $this->actingAs($primeiro, 'sanctum')
            ->postJson("/api/v1/partidas/{$partida->id}/confirmar")
            ->assertOk();

        // Segundo vai para espera
        $segundo = User::factory()->create();
        PatotaMembro::create(['patota_id' => $patota->id, 'user_id' => $segundo->id, 'papel' => 'membro', 'status' => 'ativo']);
        $this->actingAs($segundo, 'sanctum')
            ->postJson("/api/v1/partidas/{$partida->id}/confirmar")
            ->assertOk();

        $this->assertDatabaseHas('partida_confirmacoes', [
            'partida_id' => $partida->id,
            'user_id' => $segundo->id,
            'em_lista_espera' => true,
            'posicao_lista_espera' => 1,
        ]);
    }

    public function test_cancelar_promove_da_lista_de_espera(): void
    {
        [$admin, $patota] = $this->patotaCom();
        $partida = Partida::factory()->create([
            'patota_id' => $patota->id,
            'organizador_id' => $admin->id,
            'vagas_total' => 1,
        ]);

        $confirmado = User::factory()->create();
        $espera = User::factory()->create();
        foreach ([$confirmado, $espera] as $u) {
            PatotaMembro::create(['patota_id' => $patota->id, 'user_id' => $u->id, 'papel' => 'membro', 'status' => 'ativo']);
        }

        $this->actingAs($confirmado, 'sanctum')->postJson("/api/v1/partidas/{$partida->id}/confirmar");
        $this->actingAs($espera, 'sanctum')->postJson("/api/v1/partidas/{$partida->id}/confirmar");

        // Confirmado cancela
        $this->actingAs($confirmado, 'sanctum')
            ->deleteJson("/api/v1/partidas/{$partida->id}/confirmacao")
            ->assertOk();

        // Esperante deve ter sido promovido (em_lista_espera = false)
        $this->assertDatabaseHas('partida_confirmacoes', [
            'partida_id' => $partida->id,
            'user_id' => $espera->id,
            'em_lista_espera' => false,
        ]);
    }
}
