<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    /** Lấy danh sách user có tìm kiếm + phân trang */
    public function paginate(?string $keyword, int $perPage): LengthAwarePaginator;
}
