@extends('ui.dokter.layout')

@section('dokter_title', 'Dashboard Dokter')
@section('dokter_heading', 'Dashboard Dokter')
@section('dokter_subheading', 'Ringkasan layanan dokter, kunjungan hari ini, dan kondisi stok obat.')

@section('dokter_content')
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    <a href="{{ route('dokter.histories.index') }}" class="block bg-white rounded-[2rem] p-6 shadow-sm border border-transparent hover:border-blue-200 transition-colors">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Pasien</p>
        <h3 class="text-3xl font-black mt-2">{{ $stats['total_patients'] }}</h3>
    </a>
    <a href="{{ route('dokter.histories.index') }}" class="block bg-white rounded-[2rem] p-6 shadow-sm border border-transparent hover:border-blue-200 transition-colors">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Kunjungan Hari Ini</p>
        <h3 class="text-3xl font-black mt-2">{{ $stats['today_visits'] }}</h3>
    </a>
    <a href="{{ route('dokter.medicines.index', ['status' => 'ready']) }}" class="block bg-white rounded-[2rem] p-6 shadow-sm border border-transparent hover:border-blue-200 transition-colors">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Tersedia</p>
        <h3 class="text-3xl font-black mt-2 text-emerald-600">{{ $stats['ready_medicines'] }}</h3>
    </a>
    <a href="{{ route('dokter.medicines.index', ['status' => 'not_ready']) }}" class="block bg-white rounded-[2rem] p-6 shadow-sm border border-transparent hover:border-blue-200 transition-colors">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Habis</p>
        <h3 class="text-3xl font-black mt-2 text-red-600">{{ $stats['not_ready_medicines'] }}</h3>
    </a>
</section>

<section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-6 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[10px] uppercase tracking-widest font-bold text-amber-700">Warning Stok Rendah</p>
                <h3 class="mt-1 text-2xl font-black text-amber-800">{{ number_format((int) ($stats['low_stock_medicines'] ?? 0)) }} Obat</h3>
                <p class="mt-2 text-sm text-amber-800/90">Obat dengan stok 1-10 harus diprioritaskan.</p>
            </div>
            <span class="material-symbols-outlined text-[28px] text-amber-700">warning</span>
        </div>
        <div class="mt-4">
            <a href="{{ route('dokter.medicines.index', ['status' => 'low_stock']) }}" class="inline-flex rounded-lg bg-amber-700 px-3 py-2 text-xs font-bold text-white hover:bg-amber-800 transition-colors">
                Filter Stok Rendah
            </a>
        </div>
    </article>

    <article class="rounded-[2rem] border border-rose-200 bg-rose-50 p-6 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[10px] uppercase tracking-widest font-bold text-rose-700">Warning Kadaluarsa</p>
                <h3 class="mt-1 text-2xl font-black text-rose-800">{{ number_format((int) (($stats['expired_medicines'] ?? 0) + ($stats['expiring_soon_medicines'] ?? 0))) }} Obat</h3>
                <p class="mt-2 text-sm text-rose-800/90">
                    Expired: {{ number_format((int) ($stats['expired_medicines'] ?? 0)) }} •
                    Segera expired: {{ number_format((int) ($stats['expiring_soon_medicines'] ?? 0)) }}
                </p>
            </div>
            <span class="material-symbols-outlined text-[28px] text-rose-700">error</span>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('dokter.medicines.index', ['status' => 'expiring']) }}" class="inline-flex rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white hover:bg-rose-700 transition-colors">
                Filter Segera Expired
            </a>
            <a href="{{ route('dokter.medicines.index', ['status' => 'expired']) }}" class="inline-flex rounded-lg border border-rose-300 bg-white px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition-colors">
                Filter Sudah Expired
            </a>
        </div>
    </article>
</section>

<section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <a href="{{ route('dokter.consultations.index') }}" class="block bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 hover:border-blue-200 transition-colors">
        <p class="text-xs uppercase tracking-widest font-bold text-slate-400 mb-2">Fitur</p>
        <h3 class="text-lg font-extrabold text-blue-900 mb-1">Konsultasi Pasien</h3>
        <p class="text-sm text-slate-500">Input kunjungan pasien baru/lama dan simpan tindakan dokter.</p>
    </a>
    <a href="{{ route('dokter.histories.index') }}" class="block bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 hover:border-blue-200 transition-colors">
        <p class="text-xs uppercase tracking-widest font-bold text-slate-400 mb-2">Fitur</p>
        <h3 class="text-lg font-extrabold text-blue-900 mb-1">Riwayat Pasien</h3>
        <p class="text-sm text-slate-500">Lihat rekam jejak berlapis, update riwayat, dan tambah resep obat.</p>
    </a>
    <a href="{{ route('dokter.medicines.index') }}" class="block bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 hover:border-blue-200 transition-colors">
        <p class="text-xs uppercase tracking-widest font-bold text-slate-400 mb-2">Fitur</p>
        <h3 class="text-lg font-extrabold text-blue-900 mb-1">Data Obat & Stok</h3>
        <p class="text-sm text-slate-500">Pantau status tersedia/habis, stok, kategori, dosis, dan merek dagang.</p>
    </a>
</section>

<section class="grid grid-cols-1 xl:grid-cols-5 gap-8">
    <article class="xl:col-span-3 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Kunjungan Pasien Terbaru</h3>
            <p class="text-sm text-slate-500">Riwayat konsultasi terbaru yang sudah dicatat.</p>
        </div>

        <div class="space-y-3">
            @forelse ($recentVisits as $visit)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    @php
                        $age = $visit->patient?->age;
                        $height = $visit->patient?->height_cm !== null ? rtrim(rtrim(number_format((float) $visit->patient->height_cm, 2, '.', ''), '0'), '.') . ' cm' : '-';
                        $weight = $visit->patient?->weight_kg !== null ? rtrim(rtrim(number_format((float) $visit->patient->weight_kg, 2, '.', ''), '0'), '.') . ' kg' : '-';
                    @endphp
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-bold text-slate-800">{{ $visit->patient?->name ?? '-' }}</p>
                        <span class="text-xs text-slate-500">{{ optional($visit->visit_date)->format('d M Y H:i') }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Umur: {{ $age !== null ? $age.' tahun' : '-' }} | TB: {{ $height }} | BB: {{ $weight }}</p>
                    <p class="text-sm text-slate-600 mt-1">Keluhan: {{ $visit->complaint }}</p>
                    <p class="text-xs text-slate-500 mt-1">Status: {{ ucfirst($visit->status) }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada kunjungan pasien.</p>
            @endforelse
        </div>
    </article>

    <article class="xl:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Stok Rendah</h3>
            <p class="text-sm text-slate-500">Monitoring cepat obat yang perlu perhatian.</p>
        </div>

        <div class="space-y-3">
            @forelse ($lowStockMedicines as $medicine)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800">{{ $medicine->name }}</p>
                        <p class="text-xs text-slate-500">{{ $medicine->trade_name ?: '-' }} | {{ $medicine->dosage ?: '-' }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $medicine->stock > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $medicine->stock > 0 ? "READY ({$medicine->stock})" : 'NOT READY' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada data obat.</p>
            @endforelse
        </div>
    </article>
</section>
@endsection
