<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Quản lý người dùng - chỉ admin.
 */
class UserController extends Controller
{
    public function __construct(protected UserService $userService)
    {
    }

    /** GET /api/users?keyword=&per_page= */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->list($request->keyword, (int) $request->get('per_page', 10));

        return ApiResponse::paginate($users);
    }

    /** GET /api/users/{id} */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->userService->find($id));
    }

    /** POST /api/users */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return ApiResponse::created($user, 'Tạo người dùng thành công');
    }

    /** PUT /api/users/{id} */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());

        return ApiResponse::success($user, 'Cập nhật người dùng thành công');
    }

    /** DELETE /api/users/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->userService->delete($id, $request->user());

        return ApiResponse::success(null, 'Xoá người dùng thành công');
    }
}
