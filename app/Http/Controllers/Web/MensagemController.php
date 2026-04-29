<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Mensagem;
use App\Models\Patota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MensagemController extends Controller
{
    public function index(Request $request, Patota $patota): View
    {
        $this->authorizeMembro($request, $patota);

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
        $this->authorizeMembro($request, $patota);

        $dados = $request->validate([
            'conteudo' => ['required', 'string', 'max:1000'],
            'partida_id' => ['nullable', 'exists:partidas,id'],
            'tipo' => ['nullable', 'in:texto,aviso,enquete'],
            'fixada' => ['nullable', 'boolean'],
        ]);

        // Apenas admins podem fixar
        if (! empty($dados['fixada']) && $patota->criador_id !== $request->user()->id) {
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
        abort_unless(
            $mensagem->autor_id === $request->user()->id || $patota->criador_id === $request->user()->id,
            403,
            'Apenas o autor ou administrador pode remover esta mensagem.'
        );

        $mensagem->delete();

        return back()->with('status', 'Mensagem removida.');
    }

    private function authorizeMembro(Request $request, Patota $patota): void
    {
        $eMembro = $patota->membrosAtivos()
            ->where('users.id', $request->user()->id)
            ->exists();
        abort_unless($eMembro, 403);
    }
}
