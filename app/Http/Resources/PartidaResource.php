<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartidaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patota_id' => $this->patota_id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'data_hora' => $this->data_hora?->toIso8601String(),
            'duracao_minutos' => $this->duracao_minutos,
            'vagas_total' => $this->vagas_total,
            'vagas_disponiveis' => $this->vagasDisponiveis(),
            'cheia' => $this->isCheia(),
            'valor_individual' => (float) $this->valor_individual,
            'status' => $this->status,
            'confirmacao_ate' => $this->confirmacao_ate?->toIso8601String(),
            'lista_espera_habilitada' => (bool) $this->lista_espera_habilitada,
            'local' => $this->whenLoaded('local', fn () => [
                'id' => $this->local?->id,
                'nome' => $this->local?->nome,
                'endereco' => $this->local?->endereco,
                'cidade' => $this->local?->cidade,
                'acessivel_cadeirante' => (bool) $this->local?->acessivel_cadeirante,
            ]),
            'organizador' => new UsuarioResource($this->whenLoaded('organizador')),
            'confirmados_count' => $this->whenLoaded('confirmados', fn () => $this->confirmados->count()),
            'lista_espera_count' => $this->whenLoaded('listaEspera', fn () => $this->listaEspera->count()),
            'minha_confirmacao' => $this->when(
                $request->user() && $this->relationLoaded('confirmacoes'),
                fn () => optional(
                    $this->confirmacoes->firstWhere('user_id', $request->user()->id)
                )->only(['status', 'em_lista_espera', 'posicao_lista_espera'])
            ),
        ];
    }
}
