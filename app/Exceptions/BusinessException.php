<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception dùng cho lỗi nghiệp vụ (ví dụ: hết hàng, sai mật khẩu, không được huỷ đơn...).
 * Service ném exception này, bootstrap/app.php sẽ bắt và trả JSON với mã HTTP tương ứng.
 */
class BusinessException extends Exception
{
    public function __construct(string $message, protected int $status = 400)
    {
        parent::__construct($message);
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
