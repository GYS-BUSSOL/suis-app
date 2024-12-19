<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Application;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->validateCsrfTokens(except: [
        //     '/api/apps/vendor-assigment/search',
        // ]);
        $middleware->append([
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $e) {
            $data = [
                'method' => request()->getMethod(),
                'url' => request()->fullUrl(),
                'message' => $e->getMessage(),
                'userId' => 1,
                'data' => request()->all(),
            ];

            if ($e instanceof ValidationException) {
                $data['errors'] = $e->errors();
            }

            Log::channel('daily')->info(json_encode($data, JSON_PRETTY_PRINT));
        });
    })->create();
