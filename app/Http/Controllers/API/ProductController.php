<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    /**
     * GET /api/products (public)
     * Query: keyword, category_id, status, min_price, max_price, per_page, page
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['keyword', 'category_id', 'status', 'min_price', 'max_price']);
        $products = $this->productService->list($filters, (int) $request->get('per_page', 10));

        return ApiResponse::paginate($products);
    }

    /** GET /api/products/{id} (public) */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->productService->find($id));
    }

    /** POST /api/products (admin) */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return ApiResponse::created($product, 'Tạo sản phẩm thành công');
    }

    /** PUT /api/products/{id} (admin) */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->update($id, $request->validated());

        return ApiResponse::success($product, 'Cập nhật sản phẩm thành công');
    }

    /** DELETE /api/products/{id} (admin) */
    public function destroy(int $id): JsonResponse
    {
        $this->productService->delete($id);

        return ApiResponse::success(null, 'Xoá sản phẩm thành công');
    }

    /** PATCH /api/products/{id}/toggle-status (admin) */
    public function toggleStatus(int $id): JsonResponse
    {
        $product = $this->productService->toggleStatus($id);

        return ApiResponse::success($product, 'Đổi trạng thái sản phẩm thành công');
    }
}
