@extends('layouts.ui-shell')

@section('title', 'APOTEK SUMBER SEHAT - '.trim($__env->yieldContent('dokter_title', 'Dashboard Dokter')))
@section('body_class', 'bg-surface text-on-surface')

@section('content')
@php
    $dokterNavItems = [
        [
            'label' => 'Dashboard Dokter',
            'icon' => 'dashboard',
            'url' => route('dokter.dashboard'),
            'active' => request()->routeIs('dokter.dashboard'),
        ],
        [
            'label' => 'Konsultasi Pasien',
            'icon' => 'clinical_notes',
            'url' => route('dokter.consultations.index'),
            'active' => request()->routeIs('dokter.consultations.*'),
        ],
        [
            'label' => 'Riwayat Pasien',
            'icon' => 'history',
            'url' => route('dokter.histories.index'),
            'active' => request()->routeIs('dokter.histories.*') || request()->routeIs('dokter.visits.*') || request()->routeIs('dokter.prescriptions.*'),
        ],
        [
            'label' => 'Data Obat & Stok',
            'icon' => 'inventory_2',
            'url' => route('dokter.medicines.index'),
            'active' => request()->routeIs('dokter.medicines.*'),
        ],
    ];
@endphp

<div class="min-h-screen bg-slate-100">
    <div id="dokter_mobile_backdrop" class="fixed inset-0 z-50 hidden bg-slate-900/40 lg:hidden"></div>
    <aside id="dokter_mobile_drawer" class="fixed inset-y-0 left-0 z-[60] hidden w-72 max-w-[85vw] -translate-x-full flex-col border-r border-slate-200 bg-white p-4 shadow-xl transition-transform duration-200 ease-out lg:hidden">
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-container text-white">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">medical_services</span>
                </div>
                <div>
                    <h2 class="text-sm font-black text-blue-900">APOTEK SUMBER SEHAT</h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Menu Dokter</p>
                </div>
            </div>
            <button id="dokter_mobile_drawer_close" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto pr-1">
            @foreach ($dokterNavItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 {{ $item['active'] ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                    <span class="text-sm">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-4 border-t border-slate-200 pt-4">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-error hover:bg-red-50">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm">Keluar</span>
            </a>
        </div>
    </aside>

    <aside class="fixed left-0 top-0 hidden h-screen w-64 flex-col border-r border-slate-100 bg-slate-50 p-4 lg:flex">
        <div class="flex items-center gap-3 mb-8 px-2">
            <div class="w-10 h-10 bg-primary-container rounded-xl text-white flex items-center justify-center">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">medical_services</span>
            </div>
            <div>
                <h1 class="text-lg font-black text-blue-900">APOTEK SUMBER SEHAT</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Manajemen Dokter</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto pr-1">
            @foreach ($dokterNavItems as $item)
                <a href="{{ $item['url'] }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $item['active'] ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                    <span class="text-sm">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="pt-4 border-t border-slate-200">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-error hover:bg-red-50">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm">Keluar</span>
            </a>
        </div>
    </aside>

    <main class="min-h-screen lg:ml-64">
        <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/95 px-4 py-3 backdrop-blur-md sm:px-6 sm:py-4 lg:px-8">
            <div class="flex items-center justify-between lg:hidden">
                <button id="dokter_mobile_menu_button" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-blue-700 hover:bg-blue-50" aria-controls="dokter_mobile_drawer" aria-expanded="false">
                    <span class="material-symbols-outlined text-[20px]">menu</span>
                </button>
                <p class="text-base font-extrabold tracking-tight text-blue-700">APOTEK SUMBER SEHAT</p>
                <span class="inline-block h-9 w-9" aria-hidden="true"></span>
            </div>

            <div class="hidden items-start justify-between gap-3 lg:flex">
                <div class="min-w-0">
                    <h2 class="text-xl font-extrabold leading-tight text-blue-900">@yield('dokter_heading', 'Dashboard Dokter')</h2>
                    <p class="mt-0.5 text-sm font-medium text-slate-500">@yield('dokter_subheading', 'Manajemen dokter apotek.')</p>
                </div>
                <div class="text-right">
                    <p class="max-w-[220px] truncate text-sm font-semibold text-slate-700">{{ auth()->user()->name ?? 'Dokter' }}</p>
                    <p class="text-xs uppercase tracking-wider text-slate-500">Peran: Dokter</p>
                </div>
            </div>
        </header>

        @if (trim($__env->yieldContent('dokter_hide_mobile_quicknav', '')) === '')
            <div class="border-b border-slate-100 bg-white px-4 py-2 sm:px-6 lg:hidden">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($dokterNavItems as $item)
                        <a
                            href="{{ $item['url'] }}"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-2 text-xs font-bold {{ $item['active'] ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600' }}"
                        >
                            <span class="material-symbols-outlined text-base">{{ $item['icon'] }}</span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                    <a href="{{ route('logout.get') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-red-50 px-3 py-2 text-xs font-bold text-red-600">
                        <span class="material-symbols-outlined text-base">logout</span>
                        <span>Keluar</span>
                    </a>
                </div>
            </div>
        @endif

        <div class="space-y-6 px-4 py-5 sm:px-6 sm:py-7 lg:space-y-8 lg:px-8 lg:py-8">
            <div>
                @if (session('status'))
                    <div class="rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-4 text-sm font-semibold">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="rounded-2xl bg-red-50 border border-red-100 text-red-700 px-5 py-4 text-sm font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-2xl bg-red-50 border border-red-100 text-red-700 px-5 py-4 text-sm font-semibold">
                        {{ $errors->first() }}
                    </div>
                @endif
            </div>

            @yield('dokter_content')
        </div>
    </main>
</div>

<script>
    (function () {
        const menuButton = document.getElementById('dokter_mobile_menu_button');
        const drawer = document.getElementById('dokter_mobile_drawer');
        const closeButton = document.getElementById('dokter_mobile_drawer_close');
        const backdrop = document.getElementById('dokter_mobile_backdrop');

        if (!menuButton || !drawer || !backdrop) {
            return;
        }

        const openDrawer = () => {
            drawer.classList.remove('hidden');
            backdrop.classList.remove('hidden');

            requestAnimationFrame(() => {
                drawer.classList.remove('-translate-x-full');
            });

            document.body.classList.add('overflow-hidden');
            menuButton.setAttribute('aria-expanded', 'true');
        };

        const closeDrawer = () => {
            drawer.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            menuButton.setAttribute('aria-expanded', 'false');

            setTimeout(() => {
                if (drawer.classList.contains('-translate-x-full')) {
                    drawer.classList.add('hidden');
                }
            }, 200);
        };

        menuButton.addEventListener('click', openDrawer);
        closeButton?.addEventListener('click', closeDrawer);
        backdrop.addEventListener('click', closeDrawer);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDrawer();
            }
        });
    })();
</script>
@endsection
