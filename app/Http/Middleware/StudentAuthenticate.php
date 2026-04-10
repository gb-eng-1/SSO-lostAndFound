<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects student routes.
 * Ported from STUDENT/auth_check.php — checks session('student_id') AND session('student_email').
 */
class StudentAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(session('student_id')) || empty(session('student_email'))) {
            return redirect()->route('student.login');
        }

        return $next($request);
    }
}
