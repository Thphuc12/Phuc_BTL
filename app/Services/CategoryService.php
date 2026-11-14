<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Support\Str;

/**
 * Quản lý danh mục sản phẩm.
 */
class CategoryService
{
    public function __construct(protected CategoryRepositoryInterface $categoryRepository)
    {
    }

    public function list()
    {
        return $this->categoryRepository->all();
    }

    public function find(int $id): Category
    {
        return $this->categoryRepository->findOrFail($id);
    }

    public function create(array $data): Category
    {
        // Tự sinh slug từ tên nếu client không gửi
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $this->categoryRepository->create($data);
    }

    public function update(int $id, array $data): Category
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->categoryRepository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $category = $this->categoryRepository->findOrFail($id);

        // Không cho xoá danh mục còn sản phẩm
        if ($category->products()->exists()) {
            throw new BusinessException('Danh mục đang có sản phẩm, không thể xoá', 400);
        }

        $this->categoryRepository->delete($id);
    }
}
