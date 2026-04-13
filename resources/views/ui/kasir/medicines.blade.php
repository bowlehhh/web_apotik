@extends('ui.kasir.layout')

@section('kasir_title', 'Data Obat Kasir')
@section('kasir_heading', 'Data Obat & Stok')
@section('kasir_subheading', 'Kasir hanya melihat data obat dan stok yang dikelola admin.')

@section('kasir_content')
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Obat</p>
        <h3 class="text-3xl font-black mt-2">{{ $stats['total_medicines'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Aktif</p>
        <h3 class="text-3xl font-black mt-2 text-blue-700">{{ $stats['active_medicines'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Tersedia</p>
        <h3 class="text-3xl font-black mt-2 text-emerald-600">{{ $stats['ready_medicines'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Habis</p>
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
        <h3 class="text-xl font-extrabold text-blue-900">Daftar Obat Dari Admin</h3>
        <p class="text-sm text-slate-500">Halaman kasir hanya untuk melihat stok dan harga obat. Tambah/ubah/hapus obat dilakukan oleh admin.</p>
    </div>

    <form method="GET" action="{{ route('kasir.medicines.index') }}" class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-5">
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
        <a href="{{ route('kasir.medicines.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
            Reset
        </a>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] uppercase tracking-widest text-slate-400 font-black border-b border-slate-100">
                    <th class="py-3">Nama Obat</th>
                    <th class="py-3">Merek</th>
                    <th class="py-3">Dosis</th>
                    <th class="py-3">Kategori</th>
                    <th class="py-3">Stok</th>
                    <th class="py-3">Harga Beli</th>
                    <th class="py-3">Harga Jual</th>
                    <th class="py-3">Kadaluarsa</th>
                    <th class="py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm">
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
                    <tr>
                        <td class="py-4 font-bold text-slate-800">{{ $medicine->name }}</td>
                        <td class="py-4 text-slate-600">{{ $medicine->trade_name ?: '-' }}</td>
                        <td class="py-4 text-slate-600">{{ $medicine->dosage ?: '-' }}</td>
                        <td class="py-4 text-slate-600">{{ $medicine->category ?: '-' }}</td>
                        <td class="py-4 text-slate-700">{{ number_format((int) $medicine->stock) }} {{ $medicine->unit }}</td>
                        <td class="py-4 text-slate-700">Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</td>
                        <td class="py-4 text-blue-700 font-bold">Rp {{ number_format((float) $medicine->sell_price, 0, ',', '.') }}</td>
                        <td class="py-4 text-slate-700">
                            <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold {{ $expBadgeClass }}">
                                {{ $expPrefix }} {{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }}
                            </span>
                        </td>
                        <td class="py-4">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $medicine->stock > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $medicine->stock > 0 ? 'READY' : 'NOT READY' }}
                                </span>
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $medicine->is_active ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $medicine->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                                @if ($isExpired)
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
                                        SUDAH EXP
                                    </span>
                                @elseif ($isExpiringSoon)
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                        EXP {{ $daysLeft }} HARI
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-500">Belum ada data obat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
