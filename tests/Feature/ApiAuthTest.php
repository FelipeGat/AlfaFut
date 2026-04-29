<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_novo_usuario_e_recebe_token(): void
    {
        $r = $this->postJson('/api/v1/auth/registrar', [
            'name' => 'Teste',
            'email' => 'teste@alfafut.test',
            'password' => 'senha1234',
            'apelido' => 'Tt',
        ]);

        $r->assertCreated()
            ->assertJsonPath('usuario.email', 'teste@alfafut.test')
            ->assertJsonStructure(['usuario' => ['id', 'nome', 'email'], 'token']);
        $this->assertDatabaseHas('users', ['email' => 'teste@alfafut.test']);
    }

    public function test_login_com_credenciais_validas(): void
    {
        $user = User::factory()->create([
            'email' => 'login@alfafut.test',
            'password' => Hash::make('senha1234'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'login@alfafut.test',
            'password' => 'senha1234',
        ])
            ->assertOk()
            ->assertJsonPath('usuario.id', $user->id)
            ->assertJsonStructure(['token']);
    }

    public function test_login_com_senha_invalida_retorna_422(): void
    {
        User::factory()->create([
            'email' => 'erro@alfafut.test',
            'password' => Hash::make('senha1234'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'erro@alfafut.test',
            'password' => 'errada',
        ])->assertUnprocessable();
    }

    public function test_eu_retorna_usuario_autenticado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/eu')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_acesso_sem_token_retorna_401(): void
    {
        $this->getJson('/api/v1/auth/eu')->assertUnauthorized();
    }

    public function test_logout_revoga_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('teste')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertOk();
        $this->assertCount(0, $user->fresh()->tokens);
    }
}
