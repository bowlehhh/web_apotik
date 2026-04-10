@extends('layouts.ui-shell')

@section('title', trim($__env->yieldContent('master_title', 'APOTEK SUMBER SEHAT - Master Admin')))
@section('body_class', 'bg-slate-100 text-slate-900')

@section('content')
@php
    $navItems = [
        [
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'url' => route('master-admin.dashboard'),
            'active' => request()->routeIs('master-admin.dashboard'),
        ],
        [
            'label' => 'Data Obat',
            'icon' => 'inventory_2',
            'url' => route('master-admin.medicines.index'),
            'active' => request()->routeIs('master-admin.medicines.*'),
        ],
        [
            'label' => 'Input Barcode',
            'icon' => 'barcode_scanner',
            'url' => route('admin.barcode.index'),
            'active' => request()->routeIs('admin.barcode.*'),
        ],
        [
            'label' => 'Aktivitas Role',
            'icon' => 'monitoring',
            'url' => route('master-admin.activities.index'),
            'active' => request()->routeIs('master-admin.activities.*'),
        ],
        [
            'label' => 'Role & Permission',
            'icon' => 'admin_panel_settings',
            'url' => route('master-admin.role-permission.index'),
            'active' => request()->routeIs('master-admin.role-permission.*'),
        ],
        [
            'label' => 'Ubah Password',
            'icon' => 'password',
            'url' => route('master-admin.password.change'),
            'active' => request()->routeIs('master-admin.password.change'),
        ],
        [
            'label' => 'Reset Password',
            'icon' => 'lock_reset',
            'url' => route('master-admin.password.reset'),
            'active' => request()->routeIs('master-admin.password.reset'),
        ],
    ];
@endphp

<div class="min-h-screen bg-slate-100">
    <aside class="fixed left-0 top-0 hidden h-screen w-64 flex-col border-r border-slate-100 bg-slate-50 p-4 lg:flex">
        <div class="mb-8 px-2">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-container text-white">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">medical_services</span>
                </div>
                <div>
                    <h1 class="text-lg font-black text-blue-900">APOTEK SUMBER SEHAT</h1>
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Master Admin</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto pr-1">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $item['active'] ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-200/60' }}"
                >
                    <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                    <span class="text-sm">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="pt-4 border-t border-slate-200">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-error hover:bg-red-50">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm">Logout</span>
            </a>
        </div>
    </aside>

    <main class="min-h-screen lg:ml-64">
        <header class="sticky top-0 z-30 border-b border-slate-100 bg-white/85 px-4 py-3 backdrop-blur-md sm:px-6 sm:py-4 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-blue-900 sm:text-2xl">@yield('master_heading', 'Master Dashboard')</h2>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">@yield('master_subheading', 'Kontrol utama sistem apotek oleh master admin.')</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                    @yield('master_actions')
                </div>
            </div>
        </header>

        <div class="space-y-6 px-4 py-6 sm:px-6 sm:py-7 lg:space-y-8 lg:px-8 lg:py-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('master_content')
        </div>
    </main>
</div>
@endsection
