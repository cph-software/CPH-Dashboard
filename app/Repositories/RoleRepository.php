<?php

namespace App\Repositories;

use App\Models\Role;

/**
 * @method \Illuminate\Database\Eloquent\Collection getAllWithUserCount()
 */
class RoleRepository extends BaseRepository
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function getAllWithUserCount()
    {
        return $this->model->withCount('users')->get();
    }
}
