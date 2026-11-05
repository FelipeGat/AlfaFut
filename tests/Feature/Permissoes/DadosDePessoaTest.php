<?php

namespace Tests\Feature\Permissoes;

use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DadosDePessoaTest extends TestCase
{
    use RefreshDatabase;

    private const PROTEGIDOS = ['email', 'telefone', 'role', 'is_admin', 'preferencias_acessibilidade', 'necessidades'];

    private User $criador;

    private User $organizador;

    private User $membro;

    private User $colega;

    private Patota $patota;

    protected function setUp(): void
    {
        parent::setUp();

        $this->criador = User::factory()->create();
        $this->organizador = User::factory()->create();
        $this->membro = User::factory()->create();
        $this->colega = User::factory()->create([
            'telefone' => '(27) 90000-0000',
            'necessidades_acessibilidade' => ['cadeira_de_rodas'],
        ]);

        $this->patota = Patota::factory()->create(['criador_id' => $this->criador->id]);

        foreach ([
            [$this->criador, 'administrador'],
            [$this->organizador, 'organizador'],
            [$this->membro, 'membro'],
            [$this->colega, 'membro'],
        ] as [$user, $papel]) {
            PatotaMembro::create(['patota_id' => $this->patota->id, 'user_id' => $user->id, 'papel' => $papel, 'status' => 'ativo']);
        }
    }

    public function test_membro_comum_so_ve_campos_publicos_dos_demais(): void
    {
        $linha = $this->membrosVistosPor($this->membro)->firstWhere('id', $this->colega->id);

        $this->assertSame($this->colega->name, $linha['nome']);
        $this->assertArrayHasKey('apelido', $linha);
        $this->assertArrayHasKey('avatar_url', $linha);
        $this->assertArrayHasKey('posicao_preferida', $linha);
        $this->assertArrayHasKey('nivel_habilidade', $linha);

        foreach (self::PROTEGIDOS as $campo) {
            $this->assertArrayNotHasKey($campo, $linha, $campo);
        }
    }

    public function test_quem_organiza_ve_contato_e_necessidades_dos_membros(): void
    {
        foreach ([$this->organizador, $this->criador] as $quem) {
            $linha = $this->membrosVistosPor($quem)->firstWhere('id', $this->colega->id);

            $this->assertSame($this->colega->email, $linha['email']);
            $this->assertSame('(27) 90000-0000', $linha['telefone']);
            $this->assertSame(['cadeira_de_rodas'], $linha['necessidades']);
            $this->assertArrayNotHasKey('role', $linha);
            $this->assertArrayNotHasKey('preferencias_acessibilidade', $linha);
        }
    }

    public function test_admin_da_plataforma_ve_contato_e_papel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $linha = $this->membrosVistosPor($admin)->firstWhere('id', $this->colega->id);

        $this->assertSame($this->colega->email, $linha['email']);
        $this->assertSame('user', $linha['role']);
    }

    public function test_organizador_de_outra_turma_nao_recebe_nada(): void
    {
        $alheio = User::factory()->create();
        $outra = Patota::factory()->create(['criador_id' => $alheio->id]);
        PatotaMembro::create(['patota_id' => $outra->id, 'user_id' => $alheio->id, 'papel' => 'administrador', 'status' => 'ativo']);

        $this->actingAs($alheio, 'sanctum')
            ->getJson("/api/v1/patotas/{$this->patota->id}/membros")
            ->assertForbidden();
    }

    public function test_a_propria_pessoa_recebe_todos_os_dados(): void
    {
        $this->actingAs($this->colega, 'sanctum')
            ->getJson('/api/v1/auth/eu')
            ->assertOk()
            ->assertJsonPath('data.email', $this->colega->email)
            ->assertJsonPath('data.telefone', '(27) 90000-0000')
            ->assertJsonPath('data.role', 'user')
            ->assertJsonPath('data.is_admin', false)
            ->assertJsonPath('data.preferencias_acessibilidade.necessidades', ['cadeira_de_rodas']);
    }

    public function test_login_devolve_os_dados_da_propria_conta(): void
    {
        $this->postJson('/api/v1/auth/login', ['email' => $this->colega->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('usuario.email', $this->colega->email)
            ->assertJsonPath('usuario.is_admin', false);
    }

    public function test_alterar_o_perfil_devolve_os_dados_da_propria_conta(): void
    {
        $this->actingAs($this->colega, 'sanctum')
            ->patchJson('/api/v1/perfil', ['apelido' => 'Novo'])
            ->assertOk()
            ->assertJsonPath('data.email', $this->colega->email);
    }

    public function test_respostas_aninhadas_nao_vazam_contato(): void
    {
        $partida = Partida::factory()->create(['patota_id' => $this->patota->id, 'organizador_id' => $this->colega->id]);
        PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $this->colega->id, 'status' => 'confirmado', 'em_lista_espera' => false]);
        $this->patota->mensagens()->create(['autor_id' => $this->colega->id, 'conteudo' => 'Oi', 'tipo' => 'texto']);
        $this->patota->update(['criador_id' => $this->colega->id]);

        $this->actingAs($this->membro, 'sanctum');

        foreach ([
            "/api/v1/partidas/{$partida->id}",
            "/api/v1/patotas/{$this->patota->id}/partidas",
            "/api/v1/patotas/{$this->patota->id}/mensagens",
            "/api/v1/patotas/{$this->patota->id}",
            '/api/v1/patotas',
        ] as $url) {
            $corpo = $this->getJson($url)->assertOk()->getContent();

            $this->assertStringNotContainsString($this->colega->email, $corpo, $url);
            $this->assertStringNotContainsString('90000-0000', $corpo, $url);
            $this->assertStringNotContainsString('cadeira_de_rodas', $corpo, $url);
        }
    }

    public function test_telas_de_placar_nao_vazam_contato(): void
    {
        $partida = Partida::factory()->create(['patota_id' => $this->patota->id, 'organizador_id' => $this->colega->id]);
        $time = $partida->times()->create(['nome' => 'Time A', 'cor' => '#000000']);
        $time->jogadores()->attach($this->colega->id);

        foreach (['tv', 'controle', 'dados'] as $tela) {
            $quem = $tela === 'controle' ? $this->organizador : $this->membro;
            $corpo = $this->actingAs($quem)->get("/partidas/{$partida->id}/{$tela}")->assertOk()->getContent();

            $this->assertStringNotContainsString($this->colega->email, $corpo, $tela);
            $this->assertStringNotContainsString('90000-0000', $corpo, $tela);
        }

        $corpo = $this->actingAs($this->membro, 'sanctum')->getJson("/api/v1/partidas/{$partida->id}/placar")->assertOk()->getContent();
        $this->assertStringNotContainsString($this->colega->email, $corpo);
    }

    private function membrosVistosPor(User $quem)
    {
        return collect(
            $this->actingAs($quem, 'sanctum')
                ->getJson("/api/v1/patotas/{$this->patota->id}/membros")
                ->assertOk()
                ->json('data')
        );
    }
}
