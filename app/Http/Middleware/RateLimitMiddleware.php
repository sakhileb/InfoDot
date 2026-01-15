<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiter = 'global'): Response
    {
        $key = $this->resolveRequestSignature($request, $limiter);

        if (RateLimiter::tooManyAttempts($key, $this->getMaxAttempts($limiter))) {
            return response()->json([
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, $this->getDecaySeconds($limiter));

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $this->getMaxAttempts($limiter),
            $this->calculateRemainingAttempts($key, $this->getMaxAttempts($limiter))
        );
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request, string $limiter): string
    {
        if ($request->user()) {
            return sprintf(
                '%s|%s|%s',
                $limiter,
                $request->user()->id,
                $request->ip()
            );
        }

        return sprintf(
            '%s|%s',
            $limiter,
            $request->ip()
        );
    }

    /**
     * Get the maximum number of attempts for the limiter.
     */
    protected function getMaxAttempts(string $limiter): int
    {
        return match ($limiter) {
            'login' => 5,
            'register' => 3,
            'password-reset' => 3,
            'contact' => 5,
            'api' => 60,
            default => 60,
        };
    }

    /**
     * Get the decay time in seconds for the limiter.
     */
    protected function getDecaySeconds(string $limiter): int
    {
        return match ($limiter) {
            'login' => 300, // 5 minutes
            'register' => 600, // 10 minutes
            'password-reset' => 600, // 10 minutes
            'contact' => 300, // 5 minutes
            'api' => 60, // 1 minute
            default => 60, // 1 minute
        };
    }

    /**
     * Calculate the number of remaining attempts.
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - RateLimiter::attempts($key));
    }

    /**
     * Add rate limit headers to the response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);

        return $response;
    }
}
