#!/bin/sh
# Inicialização do site no Render, a cada deploy e a cada vez que o serviço acorda.
set -e

php artisan migrate --force

# Popula usuário e cardápio só no banco vazio, para não desfazer preços editados pela tela.
if ! php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); exit(App\Models\User::query()->exists() ? 0 : 1);'; then
    php artisan db:seed --force
fi

# Certificado autoassinado da NFC-e simulada. O disco do plano gratuito é apagado a cada
# reinício, então ele é recriado; as notas já emitidas ficam no banco, com o XML original.
php artisan fiscal:test-certificate

php artisan config:cache
php artisan route:cache
php artisan view:cache

# --no-reload permite os workers de PHP_CLI_SERVER_WORKERS (Dockerfile.render).
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}" --no-reload
