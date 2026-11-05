# Quickstart: Vaga, dinheiro e placar

## Cenário 1: fila anda
1. Partida de 4 vagas, lotada, com 2 na fila → 2. Um titular recusa → **Expected**: o 1º da fila vira titular e o outro passa à posição 1.

## Cenário 2: confirmação simultânea
1. Partida com 1 vaga → 2. 5 pedidos ao mesmo tempo, com MySQL → **Expected**: 1 titular e 4 na fila.

## Cenário 3: placar sem perda
1. Inicie e marque um gol → 2. Toque em iniciar de novo → **Expected**: 1x0 e sem evento novo. 3. Finalize e tente outro gol → **Expected**: recusa.

## Cenário 4: rateio
1. R$ 100,00 entre 3 → **Expected**: 33,34 + 33,33 + 33,33. 2. Baixa de 10,00 e de 5,00 → **Expected**: pago 15,00, parcial. 3. Baixa acima do saldo → **Expected**: recusa.

## Cenário 5: excluir conta
1. Jogador com vaga e parcela exclui a conta → **Expected**: fila anda, soma do rateio igual, nome "Conta excluída", login recusado.

## Cenário 6: Roundtrip End-to-End
1. Com a imagem de produção e MySQL, percorra pelo navegador e pela API real: confirmar, sortear, iniciar, gol, finalizar, lançar despesa, dar baixa → 2. Compare as respostas da API com `docs/09-api.md` → **Expected**: mesmo formato, nenhuma resposta 500.
