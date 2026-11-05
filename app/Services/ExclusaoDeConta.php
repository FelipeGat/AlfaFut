<?php

namespace App\Services;

use App\Exceptions\RegraDaTurma;
use App\Models\Campo;
use App\Models\Patota;
use App\Models\PatotaMembro;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Excluir a conta apaga os dados da pessoa, mas mantém o registro sem nenhum dado pessoal,
 * para partidas, placares e rateios dos outros continuarem certos.
 */
class ExclusaoDeConta
{
    public function __construct(private readonly VagasDaPartida $vagas) {}

    public function excluir(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->arquivarTurmasDoCriador($user);
            $this->sairDasTurmas($user);

            Campo::where('dono_id', $user->id)->update(['ativo' => false, 'contato_whatsapp' => null]);

            $user->tokens()->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();

            $user->forceFill([
                'name' => 'Conta excluída',
                'apelido' => null,
                'email' => "conta-excluida-{$user->id}@alfafut.invalid",
                'email_verified_at' => null,
                'telefone' => null,
                'avatar_url' => null,
                'data_nascimento' => null,
                'posicao_preferida' => null,
                'nivel_habilidade' => null,
                'password' => Str::random(64),
                'remember_token' => null,
                'role' => 'user',
                'tipo_usuario' => 'jogador',
                'alto_contraste' => false,
                'tamanho_fonte' => 'media',
                'reduzir_movimento' => false,
                'leitor_tela_otimizado' => false,
                'necessidades_acessibilidade' => null,
                'excluida_em' => now(),
            ])->save();
        });
    }

    private function arquivarTurmasDoCriador(User $user): void
    {
        $turmas = Patota::where('criador_id', $user->id)->get();
        $comOutros = $turmas->filter(
            fn (Patota $turma) => $turma->membrosAtivos()->where('users.id', '!=', $user->id)->exists()
        );

        if ($comOutros->isNotEmpty()) {
            $nomes = $comOutros->pluck('nome')->join(', ', ' e ');

            throw new RegraDaTurma("Você criou {$nomes}, com outros membros. Arquive antes de excluir a conta.");
        }

        $turmas->each->delete();
    }

    private function sairDasTurmas(User $user): void
    {
        $vinculos = PatotaMembro::where('user_id', $user->id)->where('status', 'ativo')->get();
        $turmas = Patota::withTrashed()->whereIn('id', $vinculos->pluck('patota_id'))->get();

        foreach ($turmas as $turma) {
            $this->vagas->liberarNaTurma($turma, $user);
        }

        foreach ($vinculos as $vinculo) {
            $vinculo->update(['status' => 'inativo', 'saiu_em' => now()]);
        }

        Patota::withTrashed()->where('responsavel_id', $user->id)->update(['responsavel_id' => DB::raw('criador_id')]);
    }
}
