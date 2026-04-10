<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('login');
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in([
                User::ROLE_DOKTER,
                User::ROLE_ADMIN,
                User::ROLE_KASIR,
                User::ROLE_MASTER_ADMIN,
            ])],
        ], [
            'role.in' => 'Role yang dipilih tidak valid.',
        ]);

        $identifier = trim($validated['identifier']);

        $userQuery = User::query()
            ->where(function ($query) use ($identifier): void {
                $query
                    ->where('email', $identifier)
                    ->orWhere('name', $identifier);
            });

        $userWithSelectedRole = (clone $userQuery)
            ->where('role', $validated['role'])
            ->first();

        if (! $userWithSelectedRole) {
            $userExists = (clone $userQuery)->exists();

            if ($userExists) {
                throw ValidationException::withMessages([
                    'role' => 'Role yang dipilih tidak sesuai dengan akun.',
                ]);
            }

            throw ValidationException::withMessages([
                'identifier' => 'Email/Nama atau password tidak valid.',
            ]);
        }

        if (! Hash::check($validated['password'], $userWithSelectedRole->password)) {
            throw ValidationException::withMessages([
                'identifier' => 'Email/Nama atau password tidak valid.',
            ]);
        }

        if (($userWithSelectedRole->is_active ?? true) === false) {
            throw ValidationException::withMessages([
                'identifier' => 'Akun dinonaktifkan. Hubungi Master Admin untuk aktivasi ulang.',
            ]);
        }

        Auth::login($userWithSelectedRole, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Berhasil logout.');
    }
}
