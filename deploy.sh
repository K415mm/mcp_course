#!/bin/bash
##############################################################
# TUNAI LMS — Production Deploy Script
# Usage: ssh root@134.199.186.91 "bash /var/www/mcp_course/deploy.sh"
# Or from local: ssh -i ~/.ssh/tunai_do root@134.199.186.91 "bash /var/www/mcp_course/deploy.sh"
##############################################################

set -e
APP_ROOT=/var/www/mcp_course
WEBAPP=$APP_ROOT/webapp

echo "🚀 Starting deploy at $(date)"

# 1. Pull latest code
echo "📥 Pulling from GitHub..."
cd $APP_ROOT
git pull origin main

# 2. PHP dependencies
echo "📦 Installing Composer packages..."
cd $WEBAPP
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# 4. Clear and rebuild caches
echo "🔄 Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Fix storage permissions
echo "🔐 Fixing permissions..."
chown -R www-data:www-data $WEBAPP/storage $WEBAPP/bootstrap/cache
chmod -R 775 $WEBAPP/storage $WEBAPP/bootstrap/cache

# 6. Restart queue workers
echo "⚙️  Restarting queue workers..."
php artisan queue:restart
supervisorctl restart laravel-worker:* 2>/dev/null || echo "Supervisor not found, skipping"

# 7. Reload PHP-FPM
echo "🔃 Reloading PHP-FPM..."
systemctl reload php8.3-fpm

echo "✅ Deploy complete at $(date)"
