# Server Requirements

This document outlines the server requirements and recommended specifications for hosting the InfoDot Laravel 11 application.

## Minimum Requirements

### Operating System
- **Ubuntu**: 22.04 LTS or 24.04 LTS (recommended)
- **Debian**: 11 or 12
- **CentOS/RHEL**: 8 or 9
- **Amazon Linux**: 2023

### PHP
- **Version**: 8.3 or 8.4
- **Required Extensions**:
  - BCMath
  - Ctype
  - cURL
  - DOM
  - Fileinfo
  - Filter
  - Hash
  - Mbstring
  - OpenSSL
  - PCRE
  - PDO
  - Session
  - Tokenizer
  - XML
  - GD or Imagick
  - Zip
  - Redis (phpredis extension)

### Web Server
- **Nginx**: 1.18+ (recommended)
- **Apache**: 2.4+ (alternative)

### Database
- **MySQL**: 8.0+
- **MariaDB**: 10.6+ (alternative)
- **PostgreSQL**: 13+ (alternative, requires code changes)

### Cache & Queue
- **Redis**: 6.0+ (recommended)
- **Memcached**: 1.6+ (alternative for cache only)

### Search Engine
- **Meilisearch**: 1.0+ (recommended)
- **TNTSearch**: Built-in (alternative for smaller deployments)
- **Algolia**: Cloud service (alternative)

### Node.js
- **Version**: 18 LTS or 20 LTS
- **NPM**: 9+ or 10+

### Additional Software
- **Composer**: 2.x
- **Git**: 2.x
- **Supervisor**: For queue workers and WebSocket server
- **Certbot**: For SSL certificates (Let's Encrypt)

## Recommended Specifications

### Small Deployment (< 1,000 users)
- **CPU**: 2 cores
- **RAM**: 4 GB
- **Storage**: 40 GB SSD
- **Bandwidth**: 100 Mbps

### Medium Deployment (1,000 - 10,000 users)
- **CPU**: 4 cores
- **RAM**: 8 GB
- **Storage**: 100 GB SSD
- **Bandwidth**: 1 Gbps

### Large Deployment (> 10,000 users)
- **CPU**: 8+ cores
- **RAM**: 16+ GB
- **Storage**: 200+ GB SSD
- **Bandwidth**: 1+ Gbps
- **Load Balancer**: Recommended
- **Database Server**: Separate server recommended
- **Redis Server**: Separate server recommended
- **Search Server**: Separate server recommended

## Port Requirements

### Required Ports
- **80**: HTTP (redirects to HTTPS)
- **443**: HTTPS
- **3306**: MySQL (internal only, not exposed to internet)
- **6379**: Redis (internal only, not exposed to internet)
- **8080**: Reverb WebSocket server (proxied through Nginx)

### Optional Ports
- **22**: SSH (for server management)
- **7700**: Meilisearch (internal only)

## Firewall Configuration

### UFW (Ubuntu)
```bash
# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow WebSocket (if not proxied)
sudo ufw allow 8080/tcp

# Enable firewall
sudo ufw enable
```

### iptables
```bash
# Allow SSH
iptables -A INPUT -p tcp --dport 22 -j ACCEPT

# Allow HTTP and HTTPS
iptables -A INPUT -p tcp --dport 80 -j ACCEPT
iptables -A INPUT -p tcp --dport 443 -j ACCEPT

# Allow WebSocket
iptables -A INPUT -p tcp --dport 8080 -j ACCEPT

# Save rules
iptables-save > /etc/iptables/rules.v4
```

## PHP Configuration

### php.ini Settings

```ini
; Memory and execution
memory_limit = 256M
max_execution_time = 300
max_input_time = 300

; File uploads
upload_max_filesize = 20M
post_max_size = 25M

; Session
session.gc_maxlifetime = 7200

; OPcache (recommended for production)
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1

; Realpath cache
realpath_cache_size = 4096K
realpath_cache_ttl = 600
```

### PHP-FPM Configuration

```ini
; /etc/php/8.3/fpm/pool.d/www.conf

[www]
user = www-data
group = www-data
listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; Slow log
slowlog = /var/log/php8.3-fpm-slow.log
request_slowlog_timeout = 5s

; PHP settings
php_admin_value[error_log] = /var/log/php8.3-fpm-error.log
php_admin_flag[log_errors] = on
php_value[session.save_handler] = redis
php_value[session.save_path] = "tcp://127.0.0.1:6379?database=3"
```

## MySQL Configuration

### my.cnf Settings

```ini
[mysqld]
# Basic settings
max_connections = 200
max_allowed_packet = 64M

# InnoDB settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query cache (disabled in MySQL 8.0+)
# query_cache_type = 0
# query_cache_size = 0

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Full-text search
ft_min_word_len = 3
```

## Redis Configuration

### redis.conf Settings

```conf
# Memory
maxmemory 1gb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Append-only file
appendonly yes
appendfsync everysec

# Security
requirepass your-redis-password

# Network
bind 127.0.0.1
port 6379
timeout 300

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log
```

## Nginx Configuration

### Main Configuration

```nginx
user www-data;
worker_processes auto;
pid /run/nginx.pid;

events {
    worker_connections 2048;
    use epoll;
    multi_accept on;
}

http {
    # Basic settings
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_tokens off;
    
    # Buffer sizes
    client_body_buffer_size 128k;
    client_max_body_size 20M;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 16k;
    
    # Timeouts
    client_body_timeout 12;
    client_header_timeout 12;
    send_timeout 10;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript 
               application/json application/javascript application/xml+rss 
               application/rss+xml font/truetype font/opentype 
               application/vnd.ms-fontobject image/svg+xml;
    
    # Logging
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;
    
    # Include site configurations
    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
```

## Supervisor Configuration

### Queue Workers

```ini
[program:infodot-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/infodot-laravel11/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/infodot-laravel11/storage/logs/worker.log
stopwaitsecs=3600
```

### Reverb Server

```ini
[program:infodot-reverb]
process_name=%(program_name)s
command=php /var/www/infodot-laravel11/artisan reverb:start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/infodot-laravel11/storage/logs/reverb.log
```

## Cron Configuration

```cron
# Laravel Scheduler
* * * * * cd /var/www/infodot-laravel11 && php artisan schedule:run >> /dev/null 2>&1

# Database backup (daily at 2 AM)
0 2 * * * /usr/local/bin/backup-database.sh

# Log rotation (weekly)
0 0 * * 0 find /var/www/infodot-laravel11/storage/logs -name "*.log" -mtime +7 -delete
```

## SSL/TLS Configuration

### Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d infodot.com -d www.infodot.com

# Auto-renewal (already configured by Certbot)
sudo certbot renew --dry-run
```

### SSL Configuration

```nginx
# Strong SSL configuration
ssl_protocols TLSv1.2 TLSv1.3;
ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
ssl_prefer_server_ciphers off;
ssl_session_cache shared:SSL:10m;
ssl_session_timeout 10m;
ssl_stapling on;
ssl_stapling_verify on;
```

## Monitoring Requirements

### System Monitoring
- **CPU Usage**: Monitor and alert if > 80%
- **Memory Usage**: Monitor and alert if > 85%
- **Disk Usage**: Monitor and alert if > 80%
- **Network Traffic**: Monitor bandwidth usage

### Application Monitoring
- **Response Time**: Monitor average response time
- **Error Rate**: Monitor 4xx and 5xx errors
- **Queue Length**: Monitor queue job backlog
- **Database Connections**: Monitor active connections

### Recommended Tools
- **System**: htop, iotop, netstat
- **Application**: Laravel Telescope, Laravel Horizon
- **External**: New Relic, Datadog, Sentry

## Backup Requirements

### What to Backup
- Database (daily)
- Application files (weekly)
- User uploads (daily)
- Configuration files (on change)
- SSL certificates (on renewal)

### Backup Storage
- **Local**: Keep 7 days of backups
- **Remote**: Keep 30 days of backups (S3, Backblaze, etc.)
- **Offsite**: Keep monthly backups for 1 year

### Backup Script Example

```bash
#!/bin/bash
# /usr/local/bin/backup-database.sh

BACKUP_DIR="/backups/infodot"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="infodot_production"

# Create backup
mysqldump -u root -p$MYSQL_PASSWORD \
  --single-transaction \
  --routines \
  --triggers \
  $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Upload to S3
aws s3 cp $BACKUP_DIR/db_$DATE.sql.gz s3://infodot-backups/database/

# Remove local backups older than 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
```

## Security Hardening

### System Security
- Keep system packages updated
- Disable root SSH login
- Use SSH keys instead of passwords
- Configure fail2ban for brute force protection
- Enable automatic security updates

### Application Security
- Keep Laravel and packages updated
- Use strong passwords for all services
- Restrict database access to localhost
- Use environment variables for secrets
- Enable HTTPS only
- Configure security headers
- Implement rate limiting

### File Permissions

```bash
# Application directory
sudo chown -R www-data:www-data /var/www/infodot-laravel11
sudo find /var/www/infodot-laravel11 -type d -exec chmod 755 {} \;
sudo find /var/www/infodot-laravel11 -type f -exec chmod 644 {} \;

# Storage and cache
sudo chmod -R 775 /var/www/infodot-laravel11/storage
sudo chmod -R 775 /var/www/infodot-laravel11/bootstrap/cache
```

## Performance Optimization

### PHP OPcache
- Enable OPcache in production
- Configure appropriate memory limits
- Set revalidation frequency

### Database Optimization
- Create indexes on frequently queried columns
- Optimize slow queries
- Use connection pooling
- Enable query caching (if applicable)

### Caching Strategy
- Use Redis for cache and sessions
- Cache configuration, routes, and views
- Implement application-level caching
- Use CDN for static assets

### Asset Optimization
- Minify CSS and JavaScript
- Optimize images
- Enable Gzip compression
- Use HTTP/2

---

**Last Updated**: January 15, 2026
