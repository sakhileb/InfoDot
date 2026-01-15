# Staging Environment Setup Guide

This guide provides instructions for setting up a staging environment for the InfoDot Laravel 11 application.

## Purpose of Staging

The staging environment serves as a production-like environment for:
- Testing deployments before production
- User acceptance testing (UAT)
- Performance testing under production-like conditions
- Training and demonstrations
- Testing database migrations with production data

## Staging Environment Specifications

### Server Configuration

**Recommended Specifications** (should match production):
- **CPU**: Same as production
- **RAM**: Same as production
- **Storage**: Same as production
- **OS**: Same as production (Ubuntu 22.04 LTS recommended)

**Network Configuration**:
- **Domain**: staging.infodot.com
- **SSL**: Let's Encrypt certificate
- **Firewall**: Same rules as production

### Software Requirements

Same versions as production:
- PHP 8.3 or 8.4
- MySQL 8.0+
- Redis 6.0+
- Nginx 1.18+
- Node.js 20 LTS
- Composer 2.x
- Supervisor

## Setup Steps

### Step 1: Provision Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y software-properties-common

# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common \
  php8.3-mysql php8.3-xml php8.3-curl php8.3-gd \
  php8.3-mbstring php8.3-zip php8.3-bcmath \
  php8.3-redis php8.3-intl

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Install Nginx
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Supervisor
sudo apt install -y supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

### Step 2: Create Database

```bash
# Create staging database
sudo mysql -u root -p << EOF
CREATE DATABASE infodot_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'infodot_staging'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON infodot_staging.* TO 'infodot_staging'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### Step 3: Deploy Application

```bash
# Create application directory
sudo mkdir -p /var/www/infodot-staging
sudo chown -R $USER:$USER /var/www/infodot-staging

# Clone repository
cd /var/www
git clone <repository-url> infodot-staging
cd infodot-staging

# Checkout staging branch (or main)
git checkout staging

# Install dependencies
composer install --optimize-autoloader
npm ci
npm run build

# Configure environment
cp .env.staging.example .env
nano .env

# Generate application key
php artisan key:generate

# Set permissions
sudo chown -R www-data:www-data /var/www/infodot-staging
sudo find /var/www/infodot-staging -type d -exec chmod 755 {} \;
sudo find /var/www/infodot-staging -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/infodot-staging/storage
sudo chmod -R 775 /var/www/infodot-staging/bootstrap/cache

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link
```

### Step 4: Configure Nginx

Create `/etc/nginx/sites-available/infodot-staging`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name staging.infodot.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name staging.infodot.com;
    root /var/www/infodot-staging/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/staging.infodot.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/staging.infodot.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Staging environment indicator
    add_header X-Environment "Staging" always;

    index index.php;
    charset utf-8;

    # Logging
    access_log /var/log/nginx/staging-infodot-access.log;
    error_log /var/log/nginx/staging-infodot-error.log;

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
sudo ln -s /etc/nginx/sites-available/infodot-staging /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 5: Configure SSL

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d staging.infodot.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### Step 6: Configure Supervisor

Create `/etc/supervisor/conf.d/infodot-staging-worker.conf`:

```ini
[program:infodot-staging-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/infodot-staging/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/infodot-staging/storage/logs/worker.log
stopwaitsecs=3600
```

Create `/etc/supervisor/conf.d/infodot-staging-reverb.conf`:

```ini
[program:infodot-staging-reverb]
process_name=%(program_name)s
command=php /var/www/infodot-staging/artisan reverb:start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/infodot-staging/storage/logs/reverb.log
```

Reload Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start infodot-staging-worker:*
sudo supervisorctl start infodot-staging-reverb
```

### Step 7: Configure Cron

```bash
sudo crontab -e -u www-data
```

Add:

```cron
* * * * * cd /var/www/infodot-staging && php artisan schedule:run >> /dev/null 2>&1
```

## Environment Configuration

### .env.staging Configuration

```env
APP_NAME="InfoDot Staging"
APP_ENV=staging
APP_KEY=base64:GENERATE_KEY_HERE
APP_DEBUG=true
APP_URL=https://staging.infodot.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=infodot_staging
DB_USERNAME=infodot_staging
DB_PASSWORD=STRONG_PASSWORD_HERE

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="staging@infodot.com"
MAIL_FROM_NAME="${APP_NAME}"

BROADCAST_DRIVER=reverb
REVERB_APP_ID=staging-app-id
REVERB_APP_KEY=staging-app-key
REVERB_APP_SECRET=staging-app-secret
REVERB_HOST="staging.infodot.com"
REVERB_PORT=8080
REVERB_SCHEME=https

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=

FILESYSTEM_DISK=local

LOG_CHANNEL=daily
LOG_LEVEL=debug

SANCTUM_STATEFUL_DOMAINS=staging.infodot.com

TELESCOPE_ENABLED=true
DEBUGBAR_ENABLED=true
```

## Data Seeding

### Option 1: Use Seeders

```bash
cd /var/www/infodot-staging
php artisan db:seed
```

### Option 2: Import Production Data (Sanitized)

```bash
# On production server, export sanitized data
php artisan db:export --sanitize > staging_data.sql

# Transfer to staging
scp staging_data.sql user@staging.infodot.com:/tmp/

# On staging server, import data
mysql -u infodot_staging -p infodot_staging < /tmp/staging_data.sql
rm /tmp/staging_data.sql
```

### Option 3: Copy Production Database

```bash
# On production server
mysqldump -u root -p infodot_production > production_backup.sql

# Transfer to staging
scp production_backup.sql user@staging.infodot.com:/tmp/

# On staging server
mysql -u infodot_staging -p infodot_staging < /tmp/production_backup.sql

# Sanitize sensitive data
mysql -u infodot_staging -p infodot_staging << EOF
UPDATE users SET 
  email = CONCAT('user', id, '@staging.infodot.com'),
  password = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; -- password
UPDATE users SET email_verified_at = NOW() WHERE email_verified_at IS NOT NULL;
EOF

rm /tmp/production_backup.sql
```

## Testing Checklist

### Deployment Testing

- [ ] Application loads successfully
- [ ] SSL certificate is valid
- [ ] All pages render correctly
- [ ] Static assets load properly
- [ ] Database connections work
- [ ] Redis connections work
- [ ] Queue workers are running
- [ ] Reverb server is running
- [ ] Cron jobs are scheduled

### Functionality Testing

- [ ] User registration works
- [ ] User login works
- [ ] Password reset works
- [ ] Question creation works
- [ ] Answer posting works
- [ ] Solution creation works
- [ ] Search functionality works
- [ ] File uploads work
- [ ] Email sending works (check Mailtrap)
- [ ] Real-time updates work
- [ ] API endpoints work

### Performance Testing

```bash
# Test page load time
curl -w "@curl-format.txt" -o /dev/null -s https://staging.infodot.com

# Load testing
ab -n 1000 -c 10 https://staging.infodot.com/

# Database query performance
# Check in Laravel Telescope
```

### Security Testing

```bash
# Check SSL configuration
openssl s_client -connect staging.infodot.com:443

# Check security headers
curl -I https://staging.infodot.com | grep -E 'X-Frame-Options|X-Content-Type-Options|X-XSS-Protection'

# Run security audit
php artisan security:audit
```

## Continuous Deployment to Staging

### GitHub Actions Workflow

Create `.github/workflows/deploy-staging.yml`:

```yaml
name: Deploy to Staging

on:
  push:
    branches: [ staging, develop ]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v4
    
    - name: Deploy to Staging
      uses: appleboy/ssh-action@v1.0.3
      with:
        host: ${{ secrets.STAGING_HOST }}
        username: ${{ secrets.STAGING_USER }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        script: |
          cd /var/www/infodot-staging
          git pull origin staging
          composer install --optimize-autoloader
          npm ci
          npm run build
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          php artisan queue:restart
          sudo supervisorctl restart infodot-staging-reverb
```

## Monitoring Staging

### Application Monitoring

```bash
# View application logs
tail -f /var/www/infodot-staging/storage/logs/laravel.log

# View worker logs
tail -f /var/www/infodot-staging/storage/logs/worker.log

# View Reverb logs
tail -f /var/www/infodot-staging/storage/logs/reverb.log

# View Nginx logs
tail -f /var/log/nginx/staging-infodot-access.log
tail -f /var/log/nginx/staging-infodot-error.log
```

### Laravel Telescope

Access at: `https://staging.infodot.com/telescope`

Monitor:
- Requests
- Commands
- Queries
- Jobs
- Exceptions
- Logs

### Laravel Horizon

Access at: `https://staging.infodot.com/horizon`

Monitor:
- Queue jobs
- Failed jobs
- Throughput
- Wait times

## Maintenance

### Regular Updates

```bash
# Update application
cd /var/www/infodot-staging
git pull origin staging
composer install --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize

# Restart services
php artisan queue:restart
sudo supervisorctl restart infodot-staging-reverb
```

### Database Refresh

```bash
# Reset database
php artisan migrate:fresh --seed

# Or import fresh production data
# (follow steps in Data Seeding section)
```

### Clear Caches

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### Application Not Loading

```bash
# Check Nginx status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.3-fpm

# Check Nginx error logs
sudo tail -f /var/log/nginx/staging-infodot-error.log

# Check PHP-FPM error logs
sudo tail -f /var/log/php8.3-fpm.log
```

### Queue Jobs Not Processing

```bash
# Check worker status
sudo supervisorctl status infodot-staging-worker:*

# Restart workers
sudo supervisorctl restart infodot-staging-worker:*

# Check failed jobs
php artisan queue:failed
```

### WebSocket Connection Failed

```bash
# Check Reverb status
sudo supervisorctl status infodot-staging-reverb

# Restart Reverb
sudo supervisorctl restart infodot-staging-reverb

# Check Reverb logs
tail -f /var/www/infodot-staging/storage/logs/reverb.log
```

## Best Practices

1. **Keep staging in sync with production**
   - Use same software versions
   - Use same configuration (except credentials)
   - Test all changes on staging first

2. **Use production-like data**
   - Import sanitized production data regularly
   - Test with realistic data volumes
   - Maintain data privacy

3. **Automate deployments**
   - Use CI/CD for automatic deployments
   - Run tests before deployment
   - Notify team of deployments

4. **Monitor staging**
   - Enable detailed logging
   - Use Laravel Telescope
   - Monitor performance metrics

5. **Regular maintenance**
   - Update dependencies regularly
   - Refresh data periodically
   - Clean up old logs and backups

---

**Last Updated**: January 15, 2026
