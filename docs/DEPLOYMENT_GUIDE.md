# Deployment Guide

This guide provides step-by-step instructions for deploying the InfoDot Laravel 11 application to production.

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Requirements](#server-requirements)
3. [Deployment Methods](#deployment-methods)
4. [Manual Deployment](#manual-deployment)
5. [Automated Deployment (CI/CD)](#automated-deployment-cicd)
6. [Post-Deployment Tasks](#post-deployment-tasks)
7. [Rollback Procedures](#rollback-procedures)
8. [Monitoring](#monitoring)

## Pre-Deployment Checklist

### Code Preparation

- [ ] All tests passing (`php artisan test`)
- [ ] Code reviewed and approved
- [ ] Dependencies updated (`composer update`, `npm update`)
- [ ] Security audit completed
- [ ] Performance testing completed
- [ ] Database migrations tested on staging
- [ ] Backup procedures verified

### Environment Configuration

- [ ] Production `.env` file configured
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Database credentials configured
- [ ] Redis configured
- [ ] Mail server configured
- [ ] Search service configured (Meilisearch)
- [ ] File storage configured (S3)
- [ ] Broadcasting configured (Reverb)
- [ ] SSL certificates installed

### Infrastructure

- [ ] Server provisioned and configured
- [ ] Database server ready
- [ ] Redis server ready
- [ ] Search server ready (Meilisearch)
- [ ] Load balancer configured (if applicable)
- [ ] CDN configured (if applicable)
- [ ] Firewall rules configured
- [ ] Backup system configured

## Server Requirements

### Minimum Requirements

- **OS**: Ubuntu 22.04 LTS or similar
- **PHP**: 8.3 or 8.4
- **Web Server**: Nginx or Apache
- **Database**: MySQL 8.0+
- **Memory**: 2GB RAM minimum, 4GB recommended
- **Storage**: 20GB minimum
- **CPU**: 2 cores minimum

### Required PHP Extensions

```bash
php -m | grep -E 'bcmath|ctype|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml|curl|gd|zip|redis'
```

Required extensions:
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- cURL
- GD
- Zip
- Redis

### Install PHP 8.3 on Ubuntu

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP 8.3 and extensions
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common \
  php8.3-mysql php8.3-xml php8.3-curl php8.3-gd \
  php8.3-mbstring php8.3-zip php8.3-bcmath \
  php8.3-redis php8.3-intl
```

### Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### Install MySQL

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### Install Redis

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### Install Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

## Deployment Methods

### Method 1: Manual Deployment

Best for: Small deployments, initial setup

### Method 2: Git-Based Deployment

Best for: Medium deployments, version control integration

### Method 3: CI/CD Pipeline

Best for: Large deployments, automated testing and deployment

## Manual Deployment

### Step 1: Prepare Server

```bash
# Create application directory
sudo mkdir -p /var/www/infodot
sudo chown -R $USER:$USER /var/www/infodot

# Clone repository
cd /var/www
git clone <repository-url> infodot
cd infodot
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm ci

# Build frontend assets
npm run build
```

### Step 3: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env

# Generate application key
php artisan key:generate
```

### Step 4: Set Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/infodot

# Set directory permissions
sudo find /var/www/infodot -type d -exec chmod 755 {} \;
sudo find /var/www/infodot -type f -exec chmod 644 {} \;

# Set storage and cache permissions
sudo chmod -R 775 /var/www/infodot/storage
sudo chmod -R 775 /var/www/infodot/bootstrap/cache
```

### Step 5: Configure Nginx

Create `/etc/nginx/sites-available/infodot`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name infodot.com www.infodot.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name infodot.com www.infodot.com;
    root /var/www/infodot/public;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/infodot.crt;
    ssl_certificate_key /etc/ssl/private/infodot.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    index index.php;

    charset utf-8;

    # Logging
    access_log /var/log/nginx/infodot-access.log;
    error_log /var/log/nginx/infodot-error.log;

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
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # WebSocket support for Reverb
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/infodot /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 6: Run Migrations

```bash
cd /var/www/infodot

# Run migrations
php artisan migrate --force

# Import search indexes
php artisan scout:import "App\Models\Question"
php artisan scout:import "App\Models\Solution"
php artisan scout:import "App\Models\User"

# Create storage link
php artisan storage:link
```

### Step 7: Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

### Step 8: Configure Supervisor

Create `/etc/supervisor/conf.d/infodot-worker.conf`:

```ini
[program:infodot-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/infodot/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/infodot/storage/logs/worker.log
stopwaitsecs=3600
```

Create `/etc/supervisor/conf.d/infodot-reverb.conf`:

```ini
[program:infodot-reverb]
process_name=%(program_name)s
command=php /var/www/infodot/artisan reverb:start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/infodot/storage/logs/reverb.log
```

Reload Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start infodot-worker:*
sudo supervisorctl start infodot-reverb
```

### Step 9: Configure Cron

Add to crontab:

```bash
sudo crontab -e -u www-data
```

Add line:

```cron
* * * * * cd /var/www/infodot && php artisan schedule:run >> /dev/null 2>&1
```

### Step 10: Verify Deployment

```bash
# Check application status
curl -I https://infodot.com

# Check queue workers
sudo supervisorctl status infodot-worker:*

# Check Reverb server
sudo supervisorctl status infodot-reverb

# Check logs
tail -f /var/www/infodot/storage/logs/laravel.log
```

## Automated Deployment (CI/CD)

### GitHub Actions

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.3'
        extensions: mbstring, xml, ctype, json, bcmath, pdo, mysql, redis
    
    - name: Install Composer Dependencies
      run: composer install --optimize-autoloader --no-dev
    
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '20'
    
    - name: Install NPM Dependencies
      run: npm ci
    
    - name: Build Assets
      run: npm run build
    
    - name: Run Tests
      run: php artisan test
    
    - name: Deploy to Server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        script: |
          cd /var/www/infodot
          git pull origin main
          composer install --optimize-autoloader --no-dev
          npm ci
          npm run build
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          php artisan queue:restart
          sudo supervisorctl restart infodot-reverb
```

### GitLab CI

Create `.gitlab-ci.yml`:

```yaml
stages:
  - test
  - build
  - deploy

variables:
  MYSQL_DATABASE: infodot_test
  MYSQL_ROOT_PASSWORD: secret

test:
  stage: test
  image: php:8.3
  services:
    - mysql:8.0
  before_script:
    - apt-get update -qq && apt-get install -y -qq git unzip
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install
  script:
    - php artisan test

build:
  stage: build
  image: node:20
  script:
    - npm ci
    - npm run build
  artifacts:
    paths:
      - public/build

deploy:
  stage: deploy
  only:
    - main
  before_script:
    - 'which ssh-agent || ( apt-get update -y && apt-get install openssh-client -y )'
    - eval $(ssh-agent -s)
    - echo "$SSH_PRIVATE_KEY" | tr -d '\r' | ssh-add -
    - mkdir -p ~/.ssh
    - chmod 700 ~/.ssh
  script:
    - ssh $SERVER_USER@$SERVER_HOST "cd /var/www/infodot && ./deploy.sh"
```

### Deployment Script

Create `deploy.sh` in project root:

```bash
#!/bin/bash

set -e

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
php artisan queue:restart
sudo supervisorctl restart infodot-reverb

# Import search indexes
php artisan scout:import "App\Models\Question"
php artisan scout:import "App\Models\Solution"
php artisan scout:import "App\Models\User"

echo "Deployment completed successfully!"
```

Make executable:

```bash
chmod +x deploy.sh
```

## Post-Deployment Tasks

### 1. Verify Application

```bash
# Check homepage
curl -I https://infodot.com

# Check API
curl -H "Authorization: Bearer token" https://infodot.com/api/user

# Check WebSocket
# Use browser console: new WebSocket('wss://infodot.com/app')
```

### 2. Monitor Logs

```bash
# Application logs
tail -f /var/www/infodot/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/infodot-access.log

# Nginx error logs
tail -f /var/log/nginx/infodot-error.log

# Queue worker logs
tail -f /var/www/infodot/storage/logs/worker.log

# Reverb logs
tail -f /var/www/infodot/storage/logs/reverb.log
```

### 3. Performance Testing

```bash
# Test page load time
curl -w "@curl-format.txt" -o /dev/null -s https://infodot.com

# Load testing with Apache Bench
ab -n 1000 -c 10 https://infodot.com/
```

### 4. Security Scan

```bash
# Check SSL configuration
openssl s_client -connect infodot.com:443

# Security headers check
curl -I https://infodot.com | grep -E 'X-Frame-Options|X-Content-Type-Options|X-XSS-Protection'
```

## Rollback Procedures

### Quick Rollback

```bash
# Stop services
sudo supervisorctl stop infodot-worker:*
sudo supervisorctl stop infodot-reverb

# Revert to previous version
cd /var/www/infodot
git reset --hard HEAD~1

# Reinstall dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Rollback migrations (if needed)
php artisan migrate:rollback

# Clear caches
php artisan optimize:clear

# Restart services
sudo supervisorctl start infodot-worker:*
sudo supervisorctl start infodot-reverb
```

### Database Rollback

```bash
# Restore from backup
mysql -u root -p infodot < /backups/infodot_backup_YYYYMMDD.sql

# Verify data
php artisan tinker
>>> User::count();
>>> Question::count();
```

## Monitoring

### Application Monitoring

**Laravel Telescope** (Development/Staging):
- Access: `https://infodot.com/telescope`
- Monitor requests, queries, jobs, exceptions

**Laravel Horizon** (Queue Monitoring):
- Access: `https://infodot.com/horizon`
- Monitor queue jobs, failed jobs, throughput

### Server Monitoring

**System Resources**:
```bash
# CPU and memory
htop

# Disk usage
df -h

# Network connections
netstat -an | grep :80
```

**Service Status**:
```bash
# Check all services
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
sudo systemctl status mysql
sudo systemctl status redis-server
sudo supervisorctl status
```

### Log Monitoring

**Centralized Logging** (Optional):
- Use services like Papertrail, Loggly, or ELK stack
- Configure in `config/logging.php`

**Error Tracking** (Optional):
- Use services like Sentry, Bugsnag, or Rollbar
- Install package: `composer require sentry/sentry-laravel`

## Troubleshooting

### Common Issues

**500 Internal Server Error**:
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Clear caches
php artisan optimize:clear
```

**Queue Jobs Not Processing**:
```bash
# Check worker status
sudo supervisorctl status infodot-worker:*

# Restart workers
sudo supervisorctl restart infodot-worker:*

# Check failed jobs
php artisan queue:failed
```

**WebSocket Connection Failed**:
```bash
# Check Reverb status
sudo supervisorctl status infodot-reverb

# Restart Reverb
sudo supervisorctl restart infodot-reverb

# Check firewall
sudo ufw status
sudo ufw allow 8080
```

**Database Connection Error**:
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql

# Check credentials in .env
```

## Security Checklist

- [ ] HTTPS enabled with valid SSL certificate
- [ ] `APP_DEBUG=false` in production
- [ ] Strong database passwords
- [ ] Firewall configured (UFW or iptables)
- [ ] SSH key authentication only
- [ ] Regular security updates
- [ ] Rate limiting enabled
- [ ] CSRF protection enabled
- [ ] XSS protection enabled
- [ ] SQL injection protection (using Eloquent)
- [ ] File upload validation
- [ ] Security headers configured
- [ ] Regular backups configured
- [ ] Monitoring and alerting configured

---

**Last Updated**: January 15, 2026
