<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request cha: khi validate lỗi sẽ trả JSON 422 theo chuẩn ApiResponse
 * thay vì redirect như mặc định của Laravel.
 */
abstract class BaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Phân quyền xử lý ở middleware 'role', không xử lý ở đây
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error('Dữ liệu không hợp lệ', 422, $validator->errors())
        );
    }
}
