@extends('ui.admin.layout')

@section('admin_title', 'Dashboard Admin')
@section('admin_heading', 'Dashboard Admin')
@section('admin_subheading', 'Pantau stok, uang keluar untuk pembelian obat, dan asal pembelian dari dashboard admin.')

@section('admin_actions')
    <a href="{{ route('admin.barcode.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50 transition-colors">
        Input Barcode
    </a>
@endsection

@section('admin_content')
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-6">
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Obat</p>
        <h3 class="text-3xl font-black text-blue-900 mt-2">{{ number_format((int) $stats['total_medicines']) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Data seluruh master obat.</p>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Aktif</p>
        <h3 class="text-3xl font-black text-emerald-600 mt-2">{{ number_format((int) $stats['active_medicines']) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Siap dipakai untuk transaksi.</p>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Stok Rendah</p>
        <h3 class="text-3xl font-black text-amber-600 mt-2">{{ number_format((int) $stats['low_stock_medicines']) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Perlu prioritas pembelian.</p>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Segera Expired</p>
        <h3 class="text-3xl font-black text-red-600 mt-2">{{ number_format((int) $stats['expiring_soon_medicines']) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Kadaluarsa dalam 30 hari.</p>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Pembelian Hari Ini</p>
        <h3 class="text-3xl font-black text-indigo-700 mt-2">{{ number_format((int) $stats['today_purchase_entries']) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Riwayat masuk gudang hari ini.</p>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Uang Keluar Hari Ini</p>
        <h3 class="mt-2 font-black leading-tight text-rose-600">
            <span class="block text-2xl sm:text-3xl">Rp</span>
            <span class="block text-2xl sm:text-3xl break-all">{{ number_format((float) $stats['today_purchase_spending'], 0, ',', '.') }}</span>
        </h3>
        <p class="text-xs text-slate-500 mt-2">Total biaya pembelian obat hari ini.</p>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Sumber Pembelian</p>
        <h3 class="text-3xl font-black text-sky-700 mt-2">{{ number_format((int) $stats['purchase_sources_count']) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Cabang, supplier, atau asal pembelian yang tercatat.</p>
    </article>
</section>

<section class="grid grid-cols-1 xl:grid-cols-5 gap-8">
    <article class="xl:col-span-3 bg-white rounded-[2rem] p-5 sm:p-8 shadow-sm border border-slate-50">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <h3 class="text-xl font-extrabold text-blue-900">Update Master Obat Terbaru</h3>
                <p class="text-sm text-slate-500">Obat yang terakhir diperbarui admin.</p>
            </div>
            <a href="{{ route('admin.warehouse') }}" class="text-xs font-bold text-blue-700 hover:text-blue-900">Kelola di gudang</a>
        </div>

        <div class="space-y-3">
            @forelse ($recentMedicines as $medicine)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        @if ($medicine->photo_path)
                            <img
                                src="{{ Storage::url($medicine->photo_path) }}"
                                alt="{{ $medicine->name }}"
                                class="w-12 h-12 rounded-xl object-cover border border-slate-200"
                            />
                        @else
                            <div class="w-12 h-12 rounded-xl bg-slate-200 text-slate-500 flex items-center justify-center">
                                <span class="material-symbols-outlined">medication</span>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 truncate">{{ $medicine->name }}</p>
                            <p class="text-xs text-slate-500 truncate">
                                {{ $medicine->trade_name ?: '-' }} |
                                {{ $medicine->category ?: '-' }} |
                                Dosis: {{ $medicine->dosage ?: '-' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-xs text-slate-500">Stok: {{ $medicine->stock }} {{ $medicine->unit }}</p>
                        <p class="text-xs font-semibold text-slate-700">Beli: Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</p>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <p class="text-sm text-slate-500">Belum ada data obat pada master.</p>
                </div>
            @endforelse
        </div>
    </article>

    <article class="xl:col-span-2 bg-white rounded-[2rem] p-5 sm:p-8 shadow-sm border border-slate-50">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Ringkasan Belanja Obat</h3>
            <p class="text-sm text-slate-500">Pantau uang keluar bulan ini dan sumber pembelian yang paling sering dipakai.</p>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div class="rounded-2xl border border-rose-100 bg-rose-50/70 p-5">
                <p class="text-[10px] uppercase tracking-widest font-bold text-rose-500">Total Uang Keluar Bulan Ini</p>
                <h3 class="mt-2 font-black leading-tight text-rose-600">
                    <span class="block text-2xl sm:text-3xl">Rp</span>
                    <span class="block text-2xl sm:text-3xl break-all">{{ number_format((float) $stats['month_purchase_spending'], 0, ',', '.') }}</span>
                </h3>
                <p class="text-xs text-slate-500 mt-2">Akumulasi `jumlah x harga beli` dari semua pembelian obat bulan berjalan.</p>
            </div>

            @if (($expiringAlertMedicines ?? collect())->isNotEmpty())
                <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-red-700 text-[20px]">warning</span>
                        <div>
                            <h4 class="font-bold text-red-700">Peringatan Expired</h4>
                            <p class="text-xs text-red-700/90">
                                {{ number_format((int) $expiringAlertMedicines->count()) }} obat akan/sudah expired.
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 space-y-2">
                        @foreach ($expiringAlertMedicines->take(5) as $medicine)
                            @php
                                $daysLeft = now()->startOfDay()->diffInDays(optional($medicine->expiry_date)->startOfDay(), false);
                                $isExpired = $daysLeft < 0;
                            @endphp
                            <div class="rounded-xl border {{ $isExpired ? 'border-red-300 bg-red-100' : 'border-red-200 bg-white/80' }} px-3 py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $medicine->name }}</p>
                                    <span class="shrink-0 text-[10px] font-bold {{ $isExpired ? 'text-red-700' : 'text-red-600' }}">
                                        {{ $isExpired ? 'SUDAH EXP' : 'SISA '.number_format((int) $daysLeft).' HARI' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1">
                                    Exp {{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }} • Stok {{ number_format((int) $medicine->stock) }} {{ $medicine->unit }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-5">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div>
                        <h4 class="font-bold text-slate-800">Asal Pembelian Teratas</h4>
                        <p class="text-xs text-slate-500">Dihitung dari total uang keluar terbesar.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($topPurchaseSources as $source)
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $source->purchase_source }}</p>
                                <p class="text-xs text-slate-500">{{ number_format((int) $source->total_logs) }} transaksi pembelian</p>
                            </div>
                            <p class="text-sm font-bold text-slate-800">Rp {{ number_format((float) $source->total_spending, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada asal pembelian yang tercatat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </article>
</section>

<section class="bg-white rounded-[2rem] p-5 sm:p-8 shadow-sm border border-slate-50">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h3 class="text-xl font-extrabold text-blue-900">Detail Pengeluaran Pembelian Terbaru</h3>
            <p class="text-sm text-slate-500">Setiap input pembelian langsung terlihat nominal uang keluar, obat yang dibeli, dan asal pembeliannya.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="medicines_limit" value="{{ $tableControls['medicines_limit'] ?? 10 }}" />
                <input type="hidden" name="sources_limit" value="{{ $tableControls['sources_limit'] ?? 5 }}" />
                <label for="purchase_per_page" class="text-xs font-semibold text-slate-600">Tampilkan</label>
                <select id="purchase_per_page" name="purchase_per_page" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-full sm:w-auto">
                    @foreach (($perPageOptions ?? [10, 25, 50, 100]) as $option)
                        <option value="{{ $option }}" @selected((int) ($tableControls['purchase_per_page'] ?? 25) === (int) $option)>
                            {{ number_format((int) $option) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition-colors w-full sm:w-auto">
                    Update
                </button>
            </form>
            <a href="{{ route('admin.warehouse') }}" class="text-xs font-bold text-blue-700 hover:text-blue-900">Input pembelian baru</a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-100">
        <table class="min-w-[760px] w-full text-left text-sm">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-3 py-3">Obat</th>
                    <th class="px-3 py-3">Asal Pembelian</th>
                    <th class="px-3 py-3">Jumlah</th>
                    <th class="px-3 py-3">Harga Beli</th>
                    <th class="px-3 py-3">Uang Keluar</th>
                    <th class="px-3 py-3">Petugas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($recentPurchaseLogs as $log)
                    <tr>
                        <td class="px-4 py-3 text-slate-700">{{ optional($log->purchased_at)->format('d M Y H:i') ?: '-' }}</td>
                        <td class="px-3 py-3">
                            <p class="font-bold text-slate-800">{{ $log->medicine?->name ?? 'Obat tidak ditemukan' }}</p>
                            <p class="text-xs text-slate-500">{{ $log->notes ?: 'Tanpa catatan tambahan' }}</p>
                        </td>
                        <td class="px-3 py-3 text-slate-700">{{ $log->purchase_source ?: '-' }}</td>
                        <td class="px-3 py-3 font-semibold text-emerald-700">+{{ $log->quantity }}</td>
                        <td class="px-3 py-3 text-slate-700">Rp {{ number_format((float) $log->buy_price, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 font-bold text-rose-600">Rp {{ number_format((float) $log->quantity * (float) $log->buy_price, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-slate-700">{{ $log->createdBy?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat pembelian gudang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $recentPurchaseLogs->links() }}
    </div>
</section>
@endsection
