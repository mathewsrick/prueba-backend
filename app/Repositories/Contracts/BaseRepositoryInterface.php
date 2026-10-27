<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function all(array $with = []): Collection;

    public function find(int $id, array $with = []): ?Model;

    public function create(array $data): Model;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): bool;

    public function paginate(int $perPage = 15, array $with = []): LengthAwarePaginator;
}