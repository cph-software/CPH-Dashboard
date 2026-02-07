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
        $menus = Menu::whereNull('parent_id')->with('children')->get();
        return view('user-management.roles.create', compact('menus'));
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

        $this->roleService->storeWithPermissions(
            $request->only('name'),
            $request->input('menu_ids', [])
        );

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
        $menus = Menu::whereNull('parent_id')->with('children')->get();
        $roleMenuIds = $role->menus->pluck('id')->toArray();
        
        return view('user-management.roles.edit', compact('role', 'menus', 'roleMenuIds'));
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
            $request->input('menu_ids', [])
        );

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
        $this->roleService->delete($id);
        return redirect()->back()->with('success', 'Role deleted successfully');
    }
}
