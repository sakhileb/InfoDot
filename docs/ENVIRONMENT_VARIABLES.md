# Environment Variables Documentation

This document describes all environment variables used in the InfoDot Laravel 11 application.

## Application Settings

### APP_NAME
- **Description**: Application name displayed in UI and emails
- **Type**: String
- **Default**: `InfoDot`
- **Example**: `APP_NAME="InfoDot Q&A Platform"`

### APP_ENV
- **Description**: Application environment
- **Type**: String (local, staging, production)
- **Default**: `local`
- **Production**: `APP_ENV=production`

### APP_KEY
- **Description**: Application encryption key (auto-generated)
- **Type**: String (base64)
- **Generate**: `php artisan key:generate`
- **Example**: `APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

### APP_DEBUG
- **Description**: Enable debug mode (shows detailed errors)
- **Type**: Boolean
- **Default**: `true`
- **Production**: `APP_DEBUG=false` (MUST be false in production)

### APP_URL
- **Description**: Application URL
- **Type**: URL
- **Default**: `http://localhost`
- **Production**: `APP_URL=https://infodot.com`

## Database Configuration

### DB_CONNECTION
- **Description**: Database driver
- **Type**: String (mysql, pgsql, sqlite, sqlsrv)
- **Default**: `mysql`
- **Example**: `DB_CONNECTION=mysql`

### DB_HOST
- **Description**: Database server hostname
- **Type**: String
- **Default**: `127.0.0.1`
- **Example**: `DB_HOST=db.example.com`

### DB_PORT
- **Description**: Database server port
- **Type**: Integer
- **Default**: `3306` (MySQL)
- **Example**: `DB_PORT=3306`

### DB_DATABASE
- **Description**: Database name
- **Type**: String
- **Default**: `infodot`
- **Example**: `DB_DATABASE=infodot_production`

### DB_USERNAME
- **Description**: Database username
- **Type**: String
- **Default**: `root`
- **Example**: `DB_USERNAME=infodot_user`

### DB_PASSWORD
- **Description**: Database password
- **Type**: String
- **Default**: (empty)
- **Example**: `DB_PASSWORD=secure_password_here`
- **Security**: Never commit this to version control

## Cache Configuration

### CACHE_DRIVER
- **Description**: Cache driver
- **Type**: String (file, redis, memcached, database, array)
- **Default**: `redis`
- **Production**: `CACHE_DRIVER=redis` (recommended)

### REDIS_HOST
- **Description**: Redis server hostname
- **Type**: String
- **Default**: `127.0.0.1`
- **Example**: `REDIS_HOST=redis.example.com`

### REDIS_PASSWORD
- **Description**: Redis password
- **Type**: String
- **Default**: `null`
- **Example**: `REDIS_PASSWORD=redis_password_here`

### REDIS_PORT
- **Description**: Redis server port
- **Type**: Integer
- **Default**: `6379`
- **Example**: `REDIS_PORT=6379`

## Queue Configuration

### QUEUE_CONNECTION
- **Description**: Queue driver
- **Type**: String (sync, database, redis, sqs, beanstalkd)
- **Default**: `redis`
- **Production**: `QUEUE_CONNECTION=redis` (recommended)

## Mail Configuration

### MAIL_MAILER
- **Description**: Mail driver
- **Type**: String (smtp, sendmail, mailgun, ses, postmark, log)
- **Default**: `smtp`
- **Example**: `MAIL_MAILER=smtp`

### MAIL_HOST
- **Description**: SMTP server hostname
- **Type**: String
- **Default**: `smtp.mailtrap.io`
- **Production**: `MAIL_HOST=smtp.gmail.com`

### MAIL_PORT
- **Description**: SMTP server port
- **Type**: Integer
- **Default**: `2525`
- **Common Ports**: 
  - 25 (unencrypted)
  - 587 (TLS)
  - 465 (SSL)

### MAIL_USERNAME
- **Description**: SMTP username
- **Type**: String
- **Default**: `null`
- **Example**: `MAIL_USERNAME=your-email@gmail.com`

### MAIL_PASSWORD
- **Description**: SMTP password
- **Type**: String
- **Default**: `null`
- **Example**: `MAIL_PASSWORD=your-app-password`
- **Security**: Never commit this to version control

### MAIL_ENCRYPTION
- **Description**: SMTP encryption method
- **Type**: String (tls, ssl, null)
- **Default**: `null`
- **Production**: `MAIL_ENCRYPTION=tls`

### MAIL_FROM_ADDRESS
- **Description**: Default sender email address
- **Type**: Email
- **Default**: `noreply@infodot.com`
- **Example**: `MAIL_FROM_ADDRESS="noreply@infodot.com"`

### MAIL_FROM_NAME
- **Description**: Default sender name
- **Type**: String
- **Default**: `${APP_NAME}`
- **Example**: `MAIL_FROM_NAME="InfoDot Support"`

## Broadcasting Configuration

### BROADCAST_DRIVER
- **Description**: Broadcasting driver
- **Type**: String (reverb, pusher, redis, log, null)
- **Default**: `reverb`
- **Production**: `BROADCAST_DRIVER=reverb`

### REVERB_APP_ID
- **Description**: Reverb application ID
- **Type**: String
- **Generate**: Set during `php artisan reverb:install`
- **Example**: `REVERB_APP_ID=123456`

### REVERB_APP_KEY
- **Description**: Reverb application key
- **Type**: String
- **Generate**: Set during `php artisan reverb:install`
- **Example**: `REVERB_APP_KEY=your-app-key`

### REVERB_APP_SECRET
- **Description**: Reverb application secret
- **Type**: String
- **Generate**: Set during `php artisan reverb:install`
- **Example**: `REVERB_APP_SECRET=your-app-secret`
- **Security**: Never commit this to version control

### REVERB_HOST
- **Description**: Reverb server hostname
- **Type**: String
- **Default**: `localhost`
- **Production**: `REVERB_HOST=ws.infodot.com`

### REVERB_PORT
- **Description**: Reverb server port
- **Type**: Integer
- **Default**: `8080`
- **Example**: `REVERB_PORT=8080`

### REVERB_SCHEME
- **Description**: Reverb connection scheme
- **Type**: String (http, https)
- **Default**: `http`
- **Production**: `REVERB_SCHEME=https`

### VITE_REVERB_APP_KEY
- **Description**: Reverb key for frontend (must match REVERB_APP_KEY)
- **Type**: String
- **Example**: `VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"`

### VITE_REVERB_HOST
- **Description**: Reverb host for frontend
- **Type**: String
- **Example**: `VITE_REVERB_HOST="${REVERB_HOST}"`

### VITE_REVERB_PORT
- **Description**: Reverb port for frontend
- **Type**: Integer
- **Example**: `VITE_REVERB_PORT="${REVERB_PORT}"`

### VITE_REVERB_SCHEME
- **Description**: Reverb scheme for frontend
- **Type**: String
- **Example**: `VITE_REVERB_SCHEME="${REVERB_SCHEME}"`

## Search Configuration (Scout)

### SCOUT_DRIVER
- **Description**: Search driver
- **Type**: String (meilisearch, tntsearch, algolia, database)
- **Default**: `meilisearch`
- **Options**:
  - `meilisearch` - Recommended for production
  - `tntsearch` - Good for small to medium sites
  - `database` - Fallback using MySQL FULLTEXT

### MEILISEARCH_HOST
- **Description**: Meilisearch server URL
- **Type**: URL
- **Default**: `http://127.0.0.1:7700`
- **Production**: `MEILISEARCH_HOST=https://search.infodot.com`

### MEILISEARCH_KEY
- **Description**: Meilisearch API key
- **Type**: String
- **Default**: (empty for local development)
- **Production**: `MEILISEARCH_KEY=your-master-key`
- **Security**: Never commit this to version control

### ALGOLIA_APP_ID
- **Description**: Algolia application ID (if using Algolia)
- **Type**: String
- **Example**: `ALGOLIA_APP_ID=your-app-id`

### ALGOLIA_SECRET
- **Description**: Algolia admin API key
- **Type**: String
- **Example**: `ALGOLIA_SECRET=your-admin-key`
- **Security**: Never commit this to version control

## File Storage Configuration

### FILESYSTEM_DISK
- **Description**: Default filesystem disk
- **Type**: String (local, public, s3)
- **Default**: `public`
- **Production**: `FILESYSTEM_DISK=s3` (recommended)

### AWS_ACCESS_KEY_ID
- **Description**: AWS access key for S3
- **Type**: String
- **Example**: `AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE`
- **Security**: Never commit this to version control

### AWS_SECRET_ACCESS_KEY
- **Description**: AWS secret key for S3
- **Type**: String
- **Example**: `AWS_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY`
- **Security**: Never commit this to version control

### AWS_DEFAULT_REGION
- **Description**: AWS region for S3
- **Type**: String
- **Default**: `us-east-1`
- **Example**: `AWS_DEFAULT_REGION=us-west-2`

### AWS_BUCKET
- **Description**: S3 bucket name
- **Type**: String
- **Example**: `AWS_BUCKET=infodot-files`

### AWS_USE_PATH_STYLE_ENDPOINT
- **Description**: Use path-style S3 endpoints
- **Type**: Boolean
- **Default**: `false`
- **Example**: `AWS_USE_PATH_STYLE_ENDPOINT=false`

## Session Configuration

### SESSION_DRIVER
- **Description**: Session storage driver
- **Type**: String (file, cookie, database, redis, memcached, array)
- **Default**: `database`
- **Production**: `SESSION_DRIVER=redis` (recommended)

### SESSION_LIFETIME
- **Description**: Session lifetime in minutes
- **Type**: Integer
- **Default**: `120`
- **Example**: `SESSION_LIFETIME=120`

## Logging Configuration

### LOG_CHANNEL
- **Description**: Default log channel
- **Type**: String (stack, single, daily, slack, stderr, syslog)
- **Default**: `stack`
- **Production**: `LOG_CHANNEL=daily`

### LOG_LEVEL
- **Description**: Minimum log level
- **Type**: String (debug, info, notice, warning, error, critical, alert, emergency)
- **Default**: `debug`
- **Production**: `LOG_LEVEL=error`

## Third-Party Services

### PUSHER_APP_ID
- **Description**: Pusher application ID (if using Pusher instead of Reverb)
- **Type**: String
- **Example**: `PUSHER_APP_ID=123456`

### PUSHER_APP_KEY
- **Description**: Pusher application key
- **Type**: String
- **Example**: `PUSHER_APP_KEY=your-app-key`

### PUSHER_APP_SECRET
- **Description**: Pusher application secret
- **Type**: String
- **Example**: `PUSHER_APP_SECRET=your-app-secret`
- **Security**: Never commit this to version control

### PUSHER_APP_CLUSTER
- **Description**: Pusher cluster
- **Type**: String
- **Default**: `mt1`
- **Example**: `PUSHER_APP_CLUSTER=us2`

## Development Tools

### TELESCOPE_ENABLED
- **Description**: Enable Laravel Telescope
- **Type**: Boolean
- **Default**: `true` (local), `false` (production)
- **Example**: `TELESCOPE_ENABLED=true`

### DEBUGBAR_ENABLED
- **Description**: Enable Laravel Debugbar
- **Type**: Boolean
- **Default**: `true` (local), `false` (production)
- **Example**: `DEBUGBAR_ENABLED=true`

## Security Settings

### SANCTUM_STATEFUL_DOMAINS
- **Description**: Domains that can make stateful API requests
- **Type**: Comma-separated list
- **Default**: `localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1`
- **Production**: `SANCTUM_STATEFUL_DOMAINS=infodot.com,www.infodot.com`

### SESSION_DOMAIN
- **Description**: Domain for session cookies
- **Type**: String
- **Default**: `null`
- **Production**: `SESSION_DOMAIN=.infodot.com`

## Environment-Specific Configurations

### Local Development (.env.local)
```env
APP_ENV=local
APP_DEBUG=true
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
BROADCAST_DRIVER=log
SCOUT_DRIVER=database
```

### Staging (.env.staging)
```env
APP_ENV=staging
APP_DEBUG=true
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
MAIL_MAILER=smtp
BROADCAST_DRIVER=reverb
SCOUT_DRIVER=meilisearch
```

### Production (.env.production)
```env
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
MAIL_MAILER=smtp
BROADCAST_DRIVER=reverb
SCOUT_DRIVER=meilisearch
FILESYSTEM_DISK=s3
SESSION_DRIVER=redis
LOG_CHANNEL=daily
LOG_LEVEL=error
```

## Security Best Practices

1. **Never commit `.env` file to version control**
2. **Use strong, unique passwords for all services**
3. **Rotate secrets regularly**
4. **Use environment-specific configurations**
5. **Enable HTTPS in production**
6. **Set `APP_DEBUG=false` in production**
7. **Use secure session drivers (redis, database)**
8. **Enable rate limiting**
9. **Keep all secrets in environment variables**
10. **Use AWS IAM roles instead of access keys when possible**

## Validation

To validate your environment configuration:

```bash
# Check configuration
php artisan config:show

# Test database connection
php artisan migrate:status

# Test cache connection
php artisan cache:clear

# Test queue connection
php artisan queue:work --once

# Test mail configuration
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

---

**Last Updated**: January 15, 2026
