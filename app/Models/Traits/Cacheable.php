<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    /**
     * Cache a query result with automatic invalidation
     *
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds (default: 1 hour)
     * @param callable $callback Query callback
     * @return mixed
     */
    public static function cacheQuery(string $key, int $ttl = 3600, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Cache a query result with tags for easy invalidation
     *
     * @param array $tags Cache tags
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds
     * @param callable $callback Query callback
     * @return mixed
     */
    public static function cacheQueryWithTags(array $tags, string $key, int $ttl = 3600, callable $callback): mixed
    {
        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache by tags
     *
     * @param array $tags
     * @return void
     */
    public static function invalidateCacheTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    /**
     * Invalidate a specific cache key
     *
     * @param string $key
     * @return void
     */
    public static function invalidateCache(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Get cache key for model instance
     *
     * @param string $suffix
     * @return string
     */
    public function getCacheKey(string $suffix = ''): string
    {
        $class = class_basename($this);
        $id = $this->getKey();
        
        return $suffix 
            ? "{$class}:{$id}:{$suffix}"
            : "{$class}:{$id}";
    }

    /**
     * Get cache tags for model
     *
     * @return array
     */
    public function getCacheTags(): array
    {
        return [class_basename($this)];
    }

    /**
     * Boot the trait and set up model event listeners for cache invalidation
     */
    protected static function bootCacheable(): void
    {
        // Invalidate cache when model is saved
        static::saved(function ($model) {
            if (method_exists($model, 'clearModelCache')) {
                $model->clearModelCache();
            }
        });

        // Invalidate cache when model is deleted
        static::deleted(function ($model) {
            if (method_exists($model, 'clearModelCache')) {
                $model->clearModelCache();
            }
        });
    }

    /**
     * Clear cache for this model instance
     * Override this method in your model to define custom cache clearing logic
     */
    public function clearModelCache(): void
    {
        // Clear instance-specific cache
        $this->invalidateCache($this->getCacheKey());
        
        // Clear tagged cache
        $this->invalidateCacheTags($this->getCacheTags());
    }
}
