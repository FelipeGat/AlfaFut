<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Mesma regra de gol no site e na API. */
class RegistrarGolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('controlar', $this->route('partida'));
    }

    public function rules(): array
    {
        return [
            'time_id' => ['required', 'exists:times,id'],
            'jogador_id' => ['nullable', 'exists:users,id'],
            'tipo' => ['nullable', 'in:gol,gol_contra'],
            'observacao' => ['nullable', 'string', 'max:120'],
        ];
    }
}
