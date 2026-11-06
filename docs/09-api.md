# 09 - API REST

> Documentação da API v1. As rotas ficam em `routes/api.php`.

Base local: `http://localhost:8100/api/v1` (compose de homologação).

Toda rota, exceto as públicas de autenticação, exige o cabeçalho `Authorization: Bearer {token}`. A API responde sempre em JSON, mesmo sem o cabeçalho `Accept: application/json`, e nunca redireciona para a tela de login.

O token vale por 60 dias (`SANCTUM_EXPIRATION`, em minutos) e não renova sozinho. Vencido, a API responde 401 e a pessoa entra de novo. Trocar ou redefinir a senha encerra todos os tokens da conta.

Conta com e-mail não confirmado recebe 403 em todas as rotas autenticadas, exceto `auth/eu`, `auth/logout`, `auth/reenviar-confirmacao` e `perfil*`.

## Formato das respostas

| Tipo de resposta | Formato |
|---|---|
| Listas, detalhe e alteração, exceto `GET /despesas/{id}` | `{ "data": ... }` |
| Criação de turma, partida, despesa e mensagem (`POST` que responde 201) | O objeto direto, **sem** `data` |
| Ações | `{ "mensagem": "..." }`; `confirmar` traz também `confirmacao`, `entrar` traz `patota` e `sair` traz `pendente` |
| Regra recusada | 422 `{ "mensagem": "..." }` |
| Validação recusada | 422 `{ "message": "...", "errors": { "campo": ["..."] } }` |
| Sem login, sem permissão, não encontrado, limite, erro interno | `{ "message": "..." }` |

A diferença entre detalhe e criação é mantida porque o aplicativo pode depender dela; padronizar fica para quando ele for auditado.

## Códigos HTTP

| Código | Significado |
|---|---|
| 200 | OK |
| 201 | Criado |
| 401 | Sem login ou token vencido |
| 403 | Sem permissão ou e-mail não confirmado |
| 404 | Não encontrado |
| 405 | Método não permitido nesta rota |
| 422 | Validação ou regra recusada |
| 429 | Excesso de tentativas, com cabeçalho `Retry-After` |
| 500 | Erro interno |

## Limites de tentativas

| Rota | Limite |
|---|---|
| `POST /auth/login` | 5/min por e-mail + IP |
| `POST /auth/registrar` | 30/h por IP |
| `POST /auth/esqueci-senha` | 3/min por IP |
| `POST /auth/reenviar-confirmacao` | 6/min por conta |
| `POST /patotas/entrar` | 10/min por usuário |
| `GET /perfil/meus-dados` | 6/min por conta |

## Autenticação

### POST /auth/registrar

Público.

```json
{
  "name": "Jogador Exemplo",
  "apelido": "Jogador",
  "email": "jogador@example.com",
  "telefone": "00900000000",
  "password": "sua-senha",
  "posicao_preferida": "meia",
  "nivel_habilidade": "avancado",
  "tipo_usuario": "jogador",
  "aceite_termos": true
}
```

- `aceite_termos` é obrigatório: sem ele, 422.
- `tipo_usuario` é opcional, entre `jogador` (padrão), `dono_pelada` e `dono_campo`. `role` é ignorado.
- Resposta 201: `{ "usuario": { ... }, "token": "1|abcd..." }`. O cadastro envia o e-mail de confirmação.

### POST /auth/login

Público.

```json
{ "email": "jogador@example.com", "password": "sua-senha", "device_name": "app-mobile" }
```

Resposta 200: `{ "usuario": { ... }, "token": "2|abcd..." }`.

### POST /auth/esqueci-senha

Público. `{ "email": "..." }`. Responde 200 com a mesma `mensagem` para qualquer e-mail válido, tenha conta ou não. Quem tem conta recebe o link por e-mail.

### POST /auth/reenviar-confirmacao

Reenvia o e-mail de confirmação. Responde 200 `{ "mensagem": "..." }`, inclusive com a conta já confirmada, sem reenviar nesse caso.

### GET /auth/eu

Responde `{ "data": ... }` com o usuário autenticado: `id`, `nome`, `apelido`, `avatar_url`, `posicao_preferida`, `nivel_habilidade`, `email`, `email_confirmado`, `telefone`, `role`, `tipo_usuario`, `is_admin` e `preferencias_acessibilidade`.

### POST /auth/logout

Revoga o token atual.

## Perfil

| Método | Rota | Descrição |
|---|---|---|
| PATCH | `/perfil` | Atualiza `name`, `apelido`, `telefone`, `avatar_url`, `data_nascimento`, `posicao_preferida` e `nivel_habilidade` |
| PATCH | `/perfil/acessibilidade` | Atualiza as preferências de acessibilidade |
| GET | `/perfil/meus-dados` | Baixa o arquivo `meus-dados-alfafut.json`, com `gerado_em`, `cadastro`, `acessibilidade`, `turmas`, `confirmacoes`, `parcelas`, `mensagens` e `campos` |

Exemplo de `PATCH /perfil/acessibilidade`:

```json
{
  "alto_contraste": true,
  "tamanho_fonte": "grande",
  "reduzir_movimento": false,
  "leitor_tela_otimizado": true,
  "necessidades_acessibilidade": ["surdo", "libras"]
}
```

## Turmas (patotas)

| Método | Rota | Descrição |
|---|---|---|
| GET | `/patotas` | Lista as turmas da pessoa |
| POST | `/patotas` | Cria turma; responde 201 sem `data` |
| GET | `/patotas/arquivadas` | Lista as turmas arquivadas de quem criou |
| GET | `/patotas/{id}` | Detalhe |
| PUT ou PATCH | `/patotas/{id}` | Atualiza |
| DELETE | `/patotas/{id}` | Arquiva |
| POST | `/patotas/{id}/restaurar` | Restaura; só quem criou |
| POST | `/patotas/entrar` | Entra com `codigo_convite`; responde `{ "mensagem", "patota" }`; reativa quem saiu; removido recebe 422 até liberarem a volta pelo site; código inválido recebe 404 |
| POST | `/patotas/{id}/sair` | Sai da turma; a resposta traz `pendente`, o valor em aberto |
| GET | `/patotas/{id}/membros` | Lista os membros ativos |

A listagem, o detalhe e a criação trazem `total_membros` e `criador`.

### Quem vê o quê em um usuário

Usuário aninhado (confirmação, partida, turma e mensagem) traz só `id`, `nome`, `apelido`, `avatar_url`, `posicao_preferida` e `nivel_habilidade`.

Em `/patotas/{id}/membros`, quem organiza e o admin global recebem também `email`, `telefone` e `necessidades`.

## Partidas

| Método | Rota | Descrição |
|---|---|---|
| GET | `/patotas/{id}/partidas?filtro=proximas\|passadas` | Lista |
| POST | `/patotas/{id}/partidas` | Cria; responde 201 sem `data` |
| GET | `/partidas/{id}` | Detalhe |
| PATCH | `/partidas/{id}` | Atualiza |
| DELETE | `/partidas/{id}` | Cancela |

- `local_id` só de local da mesma turma; `campo_id` só de campo ativo, ou o que a partida já usa; os dois juntos dão 422.
- `data_hora` é futura; `confirmacao_ate` vem antes do início; `vagas_total` vai de 4 a 200 e nunca fica abaixo dos confirmados; `valor_individual` vai até 99999,99.
- Partida em andamento, finalizada ou cancelada não muda: `PATCH` e `DELETE` respondem 422. `status` aceita só `agendada`, `confirmada` e `cancelada`.
- A resposta traz `campo` ao lado de `local`, com os mesmos campos. Datas saem no horário de Brasília (`-03:00`).

### Confirmações

| Método | Rota | Descrição |
|---|---|---|
| POST | `/partidas/{id}/confirmar` | Confirma presença |
| POST | `/partidas/{id}/recusar` | Recusa |
| DELETE | `/partidas/{id}/confirmacao` | Cancela a confirmação |

- `confirmar` recusa partida cancelada, iniciada, finalizada, com horário passado ou prazo vencido. Repetir mantém a situação. Partida cheia entra na fila, ou é recusada se a lista estiver desligada.
- `recusar` e `DELETE` valem até a partida começar e fazem a fila andar.

### Placar e sorteio

| Método | Rota | Descrição |
|---|---|---|
| GET | `/partidas/{id}/placar` | Placar ao vivo |
| POST | `/partidas/{id}/sortear` | Sorteia os times, só antes de a partida começar |
| POST | `/partidas/{id}/iniciar` | Inicia ou retoma; exige times sorteados |
| POST | `/partidas/{id}/pausar` | Pausa |
| POST | `/partidas/{id}/finalizar` | Finaliza |
| POST | `/partidas/{id}/gol` | Registra gol; exige partida iniciada e não encerrada |

`iniciar`, `pausar` e `finalizar` seguem a ordem da partida; repetir responde 200 sem mudar nada. No gol, `jogador_id` precisa ser do time informado; no gol contra, do outro time.

## Despesas e pagamentos

| Método | Rota | Descrição |
|---|---|---|
| GET | `/patotas/{id}/despesas` | Lista |
| POST | `/patotas/{id}/despesas` | Cria; responde 201 sem `data` |
| GET | `/despesas/{id}` | Detalhe; responde `{ "despesa": ..., "pagamentos": [...] }`, sem `data` |
| PATCH | `/despesas/{id}` | Atualiza |
| DELETE | `/despesas/{id}` | Remove; recusado se houver pagamento |
| POST | `/despesas/{id}/refazer-rateio` | Refaz o rateio com os confirmados |
| POST | `/despesas/{id}/marcar-paga` | Marca como paga |
| POST | `/despesas/{id}/cancelar` | Cancela |
| POST | `/pagamentos/{id}/quitar` | Registra pagamento; soma ao já pago e não passa do saldo |

- `rateada` ausente vale `false`; `rateada: true` exige `partida_id` com confirmados. `valor_total` vai até 999999,99, com 2 casas.
- `PATCH` não aceita `status`. Alterar `valor_total` refaz o rateio e não aceita menos que o já pago.
- Quem organiza só registra pagamento por quem não é mais membro ativo.

## Mural

| Método | Rota | Descrição |
|---|---|---|
| GET | `/patotas/{id}/mensagens?partida_id=N` | Lista, com filtro opcional por partida |
| POST | `/patotas/{id}/mensagens` | Publica; responde 201 sem `data`; `partida_id` precisa ser da turma |
| DELETE | `/mensagens/{id}` | Remove |

## Administração

Só para admin global, com e-mail confirmado.

| Método | Rota | Descrição |
|---|---|---|
| GET | `/admin/resumo` | Totais da plataforma |
| GET | `/admin/usuarios` | Lista de usuários |
| GET | `/admin/patotas` | Lista de turmas |
| GET | `/admin/partidas` | Lista de partidas |
| GET | `/admin/partidas-ativas` | Partidas em andamento |

## Exemplo

```bash
# 1. Entrar
curl -X POST http://localhost:8100/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"jogador@example.com","password":"sua-senha"}'

# 2. Listar as turmas, com o token recebido
curl http://localhost:8100/api/v1/patotas \
  -H "Authorization: Bearer {token}"

# 3. Confirmar presença em uma partida
curl -X POST http://localhost:8100/api/v1/partidas/1/confirmar \
  -H "Authorization: Bearer {token}"
```
