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

        $this->syncRolePermissions($role, $menuIds);

        return $role;
    }

    public function updateWithPermissions($id, array $data, array $menuIds)
    {
        $role = $this->repository->update($id, [
            'name' => $data['name']
        ]);

        $this->syncRolePermissions($role, $menuIds);

        return $role;
    }

    protected function syncRolePermissions($role, $menuIds)
    {
        if (empty($menuIds)) {
            $role->menus()->sync([]);
            $role->aplikasi()->sync([]);
            return;
        }

        // Sync Menus with default permissions
        $pivotData = [];
        $defaultPermissions = json_encode(['view', 'create', 'update', 'delete']);
        foreach ($menuIds as $menuId) {
            if ($menuId) {
                $pivotData[$menuId] = ['permissions' => $defaultPermissions];
            }
        }
        $role->menus()->sync($pivotData);

        // Automatically sync Aplikasi IDs based on selected Menus
        $appIds = \App\Models\Menu::whereIn('id', $menuIds)->pluck('aplikasi_id')->unique()->toArray();
        $role->aplikasi()->sync($appIds);
    }

    public function getAllWithUserCount()
    {
        return $this->repository->getAllWithUserCount();
    }
}
