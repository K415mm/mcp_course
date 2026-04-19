# TUNAI LMS — Server Architecture Documentation
> Extracted from live DigitalOcean droplet: 134.199.186.91  
> Date: 2026-04-19  
> Use this as migration template for new servers.

---

## Server Overview

| Property | Value |
|---|---|
| **Provider** | DigitalOcean |
| **IP** | 134.199.186.91 |
| **OS** | Ubuntu 24.04.4 LTS (Noble Numbat) |
| **CPU** | 4 vCPUs |
| **RAM** | 8 GB (7.8 GB available) |
| **Disk** | 60 GB SSD (5.1 GB used / 53 GB free) |
| **Swap** | None (consider adding 2GB) |
| **SSH User** | root |

---

## Directory Structure

```
/var/www/mcp_course/        ← Project root
  ├── webapp/               ← Laravel 12 application
  │   ├── app/              ← PHP controllers, models, services
  │   ├── bootstrap/
  │   ├── config/
  │   ├── database/         ← Migrations, seeders, factories
  │   ├── docs/
  │   ├── public/           ← Web root (served by Nginx)
  │   ├── resources/        ← Blade views, CSS (Tailwind), JS
  │   ├── routes/           ← web.php, console.php
  │   ├── storage/          ← Logs, uploaded files (owned by www-data)
  │   ├── vendor/           ← Composer packages (NOT in Git)
  │   ├── .env              ← Production secrets (NOT in Git)
  │   ├── composer.json
  │   ├── package.json
  │   └── vite.config.js
  └── corse/                ← Course content (.md files)
      ├── Course_Flow_Strategy.md
      ├── Demo_Course/
      └── RGSOC_Academy/
```

---

## Software Stack

### Web Server — Nginx 1.24.0

- **Config:** `/etc/nginx/sites-enabled/mcp_course`
- **Runs as:** `www-data`
- **Web root:** `/var/www/mcp_course/webapp/public`
- **SSL:** Let's Encrypt (Certbot, ECDSA key)
- **SSL cert expiry:** 2026-06-11 (auto-renews via Certbot)
- **Domains:** `tunai.cloud`, `www.tunai.cloud`
- **Security:** Defensia bot protection (`$defensia_blocked_ua`)

#### Nginx Site Config (`/etc/nginx/sites-enabled/mcp_course`)
```nginx
server {
    if ($defensia_blocked_ua) { return 444; }
    server_name tunai.cloud www.tunai.cloud;
    root /var/www/mcp_course/webapp/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    listen 443 ssl;
    ssl_certificate /etc/letsencrypt/live/tunai.cloud/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tunai.cloud/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
}

server {
    # HTTP → HTTPS redirect
    if ($host = tunai.cloud) { return 301 https://$host$request_uri; }
    if ($host = www.tunai.cloud) { return 301 https://$host$request_uri; }
    listen 80;
    server_name tunai.cloud www.tunai.cloud;
    return 404;
}
```

---

### PHP — 8.3.6 (php8.3-fpm)

- **Socket:** `/var/run/php/php8.3-fpm.sock`
- **Config:** `/etc/php/8.3/fpm/`
- **Runs as:** `www-data`

#### PHP-FPM Pool Settings (`/etc/php/8.3/fpm/pool.d/www.conf`)
```ini
user = www-data
group = www-data
listen = /run/php/php8.3-fpm.sock
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
```

#### PHP INI Settings (production)
```ini
max_execution_time = 30
memory_limit = 128M
post_max_size = 8M
upload_max_filesize = 2M
opcache.enable = On
```

> [!WARNING]
> `upload_max_filesize = 2M` is very low for an LMS with media uploads. Increase to at least 64M for video/file support.

#### Installed PHP Modules
`bcmath`, `calendar`, `curl`, `dom`, `exif`, `fileinfo`, `hash`, `iconv`, `json`, `mbstring`, `mysqli`, `mysqlnd`, `openssl`, `pcntl`, `PDO`, `pdo_mysql`, `Phar`, `posix`, `redis` (not currently installed), `session`, `sockets`, `sodium`, `xml`, `xmlreader`, `xmlwriter`, `xsl`, `zip`, `zlib`, `Zend OPcache`

---

### MySQL — 8.0.45

- **Socket auth:** root uses `auth_socket` (no password, only SSH access)
- **App user:** `laraveluser@localhost`
- **App database:** `mcp_course`
- **Charset:** utf8mb4

#### MySQL Users
| User | Host | Auth Plugin |
|---|---|---|
| `root` | localhost | auth_socket (no password, SSH only) |
| `laraveluser` | localhost | caching_sha2_password |
| `debian-sys-maint` | localhost | caching_sha2_password |

#### Database Tables (mcp_course)
```
cache                   game_card_plays         module_completions
cache_locks             game_cards              notes
class_course_enrollments game_players           password_reset_tokens
class_user              game_rounds             quizzes
classes                 game_sessions           sessions
course_enrollments      game_team_cards         settings
courses                 game_teams              users (83 users)
diagrams                invitations             
failed_jobs             job_batches             
jobs                    lesson_progress         
media_library           migrations              
```

**Total users in production: 83**

---

### SSL — Let's Encrypt (Certbot)

- **Certificate:** ECDSA
- **Domains:** `tunai.cloud`, `www.tunai.cloud`
- **Expiry:** 2026-06-11 (auto-renews)
- **Cert path:** `/etc/letsencrypt/live/tunai.cloud/fullchain.pem`
- **Key path:** `/etc/letsencrypt/live/tunai.cloud/privkey.pem`

---

### Node.js / npm

> [!WARNING]
> **Node.js is NOT installed** on the server. The production build was run locally then committed, or run once and the compiled assets are served statically. When deploying code changes, Node must be installed to rebuild Vite assets, or assets must be built locally and pushed to the server.

**Recommended fix for deployment:** Install Node via NVM on the droplet:
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.0/install.sh | bash
source ~/.bashrc
nvm install 20
node --version  # v20.x.x
```

---

### Queue / Background Jobs

> [!WARNING]
> **No queue worker service is running** (no Supervisor, no systemd service for Laravel queue). The app uses `QUEUE_CONNECTION=database`, but no worker process is consuming the queue. Jobs will pile up and never execute.

**Recommended fix:** Install Supervisor and configure a queue worker:
```bash
apt install supervisor -y
```

Create `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mcp_course/webapp/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/mcp_course/webapp/storage/logs/worker.log
stopwaitsecs=3600
```

---

### Firewall (UFW)

> [!WARNING]
> **UFW firewall is INACTIVE**. The server has no firewall rules enabled. All ports are openly accessible.

**Recommended fix:**
```bash
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw enable
ufw status
```

---

### Security Notes

| Item | Status |
|---|---|
| Firewall (UFW) | ❌ Inactive |
| SSL/HTTPS | ✅ Active (Let's Encrypt) |
| Queue worker | ❌ Not running |
| Defensia bot protection | ✅ Active in Nginx |
| HTTP → HTTPS redirect | ✅ Configured |
| Node.js | ❌ Not installed |
| Swap | ❌ Not configured |

---

## Environment Variables (Production)

See `docs/server/env.production.example` for the structure.  
Real values are stored only on the server at `/var/www/mcp_course/webapp/.env`.

Key settings:
- `APP_ENV=local` ← *Note: should be `production` on server*
- `APP_DEBUG=false` ← Correct for production
- `DB_CONNECTION=mysql`
- `DB_DATABASE=mcp_course`
- `DB_USERNAME=laraveluser`
- `SESSION_DRIVER=database`
- `QUEUE_CONNECTION=database`
- `CACHE_STORE=database`
- `MAIL_MAILER=resend`

---

## Migration Checklist (New Server Setup)

Use this checklist when migrating to a new server or setting up from scratch:

### 1. System Setup
- [ ] Ubuntu 24.04 LTS
- [ ] Update: `apt update && apt upgrade -y`
- [ ] Install git: `apt install git -y`

### 2. PHP 8.3 + Extensions
```bash
apt install php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-intl \
    php8.3-opcache php8.3-redis -y
```

### 3. Composer
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

### 4. Node.js 20 via NVM
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.0/install.sh | bash
source ~/.bashrc
nvm install 20
```

### 5. Nginx 1.24
```bash
apt install nginx -y
```

### 6. MySQL 8.0
```bash
apt install mysql-server -y
mysql -e "CREATE DATABASE mcp_course CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER 'laraveluser'@'localhost' IDENTIFIED BY '<password>';"
mysql -e "GRANT ALL PRIVILEGES ON mcp_course.* TO 'laraveluser'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
```

### 7. Clone App
```bash
mkdir -p /var/www/mcp_course
cd /var/www/mcp_course
git clone https://github.com/K415mm/mcp_course.git .
```

### 8. Configure Laravel
```bash
cd /var/www/mcp_course/webapp
cp .env.example .env
# Edit .env with production values
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9. Nginx Site Config
- Copy config from `docs/server/nginx-mcp_course.conf`
- `ln -s /etc/nginx/sites-available/mcp_course /etc/nginx/sites-enabled/`
- `nginx -t && systemctl reload nginx`

### 10. SSL with Certbot
```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d tunai.cloud -d www.tunai.cloud
```

### 11. Supervisor (Queue Worker)
```bash
apt install supervisor -y
# Copy config from docs/server/supervisor-laravel-worker.conf
supervisorctl reread && supervisorctl update
supervisorctl start laravel-worker:*
```

### 12. Firewall
```bash
ufw allow 22/tcp && ufw allow 80/tcp && ufw allow 443/tcp && ufw enable
```
