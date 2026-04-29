# AlfaFut

> Solucao colaborativa para gestao de patota de futebol com suporte de acessibilidade.

Atividade de Extensao da Uniasselvi - Curso de Gestao de Tecnologia / ADS.
Carga horaria: 230 horas. Modalidade: Prestacao de servicos > Outros servicos tecnicos especializados.

Desenvolvido seguindo:
- **Modelo 3C de Colaboracao** (Comunicacao, Coordenacao, Cooperacao) - Fuks et al. (2005)
- **Material Design 3** (Google) e **10 Heuristicas de Nielsen**
- **WCAG 2.1** (Web Content Accessibility Guidelines)

## Estrutura do projeto

| Pasta | Conteudo |
|-------|----------|
| `AlfaFut/` | Backend web em Laravel 12 + API REST |
| `AlfaFutApp/` | App mobile em Flutter (Android/iOS) |

## Stack

- **Backend:** PHP 8.2 + Laravel 12 + Sanctum + SQLite (dev)
- **Frontend web:** Blade + Tailwind 3 + Material Design 3 + Alpine.js
- **App mobile:** Flutter 3.41 + Riverpod + go_router + dio
- **Acessibilidade:** WCAG 2.1 AA (alto contraste AAA opcional)

## Como rodar

### Backend

```bash
cd C:\xampp\htdocs\AlfaFut
composer install
php artisan migrate:fresh --seed
php artisan serve --port=8000
```

Acesse `http://127.0.0.1:8000` ou via XAMPP em `http://localhost/AlfaFut/public`.

**Usuario admin do seed:**
- E-mail: `admin@alfafut.test`
- Senha: `senha1234`

### Rodar testes automatizados

```bash
php artisan test --filter='ApiAuthTest|ApiPatotaTest|ApiPartidaTest|ApiDespesaTest|SorteioTimesTest'
```

Cobertura atual: 18 feature tests + 2 unit tests (47 assertions).

Mais 14 jogadores ficticios sao criados (mesma senha).

### App Flutter

```bash
cd C:\xampp\htdocs\AlfaFutApp
flutter pub get
flutter run
```

Para apontar para outro host: `--dart-define=ALFAFUT_API_URL=http://192.168.x.x/AlfaFut/public/api/v1`.

## Documentacao do projeto

Toda a documentacao da atividade de extensao esta em `docs/`:

- [`docs/01-personas.md`](docs/01-personas.md) - Personas (Etapa i)
- [`docs/02-requisitos.md`](docs/02-requisitos.md) - Requisitos funcionais e nao funcionais (Etapas j, m)
- [`docs/03-modelo-3c.md`](docs/03-modelo-3c.md) - Mapeamento das funcionalidades pelo Modelo 3C (Etapa c)
- [`docs/04-acessibilidade-wcag.md`](docs/04-acessibilidade-wcag.md) - Conformidade WCAG 2.1 (Etapa g)
- [`docs/05-usabilidade-heuristicas.md`](docs/05-usabilidade-heuristicas.md) - Heuristicas de Nielsen (Etapa f)
- [`docs/06-prototipo-baixa-fidelidade.md`](docs/06-prototipo-baixa-fidelidade.md) - Apendice D (Etapa k)
- [`docs/07-prototipo-alta-fidelidade.md`](docs/07-prototipo-alta-fidelidade.md) - Apendice E (Etapa l)
- [`docs/08-arquitetura.md`](docs/08-arquitetura.md) - Diagramas e arquitetura (Etapa n)
- [`docs/09-api.md`](docs/09-api.md) - Documentacao da API REST
- [`docs/10-cronograma.md`](docs/10-cronograma.md) - Cronograma de execucao das 230h
- [`docs/11-eventos.md`](docs/11-eventos.md) - Evidencias fotograficas de campo (Etapa a)

## Status das etapas (PDF Uniasselvi)

| Etapa | Descricao | Status | Documento |
|-------|-----------|--------|-----------|
| a | Contato inicial | Parcial | docs/11 (fotos recebidas) |
| b | Pesquisa em materiais | Concluida | docs/03 a docs/05 |
| c | Modelo 3C | Concluida | docs/03-modelo-3c.md |
| d | Personas | Concluida | docs/01-personas.md |
| e | Prototipacao - estudo | Concluida | docs/06, docs/07 |
| f | Usabilidade / UX | Concluida | docs/05 |
| g | WCAG | Concluida | docs/04 |
| h | Levantamento info | Pendente | a fazer em campo |
| i | Personas detalhadas | Concluida | docs/01-personas.md |
| j | Requisitos | Concluida | docs/02-requisitos.md |
| k | Prototipo baixa fidelidade | Concluida | docs/06-prototipo-baixa-fidelidade.md |
| l | Prototipo alta fidelidade | Concluida | docs/07-prototipo-alta-fidelidade.md |
| m | Especificacao requisitos | Concluida | docs/02-requisitos.md |
| n | Especificacao / UML | Concluida | docs/08-arquitetura.md |
| o | Implementacao | **Concluida** | codigo (web + flutter completos, 3 pilares 3C) |
| p | Verificacao / validacao | Parcial | 18 feature tests + sorteio unit tests; falta validacao em campo |
| q | Relatorio / Paper | Pendente | depois das validacoes |

## Licenca

MIT.
