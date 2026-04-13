@extends('ui.dokter.layout')

@section('dokter_title', 'Data Obat dan Stok')
@section('dokter_heading', 'Data Obat & Stok')
@section('dokter_subheading', 'Pantau status tersedia/habis, stok, dosis, kategori, dan merek dagang obat.')

@section('dokter_content')
<section class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-6">
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Data Obat</p>
        <h3 class="text-3xl font-black mt-2">{{ $medicines->count() }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Tersedia</p>
        <h3 class="text-3xl font-black mt-2 text-emerald-600">{{ $stats['ready_medicines'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Habis</p>
        <h3 class="text-3xl font-black mt-2 text-red-600">{{ $stats['not_ready_medicines'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Stok Rendah</p>
        <h3 class="text-3xl font-black mt-2 text-amber-600">{{ $stats['low_stock_medicines'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Segera Expired</p>
        <h3 class="text-3xl font-black mt-2 text-rose-600">{{ $stats['expiring_soon_medicines'] }}</h3>
    </article>
</section>

<section class="bg-white rounded-[2.5rem] p-8 shadow-sm">
    <div class="mb-6">
        <h3 class="text-xl font-extrabold text-blue-900">Daftar Obat</h3>
        <p class="text-sm text-slate-500">Dokter hanya dapat melihat data obat, stok, kategori, dan status kadaluarsa (tanpa edit).</p>
    </div>

    <form method="GET" action="{{ route('dokter.medicines.index') }}" class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-5">
        <input
            type="text"
            name="q"
            value="{{ $filters['q'] ?? '' }}"
            placeholder="Cari nama, barcode, merek, kategori"
            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm md:col-span-2"
        />
        <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
            <option value="ready" @selected(($filters['status'] ?? 'all') === 'ready')>Ready</option>
            <option value="not_ready" @selected(($filters['status'] ?? 'all') === 'not_ready')>Tidak ready</option>
            <option value="low_stock" @selected(($filters['status'] ?? 'all') === 'low_stock')>Stok rendah</option>
            <option value="expiring" @selected(($filters['status'] ?? 'all') === 'expiring')>Segera expired</option>
            <option value="expired" @selected(($filters['status'] ?? 'all') === 'expired')>Sudah expired</option>
        </select>
        <button type="submit" class="rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-200 transition-colors">
            Terapkan Filter
        </button>
        <a href="{{ route('dokter.medicines.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
            Reset
        </a>
    </form>

    <div class="space-y-4">
        @forelse ($medicines as $medicine)
            @php
                $daysLeft = $medicine->expiry_date
                    ? now()->startOfDay()->diffInDays($medicine->expiry_date->copy()->startOfDay(), false)
                    : null;
                $isExpired = $daysLeft !== null && $daysLeft < 0;
                $isExpiringSoon = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30;
                $expBadgeClass = 'bg-emerald-100 text-emerald-800';
                $expPrefix = 'Belum Exp:';
                if ($isExpired) {
                    $expBadgeClass = 'bg-red-100 text-red-700';
                    $expPrefix = 'Sudah Exp:';
                } elseif ($isExpiringSoon) {
                    $expBadgeClass = 'bg-amber-100 text-amber-700';
                    $expPrefix = 'Mau Exp:';
                }
            @endphp
            <article class="rounded-2xl border border-slate-100 bg-slate-50/60 p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg">{{ $medicine->name }}</h4>
                        <p class="text-sm text-slate-500">Merek dagang: {{ $medicine->trade_name ?: '-' }}</p>
                        <p class="text-sm text-slate-500">Dosis: {{ $medicine->dosage ?: '-' }} | Kategori: {{ $medicine->category ?: '-' }}</p>
                        <p class="text-sm text-slate-500">
                            <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold {{ $expBadgeClass }}">
                                {{ $expPrefix }} {{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }}
                            </span>
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $medicine->stock > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $medicine->stock > 0 ? 'READY' : 'NOT READY' }}
                        </span>
                        @if ($isExpired)
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700">SUDAH EXP</span>
                        @elseif ($isExpiringSoon)
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">EXP {{ $daysLeft }} HARI</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Merek Dagang</p>
                        <p class="text-sm text-slate-700 mt-1">{{ $medicine->trade_name ?: '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Dosis</p>
                        <p class="text-sm text-slate-700 mt-1">{{ $medicine->dosage ?: '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Kategori</p>
                        <p class="text-sm text-slate-700 mt-1">{{ $medicine->category ?: '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Stok</p>
                        <p class="text-sm font-bold text-slate-800 mt-1">{{ $medicine->stock }} {{ $medicine->unit }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 flex items-center">
                        <p class="text-xs font-bold text-slate-500">Mode lihat saja untuk dokter</p>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <p class="text-sm text-slate-500">Belum ada data obat. Jalankan seeder obat terlebih dahulu.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
