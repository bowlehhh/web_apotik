<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match ($user->role) {
            User::ROLE_DOKTER => redirect()->route('dokter.dashboard'),
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            User::ROLE_KASIR => redirect()->route('kasir.dashboard'),
            User::ROLE_MASTER_ADMIN => redirect()->route('master-admin.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
