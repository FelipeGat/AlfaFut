#!/bin/sh
set -e

cd /var/www/html

# Recusa subir com configuração insegura; o motivo sai no log do contêiner.
php artisan alfafut:checar-producao

# O volume de storage pode chegar vazio em uma instalação nova.
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
    storage/framework/views storage/logs

php artisan optimize

# O banco pode demorar a aceitar conexão. Só migrações: a imagem nunca roda seed.
tentativa=1
until php artisan migrate --force; do
    if [ "$tentativa" -ge 30 ]; then
        echo "Migrações falharam depois de $tentativa tentativas." >&2
        exit 1
    fi
    tentativa=$((tentativa + 1))
    sleep 2
done

# Acesso vencido já é recusado; isto só tira os registros antigos do banco.
php artisan sanctum:prune-expired --hours=24 || true

exec "$@"
