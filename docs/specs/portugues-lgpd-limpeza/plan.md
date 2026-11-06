# Implementation Plan: Português, LGPD e limpeza

**Feature**: `portugues-lgpd-limpeza` | **Date**: 2026-11-05 | **Spec**: [spec.md](spec.md)

## Summary

Fechar o que falta para receber gente real sem expor conta nem dado: recuperação de senha com resposta única, acesso pela API com validade e encerrado na troca de senha, política e termos públicos com aceite no cadastro, download dos próprios dados, API que responde sempre em JSON, texto em português e remoção do que sobrou do trabalho de faculdade.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12.58, Sanctum, Blade, Tailwind 3, Alpine 3, Vite 7
**Storage**: MySQL 8.4 em produção; SQLite nos testes. Uma migração: `users.termos_aceitos_em` e `users.termos_versao`
**Testing**: PHPUnit 11; navegador em Docker com MySQL atrás do nginx da imagem (`.demos/alfafut`)
**Target Platform**: mesma imagem de `seguranca-base`
**Project Type**: monólito web com API REST v1 e PWA
**Performance Goals**: sem meta nova
**Constraints**: sessões de até 30 min; commit, push e deploy só com autorização; nomes de campo da API não mudam
**Scale/Scope**: 3 serviços novos, 2 páginas públicas, 6 páginas de erro, 1 migração, texto em cerca de 60 telas

Sem `NEEDS CLARIFICATION`. O texto jurídico é rascunho com base no que o código faz e precisa da revisão do Felipe; os dados do responsável vêm de configuração.

## Constitution Check

*GATE: Deve passar antes do Phase 0. Re-checar apos Phase 1.* A constitution continua em rascunho v1.0.0, sem ratificação.

| Principio | Status | Notas |
|-----------|--------|-------|
| I. Veracidade de Dados | PASS | Política escrita a partir do código; responsável e contato vêm de configuração, e produção não sobe sem eles; rotas novas marcadas `[PROPOSTA]` |
| II. Dado da turma só para a turma | PASS | O arquivo baixado só traz dados da própria pessoa; rotas novas exigem login |
| III. Dinheiro e vaga sem surpresa | N/A | Regras não mudam nesta frente |
| IV. LGPD e imagem | PASS | Esta frente cumpre "ver, corrigir e excluir", com o download; as fotos reais seguem para as Etapas 6 e 7 |
| V. Acessibilidade de verdade | PASS | Esta frente cumpre o MUST de português; páginas novas com rótulo e foco |
| VI. Nada de produção sem prova | PASS | Suíte, build e navegador com MySQL antes de concluir; sem deploy |

Re-check depois do design: PASS. Os serviços novos servem à mesma regra em site e API (princípio II) e não criam camada nova.

## Project Structure

### Documentation (this feature)

```text
docs/specs/portugues-lgpd-limpeza/
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
app/Services/RecuperacaoDeSenha.php             novo
app/Services/EncerramentoDeAcessos.php          novo
app/Services/DadosDoTitular.php                 novo
app/Console/Commands/ChecarProducao.php         alterado
app/Console/Commands/PartidaDemo.php            removido
app/Http/Controllers/Auth/{PasswordResetLink,NewPassword,Password,RegisteredUser}Controller.php  alterados
app/Http/Controllers/Api/{Auth,RecuperacaoSenha,Usuario,Patota}Controller.php  alterados
app/Http/Controllers/ProfileController.php      alterado
app/Http/Resources/{Patota,MeuUsuario}Resource.php  alterados
app/Models/User.php                             alterado
bootstrap/app.php                               alterado
config/{alfafut,sanctum}.php                    novo e alterado
database/migrations/                            1 nova
docker/entrypoint.sh, docker-compose.homolog.yml, .env.example  alterados
resources/views/legal/{privacidade,termos}.blade.php   novos
resources/views/errors/                         novo
resources/views/partials/rodape.blade.php       novo
resources/views/**                              texto em português
resources/views/profile/partials/*              removidos
routes/{web,api}.php                            rotas novas
README.md, docs/09-api.md                       reescritos
tests/Feature/PortuguesLgpdLimpeza/             novo
```

## Convencoes de Borda

| Camada | Case style | Validacao | Fonte da verdade |
|--------|------------|-----------|------------------|
| Colunas | snake_case | migrations | `database/migrations` |
| Formulários do site | snake_case | validação do controller | `contracts/rotas.md` |
| API (request e response) | snake_case | validação do controller, Resources | `app/Http/Resources`, `docs/09-api.md` |
| URL | minúsculas, em português | rotas | `routes/web.php`, `routes/api.php` |
| Configuração | `ALFAFUT_` em maiúsculas | `alfafut:checar-producao` | `config/alfafut.php` |

Mapper: ORM Eloquent, sem conversão de nomes. A validação roda só no servidor.

## Complexity Tracking

Sem violações.
