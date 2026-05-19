<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->name,
            'apelido' => $this->apelido,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'avatar_url' => $this->avatar_url,
            'posicao_preferida' => $this->posicao_preferida,
            'nivel_habilidade' => $this->nivel_habilidade,
            'role' => $this->role ?? 'user',
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
