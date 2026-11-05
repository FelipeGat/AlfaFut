<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Patota;
use App\Models\User;
use App\Services\MembrosDaTurma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembroController extends Controller
{
    public function __construct(private readonly MembrosDaTurma $membros) {}

    public function index(Patota $patota): View
    {
        $this->authorize('organizar', $patota);

        $ativos = $patota->membrosAtivos()->orderBy('name')->get();
        $removidos = $patota->membros()->wherePivot('status', 'removido')->orderBy('name')->get();

        return view('patotas.membros', compact('patota', 'ativos', 'removidos'));
    }

    public function papel(Request $request, Patota $patota, User $membro): RedirectResponse
    {
        $this->authorize('gerenciarMembros', $patota);

        $dados = $request->validate([
            'papel' => ['required', Rule::in(['organizador', 'membro'])],
        ]);

        $this->membros->mudarPapel($patota, $membro, $dados['papel']);

        return $this->voltar($request, $patota, 'Papel alterado.');
    }

    public function responsavel(Request $request, Patota $patota, User $membro): RedirectResponse
    {
        $this->authorize('gerenciarMembros', $patota);

        $this->membros->tornarResponsavel($patota, $membro);

        return $this->voltar($request, $patota, 'Responsável alterado.');
    }

    public function liberar(Request $request, Patota $patota, User $membro): RedirectResponse
    {
        $this->authorize('gerenciarMembros', $patota);

        $this->membros->liberarVolta($patota, $membro);

        return $this->voltar($request, $patota, 'Volta liberada: a pessoa já pode entrar com o código de convite.');
    }

    public function destroy(Request $request, Patota $patota, User $membro): RedirectResponse
    {
        $this->authorize('gerenciarMembros', $patota);

        $this->membros->remover($patota, $request->user(), $membro);

        return $this->voltar($request, $patota, 'Membro removido da turma.');
    }

    /** Quem passa a responsabilidade adiante pode perder o acesso à gestão. */
    private function voltar(Request $request, Patota $patota, string $mensagem): RedirectResponse
    {
        $destino = $request->user()->can('organizar', $patota)
            ? route('patotas.membros.index', $patota)
            : route('patotas.show', $patota);

        return redirect($destino)->with('status', $mensagem);
    }
}
