<?php

namespace Database\Seeders;

use App\Models\Despesa;
use App\Models\Local;
use App\Models\Pagamento;
use App\Models\Partida;
use App\Models\PartidaConfirmacao;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PatotaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@alfafut.test'],
            [
                'name' => 'Felipe Henrique',
                'apelido' => 'Felipe',
                'telefone' => '(47) 99999-0000',
                'password' => Hash::make('senha1234'),
                'posicao_preferida' => 'meia',
                'nivel_habilidade' => 'avancado',
            ]
        );

        $jogadores = [
            ['Carlos Oliveira', 'Carlinhos', 'goleiro', 'avancado'],
            ['Ana Paula Mendes', 'Aninha', 'atacante', 'intermediario'],
            ['Bruno Santos', 'Bruninho', 'zagueiro', 'avancado'],
            ['Diego Souza', 'Di', 'meia', 'intermediario'],
            ['Eduardo Lima', 'Du', 'atacante', 'avancado'],
            ['Fernanda Costa', 'Fer', 'meia', 'iniciante'],
            ['Gustavo Ferreira', 'Guga', 'lateral', 'intermediario'],
            ['Helena Ribeiro', 'Lena', 'zagueiro', 'iniciante'],
            ['Igor Almeida', 'Igor', 'atacante', 'intermediario'],
            ['Juliana Pereira', 'Ju', 'meia', 'intermediario'],
            ['Kaio Martins', 'Kaio', 'goleiro', 'intermediario'],
            ['Larissa Dias', 'Lari', 'lateral', 'iniciante'],
            ['Marcelo Rocha', 'Marcelo', 'zagueiro', 'avancado'],
            ['Natalia Cardoso', 'Nat', 'atacante', 'intermediario'],
        ];

        $usuarios = [];
        foreach ($jogadores as [$nome, $apelido, $posicao, $nivel]) {
            $usuarios[] = User::firstOrCreate(
                ['email' => strtolower(str_replace(' ', '.', $nome)) . '@alfafut.test'],
                [
                    'name' => $nome,
                    'apelido' => $apelido,
                    'password' => Hash::make('senha1234'),
                    'posicao_preferida' => $posicao,
                    'nivel_habilidade' => $nivel,
                ]
            );
        }

        $patota = Patota::firstOrCreate(
            ['slug' => 'patota-do-felipe'],
            [
                'nome' => 'Patota do Felipe',
                'descricao' => 'Pelada de quarta e sabado, todos os niveis sao bem-vindos.',
                'cidade' => 'Joinville',
                'estado' => 'SC',
                'criador_id' => $admin->id,
                'jogadores_por_time' => 5,
                'quantidade_times' => 2,
                'valor_mensalidade' => 30.00,
                'publica' => true,
            ]
        );

        PatotaMembro::firstOrCreate(
            ['patota_id' => $patota->id, 'user_id' => $admin->id],
            ['papel' => 'administrador', 'status' => 'ativo']
        );
        foreach ($usuarios as $u) {
            PatotaMembro::firstOrCreate(
                ['patota_id' => $patota->id, 'user_id' => $u->id],
                ['papel' => 'membro', 'status' => 'ativo']
            );
        }

        $local = Local::firstOrCreate(
            ['patota_id' => $patota->id, 'nome' => 'Arena Society Boa Vista'],
            [
                'endereco' => 'Rua das Palmeiras, 1500',
                'cidade' => 'Joinville',
                'estado' => 'SC',
                'tipo_piso' => 'grama_sintetica',
                'coberto' => true,
                'possui_vestiario' => true,
                'possui_estacionamento' => true,
                'acessivel_cadeirante' => true,
                'valor_locacao' => 180.00,
                'contato' => '(47) 3422-0000',
            ]
        );

        $proximoSabado = now()->next('Saturday')->setTime(15, 0);
        $partida = Partida::firstOrCreate(
            ['patota_id' => $patota->id, 'data_hora' => $proximoSabado],
            [
                'local_id' => $local->id,
                'organizador_id' => $admin->id,
                'titulo' => 'Pelada de sabado',
                'descricao' => 'Confirma presenca ate sexta as 18h.',
                'duracao_minutos' => 90,
                'vagas_total' => $patota->vagasPorPartida(),
                'valor_individual' => 18.00,
                'status' => 'agendada',
                'confirmacao_ate' => $proximoSabado->copy()->subDay()->setTime(18, 0),
            ]
        );

        foreach (array_slice($usuarios, 0, 8) as $u) {
            PartidaConfirmacao::firstOrCreate(
                ['partida_id' => $partida->id, 'user_id' => $u->id],
                ['status' => 'confirmado', 'em_lista_espera' => false]
            );
        }
        PartidaConfirmacao::firstOrCreate(
            ['partida_id' => $partida->id, 'user_id' => $admin->id],
            ['status' => 'confirmado']
        );

        $despesa = Despesa::firstOrCreate(
            ['partida_id' => $partida->id, 'descricao' => 'Aluguel do campo - sabado'],
            [
                'patota_id' => $patota->id,
                'criada_por_id' => $admin->id,
                'categoria' => 'locacao',
                'valor_total' => 180.00,
                'data_despesa' => $proximoSabado->toDateString(),
                'rateada' => true,
                'status' => 'aberta',
            ]
        );

        $confirmados = $partida->confirmados()->get();
        $rateio = round(180.00 / max(1, $confirmados->count()), 2);
        foreach ($confirmados as $confirmacao) {
            Pagamento::firstOrCreate(
                ['despesa_id' => $despesa->id, 'user_id' => $confirmacao->user_id],
                [
                    'valor_devido' => $rateio,
                    'valor_pago' => 0,
                    'data_vencimento' => $proximoSabado->toDateString(),
                    'status' => 'pendente',
                ]
            );
        }
    }
}
