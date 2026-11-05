<?php

namespace Tests\Feature\Permissoes;

use App\Models\Despesa;
use App\Models\EventoPartida;
use App\Models\Mensagem;
use App\Models\Pagamento;
use App\Models\Partida;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * A matriz de docs/specs/seguranca-base/data-model.md: cada ação, pelo site e pela
 * API, contra cada tipo de pessoa. Permitido é tudo que não dá 403 nem erro 5xx
 * (corpo vazio pode dar 422 depois de autorizar).
 */
class MatrizPermissoesTest extends TestCase
{
    use RefreshDatabase;

    private const PERMITE = [
        'leitura' => ['membro', 'organizador', 'responsavel', 'criador', 'admin'],
        'participacao' => ['membro', 'organizador', 'responsavel', 'criador', 'admin'],
        'organizacao' => ['organizador', 'responsavel', 'criador', 'admin'],
        'gestao' => ['responsavel', 'criador', 'admin'],
        'criador' => ['criador', 'admin'],
    ];

    private const PESSOAS = ['fora', 'ex_membro', 'ex_responsavel', 'membro', 'organizador', 'responsavel', 'criador', 'admin'];

    private const ACOES = [
        ['api', 'GET', 'patotas/{p}', 'leitura'],
        ['api', 'GET', 'patotas/{p}/membros', 'leitura'],
        ['api', 'GET', 'patotas/{p}/partidas', 'leitura'],
        ['api', 'GET', 'partidas/{x}', 'leitura'],
        ['api', 'GET', 'partidas/{x}/placar', 'leitura'],
        ['api', 'GET', 'patotas/{p}/despesas', 'leitura'],
        ['api', 'GET', 'despesas/{d}', 'leitura'],
        ['api', 'GET', 'patotas/{p}/mensagens', 'leitura'],
        ['api', 'POST', 'partidas/{x}/confirmar', 'participacao'],
        ['api', 'POST', 'partidas/{x}/recusar', 'participacao'],
        ['api', 'POST', 'patotas/{p}/mensagens', 'participacao'],
        ['api', 'POST', 'patotas/{p}/despesas', 'organizacao'],
        ['api', 'PATCH', 'despesas/{d}', 'organizacao'],
        ['api', 'DELETE', 'despesas/{d}', 'organizacao'],
        ['api', 'POST', 'patotas/{p}/partidas', 'organizacao'],
        ['api', 'PATCH', 'partidas/{x}', 'organizacao'],
        ['api', 'DELETE', 'partidas/{x}', 'organizacao'],
        ['api', 'POST', 'partidas/{x}/iniciar', 'organizacao'],
        ['api', 'POST', 'partidas/{x}/pausar', 'organizacao'],
        ['api', 'POST', 'partidas/{x}/finalizar', 'organizacao'],
        ['api', 'POST', 'partidas/{x}/gol', 'organizacao'],
        ['api', 'POST', 'partidas/{x}/sortear', 'organizacao'],
        ['api', 'PATCH', 'patotas/{p}', 'criador'],
        ['api', 'DELETE', 'patotas/{p}', 'criador'],

        ['web', 'GET', 'patotas/{p}', 'leitura'],
        ['web', 'GET', 'partidas/{x}', 'leitura'],
        ['web', 'GET', 'partidas/{x}/tv', 'leitura'],
        ['web', 'GET', 'partidas/{x}/resultado', 'leitura'],
        ['web', 'GET', 'partidas/{x}/dados', 'leitura'],
        ['web', 'GET', 'patotas/{p}/despesas', 'leitura'],
        ['web', 'GET', 'patotas/{p}/despesas/{d}', 'leitura'],
        ['web', 'GET', 'patotas/{p}/mural', 'leitura'],
        ['web', 'POST', 'partidas/{x}/confirmar', 'participacao'],
        ['web', 'POST', 'partidas/{x}/recusar', 'participacao'],
        ['web', 'POST', 'patotas/{p}/mural', 'participacao'],
        ['web', 'GET', 'patotas/{p}/despesas/criar', 'organizacao'],
        ['web', 'POST', 'patotas/{p}/despesas', 'organizacao'],
        ['web', 'DELETE', 'patotas/{p}/despesas/{d}', 'organizacao'],
        ['web', 'POST', 'partidas/{x}/sortear', 'organizacao'],
        ['web', 'GET', 'partidas/{x}/controle', 'organizacao'],
        ['web', 'POST', 'partidas/{x}/iniciar', 'organizacao'],
        ['web', 'POST', 'partidas/{x}/pausar', 'organizacao'],
        ['web', 'POST', 'partidas/{x}/finalizar', 'organizacao'],
        ['web', 'POST', 'partidas/{x}/gol', 'organizacao'],
        ['web', 'DELETE', 'partidas/{x}/eventos/{e}', 'organizacao'],
        ['web', 'GET', 'patotas/{p}/partidas/nova', 'organizacao'],
        ['web', 'POST', 'patotas/{p}/partidas', 'organizacao'],
        ['web', 'GET', 'partidas/{x}/editar', 'organizacao'],
        ['web', 'PUT', 'partidas/{x}', 'organizacao'],
        ['web', 'POST', 'partidas/{x}/cancelar', 'organizacao'],
        ['web', 'GET', 'patotas/{p}/locais', 'organizacao'],
        ['web', 'GET', 'patotas/{p}/locais/create', 'organizacao'],
        ['web', 'POST', 'patotas/{p}/locais', 'organizacao'],
        ['web', 'GET', 'patotas/{p}/membros', 'organizacao'],
        ['web', 'PATCH', 'patotas/{p}/membros/{m}/papel', 'gestao'],
        ['web', 'POST', 'patotas/{p}/membros/{m}/responsavel', 'gestao'],
        ['web', 'POST', 'patotas/{p}/membros/{m}/liberar', 'gestao'],
        ['web', 'DELETE', 'patotas/{p}/membros/{m}', 'gestao'],
        ['web', 'GET', 'patotas/{p}/edit', 'criador'],
        ['web', 'PUT', 'patotas/{p}', 'criador'],
        ['web', 'DELETE', 'patotas/{p}', 'criador'],
    ];

    /** @var array<string, User> */
    private array $pessoas = [];

    private Patota $patota;

    private Partida $partida;

    private Despesa $despesa;

    private Pagamento $pagamentoDoMembro;

    private Mensagem $mensagemDoMembro;

    private EventoPartida $evento;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::PESSOAS as $nome) {
            $this->pessoas[$nome] = User::factory()->create($nome === 'admin' ? ['role' => 'admin'] : []);
        }

        $this->patota = Patota::factory()->create([
            'criador_id' => $this->pessoas['criador']->id,
            'responsavel_id' => $this->pessoas['responsavel']->id,
        ]);

        foreach ([
            'criador' => ['administrador', 'ativo'],
            'organizador' => ['organizador', 'ativo'],
            'responsavel' => ['membro', 'ativo'],
            'membro' => ['membro', 'ativo'],
            'ex_membro' => ['membro', 'inativo'],
            'ex_responsavel' => ['organizador', 'inativo'],
        ] as $nome => [$papel, $status]) {
            PatotaMembro::create([
                'patota_id' => $this->patota->id,
                'user_id' => $this->pessoas[$nome]->id,
                'papel' => $papel,
                'status' => $status,
            ]);
        }

        $this->partida = Partida::factory()->create([
            'patota_id' => $this->patota->id,
            'organizador_id' => $this->pessoas['criador']->id,
        ]);

        $this->despesa = $this->patota->despesas()->create([
            'criada_por_id' => $this->pessoas['criador']->id,
            'descricao' => 'Aluguel',
            'categoria' => 'locacao',
            'valor_total' => 100,
            'data_despesa' => now()->toDateString(),
            'status' => 'aberta',
        ]);

        $this->pagamentoDoMembro = Pagamento::create([
            'despesa_id' => $this->despesa->id,
            'user_id' => $this->pessoas['membro']->id,
            'valor_devido' => 50,
            'data_vencimento' => now()->toDateString(),
            'status' => 'pendente',
        ]);

        $this->mensagemDoMembro = $this->patota->mensagens()->create([
            'autor_id' => $this->pessoas['membro']->id,
            'conteudo' => 'Oi',
            'tipo' => 'texto',
        ]);

        $this->evento = EventoPartida::create([
            'partida_id' => $this->partida->id,
            'tipo' => EventoPartida::TIPO_INICIO,
            'minuto' => 0,
            'registrado_por_id' => $this->pessoas['criador']->id,
        ]);
    }

    public static function matriz(): iterable
    {
        foreach (self::ACOES as [$canal, $metodo, $rota, $classe]) {
            foreach (self::PESSOAS as $pessoa) {
                yield "$canal $metodo $rota por $pessoa" => [$canal, $metodo, $rota, $classe, $pessoa];
            }
        }
    }

    #[DataProvider('matriz')]
    public function test_matriz(string $canal, string $metodo, string $rota, string $classe, string $pessoa): void
    {
        $resposta = $this->chamar($canal, $metodo, $rota, $pessoa);
        $status = $resposta->getStatusCode();

        if (in_array($pessoa, self::PERMITE[$classe], true)) {
            $this->assertNotSame(403, $status, "$pessoa deveria poder $metodo $rota");
            $this->assertLessThan(500, $status, "$metodo $rota deu erro: ".$resposta->getContent());
        } else {
            $this->assertSame(403, $status, "$pessoa não deveria poder $metodo $rota");
        }
    }

    public function test_recusa_na_api_vem_em_portugues(): void
    {
        $this->chamar('api', 'GET', 'despesas/{d}', 'fora')
            ->assertForbidden()
            ->assertJson(['message' => 'Você não tem permissão para esta ação.']);
    }

    public function test_quem_esta_fora_nao_altera_nem_apaga_despesa(): void
    {
        $this->chamar('api', 'PATCH', 'despesas/{d}', 'fora', ['valor_total' => 1])->assertForbidden();
        $this->chamar('api', 'DELETE', 'despesas/{d}', 'fora')->assertForbidden();

        $this->assertDatabaseHas('despesas', ['id' => $this->despesa->id, 'valor_total' => 100]);
    }

    #[DataProvider('canaisDeQuitacao')]
    public function test_baixa_so_no_proprio_pagamento(string $canal, string $rota): void
    {
        $dados = ['valor_pago' => 50, 'forma_pagamento' => 'pix'];
        $rota = str_replace('{g}', (string) $this->pagamentoDoMembro->id, $rota);

        foreach (['fora', 'ex_membro', 'organizador', 'criador', 'admin'] as $pessoa) {
            $this->chamar($canal, 'POST', $rota, $pessoa, $dados)->assertForbidden();
        }

        $this->assertDatabaseHas('pagamentos', ['id' => $this->pagamentoDoMembro->id, 'status' => 'pendente']);

        $status = $this->chamar($canal, 'POST', $rota, 'membro', $dados)->getStatusCode();
        $this->assertContains($status, [200, 302]);
        $this->assertDatabaseHas('pagamentos', ['id' => $this->pagamentoDoMembro->id, 'status' => 'pago']);
    }

    public static function canaisDeQuitacao(): array
    {
        return [
            'api' => ['api', 'pagamentos/{g}/quitar'],
            'web' => ['web', 'patotas/{p}/pagamentos/{g}/quitar'],
        ];
    }

    public function test_quem_saiu_nao_baixa_o_proprio_pagamento(): void
    {
        $this->pagamentoDoMembro->update(['user_id' => $this->pessoas['ex_membro']->id]);

        $this->chamar('api', 'POST', 'pagamentos/'.$this->pagamentoDoMembro->id.'/quitar', 'ex_membro', [
            'valor_pago' => 50,
            'forma_pagamento' => 'pix',
        ])->assertForbidden();
    }

    #[DataProvider('canaisDeDespesa')]
    public function test_despesa_nao_cita_partida_de_outra_turma(string $canal, string $rota): void
    {
        $outra = Patota::factory()->create(['criador_id' => $this->pessoas['fora']->id]);
        $partidaAlheia = Partida::factory()->create([
            'patota_id' => $outra->id,
            'organizador_id' => $this->pessoas['fora']->id,
        ]);
        $partidaAlheia->confirmacoes()->create(['user_id' => $this->pessoas['fora']->id, 'status' => 'confirmado', 'em_lista_espera' => false]);

        $resposta = $this->chamar($canal, 'POST', $rota, 'organizador', [
            'partida_id' => $partidaAlheia->id,
            'descricao' => 'Golpe',
            'categoria' => 'outro',
            'valor_total' => 100,
            'data_despesa' => now()->toDateString(),
            'rateada' => true,
        ]);

        $this->assertContains($resposta->getStatusCode(), [302, 422]);
        $this->assertDatabaseMissing('despesas', ['descricao' => 'Golpe']);
        $this->assertDatabaseMissing('pagamentos', ['user_id' => $this->pessoas['fora']->id]);
    }

    public static function canaisDeDespesa(): array
    {
        return [
            'api' => ['api', 'patotas/{p}/despesas'],
            'web' => ['web', 'patotas/{p}/despesas'],
        ];
    }

    #[DataProvider('canaisDeMensagem')]
    public function test_remover_mensagem(string $canal, string $rota): void
    {
        $rota = str_replace('{m}', (string) $this->mensagemDoMembro->id, $rota);

        foreach (['fora', 'ex_membro'] as $pessoa) {
            $this->chamar($canal, 'DELETE', $rota, $pessoa)->assertForbidden();
        }

        $outro = User::factory()->create();
        PatotaMembro::create(['patota_id' => $this->patota->id, 'user_id' => $outro->id, 'papel' => 'membro', 'status' => 'ativo']);
        $this->pessoas['outro'] = $outro;
        $this->chamar($canal, 'DELETE', $rota, 'outro')->assertForbidden();
        $this->assertDatabaseHas('mensagens', ['id' => $this->mensagemDoMembro->id]);

        $this->assertNotSame(403, $this->chamar($canal, 'DELETE', $rota, 'membro')->getStatusCode());
        $this->assertDatabaseMissing('mensagens', ['id' => $this->mensagemDoMembro->id]);

        $daOrganizacao = $this->patota->mensagens()->create(['autor_id' => $this->pessoas['membro']->id, 'conteudo' => 'Oi de novo', 'tipo' => 'texto']);
        $rotaOrganizador = str_replace((string) $this->mensagemDoMembro->id, (string) $daOrganizacao->id, $rota);
        $this->assertNotSame(403, $this->chamar($canal, 'DELETE', $rotaOrganizador, 'organizador')->getStatusCode());
        $this->assertDatabaseMissing('mensagens', ['id' => $daOrganizacao->id]);
    }

    public static function canaisDeMensagem(): array
    {
        return [
            'api' => ['api', 'mensagens/{m}'],
            'web' => ['web', 'patotas/{p}/mural/{m}'],
        ];
    }

    #[DataProvider('canaisDeFixar')]
    public function test_so_organizador_fixa_mensagem(string $canal, string $rota): void
    {
        $this->chamar($canal, 'POST', $rota, 'membro', ['conteudo' => 'De membro', 'fixada' => true]);
        $this->chamar($canal, 'POST', $rota, 'organizador', ['conteudo' => 'De organizador', 'fixada' => true]);

        $this->assertDatabaseHas('mensagens', ['conteudo' => 'De membro', 'fixada' => false]);
        $this->assertDatabaseHas('mensagens', ['conteudo' => 'De organizador', 'fixada' => true]);
    }

    public static function canaisDeFixar(): array
    {
        return [
            'api' => ['api', 'patotas/{p}/mensagens'],
            'web' => ['web', 'patotas/{p}/mural'],
        ];
    }

    public function test_quem_saiu_nao_ve_a_turma_nas_listas(): void
    {
        $nome = $this->patota->nome;
        $titulo = $this->partida->titulo;
        $ex = $this->pessoas['ex_membro'];

        $this->actingAs($ex)->get('/patotas')->assertOk()->assertDontSee($nome);
        $this->actingAs($ex)->get('/partidas')->assertOk()->assertDontSee($titulo);
        $this->actingAs($ex)->get('/dashboard')->assertOk()->assertDontSee($nome)->assertDontSee($titulo);
        $this->actingAs($ex, 'sanctum')->getJson('/api/v1/patotas')->assertOk()->assertJsonMissing(['id' => $this->patota->id]);
    }

    private function chamar(string $canal, string $metodo, string $rota, string $pessoa, array $dados = [])
    {
        $url = strtr($rota, [
            '{p}' => $this->patota->id,
            '{x}' => $this->partida->id,
            '{d}' => $this->despesa->id,
            '{e}' => $this->evento->id,
            '{m}' => $this->pessoas['membro']->id,
        ]);

        return $canal === 'api'
            ? $this->actingAs($this->pessoas[$pessoa], 'sanctum')->json($metodo, '/api/v1/'.$url, $dados)
            : $this->actingAs($this->pessoas[$pessoa])->call($metodo, '/'.$url, $dados);
    }
}
