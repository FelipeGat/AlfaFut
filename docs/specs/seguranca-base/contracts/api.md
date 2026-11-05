# Contrato: mudanças na API v1

Base: `routes/api.php`, `app/Http/Resources/UsuarioResource.php` (05/11/2026). Só o que muda nesta frente.

## Quem vê o quê em um usuário

### Público: `UsuarioResource` (aninhado em confirmação, partida, turma e mensagem)

Mantém `id`, `nome`, `apelido`, `avatar_url`, `posicao_preferida`, `nivel_habilidade`.
Deixa de enviar `email`, `telefone`, `role`, `is_admin`, `preferencias_acessibilidade`.

### A própria pessoa: `auth/registrar`, `auth/login`, `auth/eu`, `perfil`, `perfil/acessibilidade`

Mesmos campos de hoje, sem mudança para o cliente. Acrescenta `email_confirmado` (booleano).

### `GET /api/v1/patotas/{patota}/membros`

Campos públicos para todo membro. Para o organizador e o admin global, acrescenta `email`, `telefone` e `necessidades`.

## Respostas de erro

| Situação | Status | Corpo |
|----------|--------|-------|
| Sem permissão sobre o registro | 403 | `{ "message": "..." }` em português |
| E-mail não confirmado | 403 | `{ "message": "..." }` dizendo que falta confirmar |
| Excesso de tentativas | 429 | `{ "message": "..." }` em português, com cabeçalho `Retry-After` |

## Novas restrições em rotas existentes

| Rota | Limite | Exige e-mail confirmado |
|------|--------|-------------------------|
| `POST auth/login` | 5/min por e-mail + IP | não |
| `POST auth/registrar` | 30/h por IP | não |
| `POST auth/esqueci-senha` | 3/min por IP | não |
| `POST patotas/entrar` | 10/min por usuário | sim |
| `GET auth/eu`, `POST auth/logout` | | não |
| demais rotas autenticadas (`perfil`, turmas, partidas, despesas, mensagens, admin) | | sim, exceto `perfil` |

## Rota nova

`POST /api/v1/auth/reenviar-confirmacao`, autenticada, 6/min por conta. Responde 200 `{ "mensagem": "..." }`, inclusive quando a conta já está confirmada, sem reenviar nesse caso.
