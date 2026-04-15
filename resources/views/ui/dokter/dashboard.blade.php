@extends('ui.dokter.layout')

@section('dokter_title', 'Dashboard Dokter')
@section('dokter_heading', 'Dashboard Dokter')
@section('dokter_subheading', 'Ringkasan layanan dokter, kunjungan hari ini, dan kondisi stok obat.')
@section('dokter_hide_mobile_quicknav', '1')

@section('dokter_content')
@php
    $expiredTotal = (int) (($stats['expired_medicines'] ?? 0) + ($stats['expiring_soon_medicines'] ?? 0));
@endphp

<div class="space-y-8 lg:hidden">
    <section class="rounded-2xl bg-gradient-to-br from-blue-700 via-blue-700 to-indigo-700 px-5 py-5 text-white shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-blue-100">Apotek Sumber Sehat</p>
        <h1 class="mt-2 text-2xl font-black leading-tight">Dashboard Dokter</h1>
        <p class="mt-1 text-sm text-blue-100">Selamat datang kembali, siapkan pelayanan hari ini.</p>
    </section>

    <section class="grid grid-cols-2 gap-3">
        <a href="{{ route('dokter.histories.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="material-symbols-outlined text-2xl text-blue-700">group</span>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">Total Pasien</p>
            <p class="mt-1 text-3xl font-black text-slate-900">{{ $stats['total_patients'] }}</p>
        </a>
        <a href="{{ route('dokter.histories.index') }}" class="rounded-2xl border border-blue-200 bg-white p-4 shadow-sm">
            <span class="material-symbols-outlined text-2xl text-blue-700">calendar_today</span>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">Kunjungan</p>
            <p class="mt-1 text-3xl font-black text-slate-900">{{ $stats['today_visits'] }}</p>
        </a>
        <a href="{{ route('dokter.medicines.index', ['status' => 'ready']) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="material-symbols-outlined text-2xl text-emerald-600">inventory_2</span>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">Obat Tersedia</p>
            <p class="mt-1 text-3xl font-black text-emerald-600">{{ $stats['ready_medicines'] }}</p>
        </a>
        <a href="{{ route('dokter.medicines.index', ['status' => 'not_ready']) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="material-symbols-outlined text-2xl text-red-600">label_off</span>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">Obat Habis</p>
            <p class="mt-1 text-3xl font-black text-red-600">{{ $stats['not_ready_medicines'] }}</p>
        </a>
    </section>

    <section>
        <div class="-mx-4 flex gap-3 overflow-x-auto px-4 pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <a href="{{ route('dokter.consultations.index') }}" class="w-40 shrink-0 rounded-2xl bg-blue-600 p-4 text-white shadow-sm">
                <span class="material-symbols-outlined text-3xl">medical_services</span>
                <p class="mt-2 text-sm font-bold leading-tight">Konsultasi Pasien</p>
            </a>
            <a href="{{ route('dokter.histories.index') }}" class="w-40 shrink-0 rounded-2xl border border-slate-200 bg-white p-4 text-slate-800 shadow-sm">
                <span class="material-symbols-outlined text-3xl text-slate-700">history</span>
                <p class="mt-2 text-sm font-bold leading-tight">Riwayat Pasien</p>
            </a>
            <a href="{{ route('dokter.medicines.index') }}" class="w-40 shrink-0 rounded-2xl border border-slate-200 bg-white p-4 text-slate-800 shadow-sm">
                <span class="material-symbols-outlined text-3xl text-slate-700">pill</span>
                <p class="mt-2 text-sm font-bold leading-tight">Data Obat & Stok</p>
            </a>
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.18em] text-slate-600">
            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
            Peringatan Stok & Expired
        </h2>
        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-700">warning</span>
                    <div>
                        <p class="text-sm font-bold text-amber-900">Warning Stok Rendah</p>
                        <p class="text-xs text-amber-800/90">{{ number_format((int) ($stats['low_stock_medicines'] ?? 0)) }} obat ditemukan</p>
                    </div>
                </div>
                <a href="{{ route('dokter.medicines.index', ['status' => 'low_stock']) }}" class="rounded-full bg-amber-700 px-3 py-1 text-[11px] font-bold text-white">Filter</a>
            </div>
        </article>
        <article class="rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-rose-700">event_busy</span>
                <div>
                    <p class="text-sm font-bold text-rose-900">Warning Kadaluarsa</p>
                    <p class="text-xs text-rose-800/90">{{ number_format($expiredTotal) }} obat akan/sudah kadaluarsa</p>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-1 gap-2">
                <a href="{{ route('dokter.medicines.index', ['status' => 'expiring']) }}" class="inline-flex items-center justify-center rounded-xl bg-white px-3 py-2 text-xs font-bold text-rose-700">Filter Segera Expired</a>
                <a href="{{ route('dokter.medicines.index', ['status' => 'expired']) }}" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-xs font-bold text-white">Filter Sudah Expired</a>
            </div>
        </article>
    </section>

    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-extrabold text-slate-900">Kunjungan Terbaru</h2>
            <a href="{{ route('dokter.histories.index') }}" class="text-xs font-bold text-blue-700 underline">Lihat Semua</a>
        </div>
        <div class="space-y-3">
            @forelse ($recentVisits as $visit)
                @php
                    $name = (string) ($visit->patient?->name ?? '-');
                    $initial = strtoupper(mb_substr($name !== '' ? $name : 'P', 0, 1));
                    $age = $visit->patient?->age;
                    $weight = $visit->patient?->weight_kg !== null ? rtrim(rtrim(number_format((float) $visit->patient->weight_kg, 2, '.', ''), '0'), '.') . ' Kg' : '-';
                    $statusLabel = ucfirst((string) ($visit->status ?? '-'));
                    $statusClass = in_array(strtolower((string) $visit->status), ['selesai', 'done', 'completed'], true)
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-blue-100 text-blue-700';
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 text-base font-black text-blue-700">{{ $initial }}</div>
                            <div>
                                <p class="text-base font-bold text-slate-900">{{ $name }}</p>
                                <p class="text-xs text-slate-500">{{ $age !== null ? $age.' Thn' : '-' }} • {{ $weight }}</p>
                            </div>
                        </div>
                        <span class="rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="mt-3 rounded-lg bg-slate-50 p-3">
                        <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-slate-500">Gejala</p>
                        <p class="text-sm italic text-slate-700">"{{ $visit->complaint }}"</p>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                    Belum ada kunjungan pasien.
                </div>
            @endforelse
        </div>
    </section>

    <section class="space-y-4 pb-20">
        <h2 class="text-xl font-extrabold text-slate-900">Stok Rendah</h2>
        <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
            @forelse ($lowStockMedicines as $medicine)
                <div class="flex items-center justify-between gap-2 px-4 py-3 {{ $loop->odd ? 'bg-white' : 'bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100/70">
                            <span class="material-symbols-outlined text-red-600">pill</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $medicine->name }}</p>
                            <p class="text-xs text-slate-500">Sisa: {{ $medicine->stock }} {{ $medicine->unit }}</p>
                        </div>
                    </div>
                    <a href="{{ route('dokter.medicines.index', ['status' => 'low_stock']) }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-slate-700">
                        <span class="material-symbols-outlined text-sm">add</span>
                    </a>
                </div>
            @empty
                <div class="bg-white px-4 py-6 text-center text-sm text-slate-500">Tidak ada data stok rendah.</div>
            @endforelse
        </div>
    </section>

    <nav class="fixed bottom-0 left-0 z-40 w-full border-t border-slate-200 bg-white/90 px-3 py-2 backdrop-blur-md lg:hidden">
        <div class="grid grid-cols-4 gap-1">
            <a href="{{ route('dokter.dashboard') }}" class="flex flex-col items-center rounded-xl bg-blue-50 px-2 py-1.5 text-blue-700">
                <span class="material-symbols-outlined text-[20px]">dashboard</span>
                <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wide">Dashboard</span>
            </a>
            <a href="{{ route('dokter.consultations.index') }}" class="flex flex-col items-center rounded-xl px-2 py-1.5 text-slate-500">
                <span class="material-symbols-outlined text-[20px]">clinical_notes</span>
                <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wide">Konsultasi</span>
            </a>
            <a href="{{ route('dokter.medicines.index') }}" class="flex flex-col items-center rounded-xl px-2 py-1.5 text-slate-500">
                <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wide">Obat</span>
            </a>
            <a href="{{ route('dokter.histories.index') }}" class="flex flex-col items-center rounded-xl px-2 py-1.5 text-slate-500">
                <span class="material-symbols-outlined text-[20px]">history</span>
                <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wide">Riwayat</span>
            </a>
        </div>
    </nav>
</div>

<div class="hidden space-y-8 lg:block">
    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('dokter.histories.index') }}" class="block rounded-[2rem] border border-transparent bg-white p-6 shadow-sm transition-colors hover:border-blue-200">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Pasien</p>
            <h3 class="mt-2 text-3xl font-black">{{ $stats['total_patients'] }}</h3>
        </a>
        <a href="{{ route('dokter.histories.index') }}" class="block rounded-[2rem] border border-transparent bg-white p-6 shadow-sm transition-colors hover:border-blue-200">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Kunjungan Hari Ini</p>
            <h3 class="mt-2 text-3xl font-black">{{ $stats['today_visits'] }}</h3>
        </a>
        <a href="{{ route('dokter.medicines.index', ['status' => 'ready']) }}" class="block rounded-[2rem] border border-transparent bg-white p-6 shadow-sm transition-colors hover:border-blue-200">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Obat Tersedia</p>
            <h3 class="mt-2 text-3xl font-black text-emerald-600">{{ $stats['ready_medicines'] }}</h3>
        </a>
        <a href="{{ route('dokter.medicines.index', ['status' => 'not_ready']) }}" class="block rounded-[2rem] border border-transparent bg-white p-6 shadow-sm transition-colors hover:border-blue-200">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Obat Habis</p>
            <h3 class="mt-2 text-3xl font-black text-red-600">{{ $stats['not_ready_medicines'] }}</h3>
        </a>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-6 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-amber-700">Warning Stok Rendah</p>
                    <h3 class="mt-1 text-2xl font-black text-amber-800">{{ number_format((int) ($stats['low_stock_medicines'] ?? 0)) }} Obat</h3>
                    <p class="mt-2 text-sm text-amber-800/90">Obat dengan stok 1-10 harus diprioritaskan.</p>
                </div>
                <span class="material-symbols-outlined text-[28px] text-amber-700">warning</span>
            </div>
            <div class="mt-4">
                <a href="{{ route('dokter.medicines.index', ['status' => 'low_stock']) }}" class="inline-flex rounded-lg bg-amber-700 px-3 py-2 text-xs font-bold text-white transition-colors hover:bg-amber-800">
                    Filter Stok Rendah
                </a>
            </div>
        </article>

        <article class="rounded-[2rem] border border-rose-200 bg-rose-50 p-6 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-rose-700">Warning Kadaluarsa</p>
                    <h3 class="mt-1 text-2xl font-black text-rose-800">{{ number_format($expiredTotal) }} Obat</h3>
                    <p class="mt-2 text-sm text-rose-800/90">
                        Expired: {{ number_format((int) ($stats['expired_medicines'] ?? 0)) }} •
                        Segera expired: {{ number_format((int) ($stats['expiring_soon_medicines'] ?? 0)) }}
                    </p>
                </div>
                <span class="material-symbols-outlined text-[28px] text-rose-700">error</span>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('dokter.medicines.index', ['status' => 'expiring']) }}" class="inline-flex rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white transition-colors hover:bg-rose-700">
                    Filter Segera Expired
                </a>
                <a href="{{ route('dokter.medicines.index', ['status' => 'expired']) }}" class="inline-flex rounded-lg border border-rose-300 bg-white px-3 py-2 text-xs font-bold text-rose-700 transition-colors hover:bg-rose-100">
                    Filter Sudah Expired
                </a>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <a href="{{ route('dokter.consultations.index') }}" class="block rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm transition-colors hover:border-blue-200">
            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-slate-400">Fitur</p>
            <h3 class="mb-1 text-lg font-extrabold text-blue-900">Konsultasi Pasien</h3>
            <p class="text-sm text-slate-500">Input kunjungan pasien baru/lama dan simpan tindakan dokter.</p>
        </a>
        <a href="{{ route('dokter.histories.index') }}" class="block rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm transition-colors hover:border-blue-200">
            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-slate-400">Fitur</p>
            <h3 class="mb-1 text-lg font-extrabold text-blue-900">Riwayat Pasien</h3>
            <p class="text-sm text-slate-500">Lihat rekam jejak berlapis, update riwayat, dan tambah resep obat.</p>
        </a>
        <a href="{{ route('dokter.medicines.index') }}" class="block rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm transition-colors hover:border-blue-200">
            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-slate-400">Fitur</p>
            <h3 class="mb-1 text-lg font-extrabold text-blue-900">Data Obat & Stok</h3>
            <p class="text-sm text-slate-500">Pantau status tersedia/habis, stok, kategori, dosis, dan merek dagang.</p>
        </a>
    </section>

    <section class="grid grid-cols-1 gap-8 xl:grid-cols-5">
        <article class="xl:col-span-3 rounded-[2.5rem] bg-white p-8 shadow-sm">
            <div class="mb-6">
                <h3 class="text-xl font-extrabold text-blue-900">Kunjungan Pasien Terbaru</h3>
                <p class="text-sm text-slate-500">Riwayat konsultasi terbaru yang sudah dicatat.</p>
            </div>

            <div class="space-y-3">
                @forelse ($recentVisits as $visit)
                    @php
                        $age = $visit->patient?->age;
                        $height = $visit->patient?->height_cm !== null ? rtrim(rtrim(number_format((float) $visit->patient->height_cm, 2, '.', ''), '0'), '.') . ' cm' : '-';
                        $weight = $visit->patient?->weight_kg !== null ? rtrim(rtrim(number_format((float) $visit->patient->weight_kg, 2, '.', ''), '0'), '.') . ' kg' : '-';
                    @endphp
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-slate-800">{{ $visit->patient?->name ?? '-' }}</p>
                            <span class="text-xs text-slate-500">{{ optional($visit->visit_date)->format('d M Y H:i') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Umur: {{ $age !== null ? $age.' tahun' : '-' }} | TB: {{ $height }} | BB: {{ $weight }}</p>
                        <p class="mt-1 text-sm text-slate-600">Keluhan: {{ $visit->complaint }}</p>
                        <p class="mt-1 text-xs text-slate-500">Status: {{ ucfirst($visit->status) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada kunjungan pasien.</p>
                @endforelse
            </div>
        </article>

        <article class="xl:col-span-2 rounded-[2.5rem] bg-white p-8 shadow-sm">
            <div class="mb-6">
                <h3 class="text-xl font-extrabold text-blue-900">Stok Rendah</h3>
                <p class="text-sm text-slate-500">Monitoring cepat obat yang perlu perhatian.</p>
            </div>

            <div class="space-y-3">
                @forelse ($lowStockMedicines as $medicine)
                    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                        <div>
                            <p class="font-bold text-slate-800">{{ $medicine->name }}</p>
                            <p class="text-xs text-slate-500">{{ $medicine->trade_name ?: '-' }} | {{ $medicine->dosage ?: '-' }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $medicine->stock > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $medicine->stock > 0 ? "READY ({$medicine->stock})" : 'NOT READY' }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data obat.</p>
                @endforelse
            </div>
        </article>
    </section>
</div>
@endsection
