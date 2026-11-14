<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
    }

    /**
     * GET /api/orders?status=&user_id=&per_page=
     * Admin xem tất cả, customer chỉ xem đơn của mình.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'user_id']);
        $orders = $this->orderService->list($request->user(), $filters, (int) $request->get('per_page', 10));

        return ApiResponse::paginate($orders);
    }

    /** GET /api/orders/{id} */
    public function show(Request $request, int $id): JsonResponse
    {
        return ApiResponse::success($this->orderService->find($request->user(), $id));
    }

    /** POST /api/orders - đặt hàng */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create($request->user(), $request->validated());

        return ApiResponse::created($order, 'Đặt hàng thành công');
    }

    /** PATCH /api/orders/{id}/status (admin) */
    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateStatus($id, $request->status);

        return ApiResponse::success($order, 'Cập nhật trạng thái đơn hàng thành công');
    }

    /** PATCH /api/orders/{id}/cancel - customer huỷ đơn */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->cancel($request->user(), $id);

        return ApiResponse::success($order, 'Huỷ đơn hàng thành công');
    }

    /** GET /api/statistics (admin) */
    public function statistics(): JsonResponse
    {
        return ApiResponse::success($this->orderService->statistics());
    }
}
