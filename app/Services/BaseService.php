<?php

namespace App\Services;

use App\Repositories\BaseRepository;

abstract class BaseService
{
    protected $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getPaginated($perPage = 10, array $search = [], $orderBy = 'id', $sortBy = 'desc')
    {
        return $this->repository->paginate($perPage, $search, $orderBy, $sortBy);
    }

    public function store(array $data)
    {
        return $this->repository->create($data);
    }

    public function getById($id)
    {
        return $this->repository->find($id);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
