<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects admin routes.
 * Ported from ADMIN/auth_check.php — checks session('admin_id').
 */
class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(session('admin_id'))) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
