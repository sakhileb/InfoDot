# Migration Guide: Laravel 8 to Laravel 11

This guide provides comprehensive instructions for migrating your InfoDot installation from Laravel 8 to Laravel 11.

## Table of Contents

1. [Overview](#overview)
2. [Pre-Migration Checklist](#pre-migration-checklist)
3. [Backup Procedures](#backup-procedures)
4. [Migration Steps](#migration-steps)
5. [Data Migration](#data-migration)
6. [File Migration](#file-migration)
7. [Configuration Migration](#configuration-migration)
8. [Testing](#testing)
9. [Rollback Plan](#rollback-plan)
10. [Post-Migration Tasks](#post-migration-tasks)

## Overview

### What's Changed

**Framework**:
- Laravel 8.65 → Laravel 11.x
- PHP 7.3/8.0 → PHP 8.3/8.4

**Major Package Updates**:
- Jetstream 2.5 → 5.x
- Sanctum 2.11 → 4.x
- Livewire 3.5.2 → 3.x (latest)
- Scout 9.4 → 10.x
- Spatie Media Library 9.0 → 11.x
- Laravel WebSockets → Laravel Reverb

**Frontend**:
- Laravel Mix → Vite
- Vue 2.6 → Vue 3.x (optional)
- Tailwind CSS 3.0 → 4.x

### Migration Strategy

This migration follows a **parallel deployment** approach:

1. Set up Laravel 11 application alongside Laravel 8
2. Migrate data from Laravel 8 database
3. Test thoroughly on staging
4. Switch traffic to Laravel 11
5. Keep Laravel 8 as backup for quick rollback

### Estimated Timeline

- **Preparation**: 1-2 hours
- **Data Migration**: 2-4 hours
- **Testing**: 4-8 hours
- **Deployment**: 1-2 hours
- **Total**: 8-16 hours

## Pre-Migration Checklist

### Laravel 8 Application

- [ ] Application is running without errors
- [ ] All tests are passing
- [ ] Database is healthy and optimized
- [ ] No pending migrations
- [ ] All queue jobs are processed
- [ ] Backup system is working
- [ ] Documentation is up to date

### Infrastructure

- [ ] Server meets Laravel 11 requirements
- [ ] PHP 8.3 or 8.4 installed
- [ ] MySQL 8.0+ available
- [ ] Redis available
- [ ] Sufficient disk space (2x current usage)
- [ ] Backup storage available

### Team Preparation

- [ ] Team notified of migration schedule
- [ ] Maintenance window scheduled
- [ ] Rollback plan documented
- [ ] Support team on standby
- [ ] Users notified of potential downtime

## Backup Procedures

### 1. Database Backup

```bash
# Full database backup
mysqldump -u root -p \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  infodot_laravel8 > backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup
mysql -u root -p -e "SELECT COUNT(*) FROM users" infodot_laravel8
```

### 2. File Backup

```bash
# Backup storage directory
tar -czf storage_backup_$(date +%Y%m%d_%H%M%S).tar.gz \
  /var/www/infodot-laravel8/storage/app

# Backup public uploads
tar -czf public_backup_$(date +%Y%m%d_%H%M%S).tar.gz \
  /var/www/infodot-laravel8/public/storage
```

### 3. Configuration Backup

```bash
# Backup .env file
cp /var/www/infodot-laravel8/.env \
  /backups/env_backup_$(date +%Y%m%d_%H%M%S)

# Backup entire application
tar -czf app_backup_$(date +%Y%m%d_%H%M%S).tar.gz \
  /var/www/infodot-laravel8
```

### 4. Verify Backups

```bash
# Check backup files exist and have content
ls -lh /backups/

# Test database backup
mysql -u root -p infodot_test < backup_YYYYMMDD_HHMMSS.sql
mysql -u root -p -e "SELECT COUNT(*) FROM users" infodot_test
```

## Migration Steps

### Step 1: Set Up Laravel 11 Application

```bash
# Clone Laravel 11 repository
cd /var/www
git clone <repository-url> infodot-laravel11
cd infodot-laravel11

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Configure environment
cp .env.example .env
php artisan key:generate
```

### Step 2: Configure Environment

Edit `/var/www/infodot-laravel11/.env`:

```env
APP_NAME="InfoDot"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://infodot.com

# Use separate database initially
DB_DATABASE=infodot_laravel11

# Copy other settings from Laravel 8 .env
# Redis, Mail, Broadcasting, etc.
```

### Step 3: Create New Database

```bash
# Create new database
mysql -u root -p -e "CREATE DATABASE infodot_laravel11 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Grant permissions
mysql -u root -p -e "GRANT ALL PRIVILEGES ON infodot_laravel11.* TO 'infodot_user'@'localhost';"
```

### Step 4: Run Migrations

```bash
cd /var/www/infodot-laravel11

# Run migrations
php artisan migrate --force

# Verify schema
php artisan migrate:status
```

## Data Migration

### Method 1: Direct Database Copy (Recommended)

```bash
# Dump Laravel 8 database
mysqldump -u root -p \
  --no-create-info \
  --skip-add-drop-table \
  --complete-insert \
  infodot_laravel8 > data_export.sql

# Import to Laravel 11 database
mysql -u root -p infodot_laravel11 < data_export.sql

# Verify data
mysql -u root -p infodot_laravel11 -e "
  SELECT 
    (SELECT COUNT(*) FROM users) as users,
    (SELECT COUNT(*) FROM questions) as questions,
    (SELECT COUNT(*) FROM answers) as answers,
    (SELECT COUNT(*) FROM solutions) as solutions;
"
```

### Method 2: Table-by-Table Migration

```bash
# Export specific tables
mysqldump -u root -p infodot_laravel8 \
  users questions answers solutions steps \
  likes comments associates followers \
  teams team_user team_invitations \
  files folders objs \
  > tables_export.sql

# Import to Laravel 11
mysql -u root -p infodot_laravel11 < tables_export.sql
```

### Method 3: Using Laravel Artisan Command

Create a migration command:

```php
// app/Console/Commands/MigrateFromLaravel8.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateFromLaravel8 extends Command
{
    protected $signature = 'migrate:from-laravel8 {--connection=laravel8}';
    protected $description = 'Migrate data from Laravel 8 database';

    public function handle()
    {
        $connection = $this->option('connection');
        
        $this->info('Starting migration from Laravel 8...');
        
        // Migrate users
        $this->migrateTable('users', $connection);
        
        // Migrate questions
        $this->migrateTable('questions', $connection);
        
        // Migrate answers
        $this->migrateTable('answers', $connection);
        
        // Migrate solutions
        $this->migrateTable('solutions', $connection);
        
        // Migrate steps
        $this->migrateTable('steps', $connection);
        
        // Migrate likes
        $this->migrateTable('likes', $connection);
        
        // Migrate comments
        $this->migrateTable('comments', $connection);
        
        // Migrate associates
        $this->migrateTable('associates', $connection);
        
        // Migrate followers
        $this->migrateTable('followers', $connection);
        
        // Migrate teams
        $this->migrateTable('teams', $connection);
        $this->migrateTable('team_user', $connection);
        $this->migrateTable('team_invitations', $connection);
        
        // Migrate files
        $this->migrateTable('files', $connection);
        $this->migrateTable('folders', $connection);
        $this->migrateTable('objs', $connection);
        
        $this->info('Migration completed successfully!');
    }
    
    protected function migrateTable($table, $connection)
    {
        $this->info("Migrating {$table}...");
        
        $count = DB::connection($connection)->table($table)->count();
        $this->info("Found {$count} records");
        
        DB::connection($connection)
            ->table($table)
            ->orderBy('id')
            ->chunk(1000, function ($records) use ($table) {
                foreach ($records as $record) {
                    DB::table($table)->insert((array) $record);
                }
            });
        
        $newCount = DB::table($table)->count();
        $this->info("Migrated {$newCount} records to {$table}");
    }
}
```

Configure Laravel 8 connection in `config/database.php`:

```php
'connections' => [
    'laravel8' => [
        'driver' => 'mysql',
        'host' => env('DB_LARAVEL8_HOST', '127.0.0.1'),
        'port' => env('DB_LARAVEL8_PORT', '3306'),
        'database' => env('DB_LARAVEL8_DATABASE', 'infodot_laravel8'),
        'username' => env('DB_LARAVEL8_USERNAME', 'root'),
        'password' => env('DB_LARAVEL8_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
],
```

Run migration:

```bash
php artisan migrate:from-laravel8
```

### Data Verification

```bash
# Run verification script
php artisan tinker

# Verify counts match
>>> DB::connection('laravel8')->table('users')->count();
>>> DB::table('users')->count();

>>> DB::connection('laravel8')->table('questions')->count();
>>> DB::table('questions')->count();

# Verify relationships
>>> $user = User::first();
>>> $user->questions()->count();
>>> $user->solutions()->count();
>>> $user->answers()->count();

# Verify data integrity
>>> Question::with('answers')->first();
>>> Solution::with('steps')->first();
```

## File Migration

### Migrate Storage Files

```bash
# Copy storage files
rsync -av --progress \
  /var/www/infodot-laravel8/storage/app/ \
  /var/www/infodot-laravel11/storage/app/

# Copy public storage
rsync -av --progress \
  /var/www/infodot-laravel8/public/storage/ \
  /var/www/infodot-laravel11/public/storage/

# Set permissions
sudo chown -R www-data:www-data /var/www/infodot-laravel11/storage
sudo chmod -R 775 /var/www/infodot-laravel11/storage
```

### Migrate Media Library Files

If using Spatie Media Library:

```bash
# Use the migration command
cd /var/www/infodot-laravel11
php artisan migrate:files

# Or manually copy
rsync -av --progress \
  /var/www/infodot-laravel8/storage/app/public/media/ \
  /var/www/infodot-laravel11/storage/app/public/media/
```

### Verify File Migration

```bash
# Check file counts
find /var/www/infodot-laravel8/storage/app -type f | wc -l
find /var/www/infodot-laravel11/storage/app -type f | wc -l

# Check file sizes
du -sh /var/www/infodot-laravel8/storage/app
du -sh /var/www/infodot-laravel11/storage/app

# Test file access
php artisan tinker
>>> Storage::disk('public')->exists('test-file.jpg');
```

## Configuration Migration

### Environment Variables

Compare and migrate environment variables:

```bash
# Compare .env files
diff /var/www/infodot-laravel8/.env /var/www/infodot-laravel11/.env

# Key changes to note:
# - BROADCAST_DRIVER: pusher → reverb
# - SCOUT_DRIVER: tntsearch → meilisearch (optional)
# - Add REVERB_* variables
# - Update VITE_* variables
```

### Configuration Files

Key configuration changes:

1. **Broadcasting** (`config/broadcasting.php`):
   - Add Reverb configuration
   - Update Pusher configuration if keeping as fallback

2. **Scout** (`config/scout.php`):
   - Update to Scout 10.x configuration
   - Configure Meilisearch or TNTSearch

3. **Sanctum** (`config/sanctum.php`):
   - Update stateful domains
   - Update middleware configuration

4. **Jetstream** (`config/jetstream.php`):
   - Update to Jetstream 5.x configuration
   - Verify features enabled

## Testing

### 1. Run Test Suite

```bash
cd /var/www/infodot-laravel11

# Run all tests
php artisan test

# Expected: 286 tests, 1,024 assertions, 100% pass rate
```

### 2. Manual Testing Checklist

- [ ] User registration and login
- [ ] Password reset
- [ ] Two-factor authentication
- [ ] Question creation and viewing
- [ ] Answer posting and acceptance
- [ ] Solution creation with steps
- [ ] Like/dislike functionality
- [ ] Commenting
- [ ] Following users
- [ ] Search functionality
- [ ] File uploads
- [ ] Email notifications
- [ ] Real-time updates (WebSocket)
- [ ] API endpoints
- [ ] Team management

### 3. Performance Testing

```bash
# Test page load times
curl -w "@curl-format.txt" -o /dev/null -s https://staging.infodot.com

# Load testing
ab -n 1000 -c 10 https://staging.infodot.com/

# Database query performance
php artisan telescope:prune
# Monitor queries in Telescope
```

### 4. Data Integrity Testing

```bash
php artisan tinker

# Verify user data
>>> User::count();
>>> User::whereNull('email')->count(); // Should be 0

# Verify relationships
>>> Question::has('user')->count() === Question::count();
>>> Answer::has('question')->count() === Answer::count();
>>> Solution::has('steps')->count();

# Verify polymorphic relationships
>>> Like::has('likable')->count() === Like::count();
>>> Comment::has('commentable')->count() === Comment::count();
```

## Rollback Plan

### Quick Rollback (Switch Back to Laravel 8)

```bash
# 1. Update Nginx configuration
sudo nano /etc/nginx/sites-available/infodot

# Change root to Laravel 8:
# root /var/www/infodot-laravel8/public;

# 2. Reload Nginx
sudo nginx -t
sudo systemctl reload nginx

# 3. Restart services
sudo supervisorctl restart infodot-laravel8-worker:*
sudo supervisorctl restart infodot-laravel8-websockets
```

### Database Rollback

```bash
# If data was modified in Laravel 11, restore backup
mysql -u root -p infodot_laravel8 < backup_YYYYMMDD_HHMMSS.sql

# Verify restoration
mysql -u root -p -e "SELECT COUNT(*) FROM users" infodot_laravel8
```

### Complete Rollback

```bash
# 1. Stop Laravel 11 services
sudo supervisorctl stop infodot-worker:*
sudo supervisorctl stop infodot-reverb

# 2. Start Laravel 8 services
sudo supervisorctl start infodot-laravel8-worker:*
sudo supervisorctl start infodot-laravel8-websockets

# 3. Switch Nginx configuration
sudo ln -sf /etc/nginx/sites-available/infodot-laravel8 /etc/nginx/sites-enabled/infodot
sudo systemctl reload nginx

# 4. Verify Laravel 8 is working
curl -I https://infodot.com
```

## Post-Migration Tasks

### 1. Update DNS (if using new server)

```bash
# Update A record to point to new server
# Wait for DNS propagation (up to 48 hours)
```

### 2. Configure Search Indexes

```bash
cd /var/www/infodot-laravel11

# Import search indexes
php artisan scout:import "App\Models\Question"
php artisan scout:import "App\Models\Solution"
php artisan scout:import "App\Models\User"

# Verify search works
php artisan tinker
>>> Question::search('test')->get();
```

### 3. Set Up Monitoring

```bash
# Configure Laravel Telescope (staging only)
php artisan telescope:install
php artisan migrate

# Configure Laravel Horizon
php artisan horizon:install
php artisan migrate

# Set up log monitoring
tail -f storage/logs/laravel.log
```

### 4. Update Documentation

- [ ] Update internal documentation
- [ ] Update API documentation
- [ ] Update user guides
- [ ] Update deployment procedures

### 5. Notify Users

- [ ] Send email notification about successful migration
- [ ] Announce new features (if any)
- [ ] Provide support contact information

### 6. Clean Up

After confirming Laravel 11 is stable (1-2 weeks):

```bash
# Archive Laravel 8 application
tar -czf infodot-laravel8-archive.tar.gz /var/www/infodot-laravel8

# Move to archive storage
mv infodot-laravel8-archive.tar.gz /backups/archives/

# Remove Laravel 8 application (after confirming backup)
# rm -rf /var/www/infodot-laravel8
```

## Troubleshooting

### Common Issues

**Issue**: Database connection error
```bash
# Solution: Check database credentials in .env
php artisan config:clear
php artisan migrate:status
```

**Issue**: Missing files
```bash
# Solution: Re-run file migration
rsync -av /var/www/infodot-laravel8/storage/app/ /var/www/infodot-laravel11/storage/app/
```

**Issue**: Search not working
```bash
# Solution: Reimport search indexes
php artisan scout:flush "App\Models\Question"
php artisan scout:import "App\Models\Question"
```

**Issue**: WebSocket connection failed
```bash
# Solution: Check Reverb configuration
php artisan reverb:start
# Verify REVERB_* variables in .env
```

**Issue**: Queue jobs not processing
```bash
# Solution: Restart queue workers
sudo supervisorctl restart infodot-worker:*
php artisan queue:failed
php artisan queue:retry all
```

## Support

For migration support:
- **Email**: migration-support@infodot.com
- **Documentation**: https://docs.infodot.com/migration
- **Emergency**: +1-XXX-XXX-XXXX

---

**Last Updated**: January 15, 2026
