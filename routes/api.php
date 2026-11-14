<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  (prefix tự động: /api)
|--------------------------------------------------------------------------
| Luồng: Client -> Route -> Controller -> Service -> Repository -> DB
*/

// ---------- PUBLIC: không cần đăng nhập ----------
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);

// ---------- ĐÃ ĐĂNG NHẬP (admin + customer) ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('change-password', [AuthController::class, 'changePassword']);
    });

    // Đơn hàng
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::patch('orders/{id}/cancel', [OrderController::class, 'cancel']);

    // ---------- CHỈ ADMIN ----------
    Route::middleware('role:admin')->group(function () {
        // Quản lý người dùng
        Route::apiResource('users', UserController::class)->parameters(['users' => 'id']);

        // Quản lý danh mục
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{id}', [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

        // Quản lý sản phẩm
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);
        Route::patch('products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);

        // Quản lý trạng thái đơn hàng + thống kê
        Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::get('statistics', [OrderController::class, 'statistics']);
    });
});
