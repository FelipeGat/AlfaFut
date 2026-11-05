# Implementation Plan: Base segura para o teste com pessoas reais

**Feature**: `seguranca-base` | **Date**: 2026-11-05 | **Spec**: [spec.md](spec.md)

## Summary

Levar a decisão de permissão para Policies do Laravel, usadas igualmente por site e API; separar o que cada um
vê de uma pessoa; limitar tentativas; ligar a confirmação de e-mail; parar de guardar página autenticada no aparelho;
e refazer a imagem de produção (MySQL, nginx + PHP-FPM, sem seed, com recusa a subir mal configurada).

## Technical Context

**Language/Version**: PHP 8.3 (`Dockerfile`; a imagem base traz 8.3.36 ou superior, que corrige o CVE de 21/10/2026)
**Primary Dependencies**: Laravel 12.58 (`composer.lock`), Sanctum 4, Blade, Tailwind 3, Alpine, Vite 7
**Storage**: MySQL 8.4 em produção; SQLite em memória nos testes (`phpunit.xml`); sem mudança de esquema
**Testing**: PHPUnit 11 (`tests/Feature`, `tests/Unit`); navegador via Playwright em Docker para o service worker
**Target Platform**: Contêiner Docker; servidor do teste a definir pelo Felipe (Etapa 9)
**Project Type**: Monólito web com API REST v1 e PWA
**Performance Goals**: Sem meta numérica nova; cada checagem de permissão faz uma consulta de vínculo
**Constraints**: WSL com cerca de 7,9 GB, sem PHP nativo; sessões de até 30 min; sem commit, push ou deploy sem autorização
**Scale/Scope**: Uma instância; 8 controllers com autorização copiada (`Web/` e `Api/`), 11 + 10 controllers no total

Sem `NEEDS CLARIFICATION`: provedor de e-mail e servidor foram adiados para a Etapa 9 na seção Clarifications da spec.

## Constitution Check

*GATE: Deve passar antes do Phase 0. Re-checar apos Phase 1.* A constitution está em rascunho v1.0.0, sem ratificação do
Felipe; o check foi feito contra esse texto.

| Principio | Status | Notas |
|-----------|--------|-------|
| I. Veracidade de Dados | PASS | Contrato existente extraído do código; o novo está marcado `[PROPOSTA]` |
| II. Dado da turma só para a turma | PASS | Ponto único em `app/Policies`; teste de negação por regra; é o centro desta frente |
| III. Dinheiro e vaga sem surpresa | N/A | Etapa 4. Aqui só impede cobrar outra turma (FR-003) |
| IV. LGPD e imagem | PASS | Campos protegidos; seed sem dado real. Fotos e direito de exclusão ficam nas Etapas 4 e 5 |
| V. Acessibilidade de verdade | PASS | Telas alteradas (confirmação de e-mail) em português e WCAG AA; limite anunciado em `aria-live` |
| VI. Nada de produção sem prova | PASS | Comando de checagem recusa boot inseguro; sem `--seed`; publicação segue na Etapa 9 |

Re-check depois do design: PASS, sem camada nova além de `app/Policies`, de um comando e de um script de entrada. Sem violação.

## Decisões técnicas

Detalhes e alternativas em [research.md](research.md).

1. **Permissão**: `PatotaPolicy`, `PartidaPolicy`, `DespesaPolicy`, `PagamentoPolicy` e `MensagemPolicy` com `before()` para o admin global, sobre dois métodos novos em `Patota` (`temMembroAtivo`, `temOrganizador`). O `Controller` base recebe `AuthorizesRequests`, e os `authorize*` privados saem de todos os controllers.
2. **Dados de pessoa**: `UsuarioResource` passa a ter só os campos públicos. Novos: `MeuUsuarioResource` (a própria pessoa) e `MembroResource` (lista de membros, com contato e necessidades se quem pede organiza a turma).
3. **Limites**: limitadores nomeados em `AppServiceProvider`, aplicados por `throttle:<nome>` nas rotas de `api.php` e `auth.php`, com resposta em português.
4. **Proxy**: `TrustProxies::at(config('app.proxies_confiaveis'))`, vazio por padrão (nenhum confiável).
5. **E-mail**: `User implements MustVerifyEmail`; `verified` nas rotas de API; rota nova de reenvio; `lang/pt_BR.json` para os e-mails.
6. **Seed**: `DatabaseSeeder` recusa fora de `local` e `testing`; senha gerada no ato; e-mails `@alfafut.test`. Comando `alfafut:criar-admin`. A rota `/_demo-login` sai.
7. **Boot**: comando `alfafut:checar-producao` no script de entrada, antes das migrações.
8. **Service worker**: `alfafut-v2`, só arquivos estáticos e a página `/offline` em cache; o resto vai direto à rede. Como nada de dado fica guardado, sair da conta não tem o que limpar; as páginas autenticadas vão com `no-store`. O script na página foi descartado: o menu sai por `form.submit()`, que não dispara o evento `submit`.
9. **Imagem**: `php:8.3-fpm-alpine` + nginx + supervisor, `pdo_mysql` + `opcache`, `.dockerignore`, `docker/` com a configuração. `docker-compose.homolog.yml` reproduz a produção na máquina local, em sub-rede fixa `10.253.105.0/24`.

## Project Structure

### Documentation (this feature)

```
docs/specs/seguranca-base/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── tasks.md
└── contracts/api.md
```

### Source Code (repository root)

```
app/Policies/                      # novo: PatotaPolicy, PartidaPolicy, DespesaPolicy, PagamentoPolicy, MensagemPolicy
app/Models/Patota.php              # temMembroAtivo, temOrganizador
app/Models/User.php                # MustVerifyEmail
app/Http/Controllers/Controller.php            # AuthorizesRequests
app/Http/Controllers/Api/*, Web/*              # trocam authorize* por $this->authorize
app/Http/Resources/UsuarioResource.php         # só público
app/Http/Resources/MeuUsuarioResource.php      # novo
app/Http/Resources/MembroResource.php          # novo
app/Providers/AppServiceProvider.php           # limitadores, proxies
app/Http/Middleware/ExigeEmailConfirmado.php   # novo
lang/pt_BR.json                                # novo
app/Console/Commands/CriarAdmin.php            # novo
app/Console/Commands/ChecarProducao.php        # novo
config/app.php                     # proxies_confiaveis
database/seeders/                  # seed local fictício e com guarda
routes/api.php, routes/auth.php, routes/web.php
public/sw.js
resources/views/auth/verify-email.blade.php
Dockerfile, .dockerignore, docker/, docker-compose.yml, docker-compose.homolog.yml
scripts/seed_3_perfis.php, scripts/sqlite_to_mysql.php, README.md
tests/Feature/Permissoes/          # novo
tests/Feature/Seguranca/           # novo
```

**Structure Decision**: mantém o monólito e a separação `Web/` e `Api/`; só a decisão de permissão é unificada. Regras de negócio comuns (rateio, vagas) ficam na Etapa 4.

## Convencoes de Borda

| Camada | Case style | Validacao | Fonte da verdade |
|--------|------------|-----------|------------------|
| Colunas do banco | snake_case, em português | migrations | `database/migrations/*.php` |
| Payload da API | snake_case, em português (`valor_total`, `posicao_preferida`) | `$request->validate` | `app/Http/Resources/*.php` e [contracts/api.md](contracts/api.md) |
| Rotas | kebab-case (`auth/esqueci-senha`) | router | `routes/api.php`, `routes/web.php` |
| Erros da API | `{ "message": "..." }` com 403 e 429; `Retry-After` no 429 | handler do Laravel | [contracts/api.md](contracts/api.md) |

**Mapper layer**: Eloquent + API Resources; o nome `name` do banco vira `nome` no `UsuarioResource`.

## Complexity Tracking

Sem violações a justificar.

## Riscos

- **App Flutter**: consome a API e ainda não foi auditado. Campos removidos de membros e o 403 de e-mail não confirmado podem afetá-lo; a decisão de incluir o app está pendente.
- **SMTP fora do ar no cadastro**: o envio é síncrono, então uma falha do provedor aparece como erro no cadastro. Aceito para o teste; revisar na Etapa 9.
- **Senha no histórico do git**: limpar o arquivo atual não a remove do histórico. Depende da decisão do Felipe.
