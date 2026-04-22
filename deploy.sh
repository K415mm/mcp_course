#!/bin/bash
##############################################################
# TUNAI LMS — Production Deploy Script
# Real production runtime:
#   - Docker Compose project: /home/dluser/lms
#   - Git repo: /home/dluser/lms/mcp_course
#   - Laravel app mount: /home/dluser/lms/mcp_course/webapp -> /var/www/html
#
# Remote usage:
#   bash /home/dluser/lms/mcp_course/deploy.sh
#
# Via local SSH helper:
#   python s:/tunai/ssh_worker.py "bash /home/dluser/lms/mcp_course/deploy.sh"
##############################################################

set -euo pipefail

STACK_ROOT=/home/dluser/lms
APP_ROOT=$STACK_ROOT/mcp_course
COMPOSE="docker compose"

echo "Starting deploy at $(date)"

echo "Pulling latest code..."
git -C "$APP_ROOT" pull origin main

echo "Checking compose stack..."
cd "$STACK_ROOT"
$COMPOSE ps >/dev/null

echo "Installing Composer dependencies in app container..."
$COMPOSE exec -T app composer install --no-dev --optimize-autoloader --no-interaction

echo "Running Laravel migrations..."
$COMPOSE exec -T app php artisan migrate --force

echo "Clearing and rebuilding Laravel caches..."
$COMPOSE exec -T app php artisan optimize:clear
$COMPOSE exec -T app php artisan config:cache
$COMPOSE exec -T app php artisan route:cache
$COMPOSE exec -T app php artisan view:cache
$COMPOSE exec -T app php artisan event:cache

echo "Restarting queue workers and PHP containers..."
$COMPOSE exec -T app php artisan queue:restart
$COMPOSE restart app worker scheduler

echo "Refreshing nginx upstream connections..."
$COMPOSE restart nginx

echo "Current container status:"
$COMPOSE ps

echo "Deploy complete at $(date)"
