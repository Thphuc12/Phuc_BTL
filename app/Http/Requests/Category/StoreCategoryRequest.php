<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseRequest;

class StoreCategoryRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
        ];
    }
}
