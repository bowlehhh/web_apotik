@extends('layouts.ui-shell')

@section('title', 'APOTEK SUMBER SEHAT - '.trim($__env->yieldContent('dokter_title', 'Dashboard Dokter')))
@section('body_class', 'bg-surface text-on-surface')

@section('content')
<div class="flex min-h-screen">
    <aside class="w-64 fixed left-0 top-0 h-screen bg-slate-50 border-r border-slate-100 p-4 flex flex-col">
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
            <a href="{{ route('dokter.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dokter.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm">Dashboard Dokter</span>
            </a>
            <a href="{{ route('dokter.consultations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dokter.consultations.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined">clinical_notes</span>
                <span class="text-sm">Konsultasi Pasien</span>
            </a>
            <a href="{{ route('dokter.histories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dokter.histories.*') || request()->routeIs('dokter.visits.*') || request()->routeIs('dokter.prescriptions.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined">history</span>
                <span class="text-sm">Riwayat Pasien</span>
            </a>
            <a href="{{ route('dokter.medicines.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dokter.medicines.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="text-sm">Data Obat & Stok</span>
            </a>
        </nav>

        <div class="pt-4 border-t border-slate-200">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-error hover:bg-red-50">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm">Keluar</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 flex-1 min-h-screen">
        <header class="sticky top-0 z-40 bg-white/85 backdrop-blur-md border-b border-slate-100 px-8 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-blue-900">@yield('dokter_heading', 'Dashboard Dokter')</h2>
                <p class="text-xs text-slate-500 font-medium">@yield('dokter_subheading', 'Manajemen dokter apotek.')</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold text-slate-700">{{ auth()->user()->name ?? 'Dokter' }}</p>
                <p class="text-xs text-slate-500 uppercase tracking-wider">Peran: Dokter</p>
            </div>
        </header>

        <div class="px-8 py-8 space-y-8">
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

            @yield('dokter_content')
        </div>
    </main>
</div>
@endsection
