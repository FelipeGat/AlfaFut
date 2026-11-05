# Contrato: rotas desta frente

Rotas existentes (`routes/web.php`, `routes/api.php`) mantêm caminho e formato; mudam as recusas, sempre `422 {"mensagem": ...}` na API e aviso `erro` no site.

## [PROPOSTA — a validar na implementacao] Rotas novas

| Método | Caminho (site) | Caminho (API v1) | Quem |
|--------|----------------|------------------|------|
| POST | `/patotas/{patota}/restaurar` | `/patotas/{patota}/restaurar` | criador |
| GET | lista em `/patotas` | `/patotas/arquivadas` | criador |
| POST | `/patotas/{patota}/despesas/{despesa}/refazer-rateio` | `/despesas/{despesa}/refazer-rateio` | organizador |
| POST | `/patotas/{patota}/despesas/{despesa}/cancelar` | `/despesas/{despesa}/cancelar` | organizador |
| POST | `/patotas/{patota}/despesas/{despesa}/marcar-paga` | `/despesas/{despesa}/marcar-paga` | organizador |

## Mudanças em rotas existentes

- `PATCH /partidas/{partida}`: `status` aceita só `agendada`, `confirmada` e `cancelada`.
- `PATCH /despesas/{despesa}`: `status` deixa de ser aceito; use as rotas novas. `valor_total` refaz o rateio.
- `POST /patotas/{patota}/despesas`: `rateada` ausente vale `false`; `rateada: true` exige `partida_id` com confirmados.
- `POST /pagamentos/{pagamento}/quitar`: `valor_pago` soma ao já pago e não passa do saldo.
- `POST /partidas/{partida}/gol`: `jogador_id` precisa ser do time informado (no gol contra, do outro time).
