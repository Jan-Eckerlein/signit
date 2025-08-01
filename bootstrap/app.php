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
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\App\Exceptions\LockedModelException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        });
        $exceptions->renderable(function (\App\Exceptions\ValidateModelModificationFailedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        });
    })->create();
