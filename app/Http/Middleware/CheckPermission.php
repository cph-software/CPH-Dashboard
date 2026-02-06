<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $menu
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $menu, $permission)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        if (!$user->role) {
            abort(403, 'User has no assigned role.');
        }

        if (!$user->hasPermission($menu, $permission)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
