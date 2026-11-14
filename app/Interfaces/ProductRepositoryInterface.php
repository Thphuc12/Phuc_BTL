<?php

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Lấy danh sách sản phẩm có lọc + phân trang.
     * $filters có thể gồm: keyword, category_id, status, min_price, max_price
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    /** Lấy nhiều sản phẩm theo mảng id (dùng khi tạo đơn hàng) */
    public function findMany(array $ids);
}
