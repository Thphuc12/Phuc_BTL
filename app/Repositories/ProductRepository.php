<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->model
            ->with('category')
            // Tìm theo tên sản phẩm
            ->when($filters['keyword'] ?? null, fn ($q, $kw) => $q->where('name', 'like', "%{$kw}%"))
            // Lọc theo danh mục
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            // Lọc theo trạng thái
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            // Lọc theo khoảng giá
            ->when($filters['min_price'] ?? null, fn ($q, $min) => $q->where('price', '>=', $min))
            ->when($filters['max_price'] ?? null, fn ($q, $max) => $q->where('price', '<=', $max))
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Product
    {
        return $this->model->with('category')->findOrFail($id);
    }

    public function findMany(array $ids)
    {
        return $this->model->whereIn('id', $ids)->get()->keyBy('id');
    }
}
