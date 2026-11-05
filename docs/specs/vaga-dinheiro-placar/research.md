# Research: Vaga, dinheiro e placar

## Decision 1: Trava por partida na confirmação

**Decision**: transação com `SELECT ... FOR UPDATE` na linha da partida, antes de contar as vagas.
**Rationale**: a constitution III exige que as vagas não sejam ultrapassadas com pedidos simultâneos; a trava vale entre processos e servidores, e só serializa pedidos da mesma partida.
**Alternatives considered**: contador otimista, que obriga a repetir o pedido; trava em cache, que depende de configuração que a imagem não tem.

## Decision 2: Placar derivado dos eventos

**Decision**: gols do time, gols do jogador e `placar_a`/`placar_b` são recalculados a partir de `eventos_partida` a cada gol registrado ou removido.
**Rationale**: o código incrementava e decrementava em três lugares; remover um gol não corrigia a artilharia.
**Alternatives considered**: manter os incrementos e corrigir cada caminho, que deixa a divergência possível.

## Decision 3: Todos os times no placar

**Decision**: `placar_a` e `placar_b` continuam para os dois primeiros times, por compatibilidade com o app; as telas usam a lista `times`, que já traz os gols.
**Rationale**: até 6 times são permitidos por turma; criar colunas novas não resolve e a lista já existe na resposta.
**Alternatives considered**: tabela de confrontos entre times, que é outra funcionalidade.

## Decision 4: Dinheiro em centavos

**Decision**: `App\Support\Dinheiro` converte o decimal validado para inteiro por texto, sem ponto flutuante, e divide em inteiros, dando o centavo restante aos primeiros.
**Rationale**: constitution III. `bcmath` não está garantida na imagem; aritmética inteira resolve sem dependência.
**Alternatives considered**: `round()` em float, origem do defeito de R$ 99,99.

## Decision 5: Rateio refeito pelo organizador

**Decision**: o rateio é uma foto dos confirmados. Quando a lista muda, a tela avisa e o organizador refaz. Ao refazer, parcelas pagas acima da nova cota ficam com o valor pago, e o restante é dividido entre os demais.
**Rationale**: "sem surpresa": ninguém vê o próprio valor mudar sem ação de quem organiza. A soma segue igual ao total em qualquer combinação.
**Alternatives considered**: recálculo automático a cada confirmação, que muda a dívida de todos sem aviso e exige notificação, fora do escopo.

## Decision 6: Histórico financeiro

**Decision**: tabela `movimentos_financeiros`, só de inclusão, gravada pelo serviço na mesma transação da mudança.
**Rationale**: constitution III (RNF21). Fica no serviço porque toda mudança já passa por ele.
**Alternatives considered**: eventos de modelo, que não têm quem fez a ação.

## Decision 7: Excluir conta por anonimização

**Decision**: o registro em `users` fica, sem nenhum dado pessoal, com `excluida_em` preenchido, e-mail técnico `.invalid` e senha aleatória.
**Rationale**: `despesas.criada_por_id` e `partidas.organizador_id` não têm cascata (erro 500), `patotas.criador_id` tem (apaga a turma de todos) e parcelas apagadas quebrariam a soma do rateio.
**Alternatives considered**: mudar as chaves estrangeiras para `SET NULL`, que deixa telas sem autor e parcelas sem dono, e não resolve a soma.

## Decision 8: Turma arquivada

**Decision**: as relações `Partida::patota` e `Despesa::patota` passam a incluir arquivadas; as Policies de ação e os serviços recusam, e as de leitura mantêm o acesso.
**Rationale**: é a relação nula que derrubava as telas; o administrador passa pelo `before()` das Policies, por isso a recusa também fica nos serviços.
**Alternatives considered**: esconder a partida com 404, que perde o histórico.
