<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Lista de membros para quem organiza a turma.
 */
class MembroResource extends UsuarioResource
{
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'email' => $this->email,
            'telefone' => $this->telefone,
            'necessidades' => $this->necessidades_acessibilidade ?? [],
            'role' => $this->when($request->user()->isAdmin(), fn () => $this->role ?? 'user'),
        ];
    }
}
