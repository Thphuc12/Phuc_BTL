<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:150', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
