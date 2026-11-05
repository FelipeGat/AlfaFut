<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** Marcar ou alterar partida, com a mesma regra no site e na API. */
class SalvarPartidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $partida = $this->route('partida');

        return $partida
            ? $this->user()->can('controlar', $partida)
            : $this->user()->can('organizar', $this->route('patota'));
    }

    /** No site, um único seletor escolhe o lugar: `local:ID` ou `campo:ID`. */
    protected function prepareForValidation(): void
    {
        if (! $this->has('lugar')) {
            return;
        }

        $lugar = $this->input('lugar');
        [$tipo, $id] = is_string($lugar) ? array_pad(explode(':', $lugar, 2), 2, null) : [null, null];

        $this->merge([
            'local_id' => $tipo === 'local' ? $id : null,
            'campo_id' => $tipo === 'campo' ? $id : null,
        ]);
    }

    public function rules(): array
    {
        $partida = $this->route('partida');
        $turma = $partida?->patota ?? $this->route('patota');
        $exigido = $partida ? 'sometimes' : 'required';

        return [
            'titulo' => [$exigido, 'string', 'max:120'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'data_hora' => [$exigido, 'bail', 'date', $this->dataFutura($partida?->data_hora)],
            'duracao_minutos' => ['nullable', 'integer', 'min:30', 'max:240'],
            'vagas_total' => [$partida ? 'sometimes' : 'nullable', 'integer', 'min:4', 'max:200', function (string $atributo, mixed $valor, Closure $falha) use ($partida) {
                $confirmados = $partida?->confirmados()->count() ?? 0;

                if ($valor < $confirmados) {
                    $falha("Há {$confirmados} confirmados. As vagas não podem ficar abaixo disso.");
                }
            }],
            'valor_individual' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'confirmacao_ate' => ['nullable', 'date', 'before:'.($this->has('data_hora') ? 'data_hora' : $partida?->data_hora)],
            'lista_espera_habilitada' => ['nullable', 'boolean'],
            'local_id' => ['nullable', 'integer', 'prohibits:campo_id', Rule::exists('locais', 'id')->where('patota_id', $turma->id)],
            'campo_id' => ['nullable', 'integer', Rule::exists('campos', 'id')->where(
                fn ($q) => $q->where('ativo', true)->orWhere('id', $partida?->campo_id)
            )],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmacao_ate.before' => 'O prazo para confirmar precisa terminar antes do início da partida.',
            'local_id.exists' => 'Escolha um local desta turma.',
            'local_id.prohibits' => 'A partida acontece em um só lugar: escolha um local ou um campo.',
            'campo_id.exists' => 'Este campo não está disponível no catálogo.',
        ];
    }

    /** Os dados validados, com os padrões das colunas que não aceitam vazio e no fuso do sistema. */
    public function dados(): array
    {
        $dados = $this->validated();

        foreach (['valor_individual' => 0, 'duracao_minutos' => 90, 'lista_espera_habilitada' => true] as $campo => $padrao) {
            if (array_key_exists($campo, $dados)) {
                $dados[$campo] ??= $padrao;
            }
        }

        foreach (['data_hora', 'confirmacao_ate'] as $campo) {
            if (! empty($dados[$campo])) {
                $dados[$campo] = $this->noFuso($dados[$campo]);
            }
        }

        return $dados;
    }

    /** A data precisa ser futura, salvo quando a partida já marcada mantém a dela. */
    private function dataFutura(?Carbon $atual): Closure
    {
        return function (string $atributo, mixed $valor, Closure $falha) use ($atual) {
            $data = $this->noFuso($valor);

            if ($data->isPast() && $data->format('Y-m-d H:i') !== $atual?->format('Y-m-d H:i')) {
                $falha('A data e a hora precisam estar no futuro.');
            }
        };
    }

    private function noFuso(string $valor): Carbon
    {
        return Carbon::parse($valor)->setTimezone(config('app.timezone'));
    }
}
