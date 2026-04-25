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
        $user = auth()->user();
        $isSuperAdmin = $user->role_id == 1;

        $usersQuery = \App\Models\User::with(['role', 'karyawan', 'tyreCompany'])->latest();
        if (!$isSuperAdmin) {
            $usersQuery->where('tyre_company_id', $user->tyre_company_id);
        }
        $users = $usersQuery->get();

        $rolesQuery = \App\Models\Role::query();
        if (!$isSuperAdmin) {
            $rolesQuery->where('id', '!=', 1);
            if ($user->tyre_company_id) {
                $rolesQuery->whereHas('companies', function($q) use ($user) {
                    $q->where('tyre_companies.id', $user->tyre_company_id);
                });
            }
        }
        $roles = $rolesQuery->get();

        $companies = $isSuperAdmin ? \App\Models\TyreCompany::orderBy('company_name')->get() : collect();
        $tokos = \App\Models\Toko::limit(50)->get();

        $quotaInfo = null;
        if (!$isSuperAdmin && $user->tyre_company_id) {
            $company = \App\Models\TyreCompany::find($user->tyre_company_id);
            $quotaInfo = [
                'current' => $users->count(),
                'max' => $company->max_users ?? 10,
                'company_name' => $company->company_name,
            ];
        }

        return view('user-management.users.index', compact('users', 'roles', 'companies', 'tokos', 'isSuperAdmin', 'quotaInfo'));
    }

    /**
     * AJAX search for tokos/branches
     */
    public function getTokos(Request $request)
    {
        $search = $request->search;
        $tokos = \App\Models\Toko::where('nama_toko', 'LIKE', "%$search%")
            ->orWhere('id_toko', 'LIKE', "%$search%")
            ->limit(30)
            ->get();

        $response = [];
        foreach ($tokos as $toko) {
            $response[] = [
                'id' => $toko->id_toko,
                'text' => $toko->nama_toko . ' (' . $toko->id_toko . ')'
            ];
        }

        return response()->json($response);
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
            'role_id' => 'required|exists:role,id',
            'name' => 'required|unique:users,name',
            'password' => 'required|min:6',
            'tyre_company_id' => 'nullable|exists:tyre_companies,id',
        ], [
            'name.unique' => 'Username ini sudah digunakan.',
            'role_id.exists' => 'Role tidak valid.',
            'tyre_company_id.exists' => 'Perusahaan tidak valid.',
        ]);

        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->role_id == 1;

        if (!$isSuperAdmin && $request->role_id == 1) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk memberikan role Super Admin.');
        }

        $companyId = $isSuperAdmin ? $request->tyre_company_id : $currentUser->tyre_company_id;

        if ($companyId) {
            $company = \App\Models\TyreCompany::with('roles')->find($companyId);
            if ($company) {
                if (!$isSuperAdmin) {
                    // Check Role Whitelist
                    $allowedRoleIds = $company->roles->pluck('id')->toArray();
                    if (!in_array($request->role_id, $allowedRoleIds)) {
                        return redirect()->back()->with('error', 'Role tersebut tidak diizinkan untuk digunakan di perusahaan Anda.');
                    }
                    
                    // Check Quota
                    $currentCount = \App\Models\User::where('tyre_company_id', $companyId)->count();
                    if ($currentCount >= $company->max_users) {
                        return redirect()->back()->with('error', "Kuota user untuk {$company->company_name} sudah penuh ({$currentCount}/{$company->max_users}). Hubungi Super Admin.");
                    }
                }
            }
        }

        $this->userService->store([
            'name' => $request->name,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
            'master_karyawan_id' => $request->master_karyawan_id,
            'toko_id' => $request->toko_id,
            'tyre_company_id' => $companyId,
            'foto' => ''
        ]);

        // --- Send Notification to Super Admin if created by Company Admin ---
        if (!$isSuperAdmin && $companyId) {
            try {
                $superAdmins = \App\Models\User::where('role_id', 1)->get();
                if ($superAdmins->count() > 0) {
                    $company = \App\Models\TyreCompany::find($companyId);
                    $companyName = $company ? $company->company_name : 'Unknown Company';
                    $creatorName = $currentUser->display_name;
                    $actionUrl = route('users.index'); // or wherever user management is
                    
                    \Illuminate\Support\Facades\Notification::send($superAdmins, new \App\Notifications\NewUserCreatedNotification($request->name, $companyName, $creatorName, $actionUrl));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send New User Notification: " . $e->getMessage());
            }
        }

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
        
        $currentUser = auth()->user();
        if ($currentUser->role_id != 1 && $user->tyre_company_id != $currentUser->tyre_company_id) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

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
        $request->validate([
            'role_id' => 'required|exists:role,id',
            'name' => 'required|unique:users,name,' . $id,
            'password' => 'nullable|min:6',
            'tyre_company_id' => 'nullable|exists:tyre_companies,id',
        ], [
            'name.unique' => 'Username ini sudah digunakan.',
            'role_id.exists' => 'Role tidak valid.',
            'tyre_company_id.exists' => 'Perusahaan tidak valid.',
        ]);

        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->role_id == 1;
        $targetUser = \App\Models\User::findOrFail($id);

        if (!$isSuperAdmin && $targetUser->tyre_company_id != $currentUser->tyre_company_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke user ini.');
        }

        if (!$isSuperAdmin && $request->role_id == 1) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk memberikan role Super Admin.');
        }

        $companyId = $isSuperAdmin ? $request->tyre_company_id : $currentUser->tyre_company_id;

        if (!$isSuperAdmin && $companyId) {
            $company = \App\Models\TyreCompany::with('roles')->find($companyId);
            if ($company) {
                // Check Role Whitelist for Update
                $allowedRoleIds = $company->roles->pluck('id')->toArray();
                if (!in_array($request->role_id, $allowedRoleIds)) {
                    return redirect()->back()->with('error', 'Role tersebut tidak diizinkan untuk digunakan di perusahaan Anda.');
                }
            }
        }

        $data = [
            'name' => $request->name,
            'role_id' => $request->role_id,
            'master_karyawan_id' => $request->master_karyawan_id,
            'toko_id' => $request->toko_id,
            'tyre_company_id' => $companyId,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $userBefore = \App\Models\User::find($id);

        $this->userService->update($id, $data);

        setLogActivity(auth()->id(), 'Memperbarui user ID: ' . $id, [
            'action_type' => 'update',
            'module' => 'Users',
            'data_before' => $userBefore ? $userBefore->toArray() : null,
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
        $currentUser = auth()->user();
        $targetUser = \App\Models\User::findOrFail($id);

        if ($currentUser->role_id != 1 && $targetUser->tyre_company_id != $currentUser->tyre_company_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke user ini.');
        }

        if ($currentUser->id == $id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        setLogActivity(auth()->id(), 'Menghapus user ID: ' . $id, [
            'action_type' => 'delete',
            'module' => 'Users'
        ]);

        $this->userService->delete($id);
        return redirect()->back()->with('success', 'User deleted successfully');
    }
}
