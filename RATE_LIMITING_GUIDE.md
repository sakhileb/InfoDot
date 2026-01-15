# Rate Limiting Configuration Guide

## Overview

This document describes the rate limiting implementation for the InfoDot platform. Rate limiting protects the application from abuse, brute-force attacks, and excessive API usage by limiting the number of requests a user can make within a specific time window.

## Rate Limiting Strategy

### Web Routes Rate Limiting

#### Authentication Routes (Fortify)
- **Login**: 5 attempts per minute per email/IP combination
- **Two-Factor Authentication**: 5 attempts per minute per session
- **Registration**: 3 attempts per hour per IP address
- **Password Reset**: 3 attempts per hour per IP address

#### Public Routes
- **Contact Form**: 5 submissions per 5 minutes per IP address

### API Routes Rate Limiting

#### Public API Endpoints
- **Rate Limit**: 60 requests per minute per IP address
- **Applies to**: All unauthenticated API requests
- **Headers**: Returns `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers

#### Authenticated API Endpoints
- **Rate Limit**: 60 requests per minute per user/IP combination
- **Applies to**: All authenticated API requests (Sanctum tokens)
- **Headers**: Returns `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers

## Implementation Details

### Rate Limiter Configuration

Rate limiters are configured in `app/Providers/FortifyServiceProvider.php`:

```php
// Login rate limiting
RateLimiter::for('login', function (Request $request) {
    $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
    return Limit::perMinute(5)->by($throttleKey);
});

// Registration rate limiting
RateLimiter::for('register', function (Request $request) {
    return Limit::perHour(3)->by($request->ip());
});

// Password reset rate limiting
RateLimiter::for('password-reset', function (Request $request) {
    return Limit::perHour(3)->by($request->ip());
});
```

### Custom Rate Limiting Middleware

The `RateLimitMiddleware` class (`app/Http/Middleware/RateLimitMiddleware.php`) provides flexible rate limiting with:

- **Configurable Limits**: Different limits for different route types
- **User-based Tracking**: Tracks authenticated users separately from IP addresses
- **Response Headers**: Adds rate limit information to responses
- **Custom Error Messages**: Returns clear error messages when limits are exceeded

### Middleware Registration

Rate limiting middleware is registered in `bootstrap/app.php`:

```php
$middleware->alias([
    'throttle.login' => \App\Http\Middleware\RateLimitMiddleware::class.':login',
    'throttle.register' => \App\Http\Middleware\RateLimitMiddleware::class.':register',
    'throttle.password' => \App\Http\Middleware\RateLimitMiddleware::class.':password-reset',
    'throttle.contact' => \App\Http\Middleware\RateLimitMiddleware::class.':contact',
]);
```

### Route Application

#### Web Routes
```php
// Contact form with rate limiting
Route::post('/contact-send', [PagesController::class, 'contactSend'])
    ->name('send-contact')
    ->middleware('throttle.contact');
```

#### API Routes
```php
// Public API routes with rate limiting
Route::get('answers', [AnswerController::class, 'index'])
    ->middleware('throttle:60,1');

// Authenticated API routes with rate limiting
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('answers', [AnswerController::class, 'store']);
    // ... other routes
});
```

## Rate Limit Response

### Success Response Headers
When a request is within the rate limit, the following headers are included:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
```

### Rate Limit Exceeded Response
When the rate limit is exceeded, a 429 (Too Many Requests) response is returned:

```json
{
    "message": "Too many attempts. Please try again later.",
    "retry_after": 45
}
```

**HTTP Status Code**: 429 Too Many Requests

**Headers**:
- `Retry-After`: Number of seconds until the rate limit resets
- `X-RateLimit-Limit`: Maximum number of requests allowed
- `X-RateLimit-Remaining`: 0

## Testing Rate Limiting

### Running Rate Limiting Tests

```bash
php artisan test --filter=RateLimitingTest
```

### Test Coverage

The `RateLimitingTest` class includes tests for:

1. **Login Rate Limiting**: Verifies 5 attempts per minute limit
2. **Registration Rate Limiting**: Verifies 3 attempts per hour limit
3. **Password Reset Rate Limiting**: Verifies 3 attempts per hour limit
4. **Contact Form Rate Limiting**: Verifies 5 attempts per 5 minutes limit
5. **API Rate Limiting**: Verifies 60 requests per minute limit
6. **Authenticated API Rate Limiting**: Verifies separate limits for authenticated users
7. **Rate Limit Headers**: Verifies headers are present in responses
8. **Separate User Limits**: Verifies different users have independent rate limits

### Manual Testing

#### Testing Login Rate Limiting
```bash
# Make 6 login attempts with wrong password
for i in {1..6}; do
    curl -X POST http://localhost/login \
        -d "email=test@example.com" \
        -d "password=wrong" \
        -d "_token=YOUR_CSRF_TOKEN"
done
```

#### Testing API Rate Limiting
```bash
# Make 61 API requests
for i in {1..61}; do
    curl -X GET http://localhost/api/answers \
        -H "Accept: application/json"
done
```

## Customizing Rate Limits

### Adjusting Rate Limits

To adjust rate limits, modify the `getMaxAttempts()` and `getDecaySeconds()` methods in `RateLimitMiddleware`:

```php
protected function getMaxAttempts(string $limiter): int
{
    return match ($limiter) {
        'login' => 5,        // Change to desired limit
        'register' => 3,     // Change to desired limit
        'api' => 60,         // Change to desired limit
        default => 60,
    };
}

protected function getDecaySeconds(string $limiter): int
{
    return match ($limiter) {
        'login' => 300,      // 5 minutes
        'register' => 600,   // 10 minutes
        'api' => 60,         // 1 minute
        default => 60,
    };
}
```

### Adding New Rate Limiters

1. **Add limiter configuration** in `getMaxAttempts()` and `getDecaySeconds()`
2. **Register middleware alias** in `bootstrap/app.php`
3. **Apply to routes** in route files

Example:
```php
// In RateLimitMiddleware
'search' => 30,  // 30 requests per minute

// In bootstrap/app.php
'throttle.search' => \App\Http\Middleware\RateLimitMiddleware::class.':search',

// In routes/web.php
Route::get('/search', [SearchController::class, 'index'])
    ->middleware('throttle.search');
```

## Best Practices

### 1. Different Limits for Different Actions
- **Sensitive actions** (login, registration): Lower limits (3-5 per hour)
- **Read operations**: Higher limits (60+ per minute)
- **Write operations**: Moderate limits (10-30 per minute)

### 2. User-based vs IP-based Limiting
- **Authenticated users**: Track by user ID + IP
- **Unauthenticated users**: Track by IP only
- **Prevents**: Account sharing abuse while allowing legitimate use

### 3. Clear Error Messages
- Always return clear error messages with retry information
- Include `Retry-After` header for client guidance
- Log rate limit violations for monitoring

### 4. Monitoring and Alerting
- Monitor rate limit violations in logs
- Set up alerts for unusual patterns
- Track legitimate users hitting limits (may need adjustment)

### 5. Graceful Degradation
- Return informative error messages
- Provide retry timing information
- Consider implementing exponential backoff for repeated violations

## Security Considerations

### 1. Brute Force Protection
- Login rate limiting prevents password guessing attacks
- Registration rate limiting prevents account creation spam
- Password reset rate limiting prevents email flooding

### 2. API Abuse Prevention
- API rate limiting prevents excessive resource consumption
- Separate limits for authenticated users prevent token abuse
- Per-user tracking prevents distributed attacks

### 3. DDoS Mitigation
- Rate limiting provides basic DDoS protection
- Consider additional layers (CDN, WAF) for production
- Monitor for distributed attacks across multiple IPs

### 4. Resource Protection
- Prevents database overload from excessive queries
- Protects email services from spam
- Reduces server load from automated requests

## Production Recommendations

### 1. Use Redis for Rate Limiting
Configure Redis as the cache driver for better performance:

```env
CACHE_DRIVER=redis
REDIS_CLIENT=phpredis
```

### 2. Monitor Rate Limit Violations
Set up logging and monitoring:

```php
// In RateLimitMiddleware
if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
    Log::warning('Rate limit exceeded', [
        'ip' => $request->ip(),
        'user_id' => $request->user()?->id,
        'limiter' => $limiter,
    ]);
}
```

### 3. Adjust Limits Based on Usage
- Monitor legitimate user patterns
- Adjust limits to balance security and usability
- Consider different limits for different user tiers

### 4. Implement Exponential Backoff
For repeated violations, consider increasing the lockout period:

```php
$violations = RateLimiter::attempts($key);
$backoffMultiplier = min($violations, 5);
$decaySeconds = $this->getDecaySeconds($limiter) * $backoffMultiplier;
```

## Troubleshooting

### Rate Limit Not Working
1. Check middleware is registered in `bootstrap/app.php`
2. Verify middleware is applied to routes
3. Check cache driver is configured correctly
4. Clear cache: `php artisan cache:clear`

### Users Hitting Limits Too Quickly
1. Review rate limit configuration
2. Check for legitimate high-usage patterns
3. Consider increasing limits for authenticated users
4. Implement user-tier based limits

### Rate Limits Not Resetting
1. Verify cache driver is working
2. Check Redis connection if using Redis
3. Clear rate limiter: `RateLimiter::clear($key)`
4. Restart cache service

## Related Files

- `app/Http/Middleware/RateLimitMiddleware.php` - Custom rate limiting middleware
- `app/Providers/FortifyServiceProvider.php` - Fortify rate limiter configuration
- `bootstrap/app.php` - Middleware registration
- `routes/web.php` - Web route rate limiting
- `routes/api.php` - API route rate limiting
- `tests/Feature/RateLimitingTest.php` - Rate limiting tests

## References

- [Laravel Rate Limiting Documentation](https://laravel.com/docs/11.x/routing#rate-limiting)
- [Laravel Fortify Documentation](https://laravel.com/docs/11.x/fortify)
- [HTTP 429 Status Code](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429)
