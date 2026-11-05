<?php

namespace Tests\Feature\FluxoTurma;

use App\Models\Campo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampoTest extends TestCase
{
    use RefreshDatabase;

    private User $dono;

    private User $outroDono;

    private User $jogador;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dono = User::factory()->create(['tipo_usuario' => 'dono_campo']);
        $this->outroDono = User::factory()->create(['tipo_usuario' => 'dono_campo']);
        $this->jogador = User::factory()->create(['tipo_usuario' => 'jogador']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function campo(array $dados = []): Campo
    {
        return Campo::create(['dono_id' => $this->dono->id, 'nome' => 'Arena', 'ativo' => true, ...$dados]);
    }

    public function test_quem_nao_e_dono_de_campo_nao_cadastra(): void
    {
        foreach (['jogador', 'dono_pelada'] as $tipo) {
            $pessoa = User::factory()->create(['tipo_usuario' => $tipo]);
            $this->actingAs($pessoa);

            $this->get(route('meus-campos.index'))->assertForbidden();
            $this->get(route('meus-campos.create'))->assertForbidden();
            $this->post(route('meus-campos.store'), ['nome' => 'Clandestino'])->assertForbidden();
            $this->post(route('meus-campos.store'), [])->assertForbidden();
        }

        $this->assertSame(0, Campo::count());
    }

    public function test_dono_cadastra_e_o_campo_nasce_ativo_no_catalogo(): void
    {
        $this->actingAs($this->dono)
            ->post(route('meus-campos.store'), ['nome' => 'Campo Novo', 'coberto' => 1])
            ->assertRedirect(route('meus-campos.index'));

        $campo = Campo::where('nome', 'Campo Novo')->firstOrFail();
        $this->assertTrue($campo->ativo);
        $this->assertTrue($campo->coberto);
        $this->assertFalse($campo->possui_vestiario);

        $this->actingAs($this->jogador)->get(route('campos.catalogo'))->assertSee('Campo Novo');
    }

    public function test_caixas_desmarcam(): void
    {
        $campo = $this->campo([
            'coberto' => true,
            'possui_vestiario' => true,
            'possui_estacionamento' => true,
            'acessivel_cadeirante' => true,
        ]);

        $this->actingAs($this->dono)
            ->put(route('meus-campos.update', $campo), ['nome' => 'Arena'])
            ->assertRedirect(route('meus-campos.index'));

        $campo->refresh();
        $this->assertFalse($campo->coberto);
        $this->assertFalse($campo->possui_vestiario);
        $this->assertFalse($campo->possui_estacionamento);
        $this->assertFalse($campo->acessivel_cadeirante);
        $this->assertFalse($campo->ativo);
    }

    public function test_outro_dono_nao_altera_nem_remove(): void
    {
        $campo = $this->campo();
        $this->actingAs($this->outroDono);

        $this->get(route('meus-campos.edit', $campo))->assertForbidden();
        $this->put(route('meus-campos.update', $campo), ['nome' => 'Mudou'])->assertForbidden();
        $this->delete(route('meus-campos.destroy', $campo))->assertForbidden();

        $this->assertSame('Arena', $campo->fresh()->nome);
    }

    public function test_administrador_da_plataforma_cadastra_altera_e_remove(): void
    {
        $campo = $this->campo();
        $this->actingAs($this->admin);

        $this->get(route('meus-campos.edit', $campo))->assertOk();
        $this->put(route('meus-campos.update', $campo), ['nome' => 'Mudou', 'ativo' => 1])->assertRedirect();
        $this->assertSame('Mudou', $campo->fresh()->nome);
        $this->assertSame($this->dono->id, $campo->fresh()->dono_id);

        $this->post(route('meus-campos.store'), ['nome' => 'Do admin'])->assertRedirect();
        $this->delete(route('meus-campos.destroy', $campo))->assertRedirect();
        $this->assertNull(Campo::find($campo->id));
    }

    public function test_campo_inativo_fica_fora_do_catalogo_e_so_abre_para_dono_e_admin(): void
    {
        $campo = $this->campo(['nome' => 'Arena Fechada', 'ativo' => false]);

        $this->actingAs($this->jogador);
        $this->get(route('campos.catalogo'))->assertDontSee('Arena Fechada');
        $this->get(route('campos.show', $campo))->assertForbidden();

        $this->actingAs($this->outroDono)->get(route('campos.show', $campo))->assertForbidden();
        $this->actingAs($this->dono)->get(route('campos.show', $campo))->assertOk();
        $this->actingAs($this->admin)->get(route('campos.show', $campo))->assertOk();
    }

    public function test_validacao_com_limites(): void
    {
        $this->actingAs($this->dono)
            ->post(route('meus-campos.store'), ['nome' => 'A', 'valor_hora' => '1000000'])
            ->assertSessionHasErrors('valor_hora');
    }
}
