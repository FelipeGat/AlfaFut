<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatotaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'slug' => $this->slug,
            'descricao' => $this->descricao,
            'logo_url' => $this->logo_url,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'jogadores_por_time' => $this->jogadores_por_time,
            'quantidade_times' => $this->quantidade_times,
            'vagas_por_partida' => $this->vagasPorPartida(),
            'valor_mensalidade' => (float) $this->valor_mensalidade,
            'publica' => (bool) $this->publica,
            'codigo_convite' => $this->when(
                $request->user()?->id === $this->criador_id,
                $this->codigo_convite
            ),
            'total_membros' => $this->whenCounted('membros'),
            'criador' => new UsuarioResource($this->whenLoaded('criador')),
            'criada_em' => $this->created_at?->toIso8601String(),
        ];
    }
}
