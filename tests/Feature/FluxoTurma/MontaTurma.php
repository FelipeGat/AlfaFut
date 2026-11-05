<?php

namespace Tests\Feature\FluxoTurma;

use App\Models\Partida;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;

trait MontaTurma
{
    /** @var array<string, User> */
    protected array $pessoas = [];

    protected Patota $turma;

    protected function montarTurma(): void
    {
        foreach (['fora', 'ex_membro', 'removido', 'membro', 'organizador', 'responsavel', 'criador', 'admin'] as $nome) {
            $this->pessoas[$nome] = User::factory()->create($nome === 'admin' ? ['role' => 'admin'] : []);
        }

        $this->turma = Patota::factory()->create([
            'criador_id' => $this->pessoas['criador']->id,
            'responsavel_id' => $this->pessoas['responsavel']->id,
            'codigo_convite' => 'ABCD1234',
            'jogadores_por_time' => 5,
            'quantidade_times' => 2,
        ]);

        foreach ([
            'criador' => ['administrador', 'ativo'],
            'responsavel' => ['membro', 'ativo'],
            'organizador' => ['organizador', 'ativo'],
            'membro' => ['membro', 'ativo'],
            'ex_membro' => ['organizador', 'inativo'],
            'removido' => ['membro', 'removido'],
        ] as $nome => [$papel, $status]) {
            PatotaMembro::create([
                'patota_id' => $this->turma->id,
                'user_id' => $this->pessoas[$nome]->id,
                'papel' => $papel,
                'status' => $status,
            ]);
        }
    }

    protected function vinculo(string $nome): ?PatotaMembro
    {
        return PatotaMembro::where('patota_id', $this->turma->id)
            ->where('user_id', $this->pessoas[$nome]->id)
            ->first();
    }

    protected function novoMembro(): User
    {
        $user = User::factory()->create();
        PatotaMembro::create(['patota_id' => $this->turma->id, 'user_id' => $user->id, 'papel' => 'membro', 'status' => 'ativo']);

        return $user;
    }

    protected function partida(array $dados = []): Partida
    {
        return Partida::factory()->create([
            'patota_id' => $this->turma->id,
            'organizador_id' => $this->pessoas['criador']->id,
            'data_hora' => now()->addDays(3),
            ...$dados,
        ]);
    }

    /** @return array<string, mixed> */
    protected function dadosDePartida(array $dados = []): array
    {
        return [
            'titulo' => 'Pelada de sábado',
            'data_hora' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'duracao_minutos' => 90,
            'vagas_total' => 10,
            'valor_individual' => '18.50',
            'lista_espera_habilitada' => 1,
            ...$dados,
        ];
    }
}
