<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Patota;
use App\Models\PatotaMembro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatotaController extends Controller
{
    public function index(Request $request): View
    {
        $patotas = $request->user()->patotas()
            ->withCount('membrosAtivos as total_membros')
            ->orderBy('nome')
            ->get();

        return view('patotas.index', compact('patotas'));
    }

    public function create(): View
    {
        $clubes = config('clubes.clubes');
        return view('patotas.create', compact('clubes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'string', 'size:2'],
            'jogadores_por_time' => ['required', 'integer', 'min:3', 'max:11'],
            'quantidade_times' => ['required', 'integer', 'min:2', 'max:6'],
            'valor_mensalidade' => ['nullable', 'numeric', 'min:0'],
            'publica' => ['nullable', 'boolean'],
            'brasao' => ['nullable', 'string', 'max:120'],
        ]);

        $patota = Patota::create([
            ...$dados,
            'criador_id' => $request->user()->id,
            'publica' => (bool) ($dados['publica'] ?? false),
        ]);

        PatotaMembro::create([
            'patota_id' => $patota->id,
            'user_id' => $request->user()->id,
            'papel' => 'administrador',
            'status' => 'ativo',
        ]);

        return redirect()
            ->route('patotas.show', $patota)
            ->with('status', 'Turma criada com sucesso. Compartilhe o codigo de convite com o time.');
    }

    public function show(Request $request, Patota $patota): View
    {
        $this->authorizeMembro($request, $patota);

        $patota->load('criador');
        $patota->loadCount('membrosAtivos as total_membros');
        $proximasPartidas = $patota->partidas()->proximas()->with('local')->limit(5)->get();
        $membros = $patota->membrosAtivos()->orderBy('name')->get();

        return view('patotas.show', compact('patota', 'proximasPartidas', 'membros'));
    }

    public function edit(Request $request, Patota $patota): View
    {
        abort_unless($patota->criador_id === $request->user()->id, 403);

        $membros = $patota->membrosAtivos()->orderBy('name')->get();
        $clubes = config('clubes.clubes');

        return view('patotas.edit', compact('patota', 'membros', 'clubes'));
    }

    public function update(Request $request, Patota $patota): RedirectResponse
    {
        abort_unless($patota->criador_id === $request->user()->id, 403);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string', 'max:80'],
            'estado' => ['nullable', 'string', 'size:2'],
            'jogadores_por_time' => ['required', 'integer', 'min:3', 'max:11'],
            'quantidade_times' => ['required', 'integer', 'min:2', 'max:6'],
            'valor_mensalidade' => ['nullable', 'numeric', 'min:0'],
            'publica' => ['nullable', 'boolean'],
            'responsavel_id' => ['nullable', 'exists:users,id'],
            'brasao' => ['nullable', 'string', 'max:120'],
        ]);

        $patota->update([
            ...$dados,
            'publica' => (bool) ($dados['publica'] ?? false),
        ]);

        return redirect()->route('patotas.show', $patota)->with('status', 'Turma atualizada.');
    }

    public function destroy(Request $request, Patota $patota): RedirectResponse
    {
        abort_unless($patota->criador_id === $request->user()->id, 403);
        $patota->delete();

        return redirect()->route('patotas.index')->with('status', 'Turma arquivada.');
    }

    private function authorizeMembro(Request $request, Patota $patota): void
    {
        $eMembro = $patota->membrosAtivos()
            ->where('users.id', $request->user()->id)
            ->exists();
        abort_unless($eMembro, 403, 'Voce nao faz parte desta turma.');
    }
}
