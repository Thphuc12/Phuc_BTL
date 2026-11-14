<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($this->route('id'))],
            'description' => ['nullable', 'string'],
        ];
    }
}
