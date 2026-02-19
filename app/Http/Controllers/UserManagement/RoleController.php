<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use App\Models\Menu;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ResponseTrait;

    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $roles = $this->roleService->getAllWithUserCount();

        return view('user-management.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $aplikasi = \App\Models\Aplikasi::with('menus')->orderBy('name')->get();
        return view('user-management.roles.create', compact('aplikasi'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:role,name',
        ]);

        $role = $this->roleService->storeWithPermissions(
            $request->only('name'),
            $request->input('menu_ids', []),
            $request->input('menu_permissions', [])
        );

        setLogActivity(auth()->id(), 'Menambah role baru: ' . $request->name, [
            'action_type' => 'create',
            'module'      => 'Roles',
            'data_after'  => $request->all()
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $role = \App\Models\Role::with('menus')->findOrFail($id);
        $aplikasi = \App\Models\Aplikasi::with('menus')->orderBy('name')->get();
        $roleMenuIds = $role->menus->pluck('id')->toArray();
        
        return view('user-management.roles.edit', compact('role', 'aplikasi', 'roleMenuIds'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:role,name,' . $id,
        ]);

        $this->roleService->updateWithPermissions(
            $id,
            $request->only('name'),
            $request->input('menu_ids', []),
            $request->input('menu_permissions', [])
        );

        setLogActivity(auth()->id(), 'Memperbarui role: ' . $request->name, [
            'action_type' => 'update',
            'module'      => 'Roles',
            'data_after'  => $request->all()
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $role = \App\Models\Role::findOrFail($id);
        $roleName = $role->name;
        $this->roleService->delete($id);

        setLogActivity(auth()->id(), 'Menghapus role: ' . $roleName, [
            'action_type' => 'delete',
            'module'      => 'Roles'
        ]);

        return redirect()->back()->with('success', 'Role deleted successfully');
    }
}
