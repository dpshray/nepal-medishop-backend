<?php

use App\Support\ResponseTraitClass;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return (new ResponseTraitClass)->apiError($e->getMessage(), 401);
            }
        });
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                $firstError = collect($e->errors())->first();
                return (new ResponseTraitClass)->apiError($firstError[0] ?? 'Validation error', 401);
            }
        });
    })->create();
