# AlfaFut

Sistema para organizar a pelada: turma, partida, presença e lista de espera, sorteio de times, placar ao vivo, rateio de despesas, mural e catálogo de campos, com preferências de acessibilidade por pessoa.

Em fase de teste. Roda no navegador, pode ser instalado como aplicativo (PWA) e tem API REST v1. O app Flutter fica no repositório `AlfaFutApp`.

## Stack

- PHP 8.3 e Laravel 12, com Sanctum
- Blade, Tailwind 3, Alpine 3 e Vite 7
- MySQL 8.4 em produção e SQLite nos testes
- Imagem Docker com nginx e PHP-FPM

## Testes

Sem PHP na máquina, com Docker:

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD":/app -w /app php:8.3-cli-alpine vendor/bin/phpunit
```

## Rodar como em produção

A imagem (`Dockerfile`) roda nginx + PHP-FPM sob supervisor, sem root, e escuta na porta 8080. O banco é MySQL externo, e `storage` fica em volume. Ao subir, ela confere a configuração (`alfafut:checar-producao`), roda as migrações e nunca roda seed.

Variáveis obrigatórias: `APP_KEY`, `APP_URL`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_FROM_ADDRESS`, `ALFAFUT_RESPONSAVEL_NOME` e `ALFAFUT_CONTATO_PRIVACIDADE`. Com HTTPS, acrescente `SESSION_SECURE_COOKIE=true` e, atrás de proxy, `TRUSTED_PROXIES`. `ALFAFUT_RESPONSAVEL_DOCUMENTO` é opcional e aparece na política de privacidade.

Para reproduzir nesta máquina, com caixa de e-mail de desenvolvimento:

```bash
printf 'APP_KEY=base64:%s\nDB_PASSWORD=%s\n' "$(openssl rand -base64 32)" "$(openssl rand -hex 24)" > .env.homolog
docker compose --env-file .env.homolog -f docker-compose.homolog.yml up -d --build
docker exec -it alfafut-homolog-app php artisan alfafut:criar-admin voce@exemplo.com
```

Sistema em `http://localhost:8100` e e-mails recebidos em `http://localhost:8099`.

## Dados de demonstração

`php artisan db:seed` cria contas e turmas fictícias, com senha gerada na hora e mostrada no terminal. O seed é recusado em produção.

## Documentação

- [`docs/briefing.md`](docs/briefing.md) e [`docs/constitution.md`](docs/constitution.md): visão e princípios do projeto
- [`docs/specs/`](docs/specs): especificação, plano e tarefas de cada frente
- [`docs/09-api.md`](docs/09-api.md): API REST
- `docs/01` a `docs/11`: documentos de pesquisa e de projeto da versão original

## Licença

MIT.
