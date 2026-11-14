<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller chỉ nhận request -> gọi Service -> trả response.
 */
class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
    }

    /** POST /api/auth/register */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::created($result, 'Đăng ký thành công');
    }

    /** POST /api/auth/login */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->email, $request->password);

        return ApiResponse::success($result, 'Đăng nhập thành công');
    }

    /** POST /api/auth/logout */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Đăng xuất thành công');
    }

    /** GET /api/auth/me */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success($request->user());
    }

    /** PUT /api/auth/profile */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile($request->user(), $request->validated());

        return ApiResponse::success($user, 'Cập nhật thông tin thành công');
    }

    /** PUT /api/auth/change-password */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            $request->user(),
            $request->current_password,
            $request->new_password
        );

        return ApiResponse::success(null, 'Đổi mật khẩu thành công');
    }
}
