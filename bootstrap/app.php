<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add HTTPS redirect middleware (production only)
        $middleware->web(prepend: [
            \App\Http\Middleware\ForceHttps::class,
        ]);
        
        // Add security headers middleware to web routes
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        // Register rate limiting middleware aliases
        $middleware->alias([
            'throttle.login' => \App\Http\Middleware\RateLimitMiddleware::class.':login',
            'throttle.register' => \App\Http\Middleware\RateLimitMiddleware::class.':register',
            'throttle.password' => \App\Http\Middleware\RateLimitMiddleware::class.':password-reset',
            'throttle.contact' => \App\Http\Middleware\RateLimitMiddleware::class.':contact',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
