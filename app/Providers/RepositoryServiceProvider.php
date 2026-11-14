<?php

namespace App\Providers;

use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Gắn Interface -> Repository cụ thể.
 * Nhờ đó Service chỉ phụ thuộc vào Interface, dễ thay đổi/test sau này.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
    ];
}
