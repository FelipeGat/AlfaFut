<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfirmacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'partida_id' => $this->partida_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'em_lista_espera' => (bool) $this->em_lista_espera,
            'posicao_lista_espera' => $this->posicao_lista_espera,
            'observacao' => $this->observacao,
            'confirmado_em' => $this->confirmado_em?->toIso8601String(),
            'usuario' => new UsuarioResource($this->whenLoaded('user')),
        ];
    }
}
