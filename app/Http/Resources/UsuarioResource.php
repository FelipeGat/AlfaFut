<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * O que qualquer membro da turma vê de outra pessoa. Dados protegidos só em
 * MembroResource (quem organiza) e MeuUsuarioResource (a própria conta).
 */
class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->name,
            'apelido' => $this->apelido,
            'avatar_url' => $this->avatar_url,
            'posicao_preferida' => $this->posicao_preferida,
            'nivel_habilidade' => $this->nivel_habilidade,
        ];
    }
}
