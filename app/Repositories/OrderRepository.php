<?php

namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->model
            ->with(['user:id,name,email', 'items.product:id,name,image'])
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Order
    {
        return $this->model
            ->with(['user:id,name,email', 'items.product:id,name,image'])
            ->findOrFail($id);
    }

    public function statistics(): array
    {
        // Đếm số đơn theo từng trạng thái
        $byStatus = $this->model
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Doanh thu chỉ tính đơn đã hoàn thành
        $revenue = $this->model->where('status', Order::STATUS_COMPLETED)->sum('total_amount');

        // Top 5 sản phẩm bán chạy (theo số lượng) trong các đơn đã hoàn thành
        $topProducts = OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_COMPLETED))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->with('product:id,name,price')
            ->get();

        return [
            'total_orders' => $this->model->count(),
            'orders_by_status' => $byStatus,
            'total_revenue' => (float) $revenue,
            'top_products' => $topProducts,
        ];
    }
}
