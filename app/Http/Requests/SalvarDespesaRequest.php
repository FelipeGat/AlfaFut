<?php

namespace App\Http\Requests;

use App\Models\Despesa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Mesma regra de despesa no site e na API. */
class SalvarDespesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $despesa = $this->route('despesa');

        return $despesa instanceof Despesa
            ? $this->user()->can('update', $despesa)
            : $this->user()->can('organizar', $this->route('patota'));
    }

    public function rules(): array
    {
        $alterando = $this->route('despesa') instanceof Despesa;
        $exigido = $alterando ? 'sometimes' : 'required';

        return [
            'partida_id' => [$alterando ? 'prohibited' : 'nullable', Rule::exists('partidas', 'id')->where('patota_id', $this->route('patota')?->id)],
            'descricao' => [$exigido, 'string', 'max:160'],
            'categoria' => [$exigido, 'in:locacao,arbitragem,material,alimentacao,outro'],
            'valor_total' => [$exigido, 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
            'data_despesa' => [$exigido, 'date'],
            'rateada' => [$alterando ? 'prohibited' : 'nullable', 'boolean'],
            'status' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'partida_id.exists' => 'Escolha uma partida desta turma.',
            'status.prohibited' => 'Para encerrar a despesa, use marcar como paga ou cancelar.',
        ];
    }
}
