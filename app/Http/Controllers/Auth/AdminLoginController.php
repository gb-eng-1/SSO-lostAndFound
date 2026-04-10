<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Handles admin authentication.
 * Ported from ADMIN/login.php and ADMIN/logout.php.
 *
 * Session keys stored on successful login:
 *   admin_id    → int
 *   admin_email → string
 *   admin_name  → string
 *   admin_role  → string
 */
class AdminLoginController extends Controller
{
    /** Show the admin login form. GET /admin/login */
    public function showForm()
    {
        if (session('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    /** Process admin login. POST /admin/login */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email    = strtolower(trim($request->input('email')));
        $password = $request->input('password');

        $admin = Admin::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$admin) {
            // Auto-create the default admin on first login (matches original behavior)
            if ($email === 'admin@ub.edu.ph' && in_array($password, ['Admin', 'admin123'], true)) {
                $admin = Admin::create([
                    'email'         => 'admin@ub.edu.ph',
                    'password_hash' => Hash::make('admin123'),
                    'name'          => 'Admin',
                    'role'          => 'Admin',
                ]);
            } else {
                return back()->withInput()->with('error', 'Invalid Credentials.');
            }
        } elseif (!Hash::check($password, $admin->password_hash)) {
            // Accept legacy plain-text password and rehash (matches original behavior)
            if ($email === 'admin@ub.edu.ph' && in_array($password, ['Admin', 'admin123'], true)) {
                $admin->update(['password_hash' => Hash::make('admin123')]);
            } else {
                return back()->withInput()->with('error', 'Invalid Credentials.');
            }
        }

        session([
            'admin_id'    => $admin->id,
            'admin_email' => $admin->email,
            'admin_name'  => $admin->name ?? '',
            'admin_role'  => $admin->role ?? 'Admin',
        ]);

        return redirect()->route('admin.dashboard');
    }

    /** Log out the admin. POST /admin/logout */
    public function logout()
    {
        session()->flush();
        return redirect()->route('admin.login');
    }
}
