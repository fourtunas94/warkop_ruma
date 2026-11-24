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
        $middleware->alias([
            'auth.web' => \App\Http\Middleware\CheckWebAuth::class,
            'role.api' => \App\Http\Middleware\CheckRoleApi::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'order/midtrans-notification',
            'order/submit/*',
            'order/cek-promo/*',
        ]);
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
