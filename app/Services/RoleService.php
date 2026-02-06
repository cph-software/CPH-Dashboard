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

    public function storeWithPermissions(array $data, array $permissions)
    {
        $role = $this->repository->create([
            'name' => $data['name'],
            'status' => 'active'
        ]);

        foreach ($permissions as $menuId => $actions) {
            $role->menus()->attach($menuId, [
                'permissions' => json_encode($actions)
            ]);
        }

        return $role;
    }

    public function updateWithPermissions($id, array $data, array $permissions)
    {
        $role = $this->repository->update($id, [
            'name' => $data['name']
        ]);

        $syncData = [];
        foreach ($permissions as $menuId => $actions) {
            $syncData[$menuId] = [
                'permissions' => json_encode($actions)
            ];
        }
        $role->menus()->sync($syncData);

        return $role;
    }

    public function getAllWithUserCount()
    {
        return $this->repository->getAllWithUserCount();
    }
}
