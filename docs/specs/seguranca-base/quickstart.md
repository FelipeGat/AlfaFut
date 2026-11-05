# Quickstart: Base segura para o teste com pessoas reais

## Scenario 1: Isolamento entre turmas

1. Com dois usuários em turmas diferentes, o da turma B chama a API de despesas e placar da turma A, e abre as telas de TV e de resultado.
2. **Expected**: 403 em todas; `sondar.py` sem nenhum 200; despesa intacta.

## Scenario 2: Membro não vê contato

1. Como membro comum, `GET /api/v1/patotas/{id}/membros`.
2. Repetir como organizador.
3. **Expected**: membro sem `email`, `telefone`, `necessidades`; organizador com os três.

## Scenario 3: Limite de tentativas

1. Errar a senha 6 vezes em menos de 1 minuto, pela API e pelo site.
2. Na mesma rede, entrar com outro usuário.
3. **Expected**: 6ª tentativa recusada em português, com tempo de espera; outro usuário entra.

## Scenario 4: Conta nova

1. Criar conta pelo site, com a caixa de e-mail de desenvolvimento ligada.
2. Tentar abrir `/dashboard`.
3. Abrir o link recebido.
4. **Expected**: passo 2 leva à tela de confirmação; depois do passo 3 entra no painel.

## Scenario 5: Aparelho compartilhado

1. Em navegador real, entrar, abrir uma turma e sair.
2. Desligar a rede e reabrir a URL da turma.
3. **Expected**: página de "sem conexão"; nenhum `alfafut-v1-*` em `caches`, nenhuma página autenticada em cache.

## Scenario 6: Boot inseguro

1. Subir a imagem com `APP_DEBUG=true`, depois com `MAIL_MAILER=log`, depois sem `APP_KEY`.
2. **Expected**: o contêiner encerra com o motivo no log, nas três vezes.

## Scenario 7: Imagem de produção

1. `docker compose --env-file .env.homolog -f docker-compose.homolog.yml up -d --build` a partir de uma cópia limpa, com `.env.homolog` gerado como no README.
2. Conferir banco vazio de usuários; `alfafut:criar-admin`; criar uma conta; recriar o contêiner da aplicação.
3. `docker run --rm --entrypoint sh <imagem> -c 'ls -a /var/www/html'`.
4. **Expected**: 0 usuários no início; conta presente depois de recriar; sem `.env`, `.git`, `docs`, `database/*.sqlite`.

## Scenario 8: Roundtrip End-to-End

1. Atrás do nginx da imagem, percorrer em navegador: cadastro, confirmação, criar turma, membros.
2. Chamar a API real com o token da conta e comparar a resposta com [contracts/api.md](contracts/api.md).
3. **Expected**: campos iguais ao contrato, e as telas usam o mesmo payload real, sem mock.
