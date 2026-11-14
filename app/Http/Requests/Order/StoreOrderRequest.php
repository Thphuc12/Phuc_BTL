<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;

class StoreOrderRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'shipping_address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'note' => ['nullable', 'string'],
            // Giỏ hàng: mảng các sản phẩm + số lượng
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
