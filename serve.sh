#!/usr/bin/env bash
# Dev server with upload limits large enough for contract document uploads (up to 20 MB).
# php artisan serve does not propagate -d flags to the spawned built-in server,
# so we start the built-in server directly.
set -e
SCRIPT="$(php -r "echo realpath('vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php');")"
cd "$(dirname "$0")/public"
exec php8.4 \
  -d upload_max_filesize=20M \
  -d post_max_size=25M \
  -S "${HOST:-0.0.0.0}:${PORT:-8000}" \
  "$SCRIPT"
