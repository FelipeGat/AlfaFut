# 09 - API REST

> Documentacao da API consumida pelo `AlfaFutApp`.

Base URL local (XAMPP): `http://localhost/AlfaFut/public/api/v1`
Base URL artisan serve: `http://127.0.0.1:8000/api/v1`

Todas as rotas (exceto `auth/registrar` e `auth/login`) exigem header:
```
Authorization: Bearer {token}
Accept: application/json
```

Conta com e-mail nao confirmado recebe 403 em todas as rotas autenticadas, exceto `auth/eu`, `auth/logout`, `auth/reenviar-confirmacao` e `perfil`.

## Limites de tentativas

Excedido o limite, a resposta e 429 com `{ "message": "..." }` em portugues e cabecalho `Retry-After`.

| Rota | Limite |
|------|--------|
| `POST /auth/login` | 5/min por e-mail + IP |
| `POST /auth/registrar` | 30/h por IP |
| `POST /auth/esqueci-senha` | 3/min por IP |
| `POST /auth/reenviar-confirmacao` | 6/min por conta |
| `POST /patotas/entrar` | 10/min por usuario |

## Autenticacao

### POST /auth/registrar
Body:
```json
{
  "name": "Jogador Exemplo",
  "apelido": "Jogador",
  "email": "jogador@example.com",
  "telefone": "00900000000",
  "password": "sua-senha",
  "posicao_preferida": "meia",
  "nivel_habilidade": "avancado"
}
```
Resposta 201:
```json
{
  "usuario": { "id": 1, "nome": "...", "email": "...", "email_confirmado": false },
  "token": "1|abcd..."
}
```
O cadastro envia o e-mail de confirmacao. O link recebido confirma a conta.

### POST /auth/reenviar-confirmacao
Reenvia o e-mail de confirmacao. Responde 200 `{ "mensagem": "..." }`, inclusive quando a conta ja esta confirmada, sem reenviar nesse caso.

### POST /auth/login
Body:
```json
{ "email": "admin@alfafut.test", "password": "sua-senha", "device_name": "app-mobile" }
```

### GET /auth/eu
Retorna o usuario autenticado, com `email`, `email_confirmado`, `telefone`, `role`, `is_admin` e `preferencias_acessibilidade`.

### POST /auth/logout
Revoga o token atual.

## Perfil

### PATCH /perfil
Atualiza dados do perfil.

### PATCH /perfil/acessibilidade
Body:
```json
{
  "alto_contraste": true,
  "tamanho_fonte": "grande",
  "reduzir_movimento": false,
  "leitor_tela_otimizado": true,
  "necessidades_acessibilidade": ["surdo", "libras"]
}
```

## Patotas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/patotas` | Lista patotas do usuario |
| POST | `/patotas` | Cria nova patota |
| GET | `/patotas/{id}` | Detalhe da patota |
| PATCH | `/patotas/{id}` | Atualiza (apenas criador) |
| DELETE | `/patotas/{id}` | Arquiva (apenas criador) |
| POST | `/patotas/entrar` | Entra com codigo de convite |
| GET | `/patotas/{id}/membros` | Lista membros ativos |

### Quem ve o que em um usuario

Usuario aninhado (confirmacao, partida, turma e mensagem) traz so `id`, `nome`, `apelido`, `avatar_url`, `posicao_preferida` e `nivel_habilidade`.

Em `/patotas/{id}/membros`, organizador e admin global recebem tambem `email`, `telefone` e `necessidades`.

## Partidas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/patotas/{id}/partidas?filtro=proximas\|passadas` | Lista partidas |
| POST | `/patotas/{id}/partidas` | Cria partida (admin) |
| GET | `/partidas/{id}` | Detalhe |
| PATCH | `/partidas/{id}` | Atualiza |
| DELETE | `/partidas/{id}` | Cancela |

### Confirmacoes
| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/partidas/{id}/confirmar` | Confirma presenca |
| POST | `/partidas/{id}/recusar` | Recusa |
| DELETE | `/partidas/{id}/confirmacao` | Cancela confirmacao |

## Despesas e pagamentos

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/patotas/{id}/despesas` | Lista despesas |
| POST | `/patotas/{id}/despesas` | Cria despesa (auto-rateia se `rateada=true`) |
| GET | `/despesas/{id}` | Detalhe + pagamentos |
| PATCH | `/despesas/{id}` | Atualiza |
| DELETE | `/despesas/{id}` | Remove |
| POST | `/pagamentos/{id}/quitar` | Registra pagamento |

## Mensagens (mural)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/patotas/{id}/mensagens?partida_id=N` | Lista (filtro opcional por partida) |
| POST | `/patotas/{id}/mensagens` | Posta mensagem |
| DELETE | `/mensagens/{id}` | Remove (autor ou admin) |

## Codigos HTTP

| Codigo | Significado |
|--------|-------------|
| 200 | OK |
| 201 | Criado |
| 401 | Nao autenticado |
| 403 | Sem permissao (nao-membro / nao-admin) ou e-mail nao confirmado |
| 404 | Nao encontrado |
| 422 | Erro de validacao |
| 429 | Excesso de tentativas |

## Exemplo de fluxo completo (curl)

```bash
# 1. Login
curl -X POST http://localhost/AlfaFut/public/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@alfafut.test","password":"sua-senha"}'

# 2. Listar patotas (use o token retornado)
curl http://localhost/AlfaFut/public/api/v1/patotas \
  -H "Authorization: Bearer {token}"

# 3. Confirmar presenca em uma partida
curl -X POST http://localhost/AlfaFut/public/api/v1/partidas/1/confirmar \
  -H "Authorization: Bearer {token}"
```
