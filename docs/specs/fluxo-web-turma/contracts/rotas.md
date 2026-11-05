# Contratos: rotas do fluxo da turma

## Site `[PROPOSTA — a validar na implementacao]`

Todas exigem login e e-mail confirmado.

| Método e caminho | Nome | Permissão |
|---|---|---|
| POST `patotas/entrar` | `patotas.entrar` | logado, com limite `entrar-codigo` |
| POST `patotas/{patota}/sair` | `patotas.sair` | membro, exceto criador |
| GET `patotas/{patota}/partidas/nova` | `partidas.create` | `organizar` |
| POST `patotas/{patota}/partidas` | `partidas.store` | `organizar` |
| GET `partidas/{partida}/editar` | `partidas.edit` | `controlar` |
| PUT `partidas/{partida}` | `partidas.update` | `controlar` |
| POST `partidas/{partida}/cancelar` | `partidas.cancelar` | `controlar` |
| GET, POST `patotas/{patota}/locais` | `patotas.locais.index`, `.store` | `organizar` |
| GET `patotas/{patota}/locais/create` | `patotas.locais.create` | `organizar` |
| GET `patotas/{patota}/locais/{local}/edit` | `patotas.locais.edit` | `organizar` |
| PUT, DELETE `patotas/{patota}/locais/{local}` | `patotas.locais.update`, `.destroy` | `organizar` |
| GET `patotas/{patota}/membros` | `patotas.membros.index` | `organizar` |
| PATCH `patotas/{patota}/membros/{membro}/papel` | `patotas.membros.papel` | `gerenciarMembros` |
| POST `patotas/{patota}/membros/{membro}/responsavel` | `patotas.membros.responsavel` | `gerenciarMembros` |
| POST `patotas/{patota}/membros/{membro}/liberar` | `patotas.membros.liberar` | `gerenciarMembros` |
| DELETE `patotas/{patota}/membros/{membro}` | `patotas.membros.destroy` | `gerenciarMembros` |

## API: rotas existentes, mudanças

Fonte: `routes/api.php`, `app/Http/Controllers/Api/PatotaController.php`, `PartidaController.php`.

| Rota | Mudança |
|---|---|
| POST `api/v1/patotas/entrar` | Reativa quem saiu; recusa removido em 422 com `{mensagem}`; código inválido segue em 404 |
| POST `api/v1/patotas/{patota}/sair` | Responsável que sai devolve a responsabilidade ao criador; recusas continuam com `{mensagem}` |
| POST `api/v1/patotas/{patota}/partidas` | Aceita `campo_id`; `local_id` só da mesma turma; limites novos |
| PATCH `api/v1/partidas/{partida}` | Mesmas regras; recusa partida em andamento, finalizada ou cancelada em 422 |
| DELETE `api/v1/partidas/{partida}` | Recusa partida em andamento, finalizada ou já cancelada em 422 |
| Resposta de partida | Novo objeto `campo` quando carregado, com os mesmos campos de `local` |
