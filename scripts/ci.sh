#!/bin/sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$ROOT"

if [ ! -f .env ]; then
    cp .env.example .env
fi
touch override.env docker-compose.override.yml docker-compose.services.yml

find app -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null

TMP_DB="$(mktemp)"
rm -f "$TMP_DB"
php app/bin/migrate.php "$TMP_DB" >/dev/null
rm -f "$TMP_DB"

docker compose -f docker-compose.yml -f docker-compose.override.yml -f docker-compose.services.yml --env-file .env --env-file override.env config --quiet

echo "ci ok"
