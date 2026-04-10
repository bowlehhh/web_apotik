<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($roles === [] || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Akses ke halaman tersebut tidak sesuai role akun Anda.');
    }
}
