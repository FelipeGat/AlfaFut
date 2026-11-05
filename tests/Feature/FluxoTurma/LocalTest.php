<?php

namespace Tests\Feature\FluxoTurma;

use App\Models\Local;
use App\Models\Patota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocalTest extends TestCase
{
    use MontaTurma, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->montarTurma();
    }

    private function local(array $dados = []): Local
    {
        return Local::create(['patota_id' => $this->turma->id, 'nome' => 'Quadra da Vila', ...$dados]);
    }

    public function test_organizador_cadastra_altera_e_remove(): void
    {
        $this->actingAs($this->pessoas['organizador']);

        $this->post(route('patotas.locais.store', $this->turma), ['nome' => 'Quadra Nova', 'coberto' => 1, 'acessivel_cadeirante' => 1])
            ->assertRedirect(route('patotas.locais.index', $this->turma));
        $local = Local::where('nome', 'Quadra Nova')->firstOrFail();
        $this->assertSame($this->turma->id, $local->patota_id);
        $this->assertTrue($local->coberto);

        $this->get(route('patotas.locais.index', $this->turma))->assertSee('Quadra Nova');
        $this->get(route('patotas.locais.edit', [$this->turma, $local]))->assertOk()->assertSee('Quadra Nova');

        $this->put(route('patotas.locais.update', [$this->turma, $local]), ['nome' => 'Quadra Velha'])
            ->assertRedirect(route('patotas.locais.index', $this->turma));
        $local->refresh();
        $this->assertSame('Quadra Velha', $local->nome);
        $this->assertFalse($local->coberto, 'caixa desmarcada precisa ficar desmarcada');
        $this->assertFalse($local->acessivel_cadeirante);

        $this->delete(route('patotas.locais.destroy', [$this->turma, $local]))
            ->assertRedirect(route('patotas.locais.index', $this->turma));
        $this->assertNull(Local::find($local->id));
    }

    public function test_campos_invalidos_viram_erro(): void
    {
        $this->actingAs($this->pessoas['organizador']);

        $this->post(route('patotas.locais.store', $this->turma), ['nome' => ''])->assertSessionHasErrors('nome');
        $this->post(route('patotas.locais.store', $this->turma), ['nome' => 'A', 'estado' => 'XYZ'])->assertSessionHasErrors('estado');
        $this->post(route('patotas.locais.store', $this->turma), ['nome' => 'A', 'tipo_piso' => 'lava'])->assertSessionHasErrors('tipo_piso');
        $this->post(route('patotas.locais.store', $this->turma), ['nome' => 'A', 'valor_locacao' => '100000000'])->assertSessionHasErrors('valor_locacao');
    }

    public function test_local_de_outra_turma_nao_abre_pelo_endereco_desta(): void
    {
        $outra = Patota::factory()->create(['criador_id' => $this->pessoas['fora']->id]);
        $alheio = Local::create(['patota_id' => $outra->id, 'nome' => 'Local alheio']);
        $this->actingAs($this->pessoas['organizador']);

        $this->get(route('patotas.locais.edit', [$this->turma, $alheio]))->assertNotFound();
        $this->put(route('patotas.locais.update', [$this->turma, $alheio]), ['nome' => 'Mudou'])->assertNotFound();
        $this->delete(route('patotas.locais.destroy', [$this->turma, $alheio]))->assertNotFound();
        $this->get(route('patotas.locais.index', $this->turma))->assertDontSee('Local alheio');

        $this->assertSame('Local alheio', $alheio->fresh()->nome);
    }

    public function test_local_com_partida_futura_nao_pode_ser_removido(): void
    {
        $local = $this->local();
        $this->partida(['local_id' => $local->id]);

        $this->actingAs($this->pessoas['organizador'])
            ->delete(route('patotas.locais.destroy', [$this->turma, $local]))
            ->assertSessionHas('erro');

        $this->assertNotNull(Local::find($local->id));
    }

    public function test_local_so_com_partida_passada_ou_cancelada_pode_ser_removido(): void
    {
        $local = $this->local();
        $this->partida(['local_id' => $local->id, 'data_hora' => now()->subDays(3)]);
        $cancelada = $this->partida(['local_id' => $local->id, 'status' => 'cancelada']);

        $this->actingAs($this->pessoas['organizador'])
            ->delete(route('patotas.locais.destroy', [$this->turma, $local]))
            ->assertSessionMissing('erro');

        $this->assertNull(Local::find($local->id));
        $this->assertNull($cancelada->fresh()->local_id);
    }

    /** @return array<string, array{string}> */
    public static function semPermissao(): array
    {
        return ['fora' => ['fora'], 'ex-membro' => ['ex_membro'], 'removido' => ['removido'], 'membro' => ['membro']];
    }

    #[DataProvider('semPermissao')]
    public function test_quem_nao_organiza_e_recusado(string $pessoa): void
    {
        $local = $this->local();
        $this->actingAs($this->pessoas[$pessoa]);

        $this->get(route('patotas.locais.index', $this->turma))->assertForbidden();
        $this->get(route('patotas.locais.create', $this->turma))->assertForbidden();
        $this->post(route('patotas.locais.store', $this->turma), ['nome' => 'X'])->assertForbidden();
        $this->post(route('patotas.locais.store', $this->turma), [])->assertForbidden();
        $this->get(route('patotas.locais.edit', [$this->turma, $local]))->assertForbidden();
        $this->put(route('patotas.locais.update', [$this->turma, $local]), ['nome' => 'X'])->assertForbidden();
        $this->delete(route('patotas.locais.destroy', [$this->turma, $local]))->assertForbidden();

        $this->assertSame('Quadra da Vila', $local->fresh()->nome);
    }

    public function test_formulario_de_partida_leva_ao_cadastro_de_local(): void
    {
        $this->actingAs($this->pessoas['organizador'])
            ->get(route('partidas.create', $this->turma))
            ->assertSee(route('patotas.locais.create', $this->turma));
    }
}
