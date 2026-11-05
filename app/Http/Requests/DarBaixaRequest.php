<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Mesma regra de baixa de parcela no site e na API. */
class DarBaixaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('quitar', $this->route('pagamento'));
    }

    public function rules(): array
    {
        return [
            'valor_pago' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
            'forma_pagamento' => ['required', 'in:pix,dinheiro,transferencia,cartao'],
        ];
    }
}
