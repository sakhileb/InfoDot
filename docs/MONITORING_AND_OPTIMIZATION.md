# Monitoring and Optimization Guide

This guide provides instructions for monitoring the InfoDot Laravel 11 application and optimizing its performance in production.

## Monitoring Strategy

### What to Monitor

1. **Application Performance**
   - Response times
   - Throughput (requests per second)
   - Error rates
   - Database query performance

2. **Infrastructure**
   - CPU usage
   - Memory usage
   - Disk usage
   - Network traffic

3. **Services**
   - Web server (Nginx)
   - PHP-FPM
   - Database (MySQL)
   - Cache (Redis)
   - Queue workers
   - WebSocket server (Reverb)

4. **Business Metrics**
   - User registrations
   - Questions posted
   - Answers posted
   - Solutions created
   - Active users

## Monitoring Tools

### Laravel Telescope (Development/Staging)

**Installation**:
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Access**: `https://infodot.com/telescope`

**Features**:
- Request monitoring
- Exception tracking
- Database query logging
- Job monitoring
- Mail preview
- Cache operations
- Redis commands

**Configuration** (`config/telescope.php`):
```php
'enabled' => env('TELESCOPE_ENABLED', false),
'path' => 'telescope',
'storage' => [
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'chunk' => 1000,
    ],
],
'watchers' => [
    Watchers\QueryWatcher::class => env('TELESCOPE_QUERY_WATCHER', true),
    Watchers\RequestWatcher::class => env('TELESCOPE_REQUEST_WATCHER', true),
    // ... other watchers
],
```

### Laravel Horizon (Queue Monitoring)

**Installation**:
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

**Access**: `https://infodot.com/horizon`

**Features**:
- Real-time queue monitoring
- Job throughput metrics
- Failed job management
- Worker configuration
- Job retry management

**Configuration** (`config/horizon.php`):
```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
            'timeout' => 300,
        ],
    ],
],
```

### Application Logs

**Location**: `storage/logs/laravel.log`

**View logs**:
```bash
# Tail logs
tail -f storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log

# Count errors by type
grep "ERROR" storage/logs/laravel.log | cut -d' ' -f5 | sort | uniq -c | sort -rn
```

**Log rotation** (configure in `/etc/logrotate.d/laravel`):
```
/var/www/infodot-laravel11/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        php /var/www/infodot-laravel11/artisan cache:clear > /dev/null 2>&1
    endscript
}
```

### Server Monitoring

**System Resources**:
```bash
# CPU and memory
htop

# Disk usage
df -h

# Disk I/O
iotop

# Network connections
netstat -an | grep :80 | wc -l

# Process list
ps aux | grep php
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

### Database Monitoring

**MySQL Performance**:
```bash
# Show processlist
mysql -e "SHOW PROCESSLIST;"

# Show slow queries
mysql -e "SHOW VARIABLES LIKE 'slow_query%';"

# Show status
mysql -e "SHOW STATUS LIKE 'Threads%';"
mysql -e "SHOW STATUS LIKE 'Connections';"
mysql -e "SHOW STATUS LIKE 'Slow_queries';"
```

**Slow Query Log**:
```bash
# View slow queries
tail -f /var/log/mysql/slow-query.log

# Analyze slow queries
mysqldumpslow -s t -t 10 /var/log/mysql/slow-query.log
```

### Redis Monitoring

**Redis CLI**:
```bash
# Connect to Redis
redis-cli

# Get info
INFO

# Monitor commands
MONITOR

# Get stats
INFO stats

# Check memory
INFO memory
```

**Redis Performance**:
```bash
# Check hit rate
redis-cli INFO stats | grep keyspace

# Check memory usage
redis-cli INFO memory | grep used_memory_human

# Check connected clients
redis-cli INFO clients | grep connected_clients
```

## External Monitoring Services

### Recommended Services

1. **New Relic**
   - Application performance monitoring
   - Real-time metrics
   - Error tracking
   - Transaction tracing

2. **Datadog**
   - Infrastructure monitoring
   - Application metrics
   - Log aggregation
   - Custom dashboards

3. **Sentry**
   - Error tracking
   - Performance monitoring
   - Release tracking
   - Issue management

4. **Pingdom**
   - Uptime monitoring
   - Page speed monitoring
   - Transaction monitoring
   - Alerts

### Sentry Integration

**Installation**:
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=your-dsn-here
```

**Configuration** (`.env`):
```env
SENTRY_LARAVEL_DSN=https://your-dsn@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.2
```

## Performance Optimization

### Database Optimization

**Identify Slow Queries**:
```bash
# Using Telescope
# Access /telescope/queries and sort by duration

# Using MySQL slow query log
mysqldumpslow -s t -t 10 /var/log/mysql/slow-query.log
```

**Add Indexes**:
```php
// Create migration
php artisan make:migration add_indexes_to_questions_table

// In migration
Schema::table('questions', function (Blueprint $table) {
    $table->index('user_id');
    $table->index('status');
    $table->index('created_at');
});
```

**Optimize Queries**:
```php
// Bad: N+1 query
$questions = Question::all();
foreach ($questions as $question) {
    echo $question->user->name;
}

// Good: Eager loading
$questions = Question::with('user')->get();
foreach ($questions as $question) {
    echo $question->user->name;
}
```

**Database Maintenance**:
```bash
# Optimize tables
mysql -e "OPTIMIZE TABLE questions, answers, solutions;"

# Analyze tables
mysql -e "ANALYZE TABLE questions, answers, solutions;"

# Check table status
mysql -e "SHOW TABLE STATUS LIKE 'questions';"
```

### Cache Optimization

**Cache Configuration**:
```php
// Cache frequently accessed data
$users = Cache::remember('active_users', 3600, function () {
    return User::where('active', true)->get();
});

// Cache with tags
Cache::tags(['users', 'active'])->put('active_users', $users, 3600);

// Invalidate cache
Cache::tags(['users'])->flush();
```

**Cache Warming**:
```bash
# Create cache warming command
php artisan make:command WarmCache

# In command
public function handle()
{
    Cache::remember('popular_questions', 3600, function () {
        return Question::orderBy('views', 'desc')->take(10)->get();
    });
    
    Cache::remember('popular_solutions', 3600, function () {
        return Solution::orderBy('views', 'desc')->take(10)->get();
    });
}

# Schedule in Kernel.php
$schedule->command('cache:warm')->hourly();
```

**Cache Monitoring**:
```bash
# Check cache hit rate
redis-cli INFO stats | grep keyspace_hits
redis-cli INFO stats | grep keyspace_misses

# Calculate hit rate
# Hit Rate = hits / (hits + misses) * 100
```

### Query Optimization

**Use Query Builder Efficiently**:
```php
// Bad: Loading all records
$questions = Question::all()->where('status', 'open');

// Good: Filter in database
$questions = Question::where('status', 'open')->get();

// Better: Use pagination
$questions = Question::where('status', 'open')->paginate(15);

// Best: Select only needed columns
$questions = Question::select('id', 'question', 'created_at')
    ->where('status', 'open')
    ->paginate(15);
```

**Chunk Large Datasets**:
```php
// Process large datasets in chunks
Question::chunk(1000, function ($questions) {
    foreach ($questions as $question) {
        // Process question
    }
});
```

**Use Database Transactions**:
```php
DB::transaction(function () {
    $question = Question::create([...]);
    $question->tags()->attach([...]);
    event(new QuestionCreated($question));
});
```

### Frontend Optimization

**Asset Optimization**:
```bash
# Build for production
npm run build

# Verify minification
ls -lh public/build/assets/

# Enable Gzip in Nginx
gzip on;
gzip_vary on;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript;
```

**Image Optimization**:
```bash
# Install image optimization tools
sudo apt install optipng jpegoptim

# Optimize images
find public/img -name "*.png" -exec optipng {} \;
find public/img -name "*.jpg" -exec jpegoptim --strip-all {} \;
```

**Lazy Loading**:
```html
<!-- Lazy load images -->
<img src="placeholder.jpg" data-src="actual-image.jpg" loading="lazy" alt="Description">

<!-- Lazy load components -->
<div x-data="{ loaded: false }" x-intersect="loaded = true">
    <template x-if="loaded">
        <!-- Component content -->
    </template>
</div>
```

### PHP-FPM Optimization

**Tune PHP-FPM** (`/etc/php/8.3/fpm/pool.d/www.conf`):
```ini
; Dynamic process management
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

; Process priority
process_priority = -10

; Slow log
slowlog = /var/log/php8.3-fpm-slow.log
request_slowlog_timeout = 5s
```

**Monitor PHP-FPM**:
```bash
# Enable status page in pool config
pm.status_path = /status

# Configure Nginx location
location ~ ^/(status|ping)$ {
    access_log off;
    fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}

# Check status
curl http://localhost/status
```

### OPcache Optimization

**Configure OPcache** (`/etc/php/8.3/fpm/conf.d/10-opcache.ini`):
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=0
```

**Monitor OPcache**:
```php
// Create opcache-status.php
<?php
phpinfo(INFO_MODULES);
// Look for OPcache section

// Or use opcache_get_status()
var_dump(opcache_get_status());
```

## Alerting

### Configure Alerts

**Disk Space Alert**:
```bash
# Create alert script
#!/bin/bash
THRESHOLD=80
USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')

if [ $USAGE -gt $THRESHOLD ]; then
    echo "Disk usage is ${USAGE}%" | mail -s "Disk Space Alert" admin@infodot.com
fi

# Add to cron
0 * * * * /usr/local/bin/disk-alert.sh
```

**High CPU Alert**:
```bash
# Create alert script
#!/bin/bash
THRESHOLD=80
CPU=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)

if (( $(echo "$CPU > $THRESHOLD" | bc -l) )); then
    echo "CPU usage is ${CPU}%" | mail -s "High CPU Alert" admin@infodot.com
fi

# Add to cron
*/5 * * * * /usr/local/bin/cpu-alert.sh
```

**Application Error Alert**:
```php
// In app/Exceptions/Handler.php
public function report(Throwable $exception)
{
    if ($this->shouldReport($exception)) {
        // Send to Sentry
        if (app()->bound('sentry')) {
            app('sentry')->captureException($exception);
        }
        
        // Send email for critical errors
        if ($exception instanceof CriticalException) {
            Mail::to('admin@infodot.com')->send(new ErrorAlert($exception));
        }
    }
    
    parent::report($exception);
}
```

## Performance Benchmarks

### Target Metrics

- **Homepage Load Time**: < 1 second
- **API Response Time**: < 200ms
- **Database Query Time**: < 50ms average
- **Cache Hit Rate**: > 80%
- **Error Rate**: < 0.1%
- **Uptime**: > 99.9%

### Benchmarking Tools

**Apache Bench**:
```bash
# Test homepage
ab -n 1000 -c 10 https://infodot.com/

# Test API endpoint
ab -n 1000 -c 10 -H "Authorization: Bearer token" https://infodot.com/api/user
```

**Siege**:
```bash
# Install Siege
sudo apt install siege

# Test with multiple URLs
siege -c 10 -t 1M -f urls.txt
```

**Laravel Dusk**:
```bash
# Run browser tests
php artisan dusk

# Run with performance metrics
php artisan dusk --with-performance
```

## Continuous Optimization

### Regular Tasks

**Daily**:
- Review error logs
- Check disk space
- Monitor response times
- Review failed jobs

**Weekly**:
- Analyze slow queries
- Review cache hit rates
- Check for security updates
- Review user feedback

**Monthly**:
- Database optimization
- Review and update indexes
- Performance testing
- Capacity planning

### Performance Review Checklist

- [ ] Review application logs for errors
- [ ] Check database slow query log
- [ ] Analyze cache hit rates
- [ ] Review queue job performance
- [ ] Check server resource usage
- [ ] Test page load times
- [ ] Review API response times
- [ ] Check for N+1 queries
- [ ] Verify backup completion
- [ ] Review security alerts

---

**Last Updated**: January 15, 2026
