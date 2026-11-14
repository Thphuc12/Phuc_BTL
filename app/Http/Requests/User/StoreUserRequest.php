<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class StoreUserRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CUSTOMER])],
        ];
    }
}
