# Feature Specification: Base segura para o teste com pessoas reais

**Feature**: `seguranca-base`
**Created**: 2026-11-05
**Status**: Draft

> Triagem: o Felipe pediu o projeto inteiro pelo cstk (05/11/2026), o que vale como escolha do SDD completo.
> A frente corrige defeitos que impedem receber gente real e tem várias histórias independentes, atores
> distintos e backlog para mais de uma sessão.
>
> Fora desta frente, por já terem etapa própria: telas que faltam (criar partida, entrar e sair da turma), regras
> de vaga, rateio e placar, tradução da interface e landing.

## Clarifications

### Session 2026-11-05

Varredura do `/cstk:clarify` sem ambiguidade que impeça o plano. Nenhuma pergunta foi feita ao Felipe nesta rodada;
os itens marcados "padrão assumido" são escolhas do agente e ele pode trocar.

- Q: Quem é "organizador" para as permissões? → A: quem tem papel `administrador` ou `organizador` ativo, o criador e o responsável da turma, regra que o código do placar já aplica (fonte: `Web/PlacarController.php:211-226`).
- Q: Membro comum vê as despesas da turma? → A: sim, leitura para todo membro ativo; só o organizador altera (fonte: `Web/DespesaController.php:17,124`).
- Q: A confirmação de e-mail bloqueia o uso antes de confirmar? → A: sim, pelo site e pela API (padrão assumido; o cadastro é aberto por decisão do Felipe de 05/11 sobre a landing).
- Q: Quais os valores de limite de tentativas? → A: os dos FR-018 a FR-021 (padrão assumido, ajustável sem mudança de regra).
- Q: Qual provedor de e-mail e qual servidor? → A: adiado para a Etapa 9. Esta frente só exige que a produção recuse subir sem envio real, e o teste local usa uma caixa de e-mail de desenvolvimento.

## User Scenarios & Testing

### User Story 1 - O dinheiro e o jogo da turma só para a turma (Priority: P1)

Quem organiza uma pelada lança despesas e controla o placar. Uma pessoa de outra turma, mesmo logada, não pode
ver, alterar nem apagar nada disso. A mesma pessoa recebe a mesma resposta pelo site e pela API.

**Why this priority**: hoje quem está fora da turma lê, altera e apaga despesas e lê o placar. É a falha que mais
destrói confiança: mexe em dinheiro e atinge muitas turmas no mesmo banco.

**Independent Test**: com duas turmas e um usuário só na segunda, tentar cada ação sobre a primeira, pelo site e
pela API, e conferir a recusa e o dado intacto.

**Acceptance Scenarios**:

1. **Given** um usuário fora da turma, **When** lista, abre, altera ou apaga uma despesa dela, **Then** recebe recusa e a despesa continua igual.
2. **Given** um usuário fora da turma, **When** tenta lançar despesa nela ou dar baixa em pagamento dela, **Then** recebe recusa e nada é gravado.
3. **Given** um membro comum, **When** lista e abre as despesas da própria turma, **Then** consegue ver, mas recebe recusa ao alterar ou apagar.
4. **Given** um organizador, **When** lança uma despesa citando uma partida de outra turma, **Then** a despesa é recusada e ninguém da outra turma é cobrado.
5. **Given** um usuário fora da turma, **When** abre o placar, a tela de TV, o resultado ou os dados ao vivo da partida, **Then** recebe recusa.
6. **Given** a mesma pessoa e o mesmo registro, **When** tenta a mesma ação pelo site e pela API, **Then** as duas respostas concordam: ambas permitem ou ambas recusam.
7. **Given** um administrador da plataforma, **When** faz qualquer ação acima pelo site ou pela API, **Then** a ação é permitida da mesma forma nos dois caminhos.
8. **Given** uma pessoa que saiu da turma, **When** tenta ler dados dela a partir da saída, **Then** é tratada como alguém de fora.
9. **Given** cada regra de permissão, **When** a suíte roda, **Then** existe um teste automatizado cobrindo o acesso negado daquela regra.

---

### User Story 2 - Meu contato e minha acessibilidade não vazam para o grupo (Priority: P1)

Um jogador entra na turma e os outros veem só o necessário para jogar. Contato e necessidades de acessibilidade,
que podem revelar dado de saúde, ficam com a própria pessoa e com quem organiza a turma.

**Why this priority**: hoje qualquer membro recebe e-mail, telefone, papel na plataforma e preferências de
acessibilidade dos demais. A constitution (princípio IV) proíbe isso.

**Independent Test**: como membro comum, buscar a lista de membros e conferir que os campos protegidos não vêm
na resposta; repetir como organizador e como a própria pessoa.

**Acceptance Scenarios**:

1. **Given** um membro comum, **When** vê a lista de membros, **Then** recebe nome, apelido, foto, posição e nível, sem e-mail, telefone, papel na plataforma nem acessibilidade.
2. **Given** o organizador da turma, **When** vê a lista de membros, **Then** recebe também e-mail, telefone e necessidades de acessibilidade dos membros daquela turma.
3. **Given** um usuário logado, **When** consulta o próprio perfil, **Then** recebe todos os próprios dados.
4. **Given** um organizador de outra turma, **When** pede dados de um membro que não está na turma dele, **Then** não recebe nenhum dado protegido.

---

### User Story 3 - Nenhuma credencial de demonstração em ambiente público (Priority: P1)

O sistema publicado nasce sem usuário de demonstração, sem senha conhecida e sem atalho de login. A primeira conta
de administração é criada por quem opera o servidor.

**Why this priority**: hoje o boot da imagem recria um administrador global com senha que está em um repositório
público, e a cada reinício a restaura.

**Independent Test**: subir o sistema como produção com banco vazio, conferir que não há usuários, que a senha
de demonstração não entra, que o atalho de login não responde, e criar o administrador pelo procedimento documentado.

**Acceptance Scenarios**:

1. **Given** uma instalação de produção com banco vazio, **When** ela sobe e reinicia, **Then** nenhum usuário nem turma de demonstração é criado.
2. **Given** o repositório, **When** se procura e-mail pessoal real ou senha em texto puro nos arquivos atuais de seed, documentação e testes, **Then** nada é encontrado.
3. **Given** uma instalação fora do ambiente local, **When** alguém acessa a rota de login de demonstração, **Then** a rota não existe.
4. **Given** um operador no servidor, **When** roda o procedimento de criação de administrador, **Then** a conta é criada com senha escolhida no ato, sem ficar registrada em arquivo versionado.
5. **Given** uma instalação de produção com depuração ligada ou sem chave da aplicação, **When** o operador sobe o sistema, **Then** ele recusa iniciar e informa o motivo.
6. **Given** o ambiente local de desenvolvimento, **When** o desenvolvedor roda o seed, **Then** recebe dados fictícios com senha gerada no ato, sem dado de pessoa real.

---

### User Story 4 - Tentar senhas ou códigos em massa não funciona (Priority: P1)

Quem tenta adivinhar senha, criar contas em massa, disparar e-mails de recuperação ou descobrir códigos de turma é
barrado depois de poucas tentativas. Quem joga de verdade, inclusive vários amigos na mesma rede, não sente diferença.

**Why this priority**: hoje doze senhas erradas seguidas na API não geram bloqueio, e o código de convite é o que
dá acesso à turma.

**Independent Test**: repetir cada ação acima do limite e conferir o bloqueio; conferir que outro usuário na mesma
rede continua conseguindo entrar.

**Acceptance Scenarios**:

1. **Given** um visitante, **When** erra a senha mais de 5 vezes em 1 minuto para o mesmo e-mail e origem, **Then** a próxima tentativa é recusada com aviso de quando tentar de novo, pelo site e pela API.
2. **Given** a mesma origem, **When** faz mais de 30 cadastros em 1 hora, **Then** o próximo é recusado com o mesmo tipo de aviso.
3. **Given** a mesma origem, **When** pede recuperação de senha mais de 3 vezes em 1 minuto, **Then** o próximo pedido é recusado sem enviar e-mail.
4. **Given** um usuário logado, **When** tenta entrar por código mais de 10 vezes em 1 minuto, **Then** a próxima tentativa é recusada.
5. **Given** um usuário bloqueado por excesso de tentativas, **When** outro usuário da mesma rede entra com a senha certa, **Then** ele entra normalmente.
6. **Given** o sistema atrás de um servidor intermediário de publicação, **When** visitantes de origens diferentes acessam, **Then** cada limite conta a origem real de cada um, e não a do intermediário.

---

### User Story 5 - Conta com e-mail confirmado e recuperação que chega (Priority: P2)

Ao criar a conta grátis, a pessoa confirma o e-mail antes de usar a turma. Quem esquece a senha recebe o link de
verdade na caixa de entrada.

**Why this priority**: a landing convida para criar conta grátis, então o cadastro é aberto. Hoje a confirmação
está desligada e, na imagem de produção, o e-mail de recuperação vai para o log do servidor com o link dentro.

**Independent Test**: criar conta nova, conferir o bloqueio até confirmar, abrir o link recebido e seguir;
pedir recuperação e redefinir a senha pelo link recebido.

**Acceptance Scenarios**:

1. **Given** uma conta recém-criada pelo site, **When** tenta abrir o painel sem confirmar, **Then** é levada à tela que pede a confirmação, com opção de reenviar.
2. **Given** uma conta recém-criada pela API, **When** usa o token em rota de turma sem confirmar, **Then** recebe recusa que diz que falta confirmar o e-mail, e pode pedir o reenvio.
3. **Given** um link de confirmação válido, **When** a pessoa abre, **Then** a conta fica confirmada e ela segue para o painel.
4. **Given** contas que já existiam e estavam confirmadas, **When** a mudança entra, **Then** elas continuam entrando sem nova confirmação.
5. **Given** produção configurada, **When** alguém pede recuperação de senha, **Then** o link chega pelo e-mail real e não aparece em nenhum log.
6. **Given** produção sem envio de e-mail real configurado, **When** o operador sobe o sistema, **Then** ele recusa iniciar e informa o motivo.

---

### User Story 6 - Sair da conta limpa o aparelho (Priority: P2)

Quem usa o celular de um amigo ou um computador compartilhado sai da conta, e a pessoa seguinte não consegue ver
telas da turma nem offline.

**Why this priority**: hoje o aplicativo instalado guarda páginas autenticadas no aparelho e não as apaga ao sair,
e pode mostrar erro antigo ou dado velho.

**Independent Test**: entrar, navegar pelas telas da turma, sair, ficar sem rede e reabrir as mesmas URLs.

**Acceptance Scenarios**:

1. **Given** um usuário que navegou por telas autenticadas, **When** sai da conta e reabre uma delas sem rede, **Then** vê a página de "sem conexão", e nunca o conteúdo anterior.
2. **Given** um usuário logado sem rede, **When** abre uma tela autenticada, **Then** vê a página de "sem conexão".
3. **Given** uma resposta de erro ou recusa do servidor, **When** o aparelho a recebe, **Then** ela não fica guardada para respostas futuras.
4. **Given** um aparelho com versão anterior do aplicativo instalada, **When** a nova versão entra, **Then** o que estava guardado de páginas autenticadas é apagado.
5. **Given** uma tela de placar ao vivo com rede, **When** pede dados atualizados, **Then** recebe dados da rede, nunca uma cópia antiga.

---

### User Story 7 - Imagem de produção que sobe e guarda os dados (Priority: P1)

O operador gera a imagem de produção, sobe o sistema e o que os usuários gravam continua lá depois de reiniciar ou
atualizar, sem arquivos sensíveis dentro da imagem.

**Why this priority**: hoje a imagem não compila. Mesmo corrigida, gravaria tudo em um arquivo que some a cada nova
versão, serviria com um servidor de desenvolvimento e carregaria o arquivo local de configuração.

**Independent Test**: gerar a imagem do zero, subir com o banco da casa, criar uma conta, reiniciar e atualizar o
contêiner, e conferir que a conta segue lá; inspecionar a imagem atrás de arquivos sensíveis.

**Acceptance Scenarios**:

1. **Given** uma cópia limpa do repositório, **When** o operador gera a imagem, **Then** a geração termina sem erro.
2. **Given** a imagem no ar com o banco de produção, **When** um usuário cria a conta e o contêiner é recriado, **Then** a conta continua existindo.
3. **Given** a imagem gerada, **When** se inspeciona seu conteúdo, **Then** não há arquivo local de configuração, banco de desenvolvimento, fotos originais em `docs` nem histórico do repositório.
4. **Given** a imagem no ar, **When** recebe requisições, **Then** as atende com servidor web de produção, não com servidor de desenvolvimento, e a verificação de saúde responde.
5. **Given** uma atualização de versão, **When** a imagem nova sobe, **Then** as migrações de banco rodam sem seed e sem apagar dados.
6. **Given** o ambiente de apoio local, **When** o desenvolvedor sobe o banco e a ferramenta de administração, **Then** eles só ficam acessíveis pelo próprio computador.

---

### Edge Cases

- Registro inexistente e registro proibido devem responder de forma que não revele a existência de dados de outra turma além do identificador já conhecido.
- Membro cuja saída da turma deixou status inativo perde a leitura na mesma hora, sem esperar nova sessão.
- Criador da turma ou responsável que deixou de ser membro ativo: a regra vale pelo papel atual; o criador continua com poder de organizador enquanto a turma existir, como já ocorre hoje no controle do placar.
- Token de API de usuário ainda não confirmado continua válido para sair da conta e pedir reenvio da confirmação.
- Reenvio de confirmação também tem limite de tentativas, para não virar disparo de e-mail.
- Seed rodado por engano em produção é recusado, e o sistema avisa o motivo.
- Cadastro repetido com o mesmo e-mail não confirmado segue a regra atual de e-mail único.
- Excesso de tentativas em rede compartilhada: o limite de login é por e-mail e origem, para um colega não bloquear o outro.

## Requirements

### Functional Requirements

- **FR-001**: O sistema MUST recusar a quem está fora da turma qualquer leitura ou alteração de despesa e pagamento dela, pelo site e pela API.
- **FR-002**: O sistema MUST permitir que só organizadores da turma lancem, alterem e apaguem despesa; membro comum apenas lê, e cada pessoa dá baixa só no próprio pagamento.
- **FR-003**: O sistema MUST recusar despesa vinculada a partida de outra turma.
- **FR-004**: O sistema MUST recusar a quem está fora da turma a leitura de placar, tela de TV, resultado e dados ao vivo da partida, pelo site e pela API.
- **FR-005**: O sistema MUST decidir cada permissão num único ponto, compartilhado por site e API, de modo que a mesma pessoa receba a mesma resposta sobre o mesmo registro nos dois caminhos.
- **FR-006**: O sistema MUST ter teste automatizado cobrindo o acesso negado de cada regra de permissão.
- **FR-007**: O administrador da plataforma MUST ter ao menos o acesso de organizador em todas as turmas, igualmente pelo site e pela API, e MUST NOT dar baixa no pagamento de outra pessoa.
- **FR-008**: O sistema MUST tratar quem saiu da turma como alguém de fora, a partir da saída.
- **FR-009**: O sistema MUST devolver a membro comum só nome, apelido, foto, posição e nível dos demais.
- **FR-010**: O sistema MUST devolver e-mail, telefone e necessidades de acessibilidade de um membro só a ele próprio, a quem organiza a turma em que ele está, e ao administrador da plataforma.
- **FR-011**: O sistema MUST mostrar o papel na plataforma de uma pessoa só a ela própria e ao administrador da plataforma.
- **FR-012**: O sistema MUST NOT criar usuário ou turma de demonstração em produção, e o seed de demonstração MUST ser recusado fora do ambiente local e de teste.
- **FR-013**: Os arquivos atuais do repositório MUST NOT conter senha em texto puro nem dado pessoal real em seed, documentação e testes.
- **FR-014**: O seed local MUST usar só dados fictícios, com senha gerada no ato e mostrada a quem rodou.
- **FR-015**: O operador MUST ser capaz de criar a conta de administrador por um procedimento no servidor, escolhendo a senha no ato, sem registrá-la em arquivo versionado.
- **FR-016**: A rota de login de demonstração MUST NOT existir fora do ambiente local.
- **FR-017**: O sistema MUST recusar iniciar em produção com depuração ligada, sem chave da aplicação ou sem envio real de e-mail, informando o motivo.
- **FR-018**: O sistema MUST limitar a 5 por minuto as tentativas de login por e-mail e origem, pelo site e pela API.
- **FR-019**: O sistema MUST limitar a 30 por hora os cadastros por origem, pelo site e pela API, valor que comporta um grupo inteiro na mesma rede.
- **FR-020**: O sistema MUST limitar a 3 por minuto, por origem, os pedidos de recuperação de senha e a 6 por minuto, por conta, os reenvios de confirmação, pelo site e pela API.
- **FR-021**: O sistema MUST limitar a 10 por minuto as tentativas de entrar por código, por usuário.
- **FR-022**: A recusa por limite MUST avisar a pessoa quando tentar de novo, em português.
- **FR-023**: O sistema MUST reconhecer a origem real do visitante atrás do servidor intermediário de publicação, confiando só no intermediário configurado.
- **FR-024**: O sistema MUST exigir e-mail confirmado para usar turmas, partidas, despesas, mural, placar e campos, pelo site e pela API.
- **FR-025**: Pessoa sem e-mail confirmado MUST conseguir sair da conta, consultar a própria conta, excluir a conta e pedir o reenvio da confirmação.
- **FR-026**: Contas já confirmadas MUST continuar entrando sem nova confirmação, e a mudança MUST NOT exigir ação delas.
- **FR-027**: Em produção, o e-mail de confirmação e o de recuperação de senha MUST sair por envio real, e o link MUST NOT aparecer em log.
- **FR-028**: O aplicativo instalado MUST NOT guardar no aparelho páginas autenticadas, dados de turma ou respostas de erro; só guarda arquivos estáticos e a página de "sem conexão".
- **FR-029**: A nova versão do aplicativo instalado MUST apagar o que a versão anterior guardou, e depois de sair da conta MUST NOT restar tela autenticada acessível, nem pelo botão voltar.
- **FR-030**: A imagem de produção MUST ser gerada sem erro a partir de uma cópia limpa do repositório.
- **FR-031**: Em produção, os dados MUST ficar no banco de dados relacional padrão da casa, e sobreviver à recriação do contêiner.
- **FR-032**: A imagem de produção MUST atender as requisições com servidor web de produção, e ter verificação de saúde.
- **FR-033**: A atualização de versão MUST rodar as migrações de banco sem seed.
- **FR-034**: A imagem de produção MUST NOT conter arquivo local de configuração, banco de desenvolvimento, histórico do repositório ou fotos originais de `docs`.
- **FR-035**: O ambiente local de apoio MUST expor o banco e a ferramenta de administração só ao próprio computador, e sem senha padrão versionada para o superusuário do banco.

### Key Entities

- **Membro da turma**: vínculo entre pessoa e turma, com papel (membro, organizador, administrador) e situação (ativo, inativo). É a base das permissões.
- **Administrador da plataforma**: pessoa da equipe Alfa com acesso a todas as turmas, criada só pelo operador.
- **Dados protegidos de pessoa**: e-mail, telefone, papel na plataforma, preferências e necessidades de acessibilidade.
- **Conta confirmada**: conta cujo e-mail foi confirmado pelo link enviado; é o que libera o uso de turmas.

### Decisões de infraestrutura

- **Backup**: o sistema guarda dados reais. Agendamento, retenção e teste de restauração ficam para a Etapa 9 (publicação), quando houver servidor definido, e o backup antes de migrar segue a constitution.
- Scheduling, rotação de chave, refresh de token externo, mutex entre réplicas e idempotência: N/A nesta frente, que roda em uma instância e não tem agendamento nem integração externa com token.

## Success Criteria

### Measurable Outcomes

- **SC-001**: Em 100% das ações sobre despesa, pagamento e placar, tentadas por quem está fora da turma, a resposta é de recusa e nenhum dado muda (0 vazamentos no roteiro de sondagem).
- **SC-002**: Em todas as ações comparadas, site e API dão a mesma resposta para a mesma pessoa e o mesmo registro (0 divergências).
- **SC-003**: Um membro comum não recebe nenhum dos campos protegidos dos demais em 100% das respostas verificadas.
- **SC-004**: Uma instalação nova de produção, depois de subir, tem 0 usuários, e a senha de demonstração não entra em nenhuma conta.
- **SC-005**: Na 6ª tentativa em 1 minuto de senha errada, a recusa por limite ocorre em 100% das vezes, enquanto outra pessoa da mesma rede entra de primeira.
- **SC-006**: Uma pessoa nova cria a conta e confirma o e-mail em menos de 3 minutos, usando o link recebido.
- **SC-007**: Depois de sair da conta, 0 telas autenticadas são exibidas ao reabrir o aplicativo sem rede.
- **SC-008**: A imagem de produção é gerada do zero sem erro, e uma conta criada sobrevive a 1 ciclo completo de recriação do contêiner.
- **SC-009**: Toda a suíte automatizada passa, com pelo menos um teste de acesso negado para cada regra de permissão.

## Delta Requirements

**Skip**: o corpus canônico `docs/specs/current/` ainda não existe neste projeto; a frente será a primeira a ser arquivada e cria a base — Claude, 2026-11-05
