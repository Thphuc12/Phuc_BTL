<?php

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Lấy danh sách đơn hàng có lọc + phân trang.
     * $filters có thể gồm: user_id, status
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    /** Số liệu thống kê cho admin */
    public function statistics(): array;
}
