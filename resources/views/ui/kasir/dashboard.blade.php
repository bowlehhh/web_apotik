@extends('ui.kasir.layout')

@section('kasir_title', 'Dashboard Kasir')
@section('kasir_heading', 'Dashboard Kasir')
@section('kasir_subheading', 'Pantau resep dokter, penjualan harian, dan stok obat secara terhubung.')

@section('kasir_content')
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-10 gap-6">
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Obat</p>
        <h3 class="text-3xl font-black mt-2">{{ $stats['total_medicines'] }}</h3>
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
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Resep Menunggu</p>
        <h3 class="text-3xl font-black mt-2 text-amber-600">{{ $stats['pending_prescriptions'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Penjualan Hari Ini</p>
        <h3 class="text-3xl font-black mt-2 text-blue-700">{{ $stats['today_sales'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Uang Masuk Hari Ini</p>
        <h3 class="text-2xl font-black mt-2 text-emerald-700">Rp {{ number_format((float) $stats['today_total_revenue'], 0, ',', '.') }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Tanpa Resep Hari Ini</p>
        <h3 class="text-3xl font-black mt-2 text-indigo-700">{{ $stats['today_non_prescription_sales'] }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Omzet Tanpa Resep</p>
        <h3 class="text-2xl font-black mt-2 text-indigo-700">Rp {{ number_format((float) $stats['today_non_prescription_revenue'], 0, ',', '.') }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Pemasukan Bulan Ini</p>
        <h3 class="text-2xl font-black mt-2 text-emerald-700">Rp {{ number_format((float) $stats['month_total_revenue'], 0, ',', '.') }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Pemasukan Tahun Ini</p>
        <h3 class="text-2xl font-black mt-2 text-sky-700">Rp {{ number_format((float) $stats['year_total_revenue'], 0, ',', '.') }}</h3>
    </article>
</section>

<section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-6 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[10px] uppercase tracking-widest font-bold text-amber-700">Warning Stok Rendah</p>
                <h3 class="mt-1 text-2xl font-black text-amber-800">{{ number_format((int) ($stats['low_stock_medicines'] ?? 0)) }} Obat</h3>
                <p class="mt-2 text-sm text-amber-800/90">Obat dengan stok 1-10 butuh restock segera.</p>
            </div>
            <span class="material-symbols-outlined text-[28px] text-amber-700">warning</span>
        </div>
        <div class="mt-4">
            <a href="{{ route('kasir.medicines.index', ['status' => 'low_stock']) }}" class="inline-flex rounded-lg bg-amber-700 px-3 py-2 text-xs font-bold text-white hover:bg-amber-800 transition-colors">
                Lihat Filter Stok Rendah
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
            <a href="{{ route('kasir.medicines.index', ['status' => 'expiring']) }}" class="inline-flex rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white hover:bg-rose-700 transition-colors">
                Lihat Segera Expired
            </a>
            <a href="{{ route('kasir.medicines.index', ['status' => 'expired']) }}" class="inline-flex rounded-lg border border-rose-300 bg-white px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 transition-colors">
                Lihat Sudah Expired
            </a>
        </div>
    </article>
</section>

<section class="grid grid-cols-1 xl:grid-cols-5 gap-8">
    <article class="xl:col-span-3 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-extrabold text-blue-900">Pembelian Tanpa Resep Terbaru</h3>
                <p class="text-sm text-slate-500">Pantau transaksi non resep yang diproses kasir secara real-time.</p>
            </div>
            <a href="{{ route('kasir.transaksi') }}" class="text-xs font-bold text-blue-700 hover:text-blue-900">Input transaksi</a>
        </div>

        <div class="space-y-3">
            @forelse ($recentNonPrescriptionSales as $sale)
                <article class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="font-bold text-slate-800">{{ $sale->invoice_number }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $sale->patient_name ?: 'Pembeli umum' }} |
                            {{ optional($sale->sold_at)->format('d M Y H:i') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500">{{ $sale->total_items }} item</p>
                        <p class="text-sm font-black text-blue-700">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</p>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <p class="text-sm text-slate-500">Belum ada transaksi tanpa resep hari ini.</p>
                </div>
            @endforelse
        </div>
    </article>

    <article class="xl:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Obat Laris Tanpa Resep (Hari Ini)</h3>
            <p class="text-sm text-slate-500">Urutan obat berdasarkan jumlah item terjual tanpa resep.</p>
        </div>

        <div class="space-y-3">
            @forelse ($topNonPrescriptionMedicinesToday as $medicine)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-bold text-slate-800">{{ $medicine->medicine_name_snapshot }}</p>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">
                            {{ (int) $medicine->total_quantity }} item
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Omzet: Rp {{ number_format((float) $medicine->total_amount, 0, ',', '.') }}</p>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <p class="text-sm text-slate-500">Belum ada obat terjual tanpa resep hari ini.</p>
                </div>
            @endforelse
        </div>
    </article>
</section>

<section class="grid grid-cols-1 xl:grid-cols-5 gap-8">
    <article class="xl:col-span-3 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-extrabold text-blue-900">Resep Dokter Siap Diproses</h3>
                <p class="text-sm text-slate-500">Terhubung dari dashboard dokter. Bisa langsung diproses dan dicetak.</p>
            </div>
            <a href="{{ route('kasir.transaksi') }}" class="text-xs font-bold text-blue-700 hover:text-blue-900">Buka halaman transaksi</a>
        </div>

        <div class="space-y-4">
            @forelse ($pendingPrescriptions as $prescription)
                <article class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-bold text-slate-800">{{ $prescription->patient?->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $prescription->patient?->medical_record_number ?? '-' }} |
                                {{ optional($prescription->prescribed_at)->format('d M Y H:i') }} |
                                Dokter: {{ $prescription->doctor?->name ?? '-' }}
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">MENUNGGU</span>
                    </div>

                    <div class="mt-3 text-xs text-slate-600 space-y-1">
                        @foreach ($prescription->items as $item)
                            <p>
                                {{ $item->medicine?->name ?? '-' }} ({{ $item->quantity }}) - {{ $item->dosage_instructions }}
                                <span class="text-slate-500">| beli: Rp {{ number_format((float) ($item->medicine?->buy_price ?? 0), 0, ',', '.') }}</span>
                            </p>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('kasir.transaksi', ['prescription_id' => $prescription->id]) }}#transaksi-form" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition-colors">
                            Proses Tebus Resep
                        </a>
                        <a target="_blank" href="{{ route('kasir.prescriptions.print', $prescription) }}" class="px-3 py-2 rounded-lg bg-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-300 transition-colors">
                            Cetak Resep
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <p class="text-sm text-slate-500">Tidak ada resep yang menunggu proses saat ini.</p>
                </div>
            @endforelse
        </div>
    </article>

    <article class="xl:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Stok Rendah</h3>
            <p class="text-sm text-slate-500">Obat dengan stok paling kecil agar cepat dilakukan pengadaan ulang.</p>
        </div>

        <div class="space-y-3">
            @forelse ($lowStockMedicines as $medicine)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800">{{ $medicine->name }}</p>
                        <p class="text-xs text-slate-500">{{ $medicine->trade_name ?: '-' }} | {{ $medicine->unit }}</p>
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

<section class="bg-white rounded-[2.5rem] p-8 shadow-sm">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-xl font-extrabold text-blue-900">Riwayat Penjualan Terakhir</h3>
            <p class="text-sm text-slate-500">Semua transaksi kasir tercatat dan bisa dilacak.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('kasir.transaksi') }}" class="inline-flex items-center rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50 transition-colors">
                Kelola Riwayat
            </a>
            <a target="_blank" href="{{ route('kasir.sales.history.print', ['history_from' => now()->toDateString(), 'history_to' => now()->toDateString()]) }}" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 transition-colors">
                Cetak Hari Ini
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] uppercase tracking-widest text-slate-400 font-black border-b border-slate-100">
                    <th class="py-3">Nomor Nota</th>
                    <th class="py-3">Jenis</th>
                    <th class="py-3">Pasien</th>
                    <th class="py-3">Kasir</th>
                    <th class="py-3">Total Item</th>
                    <th class="py-3">Total</th>
                    <th class="py-3">Waktu</th>
                    <th class="py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm">
                @forelse ($recentSales as $sale)
                    <tr>
                        <td class="py-4 font-bold">{{ $sale->invoice_number }}</td>
                        <td class="py-4">{{ $sale->sale_type === 'prescription' ? 'Resep Dokter' : 'Tanpa Resep' }}</td>
                        <td class="py-4">{{ $sale->patient_name ?: '-' }}</td>
                        <td class="py-4">{{ $sale->cashier?->name ?: '-' }}</td>
                        <td class="py-4">{{ $sale->total_items }}</td>
                        <td class="py-4">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</td>
                        <td class="py-4">{{ optional($sale->sold_at)->format('d M Y H:i') }}</td>
                        <td class="py-4">
                            <a target="_blank" href="{{ route('kasir.sales.print', $sale) }}" class="inline-flex items-center rounded-lg bg-slate-200 px-3 py-2 text-[11px] font-bold text-slate-700 hover:bg-slate-300 transition-colors">
                                Cetak
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">
                            Belum ada riwayat transaksi kasir.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
