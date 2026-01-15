# Caching Strategy

## Overview
This document outlines the caching strategy implemented for the InfoDot platform to improve performance and reduce database load.

## Cache Driver Configuration

### Recommended: Redis
Redis is the recommended cache driver for production due to its performance and support for cache tags.

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

### Alternative: Database
For development or when Redis is not available:

```env
CACHE_DRIVER=database
```

## Cache Layers

### 1. Model-Level Caching
Models use the `Cacheable` trait for automatic cache management:

- **Questions**: Cached with tags `['questions']`
- **Solutions**: Cached with tags `['solutions']`
- **Users**: Cached with tags `['users', 'user:{id}']`

#### Automatic Cache Invalidation
Cache is automatically cleared when models are saved or deleted.

### 2. Query-Level Caching
The `QueryOptimizationService` provides cached queries for expensive operations:

#### Popular Questions
```php
$service = app(QueryOptimizationService::class);
$popular = $service->getPopularQuestions(10);
```
- **Cache Key**: `popular_questions:{limit}`
- **Cache Tags**: `['questions', 'popular']`
- **TTL**: 5 minutes (300 seconds)

#### Recent Questions
```php
$recent = $service->getRecentQuestions(10);
```
- **Cache Key**: `recent_questions:{limit}`
- **Cache Tags**: `['questions', 'recent']`
- **TTL**: 1 minute (60 seconds)

#### Popular Solutions
```php
$popular = $service->getPopularSolutions(10);
```
- **Cache Key**: `popular_solutions:{limit}`
- **Cache Tags**: `['solutions', 'popular']`
- **TTL**: 5 minutes (300 seconds)

#### User Profile with Statistics
```php
$profile = $service->getUserProfileWithStats($userId);
```
- **Cache Key**: `user_profile_stats:{userId}`
- **Cache Tags**: `['users', 'user:{userId}']`
- **TTL**: 10 minutes (600 seconds)

#### Trending Tags
```php
$tags = $service->getTrendingTags(20);
```
- **Cache Key**: `trending_tags:{limit}`
- **Cache Tags**: `['tags', 'trending']`
- **TTL**: 1 hour (3600 seconds)

### 3. Search Results Caching
Search results are cached to improve performance:

```php
use Illuminate\Support\Facades\Cache;

$results = Cache::tags(['search'])->remember(
    "search:{$query}:{$type}",
    300, // 5 minutes
    function () use ($query, $type) {
        return Model::search($query)->get();
    }
);
```

## Cache Tags

Cache tags allow for granular cache invalidation:

| Tag | Purpose | Invalidated When |
|-----|---------|------------------|
| `questions` | All question-related cache | Question created/updated/deleted |
| `solutions` | All solution-related cache | Solution created/updated/deleted |
| `users` | All user-related cache | User updated |
| `user:{id}` | Specific user cache | Specific user updated |
| `popular` | Popular content cache | Content popularity changes |
| `recent` | Recent content cache | New content created |
| `trending` | Trending tags cache | Tags usage changes |
| `search` | Search results cache | Content updated |

## Cache Invalidation Strategies

### Automatic Invalidation
Models with the `Cacheable` trait automatically invalidate cache on save/delete:

```php
// When a question is saved
$question->save(); // Automatically clears question cache
```

### Manual Invalidation
Clear specific cache tags:

```php
use Illuminate\Support\Facades\Cache;

// Clear all question cache
Cache::tags(['questions'])->flush();

// Clear popular content cache
Cache::tags(['popular'])->flush();

// Clear specific user cache
Cache::tags(["user:{$userId}"])->flush();
```

### Service-Level Invalidation
Use the QueryOptimizationService:

```php
$service = app(QueryOptimizationService::class);

// Clear all cache
$service->clearAllCache();

// Clear specific model cache
$service->clearModelCache('questions');
```

## Cache Warming

### Scheduled Cache Warming
Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Warm popular questions cache every 5 minutes
    $schedule->call(function () {
        app(QueryOptimizationService::class)->getPopularQuestions(10);
    })->everyFiveMinutes();

    // Warm trending tags cache every hour
    $schedule->call(function () {
        app(QueryOptimizationService::class)->getTrendingTags(20);
    })->hourly();
}
```

## Cache Monitoring

### Cache Hit Rate
Monitor cache effectiveness:

```php
// In a middleware or service provider
$hits = Cache::get('cache_hits', 0);
$misses = Cache::get('cache_misses', 0);
$hitRate = $hits / ($hits + $misses) * 100;

// Log or report hit rate
Log::info("Cache hit rate: {$hitRate}%");
```

### Cache Size
Monitor cache memory usage (Redis):

```bash
redis-cli INFO memory
```

## Best Practices

### 1. Cache TTL Guidelines
- **Static content**: 1 hour - 1 day
- **Popular content**: 5-10 minutes
- **Recent content**: 1-2 minutes
- **User-specific**: 10-15 minutes
- **Search results**: 5 minutes

### 2. Cache Key Naming
Use descriptive, hierarchical keys:
- `model:action:params` (e.g., `questions:popular:10`)
- `user:{id}:resource` (e.g., `user:123:profile`)

### 3. Cache Tags Usage
Always use tags for related cache entries:
```php
Cache::tags(['questions', 'popular'])->remember(...);
```

### 4. Avoid Over-Caching
Don't cache:
- Frequently changing data
- User-specific sensitive data
- Very small queries (< 10ms)
- Data that must be real-time

### 5. Cache Stampede Prevention
Use cache locks for expensive operations:

```php
$value = Cache::lock('expensive-operation')->get(function () {
    return Cache::remember('expensive-data', 3600, function () {
        // Expensive operation
    });
});
```

## Performance Metrics

### Before Caching
- Popular questions query: ~200ms
- User profile with stats: ~150ms
- Trending tags: ~500ms
- Search queries: ~300ms

### After Caching (Target)
- Popular questions query: ~5ms (cache hit)
- User profile with stats: ~5ms (cache hit)
- Trending tags: ~5ms (cache hit)
- Search queries: ~10ms (cache hit)

### Expected Cache Hit Rates
- Popular content: 90%+
- User profiles: 80%+
- Search results: 70%+
- Trending tags: 95%+

## Troubleshooting

### Cache Not Working
1. Verify cache driver: `php artisan cache:clear`
2. Check Redis connection: `redis-cli ping`
3. Verify cache configuration in `.env`
4. Check cache permissions (file driver)

### High Memory Usage
1. Reduce cache TTL
2. Implement cache size limits
3. Use cache eviction policies
4. Monitor and clear unused cache

### Stale Data
1. Verify cache invalidation logic
2. Check model event listeners
3. Reduce cache TTL
4. Implement manual cache clearing

### Cache Stampede
1. Implement cache locks
2. Use probabilistic early expiration
3. Stagger cache expiration times
4. Implement queue-based cache warming

## Redis Configuration

### Production Settings
```redis
# /etc/redis/redis.conf

# Memory management
maxmemory 2gb
maxmemory-policy allkeys-lru

# Persistence (optional)
save 900 1
save 300 10
save 60 10000

# Performance
tcp-backlog 511
timeout 0
tcp-keepalive 300
```

### Laravel Redis Configuration
```php
// config/database.php

'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],
    
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```

## Testing Cache

### Unit Tests
```php
public function test_cache_stores_data()
{
    Cache::put('test-key', 'test-value', 60);
    $this->assertEquals('test-value', Cache::get('test-key'));
}

public function test_cache_invalidates_on_model_save()
{
    $question = Questions::factory()->create();
    Cache::tags(['questions'])->put('test', 'value', 60);
    
    $question->update(['question' => 'Updated']);
    
    $this->assertNull(Cache::tags(['questions'])->get('test'));
}
```

### Performance Tests
```php
public function test_cached_query_is_faster()
{
    $service = app(QueryOptimizationService::class);
    
    // First call (cache miss)
    $start = microtime(true);
    $service->getPopularQuestions(10);
    $firstCallTime = microtime(true) - $start;
    
    // Second call (cache hit)
    $start = microtime(true);
    $service->getPopularQuestions(10);
    $secondCallTime = microtime(true) - $start;
    
    $this->assertLessThan($firstCallTime, $secondCallTime);
}
```

## References

- [Laravel Caching Documentation](https://laravel.com/docs/11.x/cache)
- [Redis Documentation](https://redis.io/documentation)
- [Cache Stampede Prevention](https://en.wikipedia.org/wiki/Cache_stampede)
- [Cache Invalidation Strategies](https://martinfowler.com/bliki/TwoHardThings.html)
