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
        
        // 1. Middleware-ul global de statistici (TrackVisit)
        $middleware->web(append: [
            \App\Http\Middleware\TrackVisit::class,
        ]);

        // 2. Alias pentru Admin
        $middleware->alias([
            'admin.access' => \App\Http\Middleware\AdminAccess::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();