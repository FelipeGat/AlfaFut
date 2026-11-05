# Implementation Plan: Fluxo da turma pelo site

**Feature**: `fluxo-web-turma` | **Date**: 2026-11-05 | **Spec**: [spec.md](spec.md)

## Summary

Criar as telas e rotas que faltam no site (entrar e sair da turma, partida, local, membros), reaproveitando as Policies da frente `seguranca-base`. As regras de vínculo ficam em um serviço e as de partida em uma validação única, usados por site e API. Campo ganha Policy própria. O guia passa a descrever só o que existe e as capturas são refeitas com o seed fictício.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12.58, Blade, Tailwind 3, Alpine, Vite 7
**Storage**: MySQL 8.4 em produção; SQLite nos testes. Sem migração: `partidas.campo_id` já existe e `patota_membros.status` é texto livre
**Testing**: PHPUnit 11; navegador com Playwright em Docker (`.demos/alfafut`)
**Target Platform**: mesma imagem de `seguranca-base`
**Project Type**: monólito web com API REST v1 e PWA
**Performance Goals**: sem meta nova
**Constraints**: sessões de até 30 min; sem commit, push ou deploy sem autorização
**Scale/Scope**: 3 controllers web novos ou ampliados, 1 serviço, 1 validação de partida, 1 Policy, cerca de 8 telas novas

Sem `NEEDS CLARIFICATION`. As premissas que dependem do Felipe estão na spec e não bloqueiam a implementação.

## Constitution Check

*GATE: Deve passar antes do Phase 0. Re-checar apos Phase 1.* A constitution continua em rascunho v1.0.0, sem ratificação.

| Principio | Status | Notas |
|-----------|--------|-------|
| I. Veracidade de Dados | PASS | Contrato existente extraído do código; rotas novas marcadas `[PROPOSTA]`; o guia passa a citar só o que existe |
| II. Dado da turma só para a turma | PASS | Tudo decidido no servidor por Policy ou serviço único; teste de negação para cada ação nova; local de outra turma recusado também na API |
| III. Dinheiro e vaga sem surpresa | PASS | Sem mudança em rateio; vagas não caem abaixo dos confirmados. Promoção da lista segue na Etapa 4 |
| IV. LGPD e imagem | PASS | Contato só para quem organiza; capturas com seed fictício. Fotos reais seguem na tarefa 7.4 |
| V. Acessibilidade de verdade | PASS | Telas novas e alteradas com rótulo, foco, `aria-live` e acento; mensagens de validação em português |
| VI. Nada de produção sem prova | PASS | Suíte, build e navegador antes de concluir; sem deploy |

Re-check depois do design: PASS. A única camada nova é um serviço de vínculo, exigido pelo princípio II porque site e API precisam da mesma regra.

## Project Structure

### Documentation (this feature)

```text
docs/specs/fluxo-web-turma/
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
app/Exceptions/RegraDaTurma.php                 novo
app/Services/MembrosDaTurma.php                 novo
app/Http/Requests/SalvarPartidaRequest.php      novo
app/Http/Controllers/Web/LocalController.php    novo
app/Http/Controllers/Web/MembroController.php   novo
app/Http/Controllers/Web/PartidaController.php  criar, alterar, cancelar
app/Http/Controllers/Web/PatotaController.php   entrar, sair, responsável só entre membros
app/Http/Controllers/Web/CampoController.php    Policy, caixas
app/Http/Controllers/Api/PatotaController.php   usa o serviço
app/Http/Controllers/Api/PartidaController.php  usa a validação
app/Policies/CampoPolicy.php                    novo
app/Policies/PatotaPolicy.php                   gerenciarMembros
app/Models/Partida.php                          campo, lugar, situação
config/app.php                                  fuso e idioma
lang/pt_BR/validation.php                       novo
resources/views/partidas, locais, patotas, campos, como-usar
public/images/screenshots/                      refeitas
tests/Feature/FluxoTurma/                       novo
```

## Convencoes de Borda

| Camada | Case style | Validacao | Fonte da verdade |
|--------|------------|-----------|------------------|
| Colunas | snake_case | migrations | `database/migrations` |
| Formulários do site | snake_case, mais `lugar` no formato `local:ID` ou `campo:ID` | `SalvarPartidaRequest` | `contracts/rotas.md` |
| API (request e response) | snake_case | `SalvarPartidaRequest`, Resources | `app/Http/Resources`, `docs/09-api.md` |
| URL | minúsculas, em português | rotas | `routes/web.php`, `routes/api.php` |

Mapper: ORM Eloquent, sem conversão de nomes. A validação roda só no servidor.

## Complexity Tracking

Sem violações.
