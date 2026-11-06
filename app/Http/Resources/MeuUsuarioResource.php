<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * A própria conta de quem está logado.
 */
class MeuUsuarioResource extends UsuarioResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'email' => $this->email,
            'email_confirmado' => $this->hasVerifiedEmail(),
            'telefone' => $this->telefone,
            'role' => $this->role ?? 'user',
            'tipo_usuario' => $this->tipo_usuario,
            'is_admin' => $this->isAdmin(),
            'preferencias_acessibilidade' => [
                'alto_contraste' => (bool) $this->alto_contraste,
                'tamanho_fonte' => $this->tamanho_fonte,
                'reduzir_movimento' => (bool) $this->reduzir_movimento,
                'leitor_tela_otimizado' => (bool) $this->leitor_tela_otimizado,
                'necessidades' => $this->necessidades_acessibilidade ?? [],
            ],
        ];
    }
}
