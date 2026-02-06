<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\KaryawanMasterKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'password' => 'required',
        ]);

        // 1. Find the employee first to get their ID reference (if needed) or just find user directly by employee_id linked
        // In your user table, master_karyawan_id stores the employee_id
        $user = User::where('master_karyawan_id', $request->employee_id)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->has('remember'));

            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'employee_id' => 'The provided credentials do not match our records.',
        ])->onlyInput('employee_id');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
