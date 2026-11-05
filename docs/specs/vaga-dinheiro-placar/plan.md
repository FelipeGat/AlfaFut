# Implementation Plan: Vaga, dinheiro e placar

**Feature**: `vaga-dinheiro-placar` | **Date**: 2026-11-05 | **Spec**: [spec.md](spec.md)

## Summary

Tirar dos controllers as regras de vaga, placar, sorteio e dinheiro e colocá-las em serviços únicos, usados por site e API. A confirmação serializa por partida com trava de linha, o placar passa a ser derivado dos eventos, o dinheiro é calculado em centavos inteiros, e excluir a conta anonimiza em vez de apagar.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12.58, Blade, Tailwind 3, Alpine, Vite 7
**Storage**: MySQL 8.4 em produção; SQLite nos testes. Duas migrações: `movimentos_financeiros` (nova) e `users.excluida_em`
**Testing**: PHPUnit 11; navegador e pedidos simultâneos em Docker com MySQL (`.demos/alfafut`)
**Target Platform**: mesma imagem de `seguranca-base`
**Project Type**: monólito web com API REST v1 e PWA
**Performance Goals**: sem meta nova; a trava vale só por partida
**Constraints**: sessões de até 30 min; sem commit, push ou deploy sem autorização
**Scale/Scope**: 4 serviços novos, 1 ampliado, 2 requests, 8 controllers ajustados, 7 telas ajustadas

Sem `NEEDS CLARIFICATION`. As premissas que dependem do Felipe estão na spec e não bloqueiam a implementação.

## Constitution Check

*GATE: Deve passar antes do Phase 0. Re-checar apos Phase 1.* A constitution continua em rascunho v1.0.0, sem ratificação.

| Principio | Status | Notas |
|-----------|--------|-------|
| I. Veracidade de Dados | PASS | Rotas novas marcadas `[PROPOSTA]`; o guia só muda para citar o que existe |
| II. Dado da turma só para a turma | PASS | Rotas novas entram na matriz de permissões; mensagem e gol recusam ID de outra turma |
| III. Dinheiro e vaga sem surpresa | PASS | Esta frente é a que cumpre os quatro MUST: soma ao centavo, transação, centavos inteiros, histórico |
| IV. LGPD e imagem | PASS | Exclusão remove todo dado pessoal e mantém só o registro anônimo |
| V. Acessibilidade de verdade | PASS | Telas alteradas com rótulo, `aria-live` e acento |
| VI. Nada de produção sem prova | PASS | Suíte, build e navegador com MySQL antes de concluir; sem deploy |

Re-check depois do design: PASS. Os serviços novos são exigidos pelo princípio II (site e API com a mesma regra) e pelo III (transação e centavos).

## Project Structure

### Documentation (this feature)

```text
docs/specs/vaga-dinheiro-placar/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/rotas.md
└── tasks.md
```

### Source Code

```text
app/Support/Dinheiro.php                        novo
app/Services/VagasDaPartida.php                 novo
app/Services/PlacarDaPartida.php                novo
app/Services/FinanceiroDaTurma.php              novo
app/Services/ExclusaoDeConta.php                novo
app/Services/SorteioTimes.php                   alterado
app/Services/MembrosDaTurma.php                 alterado
app/Models/MovimentoFinanceiro.php              novo
app/Models/{Partida,Despesa,Pagamento,Patota,User}.php   alterados
app/Policies/{Partida,Despesa,Pagamento,Patota}Policy.php alterados
app/Http/Requests/SalvarDespesaRequest.php      novo
app/Http/Requests/DarBaixaRequest.php           novo
app/Http/Requests/ProfileUpdateRequest.php      alterado
app/Http/Controllers/Web/{Partida,Placar,Sorteio,Despesa,Patota,Mensagem}Controller.php alterados
app/Http/Controllers/Api/{Partida,Confirmacao,Placar,Sorteio,Despesa,Patota,Mensagem,Usuario}Controller.php alterados
app/Http/Controllers/ProfileController.php      alterado
database/migrations/                            2 novas
resources/views/{partidas/show,placar/*,despesas/show,despesas/create,patotas/index,profile/partials/*}  alterados
routes/{web,api}.php                            rotas novas
tests/Feature/VagaDinheiroPlacar/               novo
```

## Convencoes de Borda

| Camada | Case style | Validacao | Fonte da verdade |
|--------|------------|-----------|------------------|
| Colunas | snake_case | migrations | `database/migrations` |
| Formulários do site | snake_case | Form Requests | `contracts/rotas.md` |
| API (request e response) | snake_case | Form Requests, Resources | `app/Http/Resources`, `docs/09-api.md` |
| URL | minúsculas, em português | rotas | `routes/web.php`, `routes/api.php` |
| Dinheiro | decimal com 2 casas na borda e na coluna; centavos inteiros no cálculo | `decimal:0,2` | `app/Support/Dinheiro.php` |

Mapper: ORM Eloquent, sem conversão de nomes. A validação roda só no servidor.

## Complexity Tracking

Sem violações.
