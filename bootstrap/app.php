<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;

use App\Http\Middleware\CorsMiddleware;
use Illuminate\Http\Middleware\HandleCors;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        // 1) Nuestro CORS manual (o el HandleCors nativo)
        $middleware->prepend(CorsMiddleware::class);

        // 2) Si quieres que Sanctum trate las peticiones SPA como stateful,
        //    **añádelo al grupo "api"** con prependToGroup:
        $middleware->prependToGroup(
            'api',
            EnsureFrontendRequestsAreStateful::class
        );
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })

    ->create();
