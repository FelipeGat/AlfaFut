# Quickstart: Fluxo da turma pelo site

```bash
cd ~/projetos/alfafut
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli-alpine vendor/bin/phpunit
```

## Cenário 1: organizador novo marca a primeira partida

1. Criar conta como dono da pelada → 2. Criar turma → 3. Em "Locais", cadastrar um local → 4. Em "Nova partida", escolher o local, com data futura → **Expected**: a partida aparece em "Próximas", no horário digitado, com o local.

## Cenário 2: jogador entra, confirma e sai

1. Com outra conta, digitar o código em minúsculas no painel → 2. Abrir a partida e confirmar → 3. Sair da turma → **Expected**: entra como membro, a presença é confirmada, depois a turma some das listas e o endereço dela dá 403.

## Cenário 3: erros

1. Marcar partida no passado → **Expected**: erro em português junto ao campo. 2. Digitar código errado → **Expected**: "código não encontrado". 3. Membro comum abrir `patotas/{id}/membros` → **Expected**: 403.

## Cenário 4: membros

1. Criador torna um membro organizador → 2. Remove esse membro → 3. Ele tenta voltar com o código → **Expected**: recusado até o criador liberar a volta.

## Cenário 5: Roundtrip End-to-End

Atrás do nginx da imagem, com MySQL (`docker-compose.homolog.yml` e `.demos/alfafut/roundtrip.mjs`): cumprir os cenários 1 a 4 no navegador e chamar `GET /api/v1/partidas/{id}` com o token de um membro. **Expected**: a resposta real traz `campo` ou `local` no formato de `contracts/rotas.md` e `data_hora` com `-03:00`.
