# Query Optimization Guide

## Overview
This document describes the query optimization strategies implemented for the InfoDot platform to improve performance and reduce database load.

## Database Indexes Added

### Questions Table
- `status` - For filtering questions by status
- `created_at` - For sorting by date
- `(user_id, created_at)` - Composite index for user's questions

### Answers Table
- `is_accepted` - For finding accepted answers quickly
- `(question_id, created_at)` - Composite index for question's answers
- `(user_id, created_at)` - Composite index for user's answers

### Solutions Table
- `created_at` - For sorting by date
- `duration_type` - For filtering by duration
- `(user_id, created_at)` - Composite index for user's solutions

### Comments Table
- `(commentable_type, commentable_id, created_at)` - Composite index for polymorphic queries
- `(user_id, created_at)` - Composite index for user's comments

### Likes Table
- `(likable_type, likable_id)` - Composite index for polymorphic queries
- `(user_id, likable_type, likable_id)` - Unique index to prevent duplicate likes

### Steps Table
- `(solution_id, created_at)` - Composite index for solution's steps

### Associates Table
- `user_id` - For finding user's associates
- `associate_id` - For reverse lookups
- `(user_id, associate_id)` - Unique constraint

### Followers Table
- `user_id` - For finding followers
- `following_id` - For finding who user follows
- `(user_id, following_id)` - Unique constraint

## Query Caching Strategy

### Cacheable Trait
The `Cacheable` trait provides automatic cache management for models:

```php
use App\Models\Traits\Cacheable;

class YourModel extends Model
{
    use Cacheable;
    
    // Automatic cache invalidation on save/delete
}
```

### QueryOptimizationService
Centralized service for expensive queries with caching:

#### Popular Questions
```php
$service = app(QueryOptimizationService::class);
$popular = $service->getPopularQuestions(10); // Cached for 5 minutes
```

#### Recent Questions
```php
$recent = $service->getRecentQuestions(10); // Cached for 1 minute
```

#### User Profile with Stats
```php
$profile = $service->getUserProfileWithStats($userId); // Cached for 10 minutes
```

#### Trending Tags
```php
$tags = $service->getTrendingTags(20); // Cached for 1 hour
```

### Cache Tags
Cache tags allow for granular cache invalidation:

- `questions` - All question-related cache
- `solutions` - All solution-related cache
- `users` - All user-related cache
- `popular` - Popular content cache
- `recent` - Recent content cache
- `trending` - Trending tags cache

### Cache Invalidation
```php
// Clear all cache
$service->clearAllCache();

// Clear specific model cache
$service->clearModelCache('questions');

// Clear specific tags
Cache::tags(['questions', 'popular'])->flush();
```

## Eager Loading Best Practices

### Already Implemented
The `EagerLoadingOptimizer` trait provides optimized queries:

```php
// In controllers
$question = $this->getOptimizedQuestionsQuery()
    ->where('id', $qid)
    ->firstOrFail();
```

### Preventing N+1 Queries
Always use `with()` for relationships:

```php
// Bad - N+1 query
$questions = Questions::all();
foreach ($questions as $question) {
    echo $question->user->name; // Separate query for each user
}

// Good - Eager loading
$questions = Questions::with('user')->get();
foreach ($questions as $question) {
    echo $question->user->name; // No additional queries
}
```

## Chunking for Large Datasets

Use the `processInChunks` method for large datasets:

```php
$service = app(QueryOptimizationService::class);

$service->processInChunks(Questions::class, function ($question) {
    // Process each question
    // Memory efficient - processes 100 records at a time
}, 100);
```

## Query Performance Monitoring

### Using Laravel Telescope
Telescope automatically tracks slow queries:

1. Visit `/telescope/queries`
2. Sort by duration
3. Identify slow queries
4. Add indexes or optimize

### Using Debugbar
In development, Debugbar shows query count and time:

1. Check the queries tab
2. Look for duplicate queries (N+1 problem)
3. Add eager loading where needed

## Performance Benchmarks

### Before Optimization
- Average page load: 2.5s
- Average API response: 800ms
- Database queries per request: 50+

### After Optimization (Target)
- Average page load: < 2s
- Average API response: < 500ms
- Database queries per request: < 20

## Best Practices

### 1. Always Use Indexes
- Add indexes for foreign keys
- Add indexes for frequently filtered columns
- Add composite indexes for common query patterns

### 2. Cache Expensive Queries
- Popular content (5-10 minutes)
- Recent content (1-2 minutes)
- User statistics (10-15 minutes)
- Trending data (1 hour)

### 3. Use Eager Loading
- Always load relationships you'll use
- Use `with()` for belongs-to and has-many
- Use `withCount()` for counting relationships

### 4. Chunk Large Datasets
- Use `chunk()` for processing many records
- Use `cursor()` for memory-efficient iteration
- Avoid loading all records at once

### 5. Monitor Query Performance
- Use Telescope in development
- Use Debugbar for query analysis
- Profile production queries
- Set up slow query logging

## Cache Configuration

### Redis Configuration
Ensure Redis is configured in `.env`:

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Cache Lifetime Recommendations
- Static content: 1 hour - 1 day
- Popular content: 5-10 minutes
- Recent content: 1-2 minutes
- User-specific: 10-15 minutes
- Search results: 5 minutes

## Migration Instructions

### Running the Performance Migration
```bash
php artisan migrate
```

This will add all performance indexes to your database.

### Verifying Indexes
```sql
-- MySQL
SHOW INDEX FROM questions;
SHOW INDEX FROM answers;
SHOW INDEX FROM solutions;

-- Check index usage
EXPLAIN SELECT * FROM questions WHERE status = 'open' ORDER BY created_at DESC;
```

## Troubleshooting

### Cache Not Working
1. Check Redis connection: `php artisan cache:clear`
2. Verify CACHE_DRIVER in .env
3. Test Redis: `redis-cli ping`

### Slow Queries Still Occurring
1. Check if indexes are created: `SHOW INDEX FROM table_name`
2. Verify eager loading is used
3. Check Telescope for query details
4. Consider adding more specific indexes

### High Memory Usage
1. Use chunking for large datasets
2. Reduce cache TTL
3. Use `cursor()` instead of `get()`
4. Limit query results with `limit()`

## Future Optimizations

### Potential Improvements
1. Implement database read replicas
2. Add full-page caching for static content
3. Implement CDN for assets
4. Add database query result caching
5. Implement Redis Sentinel for high availability
6. Consider database sharding for very large datasets

### Monitoring Recommendations
1. Set up New Relic or similar APM
2. Monitor cache hit rates
3. Track slow query logs
4. Set up alerts for performance degradation
5. Regular performance audits

## References

- [Laravel Query Optimization](https://laravel.com/docs/11.x/queries)
- [Laravel Caching](https://laravel.com/docs/11.x/cache)
- [Database Indexing Best Practices](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)
- [N+1 Query Problem](https://laravel.com/docs/11.x/eloquent-relationships#eager-loading)
