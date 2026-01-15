# Queue Configuration Guide

## Overview
This document outlines the queue worker configuration for the InfoDot platform to handle background jobs efficiently and reliably.

## Queue Driver Configuration

### Recommended: Redis
Redis is the recommended queue driver for production due to its performance and reliability.

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
```

### Alternative: Database
For development or when Redis is not available:

```env
QUEUE_CONNECTION=database
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
```

## Queue Priorities

### Queue Names
Different queues for different job priorities:

- `high` - Critical jobs (email notifications, real-time updates)
- `default` - Standard jobs (file processing, cache warming)
- `low` - Non-critical jobs (analytics, cleanup tasks)

### Configuration
Update `config/queue.php` to add priority queues:

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
        'after_commit' => false,
    ],
],
```

## Queue Workers

### Starting Queue Workers

#### Development
```bash
php artisan queue:work
```

#### Production (with options)
```bash
php artisan queue:work redis --queue=high,default,low --tries=3 --timeout=60
```

### Worker Options

| Option | Description | Recommended Value |
|--------|-------------|-------------------|
| `--queue` | Queue priority order | `high,default,low` |
| `--tries` | Max attempts before failing | `3` |
| `--timeout` | Max execution time (seconds) | `60` |
| `--sleep` | Seconds to sleep when no jobs | `3` |
| `--max-jobs` | Max jobs before restart | `1000` |
| `--max-time` | Max time before restart (seconds) | `3600` |
| `--memory` | Max memory before restart (MB) | `128` |

### Example Worker Commands

#### High Priority Worker
```bash
php artisan queue:work redis --queue=high --tries=3 --timeout=30 --sleep=1
```

#### Default Worker
```bash
php artisan queue:work redis --queue=default --tries=3 --timeout=60 --sleep=3
```

#### Low Priority Worker
```bash
php artisan queue:work redis --queue=low --tries=2 --timeout=120 --sleep=5
```

## Supervisor Configuration

### Installation
```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor

# Start supervisor
sudo systemctl start supervisor
sudo systemctl enable supervisor
```

### Configuration File
Create `/etc/supervisor/conf.d/infodot-worker.conf`:

```ini
[program:infodot-worker-high]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/infodot-laravel11/artisan queue:work redis --queue=high --tries=3 --timeout=30 --sleep=1 --max-jobs=1000 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/infodot-laravel11/storage/logs/worker-high.log
stopwaitsecs=3600

[program:infodot-worker-default]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/infodot-laravel11/artisan queue:work redis --queue=default --tries=3 --timeout=60 --sleep=3 --max-jobs=1000 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/infodot-laravel11/storage/logs/worker-default.log
stopwaitsecs=3600

[program:infodot-worker-low]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/infodot-laravel11/artisan queue:work redis --queue=low --tries=2 --timeout=120 --sleep=5 --max-jobs=500 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/infodot-laravel11/storage/logs/worker-low.log
stopwaitsecs=3600
```

### Supervisor Commands

```bash
# Reload configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start infodot-worker-high:*
sudo supervisorctl start infodot-worker-default:*
sudo supervisorctl start infodot-worker-low:*

# Stop workers
sudo supervisorctl stop infodot-worker-high:*
sudo supervisorctl stop infodot-worker-default:*
sudo supervisorctl stop infodot-worker-low:*

# Restart workers
sudo supervisorctl restart infodot-worker-high:*
sudo supervisorctl restart infodot-worker-default:*
sudo supervisorctl restart infodot-worker-low:*

# Check status
sudo supervisorctl status

# View logs
sudo supervisorctl tail -f infodot-worker-default:infodot-worker-default_00 stdout
```

## Job Dispatching

### Dispatching to Specific Queues

#### High Priority (Notifications, Real-time)
```php
use App\Jobs\SendEmailNotification;

SendEmailNotification::dispatch($user, $data)
    ->onQueue('high');
```

#### Default Priority (Standard Processing)
```php
use App\Jobs\ProcessUpload;

ProcessUpload::dispatch($file)
    ->onQueue('default');
```

#### Low Priority (Cleanup, Analytics)
```php
use App\Jobs\CleanupOldRecords;

CleanupOldRecords::dispatch()
    ->onQueue('low');
```

### Delayed Jobs
```php
use App\Jobs\SendReminder;

SendReminder::dispatch($user)
    ->delay(now()->addHours(24))
    ->onQueue('default');
```

### Job Chaining
```php
use App\Jobs\ProcessUpload;
use App\Jobs\OptimizeImage;
use App\Jobs\GenerateThumbnail;

ProcessUpload::withChain([
    new OptimizeImage($file),
    new GenerateThumbnail($file),
])->dispatch($file);
```

## Job Examples

### Email Notification Job
```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\QuestionAnsweredNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendQuestionAnsweredNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [10, 30, 60]; // Retry after 10s, 30s, 60s

    public function __construct(
        public User $user,
        public int $questionId,
        public int $answerId
    ) {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        $this->user->notify(
            new QuestionAnsweredNotification($this->questionId, $this->answerId)
        );
    }

    public function failed(\Throwable $exception): void
    {
        // Log failure
        \Log::error('Failed to send question answered notification', [
            'user_id' => $this->user->id,
            'question_id' => $this->questionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### File Processing Job
```php
<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 120;

    public function __construct(
        public File $file
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        // Process file
        $this->file->process();
        
        // Generate thumbnails if image
        if ($this->file->isImage()) {
            $this->file->generateThumbnails();
        }
        
        // Update status
        $this->file->update(['status' => 'processed']);
    }
}
```

## Laravel Horizon (Optional)

### Installation
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

### Configuration
Update `config/horizon.php`:

```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['high'],
            'balance' => 'auto',
            'processes' => 2,
            'tries' => 3,
            'timeout' => 30,
        ],
        'supervisor-2' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'processes' => 4,
            'tries' => 3,
            'timeout' => 60,
        ],
        'supervisor-3' => [
            'connection' => 'redis',
            'queue' => ['low'],
            'balance' => 'auto',
            'processes' => 1,
            'tries' => 2,
            'timeout' => 120,
        ],
    ],
],
```

### Starting Horizon
```bash
php artisan horizon
```

### Supervisor Configuration for Horizon
```ini
[program:infodot-horizon]
process_name=%(program_name)s
command=php /path/to/infodot-laravel11/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/infodot-laravel11/storage/logs/horizon.log
stopwaitsecs=3600
```

### Horizon Dashboard
Access at: `http://yourdomain.com/horizon`

## Monitoring

### Queue Metrics
Monitor these metrics:
- Jobs processed per minute
- Average job duration
- Failed job rate
- Queue depth (pending jobs)
- Worker memory usage

### Artisan Commands

#### View Failed Jobs
```bash
php artisan queue:failed
```

#### Retry Failed Job
```bash
php artisan queue:retry {id}
```

#### Retry All Failed Jobs
```bash
php artisan queue:retry all
```

#### Flush Failed Jobs
```bash
php artisan queue:flush
```

#### Monitor Queue
```bash
php artisan queue:monitor redis:high,redis:default,redis:low --max=100
```

### Logging
Configure queue logging in `config/logging.php`:

```php
'channels' => [
    'queue' => [
        'driver' => 'daily',
        'path' => storage_path('logs/queue.log'),
        'level' => 'info',
        'days' => 14,
    ],
],
```

## Performance Optimization

### Worker Scaling
Scale workers based on load:

| Load Level | High Workers | Default Workers | Low Workers |
|------------|--------------|-----------------|-------------|
| Low | 1 | 2 | 1 |
| Medium | 2 | 4 | 1 |
| High | 4 | 8 | 2 |
| Peak | 8 | 16 | 4 |

### Job Optimization
1. **Keep jobs small**: Break large jobs into smaller chunks
2. **Use job batching**: Group related jobs together
3. **Implement timeouts**: Prevent jobs from running indefinitely
4. **Use job middleware**: Add rate limiting, authentication, etc.
5. **Monitor memory**: Restart workers when memory usage is high

### Redis Optimization
```redis
# /etc/redis/redis.conf

# Memory management
maxmemory 2gb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Performance
tcp-backlog 511
timeout 0
tcp-keepalive 300
```

## Troubleshooting

### Workers Not Processing Jobs
1. Check worker status: `sudo supervisorctl status`
2. Check Redis connection: `redis-cli ping`
3. Check queue configuration in `.env`
4. View worker logs: `tail -f storage/logs/worker-default.log`

### High Memory Usage
1. Reduce `--max-jobs` option
2. Reduce `--max-time` option
3. Increase worker restart frequency
4. Check for memory leaks in jobs

### Failed Jobs
1. View failed jobs: `php artisan queue:failed`
2. Check error logs: `storage/logs/laravel.log`
3. Retry failed jobs: `php artisan queue:retry all`
4. Fix underlying issue and retry

### Slow Job Processing
1. Check worker count
2. Monitor queue depth
3. Optimize job code
4. Add more workers
5. Use job batching

## Testing

### Testing Jobs
```php
use App\Jobs\SendEmailNotification;
use Illuminate\Support\Facades\Queue;

public function test_job_is_dispatched()
{
    Queue::fake();
    
    // Trigger job dispatch
    $user = User::factory()->create();
    SendEmailNotification::dispatch($user);
    
    // Assert job was dispatched
    Queue::assertPushed(SendEmailNotification::class);
}

public function test_job_processes_correctly()
{
    $user = User::factory()->create();
    $job = new SendEmailNotification($user);
    
    // Execute job
    $job->handle();
    
    // Assert expected outcome
    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $user->id,
    ]);
}
```

## Deployment

### Deploying Queue Changes
```bash
# 1. Stop workers gracefully
sudo supervisorctl stop infodot-worker-high:*
sudo supervisorctl stop infodot-worker-default:*
sudo supervisorctl stop infodot-worker-low:*

# 2. Deploy code changes
git pull origin main
composer install --no-dev --optimize-autoloader

# 3. Restart workers
sudo supervisorctl start infodot-worker-high:*
sudo supervisorctl start infodot-worker-default:*
sudo supervisorctl start infodot-worker-low:*

# 4. Verify workers are running
sudo supervisorctl status
```

### Zero-Downtime Deployment
```bash
# 1. Start new workers with new code
sudo supervisorctl start infodot-worker-new:*

# 2. Wait for old workers to finish current jobs
sleep 60

# 3. Stop old workers
sudo supervisorctl stop infodot-worker-old:*

# 4. Rename new workers to old
sudo supervisorctl update
```

## References

- [Laravel Queues Documentation](https://laravel.com/docs/11.x/queues)
- [Laravel Horizon Documentation](https://laravel.com/docs/11.x/horizon)
- [Supervisor Documentation](http://supervisord.org/)
- [Redis Queue Best Practices](https://redis.io/topics/queues)
