<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(
            append: [
                \App\Http\Middleware\HandleInertiaRequests::class,
                \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,

                // Custom middleware to exclude 'access_token' from encryption
                \App\Http\Middleware\CustomEncryptCookiesMiddleware::class,
            ],
            remove: [
                // Remove the default cookie encryption middleware
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
            ]
        )->trustProxies(at: '*');

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
