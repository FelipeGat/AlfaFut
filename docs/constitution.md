<!--
Sync Impact Report
- Version: (nova) → 1.0.0
- Princípios criados: I Veracidade de Dados — Zero Fabricação; II Dado da turma só para a turma;
  III Dinheiro e vaga sem surpresa; IV LGPD e imagem de pessoas; V Acessibilidade de verdade;
  VI Nada de produção sem prova e sem autorização
- Seções adicionadas: Core Principles, Padrões de Qualidade, Entrega e Operação, Governance
- Seções removidas: nenhuma
- Artefatos que precisam atualização:
  - CLAUDE.md do projeto: não existe; nada a alinhar. [ok]
  - docs/specs/*: nenhuma spec cstk existente. [ok]
  - README.md: descreve estrutura de pastas, senha de seed e status acadêmico que não valem para o produto. [pendente]
  - docs/08-arquitetura.md, docs/09-api.md: conferir com o código na frente de correções. [pendente]
- TODOs pendentes: RATIFICATION_DATE, aguardando o aceite do Felipe.
-->

# AlfaFut Constitution

## Core Principles

### I. Veracidade de Dados — Zero Fabricação (NON-NEGOTIABLE)

O AlfaFut vai ser mostrado a gente real, em landing e em teste. Promessa inventada vira cobrança, e dado
inventado esconde defeito. Isso vale para código, documentação, landing, vídeo e proposta.

**MUST:**

- Nenhum artefato pode conter dado factual inventado. Assinaturas de request/response,
  URLs/endpoints/querystrings e valores concretos (financeiros, quantidades, status, IDs, datas,
  resultados de API) só podem ser escritos se vierem de fonte rastreável (código, OpenAPI/Swagger,
  doc oficial, resposta real observada). Esgotadas as fontes, a ação correta é **bloqueio humano**,
  nunca suposição plausível. Plausibilidade não é veracidade.
- Toda afirmação sobre o produto em landing, vídeo ou proposta tem fonte no código atual
  (`arquivo:linha`), registrada num arquivo de fatos antes da publicação.
- Recurso que só existe pela API, sem tela, é descrito como tal ou fica de fora.

**MUST NOT:**

- Publicar depoimento, número de usuários, "nº 1" ou "líder" sem fonte verificável.
- Prometer pagamento por PIX dentro do sistema, notificação por WhatsApp ou push, ou app em loja
  enquanto não existirem no código.

### II. Dado da turma só para a turma

O produto vai atender muitas turmas no mesmo banco. O isolamento entre elas é decidido no servidor.

**MUST:**

- Toda rota, web e API, que lê ou altera dado de turma, partida, despesa, pagamento, mensagem ou campo
  confere no servidor se o usuário tem permissão sobre aquele registro.
- Web e API aplicam a mesma regra de permissão, num ponto único do código.
- Cada regra de permissão tem teste automatizado cobrindo o acesso negado.

**MUST NOT:**

- Aceitar `role` ou qualquer campo de privilégio vindo do formulário ou da requisição.
- Confiar em esconder botão como forma de controle de acesso.

### III. Dinheiro e vaga sem surpresa

Rateio e lista de espera são o que faz o dono da pelada confiar no sistema.

**MUST:**

- A soma das parcelas de um rateio é igual ao valor da despesa, ao centavo.
- Confirmação de presença e promoção da lista de espera rodam em transação, sem ultrapassar as vagas
  mesmo com confirmações simultâneas.
- Valor em dinheiro nunca é calculado em ponto flutuante.
- Toda mudança em despesa ou pagamento registra quem fez e quando (RNF21).

### IV. LGPD e imagem de pessoas

O sistema guarda dados pessoais, e necessidades de acessibilidade podem revelar dado de saúde.

**MUST:**

- Necessidade de acessibilidade é declarada por escolha do próprio usuário e só aparece para quem organiza a turma dele.
- O usuário consegue ver, corrigir e excluir os próprios dados.
- Foto de pessoa real em material público só entra com termo de cessão de imagem arquivado.

**MUST NOT:**

- Versionar senha, token ou dado pessoal real em seed, script, teste ou documentação.
- Usar dado de pessoa real em demonstração, captura de tela ou ambiente de teste compartilhado.

### V. Acessibilidade de verdade

Acessibilidade é a origem do projeto e um diferencial, não um acabamento.

**MUST:**

- Telas novas ou alteradas atendem WCAG 2.1 AA: teclado, foco visível, contraste de 4,5:1, rótulo em campo,
  erro anunciado por `aria-live`.
- Todo texto de interface é em português do Brasil com acentuação correta, inclusive mensagens de validação, datas e e-mails.
- A landing só afirma acessibilidade que foi conferida na tela correspondente.

### VI. Nada de produção sem prova e sem autorização

**MUST:**

- Antes de publicar: suíte PHPUnit verde, build de produção compilado e fluxo principal percorrido em navegador,
  atrás do mesmo servidor web e do mesmo banco da produção.
- Commit, branch, push, PR e deploy só com autorização explícita do Felipe, pedido a pedido.

**MUST NOT:**

- Rodar `--seed` com usuário de demonstração em produção.
- Deixar rota de demonstração, `APP_DEBUG=true` ou senha padrão em ambiente acessível pela internet.

## Padrões de Qualidade

- Laravel 12 e PHP 8.3, com a mesma versão no desenvolvimento e na imagem de produção.
- Frente nova segue `specify → clarify → plan → create-tasks → execute-task`; bug pontual segue `/cstk:bugfix`.
- Código novo com teste automatizado. Correção de bug começa pelo teste que falha.
- PSR-12, verificado com Laravel Pint.

## Entrega e Operação

- Toda landing nova segue o padrão de performance e SEO das landings da casa e não publica preço sem decisão do Felipe.
- Ambiente local em Docker, com sub-rede fixa quando precisar de rede própria.
- Backup do banco antes de qualquer migração em ambiente com dado real.

## Governance

- Esta constituição prevalece sobre `CLAUDE.md`, README e demais documentos do projeto.
- Toda spec e plano passam pelo Constitution Check; violação exige justificativa escrita no plano e aceite do Felipe.
- Alteração segue versionamento semântico: MAJOR para remover ou redefinir princípio; MINOR para novo princípio ou
  expansão material; PATCH para clareza de texto.

**Version**: 1.0.0 | **Ratified**: TODO(RATIFICATION_DATE): aguardando o aceite do Felipe | **Last Amended**: 2026-11-05
