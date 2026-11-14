<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService)
    {
    }

    /** GET /api/categories (public) */
    public function index(): JsonResponse
    {
        return ApiResponse::success($this->categoryService->list());
    }

    /** GET /api/categories/{id} (public) */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->categoryService->find($id));
    }

    /** POST /api/categories (admin) */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return ApiResponse::created($category, 'Tạo danh mục thành công');
    }

    /** PUT /api/categories/{id} (admin) */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->categoryService->update($id, $request->validated());

        return ApiResponse::success($category, 'Cập nhật danh mục thành công');
    }

    /** DELETE /api/categories/{id} (admin) */
    public function destroy(int $id): JsonResponse
    {
        $this->categoryService->delete($id);

        return ApiResponse::success(null, 'Xoá danh mục thành công');
    }
}
