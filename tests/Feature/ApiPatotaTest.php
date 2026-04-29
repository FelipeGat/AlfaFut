<?php

namespace Tests\Feature;

use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPatotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_patota_e_torna_criador_admin(): void
    {
        $user = User::factory()->create();

        $r = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/patotas', [
                'nome' => 'Patota Teste',
                'cidade' => 'Joinville',
                'estado' => 'SC',
                'jogadores_por_time' => 5,
                'quantidade_times' => 2,
            ]);

        $r->assertCreated()->assertJsonPath('nome', 'Patota Teste');
        $patota = Patota::firstWhere('nome', 'Patota Teste');
        $this->assertDatabaseHas('patota_membros', [
            'patota_id' => $patota->id,
            'user_id' => $user->id,
            'papel' => 'administrador',
        ]);
    }

    public function test_lista_apenas_patotas_do_usuario(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $patotaAlice = Patota::factory()->create(['criador_id' => $alice->id]);
        Patota::factory()->create(['criador_id' => $bob->id]);
        PatotaMembro::create(['patota_id' => $patotaAlice->id, 'user_id' => $alice->id, 'papel' => 'administrador', 'status' => 'ativo']);

        $r = $this->actingAs($alice, 'sanctum')->getJson('/api/v1/patotas');
        $r->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_nao_membro_nao_pode_ver_detalhe(): void
    {
        $bob = User::factory()->create();
        $patota = Patota::factory()->create();

        $this->actingAs($bob, 'sanctum')
            ->getJson("/api/v1/patotas/{$patota->id}")
            ->assertForbidden();
    }

    public function test_entrar_por_codigo_de_convite(): void
    {
        $user = User::factory()->create();
        $patota = Patota::factory()->create(['codigo_convite' => 'TESTECOD']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/patotas/entrar', ['codigo_convite' => 'TESTECOD'])
            ->assertOk();

        $this->assertDatabaseHas('patota_membros', [
            'patota_id' => $patota->id,
            'user_id' => $user->id,
            'papel' => 'membro',
        ]);
    }
}
