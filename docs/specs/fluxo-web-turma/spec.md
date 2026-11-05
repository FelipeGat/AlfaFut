# Feature Specification: Fluxo da turma pelo site

**Feature**: `fluxo-web-turma`
**Created**: 2026-11-05
**Status**: Draft

Etapa 3 do plano do AlfaFut. Hoje, pelo navegador, o fluxo para logo depois de criar a turma: não dá para marcar partida, entrar com o código nem sair. Sem isso, confirmação, sorteio, placar e rateio ficam fora do alcance de quem usa o site.

> Decisões de infraestrutura: N/A (sem agendamento, sem criptografia nova, sem token externo, sem estado entre réplicas).

## User Scenarios & Testing

### User Story 1 - Entrar e sair da turma (Priority: P1)

O jogador recebe o código de convite, digita no painel ou em "Turmas" e passa a fazer parte da turma. Quando não quiser mais participar, ele sai pela tela da turma.

**Why this priority**: sem isso, a turma criada fica só com o criador e nada mais acontece.

**Independent Test**: com duas contas confirmadas, a segunda entra com o código da turma da primeira, vê a turma, sai e deixa de vê-la.

**Acceptance Scenarios**:

1. **Given** um jogador fora da turma, **When** digita o código de convite em minúsculas ou maiúsculas, **Then** entra como membro e vai para a tela da turma.
2. **Given** um código inválido ou de turma arquivada, **When** o jogador envia, **Then** vê a mesma mensagem de código não encontrado e nada revela se a turma existe.
3. **Given** um jogador que já é membro, **When** digita o código de novo, **Then** vai para a turma com um aviso e nada é duplicado.
4. **Given** um jogador que saiu, **When** usa o mesmo código, **Then** volta como membro comum.
5. **Given** um jogador removido da turma, **When** usa o código, **Then** a entrada é recusada com orientação para falar com quem organiza.
6. **Given** um membro, **When** confirma que quer sair, **Then** deixa de ver a turma nas listas e recebe recusa ao abrir o endereço dela.
7. **Given** o criador da turma, **When** abre a tela da turma, **Then** não encontra a opção de sair, e o servidor recusa se a ação for enviada.
8. **Given** o responsável que não é o criador, **When** sai da turma, **Then** a responsabilidade volta para o criador.
9. **Given** alguém testando códigos seguidos, **When** passa do limite de tentativas, **Then** recebe a recusa por excesso, igual à da API.
10. **Given** a API e o site, **When** recebem entrar ou sair nas mesmas condições, **Then** aplicam as mesmas regras e chegam ao mesmo resultado.

---

### User Story 2 - Marcar, alterar e cancelar partida (Priority: P1)

Quem organiza a turma marca a partida por um formulário, altera enquanto ela não começou e cancela quando precisar. Os jogadores veem a partida e confirmam presença.

**Why this priority**: é a partida que destrava confirmação, sorteio, placar e rateio.

**Independent Test**: um organizador marca uma partida e a vê em "Próximas"; um membro comum a vê, mas não encontra as opções de alterar nem cancelar.

**Acceptance Scenarios**:

1. **Given** um organizador na tela da turma, **When** abre "Nova partida", **Then** vê as vagas já sugeridas pela configuração da turma.
2. **Given** um formulário válido, **When** o organizador salva, **Then** a partida aparece em "Próximas" e na tela da turma, no horário digitado.
3. **Given** data e hora no passado, **When** o organizador salva, **Then** vê o erro em português junto ao campo, anunciado para leitor de tela, e o que digitou continua no formulário.
4. **Given** um prazo de confirmação depois do início, **When** salva, **Then** recebe erro no prazo.
5. **Given** valores acima dos limites, ou valor vazio, **When** salva, **Then** recebe erro de validação ou assume o padrão, sem erro de servidor.
6. **Given** uma partida agendada, **When** o organizador altera os dados, **Then** eles mudam e a confirmação de quem já confirmou continua valendo.
7. **Given** uma partida em andamento, finalizada ou cancelada, **When** alguém tenta alterá-la, **Then** a opção não aparece e o servidor recusa.
8. **Given** 8 confirmados, **When** o organizador baixa as vagas para 6, **Then** recebe erro explicando que há 8 confirmados.
9. **Given** uma partida agendada, **When** o organizador confirma o cancelamento, **Then** ela sai de "Próximas", aparece em "Passadas" marcada como cancelada e não aceita mais as ações de confirmar na tela.
10. **Given** um membro comum ou alguém de fora, **When** envia criar, alterar ou cancelar, **Then** o servidor recusa.
11. **Given** o horário de Brasília, **When** a partida é marcada para daqui a 1 hora, **Then** ela é aceita e continua em "Próximas" até o início, com datas escritas em português.
12. **Given** a API, **When** recebe partida com as mesmas condições, **Then** aplica as mesmas regras de datas, vagas, valores, local e campo.

---

### User Story 3 - Local da turma e campo do catálogo (Priority: P2)

O organizador mantém os locais onde a turma costuma jogar e, ao marcar a partida, escolhe um deles ou um campo do catálogo.

**Why this priority**: sem local, o jogador não sabe aonde ir nem se o lugar é acessível.

**Independent Test**: o organizador cadastra um local, marca partida nele e confere nome, endereço e acessibilidade na tela da partida; repete com um campo do catálogo.

**Acceptance Scenarios**:

1. **Given** um organizador, **When** cadastra, altera ou remove um local da turma, **Then** a lista de locais reflete a mudança.
2. **Given** um local marcado como coberto e acessível, **When** o organizador desmarca as caixas e salva, **Then** as características ficam desmarcadas.
3. **Given** um local de outra turma, **When** alguém tenta alterá-lo pelo endereço desta turma, ou usá-lo numa partida, **Then** o servidor recusa.
4. **Given** um local usado em partida que ainda vai acontecer, **When** o organizador tenta removê-lo, **Then** recebe aviso para trocar o local dessas partidas antes.
5. **Given** a partida, **When** o organizador escolhe um campo ativo do catálogo, **Then** a tela mostra nome, endereço e acessibilidade daquele campo.
6. **Given** um campo inativo, **When** alguém tenta usá-lo em nova escolha, **Then** o servidor recusa; uma partida que já o usava pode ser salva sem trocá-lo.
7. **Given** um envio com local e campo ao mesmo tempo, **When** chega ao servidor, **Then** é recusado, porque a partida acontece em um único lugar.
8. **Given** um membro comum, **When** tenta abrir ou enviar as telas de local, **Then** o servidor recusa.
9. **Given** um novo local, **When** quem organiza está no formulário de partida, **Then** encontra caminho para cadastrar o local.

---

### User Story 4 - Gestão de membros (Priority: P2)

Quem organiza vê os membros e o contato deles. Criador e responsável definem quem organiza, removem quem saiu do combinado e passam a responsabilidade a outro membro ativo.

**Why this priority**: a turma real muda de gente, e hoje nada disso existe no site.

**Independent Test**: o criador promove um membro a organizador, confere que ele passa a marcar partida, rebaixa, remove e vê a entrada pelo código ser recusada até liberar a volta.

**Acceptance Scenarios**:

1. **Given** um organizador, **When** abre "Membros", **Then** vê papel, e-mail e telefone de cada membro.
2. **Given** um membro comum, **When** abre o endereço da gestão de membros, **Then** o servidor recusa, e ele continua vendo só nome, apelido, posição e nível na tela da turma.
3. **Given** o criador ou o responsável, **When** torna um membro organizador, **Then** ele passa a organizar a turma; ao voltar a membro, perde esse acesso.
4. **Given** um organizador que não é criador nem responsável, **When** envia promover, rebaixar, remover, transferir ou liberar volta, **Then** o servidor recusa.
5. **Given** o criador da turma, **When** alguém tenta rebaixá-lo ou removê-lo, **Then** o servidor recusa.
6. **Given** um membro removido, **When** a remoção é concluída, **Then** ele perde o acesso à turma e aparece na lista de removidos.
7. **Given** um removido, **When** quem gerencia libera a volta, **Then** ele consegue entrar de novo com o código.
8. **Given** o responsável, **When** é removido pelo criador, **Then** a responsabilidade volta para o criador.
9. **Given** uma pessoa de fora ou um ex-membro, **When** alguém tenta torná-la responsável, pelos membros ou por editar turma, **Then** o servidor recusa.
10. **Given** um membro ativo, **When** o criador ou o responsável o torna responsável, **Then** a tela da turma mostra o novo responsável.
11. **Given** quem organiza, **When** abre a tela da turma, **Then** vê o código de convite; o membro comum não vê.
12. **Given** a própria pessoa, **When** gerencia membros, **Then** não encontra remover a si mesma, e sair continua sendo pela opção de sair.

---

### User Story 5 - Cadastro de campo (Priority: P3)

Só quem é dono de campo cadastra no catálogo. As características do campo podem ser marcadas e desmarcadas.

**Why this priority**: corrige uma regra furada, com alcance menor que as anteriores.

**Independent Test**: uma conta de jogador envia o cadastro de campo e é recusada; uma conta de dono cadastra, desmarca uma característica e a vê desmarcada.

**Acceptance Scenarios**:

1. **Given** uma conta que não é dona de campo, **When** envia o cadastro, **Then** o servidor recusa, mesmo sem passar pela tela.
2. **Given** um dono de campo, **When** desmarca coberto, vestiário, estacionamento, acessível ou ativo e salva, **Then** a mudança fica gravada.
3. **Given** um dono, **When** cadastra um campo, **Then** ele nasce ativo e aparece no catálogo.
4. **Given** outro dono, **When** tenta alterar ou remover o campo alheio, **Then** o servidor recusa.
5. **Given** um campo inativo, **When** outra pessoa abre o endereço dele, **Then** recebe recusa; o dono continua vendo.

---

### User Story 6 - Guia verdadeiro e capturas sem pessoa real (Priority: P3)

O "Como usar" descreve o que existe de fato, e as imagens mostram só dados fictícios.

**Why this priority**: é o que a pessoa de teste lê primeiro; a captura com nome real bloqueia a publicação (tarefa 7.5 de `seguranca-base`).

**Independent Test**: seguir os passos do guia em uma conta nova, sem encontrar opção inexistente, e abrir cada captura usada nas páginas.

**Acceptance Scenarios**:

1. **Given** o guia "Como usar", **When** a pessoa o segue do início ao fim, **Then** cada botão e opção citados existem com o nome e no lugar citados, e nada promete pagamento por PIX dentro do sistema.
2. **Given** as capturas usadas nas páginas, **When** conferidas uma a uma, **Then** nenhuma mostra nome, e-mail ou foto de pessoa real.
3. **Given** a pasta de capturas, **When** um arquivo não é mais usado, **Then** ele é apagado.
4. **Given** as telas novas e alteradas, **When** usadas só pelo teclado e em 390 px, **Then** têm rótulo, foco visível, português com acento e nenhum transbordo.

---

### Edge Cases

- Partida cheia, quem saiu da turma e partidas com lista de espera: a promoção e a liberação de vaga ficam para a Etapa 4; nesta frente, baixar vagas abaixo dos confirmados é recusado.
- Removido que ainda tem o código: continua recusado até liberarem a volta, mesmo com o código certo.
- Dois envios seguidos do mesmo código: não duplicam o vínculo.
- Turma arquivada: o código para de funcionar.
- Campo removido pelo dono com partida marcada: a partida fica com "Local a definir".
- Local removido sem partida futura: partidas antigas passam a mostrar "Local a definir".
- O responsável que sai volta a ser membro comum se voltar pelo código, sem recuperar a responsabilidade.
- O código de convite com espaços nas pontas é aceito, porque ele costuma ser copiado de mensagem.

## Requirements

### Functional Requirements

- **FR-001**: Jogadores MUST conseguir entrar em uma turma com o código de convite pelo painel e pela tela de turmas, sem diferença entre maiúsculas e minúsculas e ignorando espaços nas pontas.
- **FR-002**: O sistema MUST responder igual a código inválido e a código de turma arquivada, sem revelar se a turma existe, e MUST aplicar no site o mesmo limite de tentativas da API.
- **FR-003**: O sistema MUST reativar como membro comum quem saiu e volta com o código, e MUST não duplicar o vínculo de quem já é membro.
- **FR-004**: O sistema MUST recusar pelo código quem foi removido, até que quem gerencia libere a volta.
- **FR-005**: Membros MUST conseguir sair da turma com confirmação; o criador MUST não conseguir sair; quando o responsável sai, a responsabilidade MUST voltar ao criador.
- **FR-006**: Site e API MUST aplicar as regras de entrar e sair em um único ponto do código.
- **FR-007**: Organizadores MUST conseguir criar partida pelo site com título, descrição, data e hora, duração, vagas, valor por jogador, prazo de confirmação, lista de espera e lugar, com as vagas sugeridas pela configuração da turma.
- **FR-008**: O sistema MUST exigir data e hora futuras, prazo de confirmação antes do início e valores dentro de limites, MUST assumir o padrão quando o valor por jogador vier vazio, e nunca responder com erro de servidor por esses campos.
- **FR-009**: Organizadores MUST conseguir alterar partida agendada ou confirmada; o sistema MUST recusar alteração de partida em andamento, finalizada ou cancelada, e MUST recusar vagas abaixo do número de confirmados.
- **FR-010**: Organizadores MUST conseguir cancelar partida com confirmação; a partida cancelada MUST sair de "Próximas", aparecer em "Passadas" marcada como cancelada e não oferecer as ações de confirmar.
- **FR-011**: O sistema MUST recusar no servidor criar, alterar e cancelar partida para quem não organiza a turma, e MUST esconder essas opções de quem não pode usá-las.
- **FR-012**: O sistema MUST tratar datas e horas no horário de Brasília e escrevê-las em português.
- **FR-013**: Organizadores MUST conseguir cadastrar, alterar e remover locais da turma, e encontrar esse caminho a partir do formulário de partida; membros comuns MUST ser recusados nessas telas.
- **FR-014**: O sistema MUST recusar no site e na API local de outra turma, tanto no endereço do local quanto em uma partida.
- **FR-015**: A partida MUST ter no máximo um lugar, local da turma ou campo ativo do catálogo; o sistema MUST recusar os dois juntos e campo inativo em nova escolha, mantendo o campo inativo já usado; a tela da partida MUST mostrar nome, endereço e acessibilidade do lugar escolhido.
- **FR-016**: O sistema MUST recusar a remoção de local usado em partida que ainda vai acontecer, com aviso para trocar antes.
- **FR-017**: As características de local e de campo MUST poder ser marcadas e desmarcadas.
- **FR-018**: Organizadores MUST ver papel, e-mail e telefone dos membros em uma tela de gestão; membros comuns MUST ser recusados nela e continuar vendo só os dados públicos na tela da turma.
- **FR-019**: Criador e responsável MUST conseguir tornar membros organizadores e voltá-los a membros; os demais MUST ser recusados no servidor.
- **FR-020**: Criador e responsável MUST conseguir remover membro; o sistema MUST recusar remover ou rebaixar o criador e remover a si mesmo; quando o responsável é removido, a responsabilidade MUST voltar ao criador.
- **FR-021**: Criador e responsável MUST conseguir passar a responsabilidade só para membro ativo; o sistema MUST recusar pessoa de fora ou ex-membro, inclusive na tela de editar turma.
- **FR-022**: Criador e responsável MUST conseguir ver os removidos e liberar a volta deles.
- **FR-023**: O código de convite MUST aparecer para quem organiza a turma e MUST não aparecer para membro comum na tela da turma.
- **FR-024**: Só dono de campo e administrador da plataforma MUST conseguir cadastrar campo, com a regra aplicada ao salvar; só o dono do campo e o administrador MUST conseguir alterar e remover; o campo novo MUST nascer ativo.
- **FR-025**: Campo inativo MUST ficar fora do catálogo, e o endereço dele MUST abrir só para o dono e o administrador.
- **FR-026**: O guia "Como usar" MUST citar só botões e opções que existem, com o nome e o lugar corretos, e MUST não prometer pagamento por PIX dentro do sistema.
- **FR-027**: As capturas usadas nas páginas MUST ser refeitas só com dados fictícios, e capturas sem uso MUST ser apagadas.
- **FR-028**: As telas novas e alteradas MUST atender WCAG 2.1 AA em teclado, foco visível, rótulo e erro anunciado, em português com acento, sem transbordo em 390 px.
- **FR-029**: A API MUST aplicar as mesmas regras de partida do site e MUST informar o campo escolhido na resposta da partida.

### Key Entities

- **Vínculo de membro**: liga pessoa e turma, com papel (administrador, organizador, membro) e situação (ativo, inativo, removido). Removido é a situação nova desta frente.
- **Local**: lugar cadastrado pela turma, visível só para ela.
- **Campo**: lugar do catálogo público, cadastrado por um dono de campo.
- **Partida**: aponta para no máximo um lugar, local ou campo.

### Premissas a confirmar com o Felipe

- Só criador e responsável gerenciam membros. Organizador comum vê contatos, marca partida e cuida de locais.
- Quem foi removido só volta depois que liberam, porque remover sem isso seria desfeito com o mesmo código.
- O criador não sai nem transfere a criação da turma; se ela acabou, ele arquiva.
- Limites de partida: até 200 vagas e até R$ 99.999,99 por jogador.

### Fora do escopo

- Promoção da lista de espera, liberação de vaga e pendências de quem sai; recusa de confirmação fora do prazo, em partida cancelada ou com lista de espera desligada; placar, sorteio e rateio: Etapa 4 (itens 8 a 12, 17 a 22, 28 e 29).
- Português das demais telas, LGPD e limpeza: Etapa 5.
- Landing nova e fotos reais da página pública (tarefa 7.4): Etapas 6 e 7.
- CRUD de local pela API e app Flutter.

## Success Criteria

### Measurable Outcomes

- **SC-001**: Uma conta nova de organizador vai do painel à primeira partida marcada, com um local novo, em até 3 minutos, só pelo site.
- **SC-002**: Um jogador com o código entra na turma e confirma presença em até 1 minuto, só pelo site.
- **SC-003**: Toda ação nova tem teste de recusa para cada tipo de pessoa sem permissão, e 100% deles passam.
- **SC-004**: Nenhuma opção citada no guia deixa de existir, conferido passo a passo.
- **SC-005**: Nenhuma captura usada nas páginas mostra pessoa real, conferida uma a uma.
- **SC-006**: Todas as telas novas e alteradas abrem em 1280 e 390 px sem erro e sem transbordo.

## Delta Requirements

**Skip**: o projeto ainda não tem corpus em `docs/specs/current/`, então não há capacidade ativa documentada para alterar — Claude, 2026-11-05
