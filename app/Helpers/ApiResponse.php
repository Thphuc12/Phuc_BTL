<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Helper chuẩn hoá mọi JSON response trả về từ API.
 * Mọi response đều có dạng: { success, message, data }
 */
class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Thành công', int $status = 200): JsonResponse
    {
        // JSON_UNESCAPED_UNICODE: giữ nguyên tiếng Việt thay vì à...
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }

    public static function created(mixed $data = null, string $message = 'Tạo mới thành công'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    public static function error(string $message = 'Có lỗi xảy ra', int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Trả dữ liệu phân trang theo cấu trúc gọn: items + meta
     */
    public static function paginate(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator, string $message = 'Thành công'): JsonResponse
    {
        return self::success([
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], $message);
    }
}
