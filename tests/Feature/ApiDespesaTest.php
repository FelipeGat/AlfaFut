<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDespesaTest extends TestCase
{
    use RefreshDatabase;

    public function test_despesa_rateada_cria_pagamentos_para_confirmados(): void
    {
        $admin = User::factory()->create();
        $patota = Patota::factory()->create(['criador_id' => $admin->id]);
        PatotaMembro::create(['patota_id' => $patota->id, 'user_id' => $admin->id, 'papel' => 'administrador', 'status' => 'ativo']);

        $partida = Partida::factory()->create(['patota_id' => $patota->id, 'organizador_id' => $admin->id]);

        // Cria 4 confirmados
        $confirmados = User::factory()->count(4)->create();
        foreach ($confirmados as $c) {
            PatotaMembro::create(['patota_id' => $patota->id, 'user_id' => $c->id, 'papel' => 'membro', 'status' => 'ativo']);
            PartidaConfirmacao::create([
                'partida_id' => $partida->id,
                'user_id' => $c->id,
                'status' => 'confirmado',
                'em_lista_espera' => false,
            ]);
        }

        $r = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/patotas/{$patota->id}/despesas", [
                'partida_id' => $partida->id,
                'descricao' => 'Aluguel',
                'categoria' => 'locacao',
                'valor_total' => 200.00,
                'data_despesa' => now()->toDateString(),
                'rateada' => true,
            ]);

        $r->assertCreated();

        // 4 confirmados, 200 / 4 = 50 cada
        $this->assertDatabaseCount('pagamentos', 4);
        foreach ($confirmados as $c) {
            $this->assertDatabaseHas('pagamentos', [
                'user_id' => $c->id,
                'valor_devido' => 50.00,
                'status' => 'pendente',
            ]);
        }
    }

    public function test_pagar_quitacao_total_marca_como_pago(): void
    {
        $admin = User::factory()->create();
        $patota = Patota::factory()->create(['criador_id' => $admin->id]);
        PatotaMembro::create(['patota_id' => $patota->id, 'user_id' => $admin->id, 'papel' => 'administrador', 'status' => 'ativo']);

        $jogador = User::factory()->create();
        PatotaMembro::create(['patota_id' => $patota->id, 'user_id' => $jogador->id, 'papel' => 'membro', 'status' => 'ativo']);

        $partida = Partida::factory()->create(['patota_id' => $patota->id, 'organizador_id' => $admin->id]);
        PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $jogador->id, 'status' => 'confirmado']);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/patotas/{$patota->id}/despesas", [
                'partida_id' => $partida->id,
                'descricao' => 'Bola',
                'categoria' => 'material',
                'valor_total' => 90.00,
                'data_despesa' => now()->toDateString(),
                'rateada' => true,
            ])->assertCreated();

        $pagamento = $jogador->pagamentos()->firstOrFail();

        $this->actingAs($jogador, 'sanctum')
            ->postJson("/api/v1/pagamentos/{$pagamento->id}/quitar", [
                'valor_pago' => 90.00,
                'forma_pagamento' => 'pix',
            ])
            ->assertOk();

        $this->assertDatabaseHas('pagamentos', [
            'id' => $pagamento->id,
            'status' => 'pago',
            'valor_pago' => 90.00,
            'forma_pagamento' => 'pix',
        ]);
    }
}
