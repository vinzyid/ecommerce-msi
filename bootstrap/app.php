<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\EnsureUserIsAdmin;
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
        // Di produksi aplikasi berada di belakang Caddy. Port container hanya dibuka ke
        // 127.0.0.1, jadi satu-satunya proxy yang bisa menjangkaunya adalah Caddy di host.
        // Tanpa ini Laravel mengira request-nya HTTP dan membuat link http://.
        $middleware->trustProxies(at: '*');
        $middleware->append(AddSecurityHeaders::class);
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
