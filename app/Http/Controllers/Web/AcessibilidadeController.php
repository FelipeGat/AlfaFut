<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcessibilidadeController extends Controller
{
    public function edit(Request $request): View
    {
        return view('acessibilidade.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'alto_contraste' => ['nullable', 'boolean'],
            'tamanho_fonte' => ['required', 'in:pequena,media,grande,extra_grande'],
            'reduzir_movimento' => ['nullable', 'boolean'],
            'leitor_tela_otimizado' => ['nullable', 'boolean'],
            'necessidades_acessibilidade' => ['nullable', 'array'],
            'necessidades_acessibilidade.*' => ['string', 'max:60'],
        ]);

        $request->user()->update([
            'alto_contraste' => (bool) ($dados['alto_contraste'] ?? false),
            'tamanho_fonte' => $dados['tamanho_fonte'],
            'reduzir_movimento' => (bool) ($dados['reduzir_movimento'] ?? false),
            'leitor_tela_otimizado' => (bool) ($dados['leitor_tela_otimizado'] ?? false),
            'necessidades_acessibilidade' => $dados['necessidades_acessibilidade'] ?? null,
        ]);

        return back()->with('status', 'Preferencias de acessibilidade salvas.');
    }
}
