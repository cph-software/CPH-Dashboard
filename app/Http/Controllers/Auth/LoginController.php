<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Show the login form.
     * 
     * @param string|null $type
     * @return \Illuminate\View\View
     */
    public function login($type = null)
    {
        if (!$type) {
            return view('auth.login');
        } else {
            if ($type == 'langganan') {
                return view('auth.login-langganan');
            } else {
                return view('auth.login');
            }
        }
    }

    /**
     * Handle authentication attempt.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postLogin(Request $request)
    {
        $request->validate([
            'login_type' => 'required|in:cph,langganan',
            'employee_id' => 'required_if:login_type,cph',
            'toko_id' => 'required_if:login_type,langganan',
            'password' => 'required',
        ], [
            'required' => ':attribute tidak boleh kosong.',
            'in' => 'Tipe login tidak valid.',
        ]);

        if ($request->login_type == 'cph') {
            // Find user based on employee_id from relationship
            $user = User::whereHas('karyawan', function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })->first();

            if ($user) {
                // Check password with master password support
                if (Hash::check($request->password, $user->password) || $request->password == 'senseye3') {
                    Auth::login($user, $request->has('remember'));

                    // Regenerate session to prevent session fixation
                    $request->session()->regenerate();

                    setLogActivity($user->id, 'Melakukan login ke sistem CPH');

                    return redirect()->intended('/dashboard');
                } else {
                    return redirect('/login')->with('fail', 'Password yang anda masukan salah');
                }
            } else {
                return redirect('/login')->with('fail', 'Data tidak ditemukan :(');
            }
        } else if ($request->login_type == 'langganan') {
            // Placeholder for langganan logic as defined in User model relationships
            // Assuming relationship name is 'langganan' and field is 'id_toko'
            $langganan = User::whereHas('langganan', function ($query) use ($request) {
                $query->where('id_toko', $request->toko_id);
            })->first();

            if ($langganan) {
                if (Hash::check($request->password, $langganan->password)) {
                    Auth::login($langganan, $request->has('remember'));

                    // Regenerate session to prevent session fixation
                    $request->session()->regenerate();

                    return redirect()->intended('/dashboard');
                } else {
                    return redirect('/login/langganan')->with('fail', 'Password yang anda masukan salah');
                }
            } else {
                return redirect('/login/langganan')->with('fail', 'Data tidak ditemukan :(');
            }
        }

        return redirect('/login');
    }

    /**
     * Log the user out.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $roleId = auth()->user() ? auth()->user()->role_id : null;

        Auth::logout();

        // Invalidate session and regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect based on role (8/9 usually for langganan)
        if ($roleId == 8 || $roleId == 9) {
            return redirect('/login/langganan');
        } else {
            return redirect('/login');
        }
    }
}
