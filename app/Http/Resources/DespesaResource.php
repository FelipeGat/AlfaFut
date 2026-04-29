<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DespesaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patota_id' => $this->patota_id,
            'partida_id' => $this->partida_id,
            'descricao' => $this->descricao,
            'categoria' => $this->categoria,
            'valor_total' => (float) $this->valor_total,
            'data_despesa' => $this->data_despesa?->toDateString(),
            'rateada' => (bool) $this->rateada,
            'status' => $this->status,
            'total_pago' => $this->totalPago(),
            'saldo_aberto' => $this->saldoAberto(),
        ];
    }
}
