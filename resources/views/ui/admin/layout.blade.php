@extends('layouts.ui-shell')

@section('title', 'APOTEK SUMBER SEHAT - '.trim($__env->yieldContent('admin_title', 'Admin')))
@section('body_class', 'bg-surface text-on-surface')
@section('page_style')
    .status-modal-enter {
        animation: statusModalFade 0.2s ease-out;
    }
    .status-modal-card {
        animation: statusModalPop 0.28s ease-out;
    }
    @keyframes statusModalFade {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes statusModalPop {
        from { opacity: 0; transform: translateY(12px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
@endsection

@section('content')
@php
    $adminNavItems = [
        [
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'url' => route('dashboard'),
            'active' => request()->routeIs('admin.dashboard'),
        ],
        [
            'label' => 'Data Obat & Stok',
            'icon' => 'medication',
            'url' => route('admin.data-obat'),
            'active' => request()->routeIs('admin.warehouse')
                || request()->routeIs('admin.warehouse.*')
                || request()->routeIs('admin.data-obat')
                || request()->routeIs('admin.medicines.*'),
        ],
        [
            'label' => 'Input Barcode',
            'icon' => 'barcode_scanner',
            'url' => route('admin.barcode.index'),
            'active' => request()->routeIs('admin.barcode.*'),
        ],
        [
            'label' => 'Laporan',
            'icon' => 'analytics',
            'url' => route('admin.laporan'),
            'active' => request()->routeIs('admin.laporan'),
        ],
        [
            'label' => 'Dashboard Dokumentasi',
            'icon' => 'photo_library',
            'url' => route('admin.dokumentasi'),
            'active' => request()->routeIs('admin.dokumentasi'),
        ],
    ];
@endphp

<div class="min-h-screen bg-surface">
    <input id="admin-mobile-nav-toggle" type="checkbox" class="peer sr-only" />

    <label
        for="admin-mobile-nav-toggle"
        class="fixed inset-0 z-40 bg-slate-950/45 opacity-0 pointer-events-none transition-opacity duration-200 peer-checked:opacity-100 peer-checked:pointer-events-auto lg:hidden"
    ></label>

    <aside class="fixed inset-y-0 left-0 z-50 flex h-screen w-72 max-w-[86vw] -translate-x-full flex-col border-r border-slate-100 bg-slate-50 p-4 transition-transform duration-200 peer-checked:translate-x-0 lg:hidden">
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-container text-white">
                    <span class="material-symbols-outlined">medical_services</span>
                </div>
                <div>
                    <h1 class="text-base font-black text-blue-900">APOTEK SUMBER SEHAT</h1>
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Admin & Gudang</p>
                </div>
            </div>
            <label for="admin-mobile-nav-toggle" class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </label>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto">
            @foreach ($adminNavItems as $item)
                <a
                    data-admin-mobile-link
                    href="{{ $item['url'] }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-3 {{ $item['active'] ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-200/50' }}"
                >
                    <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                    <span class="text-sm">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="space-y-1 border-t border-slate-100 pt-4">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 px-4 py-2 text-slate-500 hover:text-error transition-colors">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-xs font-bold uppercase tracking-widest">Logout</span>
            </a>
        </div>
    </aside>

    <aside class="fixed left-0 top-0 hidden h-screen w-64 flex-col border-r border-slate-100 bg-slate-50 p-4 lg:flex">
        <div class="mb-8 px-2">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-container text-white">
                    <span class="material-symbols-outlined">medical_services</span>
                </div>
                <div>
                    <h1 class="text-lg font-black text-blue-900">APOTEK SUMBER SEHAT</h1>
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Admin & Gudang</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 space-y-1">
            @foreach ($adminNavItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-3 {{ $item['active'] ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-200/50' }}"
                >
                    <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                    <span class="text-sm">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="space-y-1 border-t border-slate-100 pt-4">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 px-4 py-2 text-slate-500 hover:text-error transition-colors">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-xs font-bold uppercase tracking-widest">Logout</span>
            </a>
        </div>
    </aside>

    <main class="min-h-screen lg:ml-64">
        <header class="sticky top-0 z-30 border-b border-slate-100 bg-white/85 px-4 py-3 backdrop-blur-md sm:px-6 sm:py-4 lg:px-8">
            <div class="space-y-3 lg:hidden">
                <div class="flex items-center justify-between">
                    <label for="admin-mobile-nav-toggle" class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full text-blue-700 hover:bg-blue-50">
                        <span class="material-symbols-outlined text-[20px]">menu</span>
                    </label>
                    <p class="text-base font-extrabold tracking-tight text-blue-700">APOTEK SUMBER SEHAT</p>
                    <span class="inline-block h-9 w-9" aria-hidden="true"></span>
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-blue-900">@yield('admin_heading', 'Dashboard Admin')</h2>
                    <p class="text-xs font-medium text-slate-500">@yield('admin_subheading', 'Manajemen obat, gudang, dan laporan operasional.')</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @yield('admin_actions')
                </div>
            </div>

            <div class="hidden flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 lg:flex">
                <div class="flex items-start gap-3">
                    <div>
                        <h2 class="text-xl font-extrabold text-blue-900 sm:text-2xl">@yield('admin_heading', 'Dashboard Admin')</h2>
                        <p class="text-xs font-medium text-slate-500 sm:text-sm">@yield('admin_subheading', 'Manajemen obat, gudang, dan laporan operasional.')</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                    @yield('admin_actions')
                </div>
            </div>
        </header>

        <div class="space-y-6 px-4 py-6 sm:px-6 sm:py-7 lg:space-y-8 lg:px-8 lg:py-8">
            @yield('admin_content')
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('admin-mobile-nav-toggle');
        if (!toggle) {
            return;
        }

        document.querySelectorAll('[data-admin-mobile-link]').forEach(function (link) {
            link.addEventListener('click', function () {
                toggle.checked = false;
            });
        });
    });
</script>

@if (session('status'))
    <div
        id="status-modal"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/45 px-4 status-modal-enter"
    >
        <div class="status-modal-card w-full max-w-2xl rounded-[2rem] border border-emerald-200 bg-white p-8 shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <span class="material-symbols-outlined text-[32px]" style="font-variation-settings:'FILL' 1">check_circle</span>
                </div>
                <div class="flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-emerald-600">Berhasil</p>
                    <h3 class="mt-2 text-3xl font-black text-slate-900">Aksi berhasil diproses</h3>
                    <p class="mt-3 text-base font-medium leading-7 text-slate-600">
                        {{ session('status') }}
                    </p>
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <button
                    type="button"
                    onclick="document.getElementById('status-modal')?.remove()"
                    class="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-700 transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endif

@php
    $errorMessages = [];
    if (session('error')) {
        $errorMessages[] = session('error');
    }
    if ($errors->any()) {
        foreach ($errors->all() as $message) {
            $errorMessages[] = $message;
        }
    }
    $errorMessages = array_values(array_unique(array_filter($errorMessages, function ($message) {
        return trim((string) $message) !== '';
    })));
@endphp
@if (count($errorMessages) > 0)
    <div
        id="error-modal"
        class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/45 px-4 status-modal-enter"
    >
        <div class="status-modal-card w-full max-w-2xl rounded-[2rem] border border-red-200 bg-white p-8 shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-700">
                    <span class="material-symbols-outlined text-[32px]" style="font-variation-settings:'FILL' 1">error</span>
                </div>
                <div class="flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-red-600">Gagal</p>
                    <h3 class="mt-2 text-3xl font-black text-slate-900">Aksi belum berhasil</h3>
                    @if (count($errorMessages) === 1)
                        <p class="mt-3 text-base font-medium leading-7 text-slate-600">
                            {{ $errorMessages[0] }}
                        </p>
                    @else
                        <ul class="mt-3 space-y-2 text-base font-medium leading-7 text-slate-600">
                            @foreach ($errorMessages as $message)
                                <li class="flex items-start gap-2">
                                    <span class="mt-2 inline-block h-2 w-2 shrink-0 rounded-full bg-red-500"></span>
                                    <span>{{ $message }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <button
                    type="button"
                    onclick="document.getElementById('error-modal')?.remove()"
                    class="rounded-2xl bg-red-600 px-6 py-3 text-sm font-bold text-white hover:bg-red-700 transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endif
@endsection
