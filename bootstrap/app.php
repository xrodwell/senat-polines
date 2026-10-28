<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cloudflare Tunnel / reverse proxy meneruskan skema asli lewat header
        // X-Forwarded-Proto. Tanpa trust proxy, Laravel membangun URL aset dengan
        // http:// sehingga di halaman https:// browser memblokir CSS/JS (mixed content).
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'portal/presensi/submit',
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
