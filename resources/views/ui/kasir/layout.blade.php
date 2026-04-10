@extends('layouts.ui-shell')

@section('title', 'APOTEK SUMBER SEHAT - '.trim($__env->yieldContent('kasir_title', 'Dashboard Kasir')))
@section('body_class', 'bg-surface text-on-surface')

@section('content')
<div class="flex min-h-screen">
    <aside class="w-64 fixed left-0 top-0 h-screen bg-slate-50 border-r border-slate-100 p-4 flex flex-col">
        <div class="flex items-center gap-3 mb-8 px-2">
            <div class="w-10 h-10 bg-primary-container rounded-xl flex items-center justify-center text-white">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">medical_services</span>
            </div>
            <div>
                <h1 class="text-lg font-black text-blue-900">APOTEK SUMBER SEHAT</h1>
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Kasir & Resep</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto pr-1">
            <a href="{{ route('kasir.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('kasir.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm">Dashboard Kasir</span>
            </a>
            <a href="{{ route('kasir.transaksi') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('kasir.transaksi') || request()->routeIs('kasir.sales.*') || request()->routeIs('kasir.prescriptions.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined">point_of_sale</span>
                <span class="text-sm">Transaksi</span>
            </a>
            <a href="{{ route('kasir.medicines.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('kasir.medicines.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="text-sm">Data Obat</span>
            </a>
        </nav>

        <div class="pt-4 border-t border-slate-200 space-y-1">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 hover:bg-slate-100">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm">Keluar</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 min-h-screen">
        <header class="sticky top-0 z-40 bg-white/85 backdrop-blur-md border-b border-slate-100 px-8 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-blue-900">@yield('kasir_heading', 'Dashboard Kasir')</h2>
                <p class="text-xs text-slate-500 font-medium">@yield('kasir_subheading', 'Manajemen kasir apotek.')</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold text-slate-700">{{ auth()->user()->name ?? 'Kasir' }}</p>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Peran: {{ auth()->user()->roleLabel() ?? 'Kasir' }}</p>
            </div>
        </header>

        @php
            $notifications = [];

            if (session('status')) {
                $notifications[] = [
                    'type' => 'success',
                    'title' => 'Berhasil',
                    'message' => session('status'),
                    'timeout' => 5000,
                ];
            }

            if (session('error')) {
                $notifications[] = [
                    'type' => 'error',
                    'title' => 'Terjadi Masalah',
                    'message' => session('error'),
                    'timeout' => 7000,
                ];
            }

            if ($errors->any()) {
                $notifications[] = [
                    'type' => 'error',
                    'title' => 'Validasi Gagal',
                    'message' => $errors->first(),
                    'timeout' => 7000,
                ];
            }
        @endphp

        @if (! empty($notifications))
            <div data-toast-overlay class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-6">
                <button type="button" data-toast-backdrop class="absolute inset-0 bg-slate-900/35 backdrop-blur-[2px]" aria-label="Tutup notifikasi"></button>
                <div class="relative z-10 w-full max-w-2xl space-y-4">
                @foreach ($notifications as $notification)
                    @php
                        $isSuccess = $notification['type'] === 'success';
                    @endphp
                    <div
                        data-toast
                        data-timeout="{{ $notification['timeout'] }}"
                        class="pointer-events-auto transform-gpu transition-all duration-300 ease-out opacity-100 scale-100 translate-y-0 rounded-3xl border shadow-2xl backdrop-blur bg-white/95 overflow-hidden {{ $isSuccess ? 'border-emerald-200' : 'border-red-200' }}"
                    >
                        <div class="p-6 sm:p-7 pr-5 flex items-start gap-4">
                            <div class="h-12 w-12 rounded-2xl flex items-center justify-center {{ $isSuccess ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                <span class="material-symbols-outlined text-[26px]" style="font-variation-settings:'FILL' 1">
                                    {{ $isSuccess ? 'check_circle' : 'error' }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-2xl font-black {{ $isSuccess ? 'text-emerald-700' : 'text-red-700' }}">{{ $notification['title'] }}</p>
                                <p class="text-lg text-slate-700 leading-7 mt-1">{{ $notification['message'] }}</p>
                            </div>
                            <button
                                type="button"
                                data-toast-close
                                class="h-10 w-10 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors"
                                aria-label="Tutup notifikasi"
                            >
                                <span class="material-symbols-outlined text-[22px]">close</span>
                            </button>
                        </div>
                        <div class="h-1.5 {{ $isSuccess ? 'bg-emerald-500/70' : 'bg-red-500/70' }}"></div>
                    </div>
                @endforeach
                </div>
            </div>
        @endif

        <div class="px-8 py-8 space-y-8">
            @yield('kasir_content')
        </div>
    </main>
</div>

<script>
    (function () {
        const overlay = document.querySelector('[data-toast-overlay]');
        const toasts = document.querySelectorAll('[data-toast]');
        if (!overlay || !toasts.length) {
            return;
        }

        const removeOverlayIfEmpty = () => {
            if (overlay.querySelectorAll('[data-toast]').length) {
                return;
            }

            overlay.classList.add('opacity-0');
            window.setTimeout(() => {
                overlay.remove();
            }, 240);
        };

        const hideToast = (toast) => {
            if (!toast || toast.dataset.closing === '1') {
                return;
            }

            toast.dataset.closing = '1';
            toast.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'scale-95', 'translate-y-2');

            window.setTimeout(() => {
                toast.remove();
                removeOverlayIfEmpty();
            }, 260);
        };

        toasts.forEach((toast, index) => {
            const closeButton = toast.querySelector('[data-toast-close]');
            const baseTimeout = Number(toast.dataset.timeout || 6000);
            const finalTimeout = baseTimeout + (index * 250);

            if (closeButton) {
                closeButton.addEventListener('click', () => hideToast(toast));
            }

            window.setTimeout(() => hideToast(toast), finalTimeout);
        });

        const backdrop = overlay.querySelector('[data-toast-backdrop]');
        if (backdrop) {
            backdrop.addEventListener('click', () => {
                toasts.forEach((toast) => hideToast(toast));
            });
        }
    })();
</script>
@endsection
