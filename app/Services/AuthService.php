<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Xử lý nghiệp vụ: đăng ký, đăng nhập, đăng xuất, thông tin cá nhân.
 */
class AuthService
{
    public function __construct(protected UserRepositoryInterface $userRepository)
    {
    }

    /** Đăng ký: tạo user với role mặc định customer và trả token */
    public function register(array $data): array
    {
        $data['role'] = User::ROLE_CUSTOMER; // không cho client tự chọn role
        $user = $this->userRepository->create($data);

        return [
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }

    /** Đăng nhập: kiểm tra email + mật khẩu, trả token Sanctum */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new BusinessException('Email hoặc mật khẩu không đúng', 401);
        }

        return [
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }

    /** Đăng xuất: xoá token hiện tại */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /** Cập nhật thông tin cá nhân (không cho đổi role ở đây) */
    public function updateProfile(User $user, array $data): User
    {
        return $this->userRepository->update($user->id, $data);
    }

    /** Đổi mật khẩu: phải nhập đúng mật khẩu cũ */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new BusinessException('Mật khẩu hiện tại không đúng', 400);
        }

        $this->userRepository->update($user->id, ['password' => $newPassword]);
    }
}
