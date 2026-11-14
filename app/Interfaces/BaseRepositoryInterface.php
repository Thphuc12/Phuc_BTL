<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;

/**
 * Các hàm CRUD cơ bản mà mọi Repository đều có.
 */
interface BaseRepositoryInterface
{
    public function all();

    public function find(int $id): ?Model;

    public function findOrFail(int $id): Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function delete(int $id): bool;
}
