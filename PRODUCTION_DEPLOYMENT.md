# Production Deployment Guide

## Issue
The production server is currently using development configuration with `APP_DEBUG=true` and pointing to the development database path `/workspaces/InfoDot/database/database.sqlite`.

## Steps to Fix Production Deployment

### 1. On Your Production Server

SSH into your production server and navigate to your application directory:
```bash
ssh infodotc@infodot.co.za
cd /home/infodotc/public_html/infodot
```

### 2. Create Production .env File

Copy the production example and customize it:
```bash
cp .env.production.example .env
```

Then edit `.env` with your production values:
```bash
nano .env
```

**Critical settings to configure:**
- `APP_KEY=` - Generate with: `php artisan key:generate`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://infodot.co.za`
- `DB_DATABASE=/home/infodotc/public_html/infodot/database/database.sqlite` (or your actual path)
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
- `SANCTUM_STATEFUL_DOMAINS=infodot.co.za`

### 3. Create/Initialize Database

```bash
# Create the database file if it doesn't exist
touch /home/infodotc/public_html/infodot/database/database.sqlite
chmod 666 /home/infodotc/public_html/infodot/database/database.sqlite

# Run migrations
php artisan migrate --force

# Cache optimization (critical for production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

### 4. Verify Permissions

Ensure storage and database directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Clear Development Artifacts

Remove any development files from production:
```bash
rm -rf tests node_modules .git
rm -f .env.example .env.*.example
```

## Important Notes

- **Never** deploy `.env` files from development to production
- **Never** use `APP_DEBUG=true` in production  
- The development tarball (`infodot-complete-*.tar.gz`) includes node_modules, tests, and .git - use the optimized tarball instead (`infodot-production-*.tar.gz`)
- Always run `php artisan migrate --force` after deployment
- Always cache configuration and routes in production with the commands above

## Troubleshooting

If you still get "Database file does not exist":
1. Verify the database path in `.env` matches your server structure
2. Confirm the file exists: `ls -la /home/infodotc/public_html/infodot/database/database.sqlite`
3. Check permissions: `stat /home/infodotc/public_html/infodot/database/database.sqlite`
4. Verify the web server user (usually `www-data`) can write to it
