<?php

use App\Http\Middleware\CheckAccess;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt' => JwtMiddleware::class,
            'access' => CheckAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e) {
            // Validación
            if ($e instanceof ValidationException) {
                $validationErrors = [];
                foreach ((array)$e->errors() as $error => $key) {
                    $message = $key[0];
                    $validationErrors[] = ["error" => $message];
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validationErrors,
                ], 422);
            }

            // No encontrado
            if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recurso no encontrado',
                    'errors' => [
                        ['error' => 'El recurso solicitado no existe en el sistema.']
                    ]
                ], 404);
            }

            // Acceso denegado
            if ($e instanceof AccessDeniedHttpException || $e instanceof AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado',
                    'errors' => [
                        ['error' => 'No tienes permiso para realizar esta acción.']
                    ]
                ], 403);
            }

            // Violación de integridad FK
            if ($e instanceof QueryException && $e->getCode() === "23000") {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de integridad',
                    'errors' => [
                        ['error' => 'No se puede eliminar este recurso porque está en uso.']
                    ]
                ], 409);
            }

            // Error interno genérico
            return response()->json([
                'success' => false,
                'message' => 'Error interno',
                'errors' => [
                    ['error' => $e->getMessage()]
                ]
            ], 500);
        });
    })->create();
