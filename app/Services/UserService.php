<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Quản lý người dùng (dành cho admin).
 */
class UserService
{
    public function __construct(protected UserRepositoryInterface $userRepository)
    {
    }

    public function list(?string $keyword, int $perPage = 10): LengthAwarePaginator
    {
        return $this->userRepository->paginate($keyword, $perPage);
    }

    public function find(int $id): User
    {
        return $this->userRepository->findOrFail($id);
    }

    public function create(array $data): User
    {
        return $this->userRepository->create($data);
    }

    public function update(int $id, array $data): User
    {
        return $this->userRepository->update($id, $data);
    }

    public function delete(int $id, User $currentUser): void
    {
        // Admin không được tự xoá chính mình
        if ($id === $currentUser->id) {
            throw new BusinessException('Không thể xoá chính tài khoản đang đăng nhập', 400);
        }

        $this->userRepository->delete($id);
    }
}
