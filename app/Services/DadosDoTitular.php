<?php

namespace App\Services;

use App\Models\Campo;
use App\Models\Mensagem;
use App\Models\Pagamento;
use App\Models\PartidaConfirmacao;
use App\Models\PatotaMembro;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cópia dos dados da própria pessoa. A lista de campos é explícita para senha, token
 * ou coluna nova não saírem por acidente, e nada aqui traz contato de outra pessoa.
 */
class DadosDoTitular
{
    public function baixar(User $user): Response
    {
        return response(
            json_encode($this->exportar($user), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            200,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="meus-dados-alfafut.json"',
                'Cache-Control' => 'no-store, private',
            ],
        );
    }

    /** @return array<string, mixed> */
    public function exportar(User $user): array
    {
        return [
            'gerado_em' => now()->toIso8601String(),
            'cadastro' => [
                'nome' => $user->name,
                'apelido' => $user->apelido,
                'email' => $user->email,
                'email_confirmado_em' => $user->email_verified_at?->toIso8601String(),
                'telefone' => $user->telefone,
                'avatar_url' => $user->avatar_url,
                'data_nascimento' => $user->data_nascimento?->toDateString(),
                'posicao_preferida' => $user->posicao_preferida,
                'nivel_habilidade' => $user->nivel_habilidade,
                'tipo_usuario' => $user->tipo_usuario,
                'conta_criada_em' => $user->created_at?->toIso8601String(),
                'termos_aceitos_em' => $user->termos_aceitos_em?->toIso8601String(),
                'termos_versao' => $user->termos_versao,
            ],
            'acessibilidade' => [
                'alto_contraste' => (bool) $user->alto_contraste,
                'tamanho_fonte' => $user->tamanho_fonte,
                'reduzir_movimento' => (bool) $user->reduzir_movimento,
                'leitor_tela_otimizado' => (bool) $user->leitor_tela_otimizado,
                'necessidades' => $user->necessidades_acessibilidade ?? [],
            ],
            'turmas' => $this->turmas($user),
            'confirmacoes' => $this->confirmacoes($user),
            'parcelas' => $this->parcelas($user),
            'mensagens' => $this->mensagens($user),
            'campos' => $this->campos($user),
        ];
    }

    private function turmas(User $user): array
    {
        return PatotaMembro::where('user_id', $user->id)
            ->with(['patota' => fn ($turma) => $turma->withTrashed()])
            ->get()
            ->map(fn (PatotaMembro $vinculo) => [
                'turma' => $vinculo->patota?->nome,
                'papel' => $vinculo->papel,
                'situacao' => $vinculo->status,
                'entrou_em' => $vinculo->entrou_em?->toIso8601String(),
                'saiu_em' => $vinculo->saiu_em?->toIso8601String(),
            ])
            ->all();
    }

    private function confirmacoes(User $user): array
    {
        return PartidaConfirmacao::where('user_id', $user->id)
            ->with('partida')
            ->get()
            ->map(fn (PartidaConfirmacao $confirmacao) => [
                'partida' => $confirmacao->partida?->titulo,
                'data_hora' => $confirmacao->partida?->data_hora?->toIso8601String(),
                'situacao' => $confirmacao->status,
                'em_lista_espera' => (bool) $confirmacao->em_lista_espera,
                'posicao_lista_espera' => $confirmacao->posicao_lista_espera,
                'observacao' => $confirmacao->observacao,
                'confirmado_em' => $confirmacao->confirmado_em?->toIso8601String(),
            ])
            ->all();
    }

    private function parcelas(User $user): array
    {
        return Pagamento::where('user_id', $user->id)
            ->with('despesa')
            ->get()
            ->map(fn (Pagamento $parcela) => [
                'despesa' => $parcela->despesa?->descricao,
                'valor_devido' => $parcela->valor_devido,
                'valor_pago' => $parcela->valor_pago,
                'situacao' => $parcela->status,
                'data_vencimento' => $parcela->data_vencimento?->toDateString(),
                'data_pagamento' => $parcela->data_pagamento?->toDateString(),
                'forma_pagamento' => $parcela->forma_pagamento,
                'observacao' => $parcela->observacao,
            ])
            ->all();
    }

    private function mensagens(User $user): array
    {
        return Mensagem::where('autor_id', $user->id)
            ->with(['patota' => fn ($turma) => $turma->withTrashed()])
            ->get()
            ->map(fn (Mensagem $mensagem) => [
                'turma' => $mensagem->patota?->nome,
                'conteudo' => $mensagem->conteudo,
                'enviada_em' => $mensagem->created_at?->toIso8601String(),
            ])
            ->all();
    }

    private function campos(User $user): array
    {
        return Campo::where('dono_id', $user->id)
            ->get()
            ->map(fn (Campo $campo) => [
                'nome' => $campo->nome,
                'endereco' => $campo->endereco,
                'cidade' => $campo->cidade,
                'estado' => $campo->estado,
                'contato_whatsapp' => $campo->contato_whatsapp,
                'ativo' => (bool) $campo->ativo,
            ])
            ->all();
    }
}
