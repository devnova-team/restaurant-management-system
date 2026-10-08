<?php

use App\Http\Middleware\EnsureStaffHasRole;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureStaffHasRole::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        // Register custom exception renderers for API responses

        // Handle authentication exceptions for API routes
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('غير مصرح، من فضلك سجّل الدخول', 401);
            }
        });

        // Handle access denied exceptions for API routes
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('ليس لديك صلاحية لتنفيذ هذا الإجراء', 403);
            }
        });

        // Handle not found exceptions for API routes
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('المورد المطلوب غير موجود', 404);
            }
        });

        // Handle validation exceptions for API routes
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error($e->validator->errors()->first(), 422);
            }
        });

        // Handle rate limiting exceptions for API routes
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('عدد محاولات تسجيل الدخول تجاوز الحد المسموح به، يرجى المحاولة لاحقًا', 429);
            }
        });

        // Handle generic HTTP exceptions for API routes
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $e->getMessage() ?: 'حدث خطأ في الطلب',
                    $e->getStatusCode()
                );
            }
        });

        // Handle any other exceptions for API routes
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') && ! config('app.debug')) {
                return ApiResponse::error('حدث خطأ داخلي في السيرفر', 500);
            }
        });

    })->create();
