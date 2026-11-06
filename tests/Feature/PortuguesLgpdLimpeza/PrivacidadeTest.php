<?php

namespace Tests\Feature\PortuguesLgpdLimpeza;

use App\Models\Despesa;
use App\Models\Mensagem;
use App\Models\Pagamento;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PrivacidadeTest extends TestCase
{
    use RefreshDatabase;

    private const CADASTRO_API = [
        'name' => 'Pessoa Teste',
        'email' => 'pessoa@alfafut.test',
        'password' => 'senha-forte-12345',
    ];

    private const CADASTRO_SITE = [
        ...self::CADASTRO_API,
        'password_confirmation' => 'senha-forte-12345',
        'tipo_usuario' => 'jogador',
    ];

    #[DataProvider('paginasPublicas')]
    public function test_pagina_abre_sem_login(string $rota, string $texto): void
    {
        $this->get($rota)->assertOk()->assertSee($texto);
    }

    public static function paginasPublicas(): array
    {
        return [
            'política' => ['/privacidade', 'Política de privacidade'],
            'termos' => ['/termos', 'Termos de uso'],
        ];
    }

    #[DataProvider('telasComRodape')]
    public function test_rodape_liga_as_duas_paginas(string $rota): void
    {
        $this->get($rota)->assertOk()
            ->assertSee(route('privacidade'), false)
            ->assertSee(route('termos'), false);
    }

    public static function telasComRodape(): array
    {
        return [['/'], ['/como-usar'], ['/login'], ['/register'], ['/privacidade'], ['/termos']];
    }

    public function test_politica_mostra_o_responsavel_da_configuracao(): void
    {
        config([
            'alfafut.responsavel.nome' => 'Empresa de Exemplo',
            'alfafut.responsavel.documento' => 'Documento de exemplo',
            'alfafut.responsavel.contato_privacidade' => 'contato@alfafut.test',
        ]);

        $this->get('/privacidade')
            ->assertSee('Empresa de Exemplo')
            ->assertSee('Documento de exemplo')
            ->assertSee('contato@alfafut.test');
    }

    public function test_cadastro_no_site_sem_aceite_e_recusado(): void
    {
        $this->post('/register', self::CADASTRO_SITE)->assertSessionHasErrors('aceite_termos');

        $this->assertDatabaseMissing('users', ['email' => self::CADASTRO_SITE['email']]);
    }

    public function test_cadastro_na_api_sem_aceite_e_recusado(): void
    {
        $this->postJson('/api/v1/auth/registrar', self::CADASTRO_API)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('aceite_termos');

        $this->assertDatabaseMissing('users', ['email' => self::CADASTRO_API['email']]);
    }

    public function test_cadastro_com_aceite_guarda_data_e_versao(): void
    {
        config(['alfafut.versao_termos' => '2026-11-05']);

        $this->post('/register', [...self::CADASTRO_SITE, 'aceite_termos' => '1'])->assertRedirect();
        $this->postJson('/api/v1/auth/registrar', [
            ...self::CADASTRO_API,
            'email' => 'api@alfafut.test',
            'aceite_termos' => true,
        ])->assertCreated();

        foreach ([self::CADASTRO_SITE['email'], 'api@alfafut.test'] as $email) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user->termos_aceitos_em);
            $this->assertSame('2026-11-05', $user->termos_versao);
        }
    }

    public function test_aceite_nao_pode_ser_preenchido_pelo_formulario_do_perfil(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->patchJson('/api/v1/perfil', ['termos_versao' => 'forjada', 'termos_aceitos_em' => '2000-01-01']);

        $this->assertNull($user->fresh()->termos_versao);
        $this->assertNull($user->fresh()->termos_aceitos_em);
    }

    public function test_arquivo_traz_os_dados_da_pessoa_e_nada_de_outras(): void
    {
        [$pessoa, $outra, $turma] = $this->pessoaComTurma();
        $pessoa->createToken('app');

        $resposta = $this->actingAs($pessoa)->get('/perfil/meus-dados');

        $resposta->assertOk()->assertDownload('meus-dados-alfafut.json');
        $this->assertStringContainsString('no-store', $resposta->headers->get('Cache-Control'));

        $dados = json_decode($resposta->getContent(), true);
        $this->assertSame($pessoa->email, $dados['cadastro']['email']);
        $this->assertSame(['visao baixa'], $dados['acessibilidade']['necessidades']);
        $this->assertSame($turma->nome, $dados['turmas'][0]['turma']);
        $this->assertSame('confirmado', $dados['confirmacoes'][0]['situacao']);
        $this->assertSame('25.00', $dados['parcelas'][0]['valor_devido']);
        $this->assertSame('Levo a bola', $dados['mensagens'][0]['conteudo']);

        $texto = json_encode($dados);
        foreach (['password', 'remember_token', 'token', $pessoa->password, $outra->email, $outra->telefone, 'Muleta'] as $proibido) {
            $this->assertStringNotContainsString($proibido, $texto);
        }
    }

    public function test_arquivo_na_api_nao_exige_email_confirmado(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/perfil/meus-dados')->assertOk()->assertDownload('meus-dados-alfafut.json');
    }

    #[DataProvider('rotasDoArquivo')]
    public function test_arquivo_exige_login(string $rota, int $status): void
    {
        $this->get($rota)->assertStatus($status);
    }

    public static function rotasDoArquivo(): array
    {
        return [
            'site' => ['/perfil/meus-dados', 302],
            'api' => ['/api/v1/perfil/meus-dados', 401],
        ];
    }

    /** @return array{User, User, Patota} */
    private function pessoaComTurma(): array
    {
        $pessoa = User::factory()->create(['necessidades_acessibilidade' => ['visao baixa']]);
        $outra = User::factory()->create(['telefone' => '00911112222', 'necessidades_acessibilidade' => ['Muleta']]);
        $turma = Patota::factory()->create(['criador_id' => $pessoa->id]);

        foreach ([$pessoa, $outra] as $membro) {
            PatotaMembro::create(['patota_id' => $turma->id, 'user_id' => $membro->id, 'papel' => 'membro', 'status' => 'ativo']);
        }

        $partida = Partida::factory()->create(['patota_id' => $turma->id, 'organizador_id' => $pessoa->id]);
        PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $pessoa->id, 'status' => 'confirmado']);
        PartidaConfirmacao::create(['partida_id' => $partida->id, 'user_id' => $outra->id, 'status' => 'confirmado']);

        $despesa = Despesa::create([
            'patota_id' => $turma->id,
            'criada_por_id' => $pessoa->id,
            'descricao' => 'Aluguel',
            'categoria' => 'aluguel',
            'valor_total' => 50,
            'data_despesa' => now(),
            'status' => 'aberta',
        ]);
        Pagamento::create(['despesa_id' => $despesa->id, 'user_id' => $pessoa->id, 'valor_devido' => 25, 'status' => 'pendente']);

        Mensagem::create(['patota_id' => $turma->id, 'autor_id' => $pessoa->id, 'conteudo' => 'Levo a bola', 'tipo' => 'mensagem']);
        Mensagem::create(['patota_id' => $turma->id, 'autor_id' => $outra->id, 'conteudo' => 'Muleta no carro', 'tipo' => 'mensagem']);

        return [$pessoa, $outra, $turma];
    }
}
