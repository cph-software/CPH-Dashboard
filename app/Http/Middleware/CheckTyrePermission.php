<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckTyrePermission
{
    /**
     * Middleware yang otomatis cek permission berdasarkan route action.
     * 
     * Cara kerja:
     * - Mengambil nama menu dari parameter middleware
     * - Mengambil action dari route (index/show → view, create/store → create, edit/update → update, destroy → delete)
     * - Cek apakah user punya permission tersebut untuk menu tersebut
     * 
     * Penggunaan di route:
     *   Route::resource('master_tyre', ...)->middleware('tyre.permission:Master Tyre');
     *   atau per-route:
     *   Route::post('store', ...)->middleware('tyre.permission:Master Tyre,create');
     *
     * @param Request $request
     * @param Closure $next
     * @param string $menuName  Nama menu di database
     * @param string|null $forcePermission  Jika di-set, paksa permission ini (bypass auto-detect)
     */
    public function handle(Request $request, Closure $next, $menuName, $forcePermission = null)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        if (!$user->role) {
            abort(403, 'User tidak memiliki role.');
        }

        // 1. Administrator (role_id 1) bypass
        if ($user->role_id == 1) {
            return $next($request);
        }

        // 2. Cek apakah user punya akses ke menu ini sama sekali
        if (!$user->hasPermission($menuName)) {
            abort(403, 'Anda tidak memiliki akses ke menu ini.');
        }

        // 2. Tentukan permission yang dibutuhkan
        $permission = $forcePermission ?? $this->detectPermission($request);

        // 3. Cek granular permission
        if ($permission && !$user->hasPermission($menuName, $permission)) {
            // Untuk AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => "Anda tidak memiliki izin untuk '{$permission}' pada menu '{$menuName}'."
                ], 403);
            }

            // Untuk form submission, redirect back dengan error
            if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('delete')) {
                return redirect()->back()->with('error', "Anda tidak memiliki izin untuk melakukan aksi ini.");
            }

            abort(403, "Anda tidak memiliki izin untuk '{$permission}' pada menu '{$menuName}'.");
        }

        return $next($request);
    }

    /**
     * Auto-detect permission yang diperlukan berdasarkan HTTP method & route action
     */
    private function detectPermission(Request $request): ?string
    {
        // Cek dari route action name dulu (e.g. tyre-master.store → store)
        $routeName = $request->route()->getName();
        if ($routeName) {
            $action = last(explode('.', $routeName));

            $actionMap = [
                'index'   => 'view',
                'show'    => 'view',
                'data'    => 'view',    // DataTables AJAX
                'create'  => 'create',
                'store'   => 'create',
                'edit'    => 'update',
                'update'  => 'update',
                'destroy' => 'delete',
            ];

            if (isset($actionMap[$action])) {
                return $actionMap[$action];
            }
        }

        // Fallback: detect dari HTTP method
        $method = $request->method();
        $methodMap = [
            'GET'    => 'view',
            'POST'   => 'create',
            'PUT'    => 'update',
            'PATCH'  => 'update',
            'DELETE' => 'delete',
        ];

        return $methodMap[$method] ?? 'view';
    }
}
