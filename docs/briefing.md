# Project Briefing: AlfaFut

**Data**: 2026-11-05
**Status**: Draft (aguardando as decisões da seção "Itens a Definir")
**Versão**: 1.0

> Derivado de `README.md`, `docs/01` a `docs/11` e do código da `main` (`b9c39d9`, 06/06/2026).
> O pedido do Felipe de 05/11/2026 está marcado como `[fornecido]`; o que veio dos arquivos, como `[inferido]`.

---

## 1. Visão e Propósito

**O que é**: sistema para organizar a pelada: quem vai, quem fica na lista de espera, como ficam os times,
quanto cada um paga, os avisos da turma e o placar do jogo. Nasceu como atividade de extensão da Uniasselvi. `[inferido]`

Nas palavras do Felipe: "fiz ele para a faculdade, deu certo e querem testar ela agora. A ideia é que ele seja
um sistema escalável da Alfa." `[fornecido]`

**Problema que resolve** `[inferido, docs/01-personas.md]`:
- A pelada vive em grupo de WhatsApp e planilha.
- Gente confirma e não aparece, ou desiste em cima da hora.
- Mensagem importante some no meio da conversa.
- Ninguém lembra quem já pagou, e cobrar atrasado desgasta.
- Quem é surdo ou tem baixa visão fica de fora de grupo que funciona por áudio, e não sabe se o local é acessível.

**Proposta de valor** `[inferido, resources/views/welcome.blade.php]`: "Sua turma organizada do jeito que ela merece."
A frase definitiva sai da landing.

## 2. Usuários e Stakeholders

| Ator | Papel | Ações principais | Fonte |
|---|---|---|---|
| Jogador | Membro da turma | Entra pelo código de convite, confirma presença em 1 toque, entra na lista de espera, dá baixa no próprio pagamento, usa o mural | `[inferido]` |
| Dono da pelada | Organizador | Cria a turma, marca partida, sorteia times, lança e rateia despesa, controla o placar | `[inferido]` |
| Dono do campo | Oferta de campo | Cadastra o campo (piso, vestiário, acessibilidade, valor por hora, WhatsApp) no catálogo | `[inferido]` |
| Jogador com deficiência | Membro com necessidades declaradas | Alto contraste, fonte maior, menos movimento, leitor de tela, declara necessidades ao organizador | `[inferido]` |
| Admin global (equipe Alfa) | Plataforma | Consulta resumo, usuários, turmas e partidas pela API `/api/v1/admin/*` | `[inferido]` |

**Stakeholders de decisão**: Felipe. Quem mais participa de desenvolvimento e publicação: a definir.

## 3. Escopo

### MVP (o que precisa estar funcional para o teste real) `[inferido do código + fornecido]`

1. Cadastro e login, com os três perfis (jogador, dono da pelada, dono do campo).
2. Turma com código de convite de 8 caracteres.
3. Partida com confirmação de presença, lista de espera e promoção automática.
4. Sorteio de times por posição e nível.
5. Despesa com rateio entre confirmados e baixa de pagamento (registro manual; não há gateway).
6. Mural da turma.
7. Placar ao vivo: controle, modo TV e resultado.
8. Catálogo de campos.
9. Acessibilidade configurável por usuário.
10. PWA instalável no Android.
11. API REST v1 com Sanctum.
12. Landing nova, no padrão das landings do AlfaGym, AlfaControl e AlfaJornada. `[fornecido]`
13. Correção dos erros encontrados na análise. `[fornecido]`

### Pós-MVP (citado nos documentos, fora desta etapa) `[inferido]`

1. App Flutter (`FelipeGat/AlfaFutApp`, repositório separado, último push em 19/05/2026). A decisão de incluir está em "Itens a Definir".
2. Pagamento por PIX dentro do app (desejo da persona Carlos, `docs/01-personas.md`).
3. Cache offline das próximas partidas no app (RNF18).

### Fora de Escopo

- Publicar em produção sem autorização do Felipe. `[fornecido, regra da casa]`
- Entregáveis da faculdade (relatório, paper, validações presenciais de `docs/10-cronograma.md`). `[inferido]`

## 4. Prioridades e Trade-offs

**Ordem de prioridade** `[inferido do pedido]`: funcionar de verdade com dado de gente real > landing > funcionalidade nova.

**Decisões explícitas**:
- Trabalho em etapas de até 30 minutos, cada uma com ponto de parada claro. `[fornecido]`
- O projeto inteiro passa pelo cstk. `[fornecido]`
- Durante o teste, a chamada principal da landing é criar conta grátis, sem preço publicado:
  jogador e organizador entram sozinhos pelo cadastro. `[fornecido, 05/11/2026]`

## 5. Restrições

| Restrição | Valor | Notas |
|---|---|---|
| Prazo | Não informado | "Querem testar agora"; data a confirmar |
| Equipe | Felipe + agentes | Demais pessoas a definir |
| Budget | Não informado | |
| Sessão de trabalho | Até 30 minutos | `[fornecido]` |
| Ambiente local | WSL com cerca de 7,9 GB de RAM, sem PHP nativo | Tudo roda em Docker |
| Repositório | Público no GitHub (`FelipeGat/AlfaFut`) | Conferido na API do GitHub em 05/11/2026 |

## 6. Stack Técnica `[inferido]`

| Camada | Tecnologia | Observação |
|---|---|---|
| Backend | PHP 8.2+ (imagem de produção em 8.3), Laravel 12, Sanctum | `composer.json`, `Dockerfile` |
| Frontend | Blade, Tailwind 3, Alpine.js, Vite 7 | Monólito, sem SPA |
| Banco de dados | SQLite no desenvolvimento e na imagem Docker; `docs/02-requisitos.md` prevê MySQL/MariaDB em produção | `docker-compose.yml` sobe MySQL 8.4 só como apoio |
| Infraestrutura | `Dockerfile` com `php artisan serve`, pensado para Railway/Render | Sem ambiente de produção conhecido |
| App | PWA em `public/`; Flutter em repositório separado | |
| Integrações | Nenhuma externa; e-mail em `log` por padrão | |

## 7. Qualidade e Padrões `[inferido, docs/02 e docs/04]`

**Padrões adotados**:
- WCAG 2.1 AA, com alto contraste AAA opcional.
- 10 heurísticas de Nielsen e Material Design 3.
- PSR-12 no PHP.
- Testes automatizados com PHPUnit: 43 testes; 42 passam com o front compilado (medido em 05/11/2026, PHP 8.3).

**Compliance**: LGPD. O sistema guarda nome, e-mail, telefone, data de nascimento e necessidades de acessibilidade
(`users.necessidades_acessibilidade`), este último com potencial de dado de saúde.
Imagem de pessoas: `docs/11-eventos.md` exige termo de cessão para as fotos reais usadas na página inicial.

## 8. Visão de Futuro

**6 a 12 meses**: "sistema escalável da Alfa". `[fornecido]` Metas de números e modelo comercial: a definir.

**Riscos conhecidos**:
- Código feito para trabalho acadêmico recebendo dado de gente real: ver a análise de 05/11/2026.
- Repositório público com dados do projeto.

---

## Itens a Definir

| Item | Dimensão | Impacto |
|---|---|---|
| Quem vai testar e quando (grupo, quantidade, data) | Usuários / Prazo | Alto |
| Quem paga depois do teste (organizador, dono do campo). No teste é grátis: decidido em 05/11/2026 | Visão / Comercial | Médio |
| Domínio e servidor do teste (padrão da casa sugere `fut.alfasolucoes.cloud`) | Infraestrutura | Alto |
| App Flutter entra nesta etapa ou o teste vai só pelo PWA | Escopo | Médio |
| Nome na interface: "turma" (telas) ou "patota" (documentos) | Produto / SEO | Médio |
| Fotos reais de partidas: existem os termos de cessão? | LGPD | Médio |
| Repositório continua público? | Segurança | Alto |

---

**Próximo passo recomendado**: `/cstk:constitution`, e depois `/cstk:specify` da frente de correções.
