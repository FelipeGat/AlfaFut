<?php

namespace Tests\Feature\Seguranca;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceWorkerTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginas_autenticadas_nao_ficam_em_cache(): void
    {
        $user = User::factory()->create();

        foreach (['/dashboard', '/patotas', '/partidas', '/profile', '/acessibilidade'] as $url) {
            $cabecalho = $this->actingAs($user)->get($url)->assertOk()->headers->get('Cache-Control');

            $this->assertStringContainsString('no-store', $cabecalho, $url);
            $this->assertStringContainsString('private', $cabecalho, $url);
        }
    }

    public function test_service_worker_nao_guarda_o_que_nao_e_publico(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        $this->assertStringNotContainsString('dinamico', $sw);
        $this->assertStringNotContainsString("'/',", $sw);
    }
}
