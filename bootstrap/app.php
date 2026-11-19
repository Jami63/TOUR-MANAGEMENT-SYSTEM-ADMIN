<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin*')) {
                return route('admin.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Let ValidationException return standard 422 with validation errors
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null;
                }

                // Clean 401 for unauthenticated API requests
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthenticated. Please log in to continue.',
                    ], 401);
                }

                // Clean 404 for missing resources or endpoints
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Requested resource or API endpoint not found.',
                    ], 404);
                }

                // Clean 403 for unauthorized access
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. You do not have permission to access this resource.',
                    ], 403);
                }

                // Other HTTP exceptions: preserve status code with clean JSON message
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'An error occurred processing your request.',
                    ], $e->getStatusCode());
                }

                // In production or when debug mode is disabled: strictly mask internal errors
                // Never leak stack traces, database queries, file paths, or credentials
                if (!config('app.debug')) {
                    \Illuminate\Support\Facades\Log::error('API Unhandled Exception: ' . $e->getMessage(), [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'An unexpected server error occurred. Please try again later.',
                    ], 500);
                }
            }

            return null;
        });
    })->create();
