<?php

use App\Http\Middleware\EnsureUserIsArtist;
use App\Http\Middleware\PrelaunchMode;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust only the configured reverse proxies so X-Forwarded-For is
        // resolved correctly. Use APP_TRUSTED_PROXIES=* to trust all (dev/local),
        // or a comma-separated list of proxy IPs for production.
        $middleware->trustProxies(at: env('APP_TRUSTED_PROXIES', '*'));

        $middleware->web(prepend: [
            PrelaunchMode::class,
        ]);

        $middleware->alias([
            'artist' => EnsureUserIsArtist::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
