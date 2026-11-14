<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:150', Rule::unique('users', 'email')->ignore($this->route('id'))],
            'password' => ['sometimes', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'role' => ['sometimes', Rule::in([User::ROLE_ADMIN, User::ROLE_CUSTOMER])],
        ];
    }
}
