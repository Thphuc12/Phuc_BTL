<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;
use App\Models\Product;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
        ];
    }
}
