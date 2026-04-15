<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckEula
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && is_null($user->eula_accepted_at)) {
            // Ijinkan akses ke route eula, accept eula, dan logout
            if (!$request->is('eula') && !$request->is('eula/accept') && !$request->is('logout')) {
                return redirect()->route('eula.show');
            }
        }

        return $next($request);
    }
}
