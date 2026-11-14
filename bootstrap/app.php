<?php

use App\Exceptions\BusinessException;
use App\Helpers\ApiResponse;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đăng ký alias 'role' để dùng trong routes: middleware('role:admin')
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API luôn trả JSON
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Lỗi nghiệp vụ do Service ném ra
        $exceptions->render(function (BusinessException $e, Request $request) {
            return ApiResponse::error($e->getMessage(), $e->getStatus());
        });

        // Chưa đăng nhập / token sai
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Bạn chưa đăng nhập hoặc token không hợp lệ', 401);
            }
        });

        // 404: Laravel tự bọc ModelNotFoundException (findOrFail) thành NotFoundHttpException,
        // nên phân biệt qua getPrevious() để trả thông báo đúng.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $message = $e->getPrevious() instanceof ModelNotFoundException
                    ? 'Không tìm thấy dữ liệu'
                    : 'Không tìm thấy đường dẫn API';

                return ApiResponse::error($message, 404);
            }
        });
    })->create();
