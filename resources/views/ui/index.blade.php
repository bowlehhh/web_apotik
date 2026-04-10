@extends('layouts.ui-shell')

@section('title', 'UI Preview | APOTEK SUMBER SEHAT')
@section('body_class', 'bg-background text-on-surface min-h-screen')

@section('content')
<main class="max-w-5xl mx-auto px-6 py-12">
    <h1 class="text-4xl font-extrabold tracking-tight text-primary mb-2">UI Preview APOTEK SUMBER SEHAT</h1>
    <p class="text-on-surface-variant mb-10">Kumpulan halaman static dari design reference kamu.</p>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @php
            $pages = [
                ['label' => 'Login', 'path' => '/login'],
                ['label' => 'Dashboard Redirect', 'path' => '/dashboard'],
                ['label' => 'Dokter Dashboard', 'path' => '/dokter/dashboard'],
                ['label' => 'Kasir Dashboard', 'path' => '/kasir/dashboard'],
                ['label' => 'Kasir Transaksi', 'path' => '/kasir/transaksi'],
                ['label' => 'Admin Dashboard', 'path' => '/admin/dashboard'],
                ['label' => 'Admin Data Obat', 'path' => '/admin/data-obat'],
                ['label' => 'Admin Laporan', 'path' => '/admin/laporan'],
                ['label' => 'Master Admin', 'path' => '/master-admin/dashboard'],
                ['label' => 'Admin Dashboard (UI)', 'path' => '/ui/admin-dashboard'],
                ['label' => 'Kasir Dashboard', 'path' => '/ui/kasir-dashboard'],
                ['label' => 'Master Admin Dashboard', 'path' => '/ui/master-admin-dashboard'],
                ['label' => 'Transaksi POS', 'path' => '/ui/transaksi-penjualan'],
                ['label' => 'Laporan & Analitik', 'path' => '/ui/laporan-analitik'],
                ['label' => 'Data Obat / Inventory', 'path' => '/ui/data-obat'],
                ['label' => 'Manajemen Dokter (UI)', 'path' => '/ui/mobile-dashboard'],
            ];
        @endphp

        @foreach ($pages as $page)
            <a href="{{ $page['path'] }}" class="rounded-2xl bg-surface-container-lowest border border-surface-container-high p-5 hover:border-primary/40 hover:shadow-md transition-all">
                <p class="text-lg font-bold text-primary">{{ $page['label'] }}</p>
                <p class="text-xs uppercase tracking-widest text-on-surface-variant mt-2">{{ $page['path'] }}</p>
            </a>
        @endforeach
    </div>
</main>
@endsection
