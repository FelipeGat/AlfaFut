<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeuUsuarioResource;
use App\Services\DadosDoTitular;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsuarioController extends Controller
{
    public function atualizarPerfil(Request $request): MeuUsuarioResource
    {
        $dados = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'apelido' => ['nullable', 'string', 'max:60'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'avatar_url' => ['nullable', 'url', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],
            'posicao_preferida' => ['nullable', 'in:goleiro,zagueiro,lateral,meia,atacante'],
            'nivel_habilidade' => ['nullable', 'in:iniciante,intermediario,avancado'],
        ]);

        $request->user()->update($dados);

        return new MeuUsuarioResource($request->user()->fresh());
    }

    public function atualizarAcessibilidade(Request $request): MeuUsuarioResource
    {
        $dados = $request->validate([
            'alto_contraste' => ['nullable', 'boolean'],
            'tamanho_fonte' => ['nullable', 'in:pequena,media,grande,extra_grande'],
            'reduzir_movimento' => ['nullable', 'boolean'],
            'leitor_tela_otimizado' => ['nullable', 'boolean'],
            'necessidades_acessibilidade' => ['nullable', 'array'],
            'necessidades_acessibilidade.*' => ['string', 'max:60'],
        ]);

        $request->user()->update($dados);

        return new MeuUsuarioResource($request->user()->fresh());
    }

    public function meusDados(Request $request, DadosDoTitular $dados): Response
    {
        return $dados->baixar($request->user());
    }
}
