<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;
use App\Models\Order;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Order::STATUSES)],
        ];
    }
}
