<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PatotaFactory extends Factory
{
    public function definition(): array
    {
        $nome = 'Patota ' . $this->faker->company();
        return [
            'nome' => $nome,
            'slug' => Str::slug($nome) . '-' . Str::random(5),
            'descricao' => $this->faker->sentence(),
            'cidade' => $this->faker->city(),
            'estado' => 'SC',
            'criador_id' => User::factory(),
            'jogadores_por_time' => 5,
            'quantidade_times' => 2,
            'valor_mensalidade' => 30,
            'publica' => true,
            'codigo_convite' => strtoupper(Str::random(8)),
        ];
    }
}
