<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Services\RoleService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ResponseTrait;

    protected $userService;
    protected $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $users = \App\Models\User::with(['role', 'karyawan'])->get();
        $roles = $this->roleService->getAll();

        return view('user-management.users.index', compact('users', 'roles'));
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
            'role_id' => 'required',
            'password' => 'required|min:6'
        ]);

        $this->userService->store([
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
            'master_karyawan_id' => $request->master_karyawan_id,
            'toko_id' => $request->toko_id,
            'foto' => ''
        ]);

        setLogActivity(auth()->id(), 'Menambah user baru (Employee: ' . $request->master_karyawan_id . ')', [
            'action_type' => 'create',
            'module' => 'Users',
            'data_after' => $request->except('password')
        ]);

        return redirect()->back()->with('success', 'User created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $user = $this->userService->getById($id);
        return response()->json($user);
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
        $data = [
            'role_id' => $request->role_id,
            'master_karyawan_id' => $request->master_karyawan_id,
            'toko_id' => $request->toko_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $this->userService->update($id, $data);

        setLogActivity(auth()->id(), 'Memperbarui user ID: ' . $id, [
            'action_type' => 'update',
            'module' => 'Users',
            'data_after' => $request->except('password')
        ]);

        return redirect()->back()->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        setLogActivity(auth()->id(), 'Menghapus user ID: ' . $id, [
            'action_type' => 'delete',
            'module' => 'Users'
        ]);

        $this->userService->delete($id);
        return redirect()->back()->with('success', 'User deleted successfully');
    }
}
