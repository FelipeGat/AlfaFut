<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MensagemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patota_id' => $this->patota_id,
            'partida_id' => $this->partida_id,
            'conteudo' => $this->conteudo,
            'tipo' => $this->tipo,
            'fixada' => (bool) $this->fixada,
            'autor' => new UsuarioResource($this->whenLoaded('autor')),
            'criada_em' => $this->created_at?->toIso8601String(),
        ];
    }
}
