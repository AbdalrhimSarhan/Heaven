#!/bin/sh
set -e

apk add --no-cache inotify-tools docker-cli > /dev/null 2>&1

COOLDOWN=2
LAST=0

echo "[watcher] Watching for PHP/config changes..."

inotifywait -m -r -e modify,create,move,delete \
  --include '.*\.(php|env)$' \
  /var/www/html/app \
  /var/www/html/config \
  /var/www/html/routes \
  /var/www/html/bootstrap/app.php \
  2>/dev/null |
while read path event file; do
  NOW=$(date +%s)
  if [ $((NOW - LAST)) -ge $COOLDOWN ]; then
    echo "[watcher] '$file' changed — reloading Octane and Queue Worker..."
    docker exec laravel_app1 php artisan octane:reload 2>&1 | sed 's/^/  [app1] /'
    docker exec laravel_app2 php artisan octane:reload 2>&1 | sed 's/^/  [app2] /'
    docker restart laravel_queue_worker 2>&1 | sed 's/^/  [queue] /'
    LAST=$NOW
  fi
done
