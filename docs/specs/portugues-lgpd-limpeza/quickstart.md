# Quickstart: Português, LGPD e limpeza

## Cenário 1: recuperação sem revelar conta
1. Peça a recuperação com e-mail com conta → 2. Peça com e-mail sem conta → **Expected**: mesma resposta, e só o primeiro recebe e-mail. 3. Na tela de nova senha, envie token errado para os dois → **Expected**: mesma mensagem.

## Cenário 2: troca de senha encerra acessos
1. Entre pela API e em dois navegadores → 2. Troque a senha em um deles → **Expected**: o token recebe 401, o outro navegador volta ao login e o atual continua. 3. Com um token de mais de 60 dias → **Expected**: 401.

## Cenário 3: privacidade
1. Abra `/privacidade` e `/termos` sem login → 2. Cadastre sem aceitar → **Expected**: recusa, sem conta criada. 3. Cadastre aceitando e baixe os dados → **Expected**: arquivo sem senha, token ou contato de outra pessoa.

## Cenário 4: produção sem responsável
1. Suba a imagem sem `ALFAFUT_RESPONSAVEL_NOME` → **Expected**: recusa subir e diz o que falta.

## Cenário 5: API em JSON
1. Chame `/api/v1/patotas` sem token e sem `Accept` → **Expected**: 401 em JSON. 2. Abra uma turma inexistente com token → **Expected**: 404 em JSON, em português, sem nome de classe.

## Cenário 6: Roundtrip End-to-End
1. Com a imagem de produção e MySQL, cadastre pela API real com `aceite_termos`, consulte `auth/eu`, crie turma e baixe os dados → 2. Compare as respostas com `docs/09-api.md` → **Expected**: mesmo formato, nenhuma resposta 500.

## Cenário 7: texto
1. Abra as telas com a varredura em navegador → **Expected**: nenhuma palavra da lista sem acento e nenhuma citação à faculdade.
