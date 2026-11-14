<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Quản lý đơn hàng: đặt hàng, xem đơn, đổi trạng thái, huỷ đơn, thống kê.
 */
class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * Danh sách đơn hàng.
     * - Admin: xem tất cả (có thể lọc theo user_id, status)
     * - Customer: chỉ xem đơn của mình
     */
    public function list(User $user, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        if (! $user->isAdmin()) {
            $filters['user_id'] = $user->id;
        }

        return $this->orderRepository->paginate($filters, $perPage);
    }

    /** Xem chi tiết đơn; customer chỉ xem được đơn của mình */
    public function find(User $user, int $id): Order
    {
        $order = $this->orderRepository->findOrFail($id);

        if (! $user->isAdmin() && $order->user_id !== $user->id) {
            throw new BusinessException('Bạn không có quyền xem đơn hàng này', 403);
        }

        return $order;
    }

    /**
     * Đặt hàng:
     * 1. Lấy toàn bộ sản phẩm trong giỏ
     * 2. Kiểm tra sản phẩm còn bán và đủ tồn kho
     * 3. Tạo order + order_items, trừ tồn kho (trong 1 transaction)
     */
    public function create(User $user, array $data): Order
    {
        $items = collect($data['items']);
        $products = $this->productRepository->findMany($items->pluck('product_id')->all());

        return DB::transaction(function () use ($user, $data, $items, $products) {
            $total = 0;
            $orderItems = [];

            foreach ($items as $item) {
                /** @var Product|null $product */
                $product = $products->get($item['product_id']);

                if (! $product || $product->status !== Product::STATUS_ACTIVE) {
                    throw new BusinessException("Sản phẩm #{$item['product_id']} không tồn tại hoặc đã ngừng bán", 400);
                }
                if ($product->stock < $item['quantity']) {
                    throw new BusinessException("Sản phẩm '{$product->name}' chỉ còn {$product->stock} sản phẩm", 400);
                }

                $total += $product->price * $item['quantity'];
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];

                // Trừ tồn kho
                $product->decrement('stock', $item['quantity']);
            }

            $order = $this->orderRepository->create([
                'user_id' => $user->id,
                'total_amount' => $total,
                'status' => Order::STATUS_PENDING,
                'shipping_address' => $data['shipping_address'],
                'phone' => $data['phone'],
                'note' => $data['note'] ?? null,
            ]);
            $order->items()->createMany($orderItems);

            return $this->orderRepository->findOrFail($order->id);
        });
    }

    /**
     * Admin đổi trạng thái đơn theo luồng:
     * pending -> processing -> completed, hoặc huỷ (cancelled) khi chưa hoàn thành.
     */
    public function updateStatus(int $id, string $status): Order
    {
        $order = $this->orderRepository->findOrFail($id);

        $allowed = [
            Order::STATUS_PENDING => [Order::STATUS_PROCESSING, Order::STATUS_CANCELLED],
            Order::STATUS_PROCESSING => [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED],
            Order::STATUS_COMPLETED => [],
            Order::STATUS_CANCELLED => [],
        ];

        if (! in_array($status, $allowed[$order->status], true)) {
            throw new BusinessException("Không thể chuyển trạng thái từ '{$order->status}' sang '{$status}'", 400);
        }

        return DB::transaction(function () use ($order, $status) {
            if ($status === Order::STATUS_CANCELLED) {
                $this->restoreStock($order);
            }

            return $this->orderRepository->update($order->id, ['status' => $status]);
        });
    }

    /** Customer tự huỷ đơn của mình khi đơn còn ở trạng thái pending */
    public function cancel(User $user, int $id): Order
    {
        $order = $this->find($user, $id);

        if ($order->status !== Order::STATUS_PENDING) {
            throw new BusinessException('Chỉ có thể huỷ đơn hàng đang chờ xử lý', 400);
        }

        return DB::transaction(function () use ($order) {
            $this->restoreStock($order);

            return $this->orderRepository->update($order->id, ['status' => Order::STATUS_CANCELLED]);
        });
    }

    /** Thống kê cho admin */
    public function statistics(): array
    {
        return $this->orderRepository->statistics();
    }

    /** Hoàn lại tồn kho khi đơn bị huỷ */
    private function restoreStock(Order $order): void
    {
        foreach ($order->items as $item) {
            Product::where('id', $item->product_id)->increment('stock', $item->quantity);
        }
    }
}
