# Data Model: Base segura para o teste com pessoas reais

**Sem mudança de esquema.** Todas as colunas usadas já existem; não há migração nesta frente.

## Entity: Vínculo de turma (`patota_membros`)

Fonte: `database/migrations/2026_04_29_112515_create_patota_membros_table.php`, `app/Models/PatotaMembro.php`.

| Campo | Uso nas permissões |
|-------|--------------------|
| `papel` | `administrador` ou `organizador` organizam; `membro` é o padrão |
| `status` | só o vínculo ativo vale (`membrosAtivos`); `inativo` = fora da turma |

**State transitions**: `ativo` → `inativo` ao sair da turma; a leitura cai na mesma requisição.

## Entity: Usuário (`users`)

| Campo | Regra |
|-------|-------|
| `role` | `admin` é o administrador da plataforma; só a própria pessoa e admins veem o valor |
| `email_verified_at` | nulo = conta não confirmada; preenchido só pelo link ou pelo comando do operador |
| `email`, `telefone`, `necessidades_acessibilidade` | protegidos (FR-010) |
| `alto_contraste`, `tamanho_fonte`, `reduzir_movimento`, `leitor_tela_otimizado` | só a própria pessoa |

## Matriz de permissões

"Organizador" = o criador, ou quem está ativo com `papel` organizador ou administrador, ou o responsável enquanto ativo. O administrador da plataforma pode tudo, menos dar baixa no pagamento de outra pessoa.

| Ação | Fora da turma | Membro | Organizador |
|------|---------------|--------|-------------|
| Ver turma, partidas, membros, mural, despesas, placar, TV, resultado, dados ao vivo | não | sim | sim |
| Ver contato e necessidades de membros da turma | não | não | sim |
| Confirmar e recusar presença, escrever no mural | não | sim | sim |
| Criar, alterar e apagar despesa | não | não | sim |
| Dar baixa em pagamento | não | só o próprio | só o próprio |
| Apagar mensagem | não | só a própria | sim |
| Alterar e apagar turma | não | não | só o criador (regra atual do código) |
| Criar e alterar partida, sortear, controlar placar | não | não | sim |
