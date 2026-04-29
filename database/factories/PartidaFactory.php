<?php

namespace Database\Factories;

use App\Models\Patota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartidaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patota_id' => Patota::factory(),
            'organizador_id' => User::factory(),
            'titulo' => 'Pelada de ' . $this->faker->dayOfWeek(),
            'descricao' => $this->faker->sentence(),
            'data_hora' => now()->addDays(rand(1, 14))->setTime(rand(15, 21), 0),
            'duracao_minutos' => 90,
            'vagas_total' => 10,
            'valor_individual' => 18.00,
            'status' => 'agendada',
            'lista_espera_habilitada' => true,
        ];
    }
}
