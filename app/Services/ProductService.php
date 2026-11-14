<?php

namespace App\Services;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Quản lý sản phẩm: CRUD, tìm kiếm, lọc, phân trang, đổi trạng thái.
 */
class ProductService
{
    public function __construct(protected ProductRepositoryInterface $productRepository)
    {
    }

    public function list(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->productRepository->paginate($filters, $perPage);
    }

    public function find(int $id): Product
    {
        return $this->productRepository->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function update(int $id, array $data): Product
    {
        return $this->productRepository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->productRepository->delete($id);
    }

    /** Bật/tắt trạng thái bán của sản phẩm */
    public function toggleStatus(int $id): Product
    {
        $product = $this->productRepository->findOrFail($id);
        $newStatus = $product->status === Product::STATUS_ACTIVE
            ? Product::STATUS_INACTIVE
            : Product::STATUS_ACTIVE;

        return $this->productRepository->update($id, ['status' => $newStatus]);
    }
}
