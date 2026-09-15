<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

    // Role-based middleware
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);

    $middleware->redirectGuestsTo(function ($request) {
        if ($request->is('api/*')) {
            return null;
        }

        return route('login');
    });
})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            if ($exception instanceof AuthorizationException) {
                return response()->json([
                    'message' => 'You are not authorized to perform this action.',
                ], 403);
            }

            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                ], 404);
            }

            if ($exception instanceof HttpExceptionInterface) {
                return response()->json([
                    'message' => $exception->getMessage() ?: 'Request failed.',
                ], $exception->getStatusCode());
            }

            return response()->json([
                'message' => 'Server error. Please try again later.',
            ], 500);
        });
    })
    ->create();