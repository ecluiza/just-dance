#!/usr/bin/env bash
# Cria o projeto Laravel 13 + Filament 5 do Just Dance de Pobre.
# Uso:  bash instalar.sh [pasta-de-destino]
# Precisa de: PHP 8.3+ (com pdo_sqlite, intl, zip, mbstring, gd) e Composer.
set -euo pipefail

DESTINO="${1:-just-dance}"
AQUI="$(cd "$(dirname "$0")" && pwd)"

echo "==> Criando o projeto Laravel em ./$DESTINO"
composer create-project laravel/laravel "$DESTINO" --no-interaction
cd "$DESTINO"

echo "==> Instalando o Filament 5"
composer require filament/filament:"^5.0" --no-interaction
php artisan filament:install --panels --no-interaction   # cria o painel "admin" em /admin

echo "==> Copiando os arquivos do jogo e do painel"
for pasta in app database resources routes; do
  cp -R "$AQUI/$pasta/." "$pasta/"
done

echo "==> Ajustando o .env (português, usuário do painel)"
php -r '
$env = file_get_contents(".env");
$valores = [
    "APP_NAME" => "\"Just Dance de Pobre\"",
    "APP_LOCALE" => "pt_BR",
    "APP_FAKER_LOCALE" => "pt_BR",
    "ADMIN_EMAIL" => "admin@justdance.local",
    "ADMIN_PASSWORD" => bin2hex(random_bytes(6)),
];
foreach ($valores as $chave => $valor) {
    $env = preg_match("/^$chave=.*/m", $env)
        ? preg_replace("/^$chave=.*/m", "$chave=$valor", $env)
        : rtrim($env)."\n$chave=$valor\n";
}
file_put_contents(".env", $env);
'

echo "==> Criando o banco (SQLite) e o conteúdo inicial"
php artisan migrate --seed --force
php artisan storage:link

SENHA=$(grep '^ADMIN_PASSWORD=' .env | cut -d= -f2)
cat <<FIM

Pronto!
  Rodar:   cd $DESTINO && php artisan serve
  Jogo:    http://localhost:8000
  Painel:  http://localhost:8000/admin
  Login:   admin@justdance.local
  Senha:   $SENHA   (está no .env, em ADMIN_PASSWORD)

FIM
