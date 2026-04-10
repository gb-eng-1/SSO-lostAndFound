<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * API authentication controller.
 * Ported from api/routes/auth.php.
 * Returns JSON — intended for the mobile app or SPA.
 *
 * Unlike the web Auth controllers, this does NOT redirect — it returns
 * plain-session JSON so the existing JS fetch calls keep working.
 */
class AuthController extends Controller
{
    /** POST /api/auth/admin/login */
    public function adminLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email    = strtolower(trim($request->input('email')));
        $password = $request->input('password');

        $admin = Admin::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$admin || !Hash::check($password, $admin->password_hash)) {
            return response()->json(['ok' => false, 'error' => 'Invalid credentials.'], 401);
        }

        session([
            'admin_id'    => $admin->id,
            'admin_email' => $admin->email,
            'admin_name'  => $admin->name ?? '',
            'admin_role'  => $admin->role ?? 'Admin',
        ]);

        return response()->json([
            'ok'   => true,
            'user' => [
                'id'    => $admin->id,
                'email' => $admin->email,
                'name'  => $admin->name,
                'role'  => $admin->role,
            ],
        ]);
    }

    /** POST /api/auth/student/login */
    public function studentLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $email    = strtolower(trim($request->input('email')));
        $password = $request->input('password');

        $student = Student::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$student || !Hash::check($password, $student->password_hash)) {
            return response()->json(['ok' => false, 'error' => 'Invalid credentials.'], 401);
        }

        session([
            'student_id'    => $student->id,
            'student_email' => $student->email,
            'student_name'  => $student->name ?? '',
        ]);

        return response()->json([
            'ok'   => true,
            'user' => [
                'id'         => $student->id,
                'email'      => $student->email,
                'name'       => $student->name,
                'student_id' => $student->student_id,
            ],
        ]);
    }

    /** POST /api/auth/logout */
    public function logout(): JsonResponse
    {
        session()->flush();
        return response()->json(['ok' => true]);
    }

    /** GET /api/auth/me */
    public function me(Request $request): JsonResponse
    {
        if (session('admin_id')) {
            $admin = Admin::find(session('admin_id'));
            return response()->json(['ok' => true, 'role' => 'admin', 'user' => $admin]);
        }

        if (session('student_id')) {
            $student = Student::find(session('student_id'));
            return response()->json(['ok' => true, 'role' => 'student', 'user' => $student]);
        }

        return response()->json(['ok' => false, 'error' => 'Not authenticated.'], 401);
    }
}
