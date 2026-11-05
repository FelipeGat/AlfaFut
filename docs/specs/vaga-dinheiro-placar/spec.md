# Feature Specification: Vaga, dinheiro e placar

**Feature**: `vaga-dinheiro-placar`
**Created**: 2026-11-05
**Status**: Draft

Etapa 4 do plano do AlfaFut. Depois da Etapa 3, a turma já usa o sistema só pelo navegador, mas as regras por trás ainda quebram: quem repete a confirmação perde a vaga, a fila não anda, um segundo toque zera o placar, o rateio não fecha no centavo e excluir a conta dá erro ou apaga a turma dos outros.

> Decisões de infraestrutura: Idempotência: repetir confirmar, iniciar, pausar ou finalizar não muda nada além do primeiro pedido. Concorrência: a confirmação serializa os pedidos de uma mesma partida dentro de uma transação do banco, o que vale também com mais de um servidor. Sem agendamento, sem criptografia nova, sem token externo. Backup segue a regra da constitution.

## User Scenarios & Testing

### User Story 1 - Vaga sem surpresa (Priority: P1)

O jogador confirma presença e sabe que o lugar dele está garantido. Se a partida lotou, entra na fila, e a fila anda sozinha quando alguém desiste.

**Why this priority**: a lista de presença é o que faz o dono da pelada abandonar o grupo de mensagens.

**Independent Test**: em partida de 4 vagas lotada, repetir a confirmação não muda nada; um titular recusa e o primeiro da fila vira titular, no site e na API.

**Acceptance Scenarios**:

1. **Given** um titular, **When** confirma de novo com a partida cheia, **Then** continua titular e a fila não muda.
2. **Given** alguém na fila, **When** confirma de novo, **Then** mantém a mesma posição.
3. **Given** partida com prazo de confirmação vencido, **When** um jogador tenta confirmar, **Then** recebe recusa dizendo que o prazo acabou.
4. **Given** partida cancelada, iniciada, finalizada ou com o horário já passado, **When** um jogador tenta confirmar, **Then** recebe recusa e nada é gravado.
5. **Given** partida cheia com a lista de espera desligada, **When** um jogador tenta confirmar, **Then** recebe recusa dizendo que está lotada, sem entrar em fila.
6. **Given** partida cheia com fila, **When** um titular recusa no site ou na API, **Then** o primeiro da fila vira titular e os demais sobem uma posição.
7. **Given** partida com o prazo de confirmação vencido que ainda não começou, **When** um titular avisa que não vai, **Then** a recusa é aceita e a fila anda.
8. **Given** quem está na fila, **When** recusa, **Then** sai da fila e os que vinham depois sobem.
9. **Given** duas confirmações ao mesmo tempo para a última vaga, **When** o sistema processa, **Then** só uma fica com a vaga e a outra vai para a fila ou é recusada.
10. **Given** um membro confirmado em partida que não começou, **When** sai da turma ou é removido, **Then** perde a vaga e a fila anda; partidas iniciadas ou encerradas não mudam.
11. **Given** partida com fila, **When** quem organiza aumenta as vagas, **Then** os primeiros da fila viram titulares até preencher.
12. **Given** um jogador em time sorteado, **When** recusa antes de a partida começar, **Then** sai do time e quem organiza pode sortear de novo.

---

### User Story 2 - Placar que não se perde (Priority: P1)

Quem organiza controla a partida ao vivo, no campo, com o celular na mão. Um toque repetido, um gol depois do apito ou um terceiro time não podem bagunçar o resultado.

**Why this priority**: placar zerado no meio do jogo é o tipo de erro que faz a turma desistir na primeira semana.

**Independent Test**: inicie, marque um gol, toque em iniciar de novo; o placar segue 1x0. Finalize e tente marcar outro gol; é recusado.

**Acceptance Scenarios**:

1. **Given** partida em andamento com 1x0, **When** alguém toca em iniciar de novo, **Then** o placar e o tempo não mudam e nenhum evento novo é registrado.
2. **Given** partida pausada, **When** quem organiza retoma, **Then** o tempo continua de onde parou e o placar é mantido.
3. **Given** partida cancelada ou finalizada, **When** alguém tenta iniciar, pausar ou registrar gol, **Then** recebe recusa, e nada muda.
4. **Given** partida sem times sorteados, **When** quem organiza tenta iniciar, **Then** recebe a orientação de sortear antes.
5. **Given** partida finalizada, **When** alguém finaliza de novo, **Then** a hora e o tempo finais não mudam.
6. **Given** partida que não começou, **When** alguém tenta finalizar ou registrar gol, **Then** recebe recusa.
7. **Given** partida em andamento, **When** quem organiza registra gol de um jogador de outro time ou de fora da partida, **Then** recebe recusa.
8. **Given** um gol registrado por engano, **When** quem organiza remove, **Then** placar, gols do time e artilharia voltam ao valor certo.
9. **Given** partida finalizada, **When** alguém tenta remover um gol, **Then** recebe recusa.
10. **Given** partida com três times ou mais, **When** abre controle, placar de TV e resultado, **Then** todos os times aparecem com os próprios gols, e o resultado aponta o vencedor ou o empate entre todos.
11. **Given** a edição de partida na API, **When** alguém envia a situação em andamento ou finalizada, **Then** recebe recusa: essas situações só mudam pelo controle da partida.

---

### User Story 3 - Sorteio justo e seguro (Priority: P2)

Quem organiza sorteia os times sabendo que eles ficam equilibrados em quantidade e nível, e que um toque depois do apito não apaga a partida.

**Why this priority**: sortear de novo hoje apaga os times e os gols de partida já jogada.

**Independent Test**: com 7 confirmados e 2 times, os times ficam com 4 e 3 jogadores. Depois de iniciar a partida, sortear é recusado.

**Acceptance Scenarios**:

1. **Given** partida iniciada, finalizada ou cancelada, **When** alguém tenta sortear, **Then** recebe recusa e os times não mudam.
2. **Given** qualquer combinação de níveis e goleiros, **When** sorteia, **Then** a diferença entre o maior e o menor time é de no máximo 1 jogador.
3. **Given** quantidade igual de jogadores por nível, **When** sorteia, **Then** a soma de nível dos times fica equilibrada.
4. **Given** menos confirmados que times previstos, **When** sorteia, **Then** nenhum time fica vazio.
5. **Given** um jogador sem nível ou posição informados, **When** sorteia, **Then** ele entra como nível intermediário, sem erro.
6. **Given** um jogador logado, **When** abre o perfil no site, **Then** informa apelido, posição preferida e nível, e o sorteio passa a usar esses dados.

---

### User Story 4 - Dinheiro que fecha (Priority: P1)

Quem organiza lança uma despesa e o sistema divide entre os confirmados. A soma bate ao centavo, cada baixa soma ao que já foi pago e o histórico mostra quem fez o quê.

**Why this priority**: um centavo a menos, ou um pagamento que some, faz o organizador voltar para a planilha.

**Independent Test**: R$ 100,00 entre 3 confirmados geram 33,34 + 33,33 + 33,33. Baixa de 10,00 e depois 5,00 somam 15,00; baixa acima do saldo é recusada.

**Acceptance Scenarios**:

1. **Given** R$ 100,00 entre 3 confirmados, **When** a despesa é criada no site ou na API, **Then** a soma das parcelas é R$ 100,00 exatos e os centavos a mais ficam com os primeiros a confirmar.
2. **Given** despesa rateada sem partida, ou com partida sem confirmados, **When** quem organiza tenta salvar, **Then** recebe recusa explicando o que falta, em vez de uma despesa que nunca fecha.
3. **Given** a lista de confirmados mudou depois do rateio, **When** quem organiza abre a despesa, **Then** vê o aviso e pode refazer o rateio.
4. **Given** um rateio refeito, **When** alguém já pagou, **Then** o que foi pago é mantido, quem saiu sem pagar deixa de dever, quem entrou passa a dever, e a soma continua igual ao total.
5. **Given** despesa com parcelas, **When** quem organiza altera o valor pela API, **Then** o rateio é refeito; valor abaixo do que já foi pago é recusado.
6. **Given** parcela de R$ 33,34, **When** a pessoa dá baixa de R$ 10,00 e depois de R$ 5,00, **Then** o pago é R$ 15,00 e a situação é parcial.
7. **Given** parcela com saldo de R$ 5,00, **When** a pessoa tenta dar baixa de R$ 6,00, **Then** recebe recusa informando o saldo.
8. **Given** todas as parcelas pagas, **When** entra a última baixa, **Then** a despesa fica fechada sozinha.
9. **Given** despesa não rateada, **When** quem organiza marca como paga, **Then** ela fica fechada com saldo zero.
10. **Given** despesa com algum pagamento, **When** alguém tenta remover, **Then** recebe recusa e a orientação de cancelar; cancelar mantém o que foi pago e encerra o resto.
11. **Given** despesa cancelada ou fechada, **When** alguém tenta dar baixa, **Then** recebe recusa.
12. **Given** partida cancelada com despesa vinculada aberta, **When** quem organiza abre a despesa, **Then** vê o aviso de que a partida foi cancelada.
13. **Given** qualquer mudança em despesa ou pagamento, **When** quem organiza abre a despesa, **Then** vê no histórico quem fez, o quê e quando.
14. **Given** parcela de quem não é mais membro ativo, **When** quem organiza registra o recebimento, **Then** a baixa é aceita; para membro ativo, só a própria pessoa dá baixa.

---

### User Story 5 - Excluir a conta sem prejudicar a turma (Priority: P2)

O jogador pede a exclusão da conta. Os dados pessoais somem, mas a turma, o placar e o rateio dos outros continuam certos.

**Why this priority**: é direito da pessoa pela LGPD, e hoje a ação dá erro ou apaga a turma de todos.

**Independent Test**: um jogador com partida e parcela exclui a conta; outro membro continua vendo a turma, a soma do rateio não muda, e o nome aparece como "Conta excluída".

**Acceptance Scenarios**:

1. **Given** um jogador em turmas, partidas e parcelas, **When** exclui a conta com a senha correta, **Then** nome, e-mail, telefone, data de nascimento, foto e preferências somem, e ele não entra mais.
2. **Given** a conta excluída, **When** outra pessoa olha partida, placar ou despesa antiga, **Then** vê "Conta excluída" no lugar do nome, e os totais continuam iguais.
3. **Given** um jogador confirmado em partida que não começou, **When** exclui a conta, **Then** a vaga é liberada e a fila anda.
4. **Given** quem criou uma turma com outros membros ativos, **When** tenta excluir a conta, **Then** recebe recusa que cita a turma e orienta a arquivá-la antes.
5. **Given** quem criou uma turma em que está sozinho, **When** exclui a conta, **Then** a turma é arquivada junto.
6. **Given** um dono de campo, **When** exclui a conta, **Then** os campos dele saem do catálogo.
7. **Given** o e-mail de uma conta excluída, **When** alguém se cadastra de novo com ele, **Then** o cadastro é aceito e não herda nada da conta antiga.

---

### User Story 6 - Turma arquivada e dados de outra turma (Priority: P3)

Turma arquivada vira histórico: dá para consultar e quem criou pode restaurar. E nenhum formulário aceita identificador de outra turma ou valor que estoure a coluna.

**Why this priority**: são defeitos raros, mas que hoje terminam em erro 500 ou em dado misturado.

**Independent Test**: arquive uma turma com partida. A tela da partida abre sem botões de ação. O criador restaura e tudo volta.

**Acceptance Scenarios**:

1. **Given** turma arquivada, **When** um membro abre o endereço de uma partida dela, **Then** a tela abre só para leitura, com aviso de turma arquivada.
2. **Given** turma arquivada, **When** alguém tenta confirmar, sortear, iniciar ou registrar gol, **Then** recebe recusa, inclusive o administrador da plataforma.
3. **Given** quem criou a turma, **When** abre a lista de turmas, **Then** vê as arquivadas e pode restaurar, no site e na API; outro membro não consegue restaurar.
4. **Given** uma mensagem no mural, **When** é enviada com partida de outra turma, **Then** recebe recusa, no site e na API.
5. **Given** turma com mensalidade em branco, **When** é salva, **Then** a mensalidade fica zero, sem erro.
6. **Given** valor de mensalidade, despesa ou baixa acima do teto, **When** é enviado, **Then** recebe recusa com mensagem clara, sem erro 500.
7. **Given** o site e a API, **When** recebem o mesmo pedido desta frente nas mesmas condições, **Then** aplicam as mesmas regras, num ponto único do código, e chegam ao mesmo resultado.

---

### Edge Cases

- Fila com lacunas de numeração: a renumeração acontece a cada saída, então a posição mostrada é sempre 1, 2, 3...
- Aumentar vagas sem fila: nada acontece além do novo total.
- Refazer rateio quando alguém pagou mais do que a nova parcela: o que foi pago fica como parcela dessa pessoa, e o restante é dividido entre os outros.
- Refazer rateio sem nenhum confirmado: é recusado, sem apagar parcelas.
- Baixa com valor decimal além de 2 casas: é recusada pela validação.
- Pagamento pendente de quem saiu da turma: a dívida continua registrada, e quem saiu é avisado do valor ao sair.
- Resultado com três times em que dois empatam na liderança: mostra empate entre eles.
- Gol contra: conta para o time informado e não entra na artilharia de ninguém.
- Limite de gols por time: passar de 255 é recusado, sem erro 500.
- Partida em turma arquivada que foi restaurada: volta como estava, sem mudar situação.

## Requirements

### Functional Requirements

**Vaga**

- **FR-001**: O sistema MUST recusar confirmação em partida cancelada, iniciada, finalizada, com horário já passado ou com prazo de confirmação vencido, com mensagem que diga o motivo.
- **FR-002**: Confirmar de novo MUST manter a situação atual: titular continua titular e quem está na fila mantém a posição.
- **FR-003**: Com a partida cheia, o sistema MUST pôr o jogador na fila se ela estiver ligada, e recusar dizendo que está lotada se estiver desligada.
- **FR-004**: Confirmação e promoção MUST rodar em transação serializada por partida, sem ultrapassar as vagas com pedidos simultâneos.
- **FR-005**: Recusar ou cancelar MUST liberar a vaga e promover o primeiro da fila, renumerando a fila, no site e na API.
- **FR-006**: Recusar MUST ser aceito até a partida começar, mesmo com o prazo de confirmação vencido.
- **FR-007**: Quem sai da turma, é removido ou exclui a conta MUST perder a vaga nas partidas que não começaram, com a fila andando; partidas iniciadas ou encerradas não mudam.
- **FR-008**: Aumentar as vagas MUST promover os primeiros da fila até preencher.
- **FR-009**: Quem recusa antes de a partida começar MUST sair do time sorteado.

**Placar**

- **FR-010**: Iniciar, pausar e finalizar MUST seguir a ordem da partida, e repetir o mesmo pedido não muda placar, tempo nem eventos.
- **FR-011**: Partida cancelada ou de turma arquivada MUST recusar qualquer ação do controle; partida sem times sorteados MUST recusar o início com a orientação de sortear antes.
- **FR-012**: Finalizar MUST exigir partida iniciada e nunca sobrescrever a hora e o tempo finais.
- **FR-013**: Registrar gol MUST exigir partida em andamento ou pausada, e o jogador MUST pertencer ao time informado; no gol contra, ao outro time da partida.
- **FR-014**: Remover gol MUST corrigir placar, gols do time e artilharia, e MUST ser recusado depois do fim da partida.
- **FR-015**: Controle, placar de TV e resultado MUST mostrar todos os times da partida com os próprios gols; o resultado MUST apontar vencedor ou empate considerando todos.
- **FR-016**: A edição de partida na API MUST aceitar só as situações agendada, confirmada e cancelada.

**Sorteio**

- **FR-017**: Sortear MUST ser recusado em partida iniciada, finalizada ou cancelada, sem apagar times nem eventos.
- **FR-018**: O sorteio MUST deixar os times com diferença de no máximo 1 jogador e, respeitado isso, equilibrar a soma de nível.
- **FR-019**: O sorteio MUST NOT criar time vazio quando houver menos confirmados que times previstos.
- **FR-020**: O perfil no site MUST permitir informar apelido, posição preferida e nível, com os mesmos valores aceitos pela API.

**Dinheiro**

- **FR-021**: A soma das parcelas de um rateio MUST ser igual ao valor da despesa, ao centavo, no site e na API; os centavos restantes ficam com os primeiros a confirmar.
- **FR-022**: Valores em dinheiro MUST ser calculados em centavos inteiros, nunca em ponto flutuante.
- **FR-023**: Despesa rateada MUST exigir partida da turma com pelo menos um confirmado.
- **FR-024**: Quem organiza MUST poder refazer o rateio com os confirmados atuais, mantendo o que já foi pago e a soma igual ao total; a tela MUST avisar quando os confirmados mudaram.
- **FR-025**: Alterar o valor de despesa com parcelas MUST refazer o rateio, e valor abaixo do já pago MUST ser recusado.
- **FR-026**: A baixa MUST somar ao que já foi pago, recusar valor acima do saldo da parcela e recusar parcela de despesa cancelada ou fechada.
- **FR-027**: A despesa MUST fechar sozinha quando todas as parcelas estiverem pagas; despesa não rateada MUST poder ser marcada como paga por quem organiza.
- **FR-028**: Remover despesa com pagamento MUST ser recusado; cancelar MUST manter o que foi pago e encerrar as parcelas em aberto; despesa de partida cancelada MUST mostrar o aviso.
- **FR-029**: Toda mudança em despesa ou pagamento MUST registrar quem fez, o quê e quando, visível para quem organiza.
- **FR-030**: Quem organiza MUST poder registrar a baixa da parcela de quem não é mais membro ativo; de membro ativo, só a própria pessoa dá baixa.

**Conta**

- **FR-031**: Excluir a conta MUST remover nome, e-mail, telefone, data de nascimento, foto, posição, nível e preferências, encerrar os acessos e impedir novo login, mantendo os registros da turma sob o nome "Conta excluída".
- **FR-032**: Quem criou turma com outros membros ativos MUST ser recusado ao excluir a conta, com a turma citada e a orientação de arquivar; turma em que está sozinho MUST ser arquivada junto.
- **FR-033**: Excluir a conta MUST tirar os campos da pessoa do catálogo, e o e-mail MUST ficar livre para novo cadastro.

**Turma arquivada e validação**

- **FR-034**: As telas da partida de turma arquivada MUST abrir só para leitura, com aviso, sem erro.
- **FR-035**: Quem criou a turma MUST ver as arquivadas e poder restaurar, no site e na API; os demais membros não.
- **FR-036**: Ações de vaga, sorteio e placar em turma arquivada MUST ser recusadas para todos, inclusive o administrador da plataforma.
- **FR-037**: Mensagem do mural ligada a partida de outra turma MUST ser recusada, no site e na API.
- **FR-038**: Mensalidade em branco MUST virar zero; valores de mensalidade, despesa e baixa acima do teto MUST ser recusados com mensagem clara, sem erro 500.
- **FR-039**: Site e API MUST aplicar as mesmas regras desta frente, num ponto único do código.

### Key Entities

- **Confirmação**: a resposta de uma pessoa a uma partida; titular, fila com posição ou recusa.
- **Partida**: passa por agendada, em andamento, pausada e finalizada, ou cancelada antes de começar.
- **Despesa**: valor da turma, opcionalmente dividido entre os confirmados de uma partida; aberta, fechada ou cancelada.
- **Parcela (pagamento)**: quanto cada pessoa deve e quanto já pagou; pendente, parcial, paga ou cancelada.
- **Histórico financeiro**: o registro de quem fez o quê em despesa e parcela.
- **Conta excluída**: o registro da pessoa sem nenhum dado pessoal, mantido para os totais da turma baterem.

### Premissas a confirmar com o Felipe

- O rateio não muda sozinho quando a lista de confirmados muda: o organizador vê o aviso e refaz, para ninguém ver o próprio valor mudar sem saber.
- Cancelar a partida não cancela a despesa: o aluguel do campo pode ter sido cobrado mesmo assim. O organizador decide.
- Quem criou turma com outros membros precisa arquivá-la antes de excluir a conta, porque a criação não é transferida (regra da Etapa 3).
- As mensagens de quem excluiu a conta ficam no mural sob "Conta excluída".
- Placar fechado depois de finalizar: gol esquecido não entra mais.
- Teto de R$ 999.999,99 por despesa e de R$ 99.999,99 de mensalidade.
- Organizador dá baixa só de quem não é mais membro ativo; o membro ativo dá a própria baixa.

### Fora do escopo

- Pagamento real por PIX ou cartão: o sistema só registra.
- Aviso por e-mail quando a fila anda ou o rateio muda.
- Português das demais telas, LGPD completa e limpeza: Etapa 5.
- App Flutter.

## Success Criteria

### Measurable Outcomes

- **SC-001**: Em 100% dos rateios gerados, a soma das parcelas é igual ao valor da despesa, ao centavo.
- **SC-002**: Com 5 confirmações simultâneas para 1 vaga, exatamente 1 pessoa fica titular.
- **SC-003**: Repetir iniciar, pausar, finalizar ou confirmar 3 vezes seguidas deixa placar, tempo e lista iguais aos do primeiro pedido.
- **SC-004**: Em 100 sorteios com combinações variadas, a diferença entre os times nunca passa de 1 jogador.
- **SC-005**: Nenhuma ação desta frente responde erro 500 nas verificações de ponta a ponta.
- **SC-006**: Depois de excluir a conta, nenhum dado pessoal da pessoa aparece em tela, resposta da API ou registro da pessoa no banco.

## Delta Requirements

**Skip**: o corpus `docs/specs/current/` ainda não existe neste projeto; as mudanças de comportamento estão nos FRs acima — Claude, 2026-11-05
