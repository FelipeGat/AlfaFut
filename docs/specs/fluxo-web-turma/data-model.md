# Data Model: Fluxo da turma pelo site

Sem migração nesta frente.

## Entity: Vínculo de membro (`patota_membros`)

| Campo | Tipo | Regra |
|---|---|---|
| papel | texto(20) | `administrador`, `organizador`, `membro` |
| status | texto(20) | `ativo`, `inativo`, `removido` (novo) |
| saiu_em | data e hora | preenchido ao sair ou ser removido |

### State transitions

```text
(sem vínculo) --código--> ativo
ativo --sair--> inativo --código--> ativo (papel volta a membro)
ativo --remover--> removido --liberar--> inativo
removido --código--> recusado
```

## Entity: Turma (`patotas`)

`responsavel_id` só aceita membro ativo. Se o responsável sair ou for removido, volta a ser `criador_id`.

## Entity: Partida (`partidas`)

| Campo | Regra |
|---|---|
| local_id | local da mesma turma; não junto com `campo_id` |
| campo_id | campo ativo, ou o que a partida já usa; passa a ser preenchível |
| data_hora | futura; ao alterar, pode manter a data atual |
| confirmacao_ate | antes de `data_hora` |
| duracao_minutos | 30 a 240; vazio vira 90 |
| vagas_total | 4 a 200; vazio ao criar vem da turma; ao alterar, nunca abaixo dos confirmados |
| valor_individual | 0 a 99.999,99; vazio vira 0 |

### State transitions

Alterar e cancelar valem só com `status` em `agendada` ou `confirmada`, sem início nem fim registrados. A partida cancelada não volta a estado anterior nesta frente.

## Permissões novas

| Ação | fora / ex-membro | membro | organizador | responsável | criador | admin plataforma |
|---|---|---|---|---|---|---|
| Entrar com código | sim | aviso | aviso | aviso | aviso | sim |
| Sair | não | sim | sim | sim | não | só se for membro |
| Criar, alterar e cancelar partida | não | não | sim | sim | sim | sim |
| Telas de local | não | não | sim | sim | sim | sim |
| Ver gestão de membros | não | não | sim | sim | sim | sim |
| Mudar papel, remover, liberar, transferir | não | não | não | sim | sim | sim |

| Ação sobre campo | outro perfil | dono de campo | dono deste campo | admin plataforma |
|---|---|---|---|---|
| Cadastrar | não | sim | sim | sim |
| Alterar e remover | não | não | sim | sim |
| Abrir campo inativo | não | não | sim | sim |
