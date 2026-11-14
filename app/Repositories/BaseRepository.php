<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Repository cha: cài sẵn các hàm CRUD cơ bản.
 * Các repository con chỉ cần truyền Model vào constructor và thêm hàm riêng.
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model)
    {
    }

    public function all()
    {
        return $this->model->latest()->get();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): Model
    {
        // Nếu không tìm thấy sẽ ném ModelNotFoundException -> trả 404 tự động
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record;
    }

    public function delete(int $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
