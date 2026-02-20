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

    public function storeWithPermissions(array $data, array $menuIds, array $menuPermissions = [])
    {
        $role = $this->repository->create([
            'name' => $data['name'],
            'status' => 'active'
        ]);

        $this->syncRolePermissions($role, $menuIds, $menuPermissions);

        return $role;
    }

    public function updateWithPermissions($id, array $data, array $menuIds, array $menuPermissions = [])
    {
        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        $role = $this->repository->update($id, $updateData);

        $this->syncRolePermissions($role, $menuIds, $menuPermissions);

        return $role;
    }

    protected function syncRolePermissions($role, $menuIds, $menuPermissions = [])
    {
        if (empty($menuIds)) {
            $role->menus()->sync([]);
            $role->aplikasi()->sync([]);
            return;
        }

        // Sync Menus with specific permissions
        $pivotData = [];
        foreach ($menuIds as $menuId) {
            if ($menuId) {
                // Get permissions for this menu, default to ['view'] if none provided
                $perms = isset($menuPermissions[$menuId]) ? $menuPermissions[$menuId] : ['view'];
                $pivotData[$menuId] = ['permissions' => json_encode(array_values($perms))];
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
