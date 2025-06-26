<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // === ВОТ ОНО. ЕДИНСТВЕННОЕ, ЧТО БЫЛО НУЖНО ===
        // Мы говорим Laravel доверять любому прокси, который стоит перед ним.
        // Это исправит генерацию ссылок на стили и скрипты.
        $middleware->trustProxies(at: '*');
        
        // Эта строка здесь уже была. Оставь ее.
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();