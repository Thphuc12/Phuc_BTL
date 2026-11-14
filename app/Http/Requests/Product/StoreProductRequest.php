<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;
use App\Models\Product;
use Illuminate\Validation\Rule;

class StoreProductRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
        ];
    }
}
