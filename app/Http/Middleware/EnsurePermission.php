<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($permissions === [] || $user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Anda tidak memiliki permission untuk aksi ini.');
        }

        return redirect()
            ->route('dashboard')
            ->with('error', 'Akses ditolak. Permission akun Anda tidak mencukupi untuk aksi tersebut.');
    }
}
