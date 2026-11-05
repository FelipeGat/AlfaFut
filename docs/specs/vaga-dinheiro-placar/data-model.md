# Data Model: Vaga, dinheiro e placar

## Entity: MovimentoFinanceiro (nova)

| Campo | Tipo | Regra |
|-------|------|-------|
| patota_id | FK patotas | cascata ao apagar a turma |
| despesa_id | FK despesas, nulo | fica nulo se a despesa for removida |
| pagamento_id | FK pagamentos, nulo | fica nulo se a parcela sair no rateio refeito |
| user_id | FK users, nulo | quem fez |
| acao | texto curto | criou, alterou, refez_rateio, baixa, marcou_paga, cancelou, removeu |
| descricao | texto | frase pronta para a tela |
| valor_centavos | inteiro, nulo | valor envolvido |
| created_at | data e hora | quando |

## Entity: User (alterada)

| Campo | Tipo | Regra |
|-------|------|-------|
| excluida_em | data e hora, nulo | preenchido ao excluir a conta |

## Entity: Confirmação (sem coluna nova)

Estados: titular (`confirmado`, fora da fila), fila (`confirmado`, em fila com posição), recusa (`recusado`). A posição é renumerada a cada saída.

## Entity: Partida (sem coluna nova)

Transições: agendada ou confirmada → em andamento ↔ pausada → finalizada; agendada ou confirmada → cancelada. Pausada é `em_andamento` com `pausada_em`.

## Entity: Despesa (sem coluna nova)

Estados: aberta → fechada (sozinha quando as parcelas quitam, ou marcada por quem organiza se não for rateada); aberta → cancelada.

## Entity: Pagamento (sem coluna nova)

Estados: pendente → parcial → pago; pendente ou parcial → cancelado. `valor_pago` só cresce.
