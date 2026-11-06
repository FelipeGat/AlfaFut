# Feature Specification: Português, LGPD e limpeza

**Feature**: `portugues-lgpd-limpeza`
**Created**: 2026-11-05
**Status**: Draft

Etapa 5 do plano do AlfaFut. As regras da turma já funcionam, mas a interface ainda tem texto sem acento, não existe aviso de privacidade nem termos de uso, a recuperação de senha revela quem tem conta, o acesso pela API não expira e sobraram restos do trabalho de faculdade.

> Decisões de infraestrutura: Refresh policy: o acesso pela API tem validade fixa e não renova sozinho; vencido, a pessoa entra de novo, sem janela de tolerância. Sem agendamento: acessos vencidos são recusados na hora, e a remoção dos registros vencidos acontece quando o sistema sobe. Sem criptografia nova, sem concorrência nova. Backup segue a regra da constitution.

## Clarifications

### Session 2026-11-05

Nenhuma ambiguidade bloqueante. Sem perguntas: pedido pela Central, com as premissas marcadas nos requisitos para o Felipe conferir (FR-002 e FR-011). Dados do responsável (FR-008) ficam em configuração, porque não há fonte para eles no repositório.

## User Scenarios & Testing

### User Story 1 - Conta protegida (Priority: P1)

Quem usa o AlfaFut espera que ninguém descubra pelo sistema se tem conta, e que trocar a senha expulse quem estava usando a conta indevidamente.

**Why this priority**: é falha de segurança em produto que recebe gente real, e a confirmação de e-mail e o limite de tentativas da Etapa 2 ficam incompletos sem isso.

**Independent Test**: pedir a recuperação com um e-mail que existe e com um que não existe e comparar as respostas; trocar a senha e tentar usar um acesso antigo.

**Acceptance Scenarios**:

1. **Given** um e-mail sem conta, **When** alguém pede a recuperação de senha no site ou na API, **Then** recebe exatamente a mesma resposta dada a um e-mail com conta, e nenhum e-mail é enviado.
2. **Given** um e-mail com conta que já pediu a recuperação há poucos segundos, **When** pede de novo, **Then** a resposta é a mesma, sem indicar que houve bloqueio por esse e-mail.
3. **Given** um acesso vencido pela API, emitido há mais tempo que a validade, **When** é usado, **Then** a API recusa o acesso com 401.
4. **Given** uma pessoa com acesso ativo pela API e sessão em outro navegador, **When** troca a senha no perfil, **Then** os acessos pela API e as sessões nos outros navegadores deixam de valer, e o navegador atual continua conectado.
5. **Given** uma pessoa com acesso ativo pela API, **When** redefine a senha pelo link recebido por e-mail, **Then** todos os acessos pela API e todas as sessões anteriores deixam de valer.

---

### User Story 2 - Privacidade e termos (Priority: P1)

Antes de criar a conta, a pessoa consegue ler o que o AlfaFut guarda, para quê e com quem compartilha, e aceita os termos. Depois, consegue baixar uma cópia dos próprios dados.

**Why this priority**: o sistema guarda dados pessoais e necessidades de acessibilidade, que podem revelar dado de saúde; a constitution IV exige isso antes de receber gente real.

**Independent Test**: abrir a política e os termos sem login, tentar cadastrar sem aceitar, cadastrar aceitando e baixar os próprios dados.

**Acceptance Scenarios**:

1. **Given** um visitante sem login, **When** abre a política de privacidade ou os termos de uso, **Then** a página abre e está ligada no rodapé e no cadastro.
2. **Given** um visitante no cadastro do site ou da API, **When** tenta criar a conta sem aceitar os termos, **Then** recebe recusa com o motivo, e a conta não é criada.
3. **Given** um visitante que aceita, **When** cria a conta, **Then** o sistema guarda a data e a versão aceitas.
4. **Given** uma pessoa com conta, **When** pede seus dados no perfil ou na API, **Then** recebe um arquivo com cadastro, acessibilidade, turmas, confirmações, parcelas e mensagens dela.
5. **Given** o arquivo baixado, **When** é conferido, **Then** não traz senha, token, nem e-mail, telefone ou acessibilidade de outras pessoas.
6. **Given** o sistema em produção sem o nome do responsável pelos dados ou sem o contato de privacidade, **When** ele tenta subir, **Then** recusa subir e diz o que falta.

---

### User Story 3 - Tudo em português (Priority: P1)

Toda tela, mensagem de erro e e-mail aparecem em português do Brasil com acentuação correta, inclusive o que a API devolve.

**Why this priority**: a constitution V exige, e erro de acento é o primeiro sinal de produto inacabado para quem vai testar.

**Independent Test**: varrer as telas abertas e as mensagens das respostas e procurar palavras conhecidas sem acento.

**Acceptance Scenarios**:

1. **Given** qualquer tela do sistema, **When** é aberta, **Then** não mostra palavra sem o acento devido.
2. **Given** uma recusa, erro de validação ou confirmação na API ou no site, **When** é lida, **Then** a mensagem está em português correto.
3. **Given** a tela sem conexão, os e-mails do sistema e o nome do aplicativo instalado, **When** aparecem, **Then** seguem a mesma regra.

---

### User Story 4 - API previsível para o aplicativo (Priority: P2)

Quem integra com a API recebe JSON em qualquer erro e os campos prometidos.

**Why this priority**: o app e futuras integrações não conseguem tratar redirecionamento para tela de login nem campo que nunca vem.

**Independent Test**: chamar a API sem token e sem o cabeçalho de JSON; conferir os campos da turma e da criação de partida.

**Acceptance Scenarios**:

1. **Given** um pedido à API sem token e sem pedir JSON, **When** é recebido, **Then** a resposta é 401 em JSON, sem redirecionar.
2. **Given** um pedido que cria turma, partida ou despesa, **When** a resposta chega, **Then** o formato bate com o documentado para essa rota, porque o aplicativo depende dele.
3. **Given** uma turma listada ou aberta na API, **When** a resposta chega, **Then** traz o total de membros e quem criou, em vez de omitir esses campos.
4. **Given** uma pessoa autenticada, **When** consulta o perfil próprio na API, **Then** a resposta deve trazer o perfil de uso em `tipo_usuario`.
5. **Given** um cadastro pela API com perfil de uso público, **When** é enviado, **Then** a conta nasce com esse perfil; com qualquer outro valor, é recusado.

---

### User Story 5 - Sem restos do trabalho de faculdade (Priority: P3)

O produto se apresenta como produto da Alfa: sem citação à faculdade, sem código sem uso, com o botão de instalar funcionando e com a documentação batendo com o código.

**Why this priority**: não impede o uso, mas o rodapé é visto por todo mundo e a documentação errada atrasa quem for manter.

**Independent Test**: procurar a citação nas telas; instalar pelo banner em navegador compatível; conferir as rotas documentadas contra as rotas reais.

**Acceptance Scenarios**:

1. **Given** qualquer tela, **When** o rodapé aparece, **Then** cita o AlfaFut e os links de privacidade e termos, sem citar a faculdade.
2. **Given** o perfil de quem não confirmou o e-mail, **When** clica em reenviar a confirmação, **Then** o link é reenviado, sem salvar o perfil junto.
3. **Given** um navegador que oferece instalação, **When** a página abre, **Then** o banner de instalar aparece e o botão abre a instalação.
4. **Given** um pedido que tenta definir o próprio papel com `role` ou `tipo_usuario`, **When** é processado no perfil ou na API fora dos fluxos que os definem, **Then** o sistema ignora o valor enviado.
5. **Given** a lista de comandos e de telas do sistema, **When** é conferida, **Then** nenhum comando ou tela sem uso continua lá.
6. **Given** a documentação da API, **When** é comparada com as rotas reais, **Then** todas as rotas constam e nenhuma rota inexistente é citada.

---

### User Story 6 - Capturas atualizadas (Priority: P3)

As capturas mostradas no guia e no cadastro mostram as telas em português correto, só com dados fictícios.

**Why this priority**: elas foram feitas antes da correção dos textos e depende das outras histórias.

**Independent Test**: refazer as capturas e conferir uma a uma.

**Acceptance Scenarios**:

1. **Given** as telas já corrigidas, **When** as capturas são refeitas com o seed fictício, **Then** nenhuma mostra palavra sem acento nem dado de pessoa real.

---

### Edge Cases

- E-mail com maiúsculas na recuperação: recebe a mesma resposta e, se a conta existir, o link.
- Conta excluída: pedir a recuperação com o e-mail antigo não envia nada e responde igual.
- Recuperação com limite excedido por rede: responde 429 igual para qualquer e-mail, sem revelar conta.
- Cadastro por e-mail já usado continua avisando que o e-mail existe, porque cadastro precisa dizer por que não criou. Fica como limitação conhecida, protegida pelo limite de cadastros por rede.
- Quem baixa os dados tendo saído de uma turma: o arquivo traz a participação com a situação atual.
- Mensagem de conta anonimizada não aparece em arquivo nenhum: a conta excluída não acessa mais o sistema.
- Texto que a pessoa digitou sem acento continua como digitado: a correção vale só para o texto do sistema.
- Termos de uso mudam de versão: quem já aceitou continua com a versão registrada; pedir novo aceite fica fora desta frente.

## Requirements

### Functional Requirements

**Conta**

- **FR-001**: O sistema MUST responder igual ao pedido de recuperação de senha com e-mail com conta, sem conta ou em bloqueio por e-mail, no site e na API.
- **FR-002**: O acesso pela API MUST vencer em 60 dias depois de emitido. Premissa a confirmar.
- **FR-003**: A API MUST recusar com 401 o acesso vencido.
- **FR-004**: Trocar a senha no perfil MUST invalidar todos os acessos pela API e as sessões nos outros navegadores, mantendo o navegador atual conectado.
- **FR-005**: Redefinir a senha pelo link MUST invalidar todos os acessos pela API e todas as sessões anteriores da conta.

**Privacidade**

- **FR-006**: O sistema MUST ter páginas públicas de política de privacidade e de termos de uso, ligadas no rodapé e no cadastro.
- **FR-007**: A política MUST listar os dados que o sistema realmente guarda, a finalidade, quem pode ver, o que acontece ao excluir a conta e como exercer os direitos do titular, com cada item conferido contra o código.
- **FR-008**: O nome do responsável pelos dados, o documento e o contato de privacidade MUST vir de configuração, sem valor inventado em código ou documento.
- **FR-009**: O sistema MUST recusar subir em produção sem o nome do responsável ou sem o contato de privacidade.
- **FR-010**: O cadastro no site e na API MUST exigir o aceite dos termos e da política, e MUST guardar a data e a versão aceitas.
- **FR-011**: Os termos MUST dizer que o AlfaFut só registra pagamentos, sem processar dinheiro, e MUST exigir 18 anos ou autorização do responsável legal. Premissa a confirmar.
- **FR-012**: A pessoa MUST conseguir baixar, no perfil e na API, um arquivo com cadastro, acessibilidade, turmas, confirmações, parcelas e mensagens dela.
- **FR-013**: O arquivo baixado MUST NOT trazer senha, token, nem dados de contato ou de acessibilidade de outras pessoas.

**Português**

- **FR-014**: Todo texto de tela, rótulo, título, mensagem de sistema, mensagem de erro e e-mail MUST estar em português do Brasil com acentuação correta.
- **FR-015**: As respostas da API MUST trazer mensagens em português com acento.
- **FR-016**: A tela sem conexão e o nome e a descrição do aplicativo instalado MUST seguir a mesma regra.
- **FR-017**: Nomes de campos e valores da API MUST NOT mudar por causa da correção de texto, para não quebrar o aplicativo.

**API**

- **FR-018**: Pedidos à API sem autenticação MUST receber 401 em JSON, mesmo sem pedir JSON no cabeçalho, sem redirecionar.
- **FR-019**: Erros da API (validação, permissão, não encontrado, limite) MUST sair em JSON, mesmo sem pedir JSON no cabeçalho.
- **FR-020**: As respostas de criação na API MUST manter o formato atual, sem o envelope `data`, e a documentação MUST dizer isso rota a rota, até o aplicativo ser auditado.
- **FR-021**: A turma na API MUST trazer o total de membros e quem criou na listagem e ao abrir.
- **FR-022**: O perfil próprio na API MUST trazer o perfil de uso em `tipo_usuario`.
- **FR-023**: `POST /auth/registrar` MUST aceitar o perfil de uso entre os valores públicos, recusando qualquer valor de administrador.

**Limpeza**

- **FR-024**: Nenhuma tela nem o README MUST citar a faculdade como origem do produto. Os documentos históricos do trabalho ficam como estão.
- **FR-025**: O reenvio de confirmação no perfil MUST funcionar sem salvar os dados do formulário do perfil.
- **FR-026**: O banner de instalar MUST aparecer quando o navegador oferecer a instalação e abrir a instalação ao tocar.
- **FR-027**: O sistema MUST ignorar o valor enviado em `role` e `tipo_usuario` fora dos fluxos que os definem de forma explícita.
- **FR-028**: Comando e telas sem uso MUST ser removidos da lista do sistema.
- **FR-029**: `README.md` e `docs/09-api.md` MUST bater com as rotas, os limites e a forma de subir o sistema de hoje.

**Capturas**

- **FR-030**: As capturas usadas pelas páginas MUST ser refeitas depois da correção dos textos, com dados fictícios.

### Key Entities

- **Aceite dos termos**: data e versão do texto aceito pela pessoa, guardadas no cadastro dela.
- **Responsável pelos dados**: nome, documento e contato de privacidade, vindos da configuração do ambiente.
- **Acesso pela API**: credencial emitida no login, com data de vencimento.

## Success Criteria

### Measurable Outcomes

- **SC-001**: 0 diferença entre a resposta da recuperação para e-mail com conta e sem conta, no site e na API.
- **SC-002**: 100% dos acessos pela API anteriores à troca de senha são recusados depois dela.
- **SC-003**: 0 palavra da lista de conferência sem acento nas telas abertas e nas mensagens do sistema, medido por varredura.
- **SC-004**: Um visitante encontra a política e os termos em no máximo 1 clique a partir de qualquer tela pública.
- **SC-005**: 100% das rotas de `routes/api.php` constam em `docs/09-api.md`, e nenhuma rota citada no documento deixa de existir.
- **SC-006**: 0 citação à faculdade nas telas e no README.
- **SC-007**: 100% das recusas da API saem em JSON, com ou sem o cabeçalho de JSON, nos 5 tipos testados: sem login, validação, permissão, não encontrado e limite.

## Delta Requirements

**Skip**: o corpus `docs/specs/current/` ainda não existe neste projeto; a frente será comparada com as specs anteriores no converge da Etapa 8 — Claude, 2026-11-05
