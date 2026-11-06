<?php

namespace Tests\Feature\PortuguesLgpdLimpeza;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LimpezaTest extends TestCase
{
    use RefreshDatabase;

    public function test_nenhuma_tela_nem_o_readme_citam_a_faculdade(): void
    {
        $arquivos = [...File::allFiles(resource_path('views')), base_path('README.md'), public_path('manifest.webmanifest')];

        foreach ($arquivos as $arquivo) {
            $this->assertStringNotContainsStringIgnoringCase('uniasselvi', File::get($arquivo), (string) $arquivo);
        }
    }

    public function test_comando_de_demonstracao_foi_removido(): void
    {
        $this->assertArrayNotHasKey('partida:demo', Artisan::all());
    }

    public function test_componentes_que_sobraram_estao_em_uso(): void
    {
        $telas = collect(File::allFiles(resource_path('views')))->map(fn ($arquivo) => File::get($arquivo))->implode("\n");

        foreach (File::files(resource_path('views/components')) as $componente) {
            $this->assertStringContainsString('<x-'.$componente->getBasename('.blade.php'), $telas);
        }
    }

    public function test_perfil_sem_formulario_dentro_de_formulario(): void
    {
        $user = User::factory()->unverified()->create();

        $html = $this->actingAs($user)->get('/profile')->assertOk()->getContent();

        libxml_use_internal_errors(true);
        $documento = new DOMDocument;
        $documento->loadHTML($html);
        libxml_clear_errors();

        $this->assertSame(0, (new DOMXPath($documento))->query('//form//form')->length);
        $this->assertStringContainsString('form="reenviar-confirmacao"', $html);
        $this->assertStringContainsString('id="reenviar-confirmacao"', $html);
    }

    public function test_reenviar_confirmacao_nao_salva_o_perfil(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['name' => 'Nome Original']);

        $this->actingAs($user)->post(route('verification.send'), ['name' => 'Nome Trocado'])->assertRedirect();

        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertSame('Nome Original', $user->fresh()->name);
    }

    public function test_paginas_de_erro_em_portugues(): void
    {
        $this->get('/pagina-que-nao-existe')->assertNotFound()->assertSee('Página não encontrada')->assertDontSee('Not Found');
    }

    public function test_mensagens_padrao_em_portugues(): void
    {
        $this->assertSame('E-mail ou senha incorretos.', __('auth.failed'));
        $this->assertStringContainsString('inválido', __('passwords.token'));
        $this->assertSame('Os dados enviados são inválidos.', __('The given data was invalid.'));
    }
}
