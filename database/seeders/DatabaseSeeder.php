<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Dữ liệu mẫu để test nhanh API.
 * Chạy: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tài khoản admin + customer mẫu
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '123456',
            'role' => User::ROLE_ADMIN,
            'phone' => '0900000001',
            'address' => 'Hà Nội',
        ]);

        User::create([
            'name' => 'Nguyễn Văn A',
            'email' => 'customer@example.com',
            'password' => '123456',
            'role' => User::ROLE_CUSTOMER,
            'phone' => '0900000002',
            'address' => 'TP. Hồ Chí Minh',
        ]);

        // Danh mục + sản phẩm mẫu (ảnh nằm trong public/images/products/)
        $img = fn (string $file) => "/images/products/{$file}";

        $categories = [
            'Điện thoại' => [
                ['name' => 'iPhone 15', 'price' => 22990000, 'stock' => 20, 'image' => $img('iphone.jpg')],
                ['name' => 'Samsung Galaxy S24', 'price' => 19990000, 'stock' => 15, 'image' => $img('samsung.jpg')],
                ['name' => 'Xiaomi 14', 'price' => 14990000, 'stock' => 30, 'image' => $img('xiaomi.jpg')],
            ],
            'Laptop' => [
                ['name' => 'MacBook Air M3', 'price' => 27990000, 'stock' => 10],
                ['name' => 'Dell XPS 13', 'price' => 32990000, 'stock' => 8, 'image' => $img('dell.jpg')],
            ],
            'Máy tính bảng' => [
                ['name' => 'iPad Air M2', 'price' => 16990000, 'stock' => 12, 'image' => $img('ipad.jpg')],
            ],
            'Phụ kiện' => [
                ['name' => 'Tai nghe AirPods Pro', 'price' => 5990000, 'stock' => 50, 'image' => $img('airpod.jpg')],
                ['name' => 'Sạc nhanh 65W', 'price' => 490000, 'stock' => 100, 'image' => $img('65w.jpg')],
                ['name' => 'Chuột không dây', 'price' => 350000, 'stock' => 0, 'status' => 'inactive'],
            ],
        ];

        foreach ($categories as $categoryName => $products) {
            $category = Category::create([
                'name' => $categoryName,
                'slug' => \Illuminate\Support\Str::slug($categoryName),
                'description' => "Danh mục {$categoryName}",
            ]);

            foreach ($products as $product) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $product['name'],
                    'description' => "Mô tả sản phẩm {$product['name']}",
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'image' => $product['image'] ?? null,
                    'status' => $product['status'] ?? Product::STATUS_ACTIVE,
                ]);
            }
        }
    }
}
