<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    /**
     * Paginate records with optional search and ordering.
     *
     * @param int $perPage
     * @param array $search (e.g. ['column' => 'value'])
     * @param string $orderBy
     * @param string $sortBy
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 10, array $search = [], $orderBy = 'id', $sortBy = 'desc')
    {
        $query = $this->model->newQuery();

        if (!empty($search)) {
            foreach ($search as $column => $value) {
                if ($value) {
                    $query->where($column, 'LIKE', "%{$value}%");
                }
            }
        }

        return $query->orderBy($orderBy, $sortBy)->paginate($perPage);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete($id)
    {
        $record = $this->model->findOrFail($id);
        return $record->delete();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }
}
