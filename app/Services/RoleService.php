<?php

namespace App\Services;

use App\Repositories\RoleRepository;

/**
 * @property RoleRepository $repository
 */
class RoleService extends BaseService
{
    public function __construct(RoleRepository $repository)
    {
        parent::__construct($repository);
    }

    public function storeWithPermissions(array $data, array $menuIds)
    {
        $role = $this->repository->create([
            'name' => $data['name'],
            'status' => 'active'
        ]);

        $role->menus()->attach($menuIds);

        return $role;
    }

    public function updateWithPermissions($id, array $data, array $menuIds)
    {
        $role = $this->repository->update($id, [
            'name' => $data['name']
        ]);

        $role->menus()->sync($menuIds);

        return $role;
    }

    public function getAllWithUserCount()
    {
        return $this->repository->getAllWithUserCount();
    }
}
