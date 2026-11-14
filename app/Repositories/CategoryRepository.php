<?php

namespace App\Repositories;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        // Kèm số lượng sản phẩm của mỗi danh mục
        return $this->model->withCount('products')->latest()->get();
    }
}
