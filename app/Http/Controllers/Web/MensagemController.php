<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Mensagem;
use App\Models\Patota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MensagemController extends Controller
{
    public function index(Request $request, Patota $patota): View
    {
        $this->authorize('view', $patota);

        $mensagens = $patota->mensagens()
            ->with('autor', 'partida')
            ->orderByDesc('fixada')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('mensagens.index', compact('patota', 'mensagens'));
    }

    public function store(Request $request, Patota $patota): RedirectResponse
    {
        $this->authorize('participar', $patota);

        $dados = $request->validate([
            'conteudo' => ['required', 'string', 'max:1000'],
            'partida_id' => ['nullable', Rule::exists('partidas', 'id')->where('patota_id', $patota->id)],
            'tipo' => ['nullable', 'in:texto,aviso,enquete'],
            'fixada' => ['nullable', 'boolean'],
        ]);

        // Apenas quem organiza pode fixar
        if (! empty($dados['fixada']) && $request->user()->cannot('organizar', $patota)) {
            $dados['fixada'] = false;
        }

        $patota->mensagens()->create([
            ...$dados,
            'autor_id' => $request->user()->id,
            'tipo' => $dados['tipo'] ?? 'texto',
        ]);

        return redirect()->route('patotas.mensagens.index', $patota)
            ->with('status', 'Mensagem publicada no mural.');
    }

    public function destroy(Request $request, Patota $patota, Mensagem $mensagem): RedirectResponse
    {
        abort_unless($mensagem->patota_id === $patota->id, 404);
        $this->authorize('delete', $mensagem);

        $mensagem->delete();

        return back()->with('status', 'Mensagem removida.');
    }
}
