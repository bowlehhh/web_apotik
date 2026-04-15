@extends('ui.master.layout')

@section('master_title', 'APOTEK SUMBER SEHAT - Master Dashboard')
@section('master_heading', 'Master Dashboard')
@section('master_subheading', 'Ringkasan operasional utama untuk pantauan cepat master admin.')

@section('master_actions')
    <a href="{{ route('master-admin.medicines.index') }}" class="rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-container">
        Kelola Data Obat
    </a>
    <a href="{{ route('admin.barcode.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">
        Scan Barcode
    </a>
@endsection

@section('master_content')
    <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-8">
        <article class="col-span-2 overflow-hidden rounded-[1.75rem] border border-blue-200 bg-gradient-to-br from-blue-700 via-blue-700 to-indigo-700 p-5 text-white shadow-sm sm:p-6 lg:col-span-2 xl:col-span-2">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-blue-100">Omzet Total</p>
            <h3 class="mt-2 text-xl font-black leading-tight sm:text-2xl">Rp {{ number_format((float) ($masterStats['sales_total'] ?? 0), 0, ',', '.') }}</h3>
            <p class="mt-2 text-[11px] text-blue-100">Akumulasi nominal penjualan obat.</p>
        </article>
        <article class="rounded-[1.4rem] border border-slate-200 bg-white/90 p-4 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Barang Masuk</p>
            <h3 class="mt-1 text-lg font-black text-blue-900 sm:text-xl">{{ number_format((int) ($masterStats['purchase_entries'] ?? 0)) }}</h3>
            <p class="mt-1 text-[11px] text-slate-500">Log masuk.</p>
        </article>
        <article class="rounded-[1.4rem] border border-slate-200 bg-white/90 p-4 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Belanja</p>
            <h3 class="mt-1 text-lg font-black text-rose-600 sm:text-xl">Rp {{ number_format((float) ($masterStats['purchase_spending'] ?? 0), 0, ',', '.') }}</h3>
            <p class="mt-1 text-[11px] text-slate-500">Total beli obat.</p>
        </article>
        <article class="rounded-[1.4rem] border border-slate-200 bg-white/90 p-4 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Transaksi</p>
            <h3 class="mt-1 text-lg font-black text-emerald-600 sm:text-xl">{{ number_format((int) ($masterStats['sales_transactions'] ?? 0)) }}</h3>
            <p class="mt-1 text-[11px] text-slate-500">Transaksi jual.</p>
        </article>
        <article class="rounded-[1.4rem] border border-slate-200 bg-white/90 p-4 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Bulan Ini</p>
            <h3 class="mt-1 text-lg font-black text-emerald-700 sm:text-xl">Rp {{ number_format((float) ($masterStats['sales_month_total'] ?? 0), 0, ',', '.') }}</h3>
            <p class="mt-1 text-[11px] text-slate-500">Pemasukan bulan.</p>
        </article>
        <article class="rounded-[1.4rem] border border-slate-200 bg-white/90 p-4 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Tahun Ini</p>
            <h3 class="mt-1 text-lg font-black text-sky-700 sm:text-xl">Rp {{ number_format((float) ($masterStats['sales_year_total'] ?? 0), 0, ',', '.') }}</h3>
            <p class="mt-1 text-[11px] text-slate-500">Pemasukan tahun.</p>
        </article>
        <article class="rounded-[1.4rem] border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-700">Stok Rendah</p>
            <h3 class="mt-1 text-lg font-black text-amber-700 sm:text-xl">{{ number_format((int) ($masterStats['low_stock_medicines'] ?? 0)) }}</h3>
            <p class="mt-1 text-[11px] text-amber-800/80">Stok <= 10.</p>
        </article>
        <article class="rounded-[1.4rem] border border-cyan-200 bg-cyan-50 p-4 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-cyan-700">User Aktif</p>
            <h3 class="mt-1 text-lg font-black text-cyan-700 sm:text-xl">{{ number_format((int) ($masterStats['active_users'] ?? 0)) }}</h3>
            <p class="mt-1 text-[11px] text-cyan-800/80">Akun aktif.</p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <article class="xl:col-span-2 rounded-[2rem] border border-slate-200 bg-white/90 p-4 shadow-sm sm:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-lg font-extrabold text-blue-900 sm:text-xl">Barang Masuk Terbaru</h3>
                    <p class="text-xs text-slate-500 sm:text-sm">Pantauan item obat masuk terbaru.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-700">
                    Hari ini: {{ number_format((int) ($masterStats['today_purchases'] ?? 0)) }}
                </span>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse ($purchaseLogs as $log)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-bold text-slate-800">{{ $log->medicine?->name ?? 'Obat tidak ditemukan' }}</p>
                            <span class="text-xs font-semibold text-emerald-700">+{{ $log->quantity }}</span>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500">{{ optional($log->purchased_at)->format('d M Y H:i') ?: '-' }}</p>
                        <p class="mt-2 text-[11px] text-slate-600">Sumber: {{ $log->purchase_source ?: '-' }}</p>
                        <p class="text-[11px] text-slate-600">Harga: Rp {{ number_format((float) $log->buy_price, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs font-bold text-rose-600">Total: Rp {{ number_format((float) $log->quantity * (float) $log->buy_price, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                        Belum ada barang masuk yang tercatat.
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto rounded-xl border border-slate-100 md:block">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-3 py-3">Barang</th>
                            <th class="px-3 py-3">Sumber</th>
                            <th class="px-3 py-3">Jumlah</th>
                            <th class="px-3 py-3">Harga</th>
                            <th class="px-3 py-3">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($purchaseLogs as $log)
                            <tr>
                                <td class="px-4 py-3 text-slate-700">{{ optional($log->purchased_at)->format('d M Y H:i') ?: '-' }}</td>
                                <td class="px-3 py-3">
                                    <p class="font-bold text-slate-800">{{ $log->medicine?->name ?? 'Obat tidak ditemukan' }}</p>
                                    <p class="text-xs text-slate-500">{{ $log->notes ?: 'Tanpa catatan' }}</p>
                                </td>
                                <td class="px-3 py-3 text-slate-600">{{ $log->purchase_source ?: '-' }}</td>
                                <td class="px-3 py-3 font-semibold text-emerald-700">+{{ $log->quantity }}</td>
                                <td class="px-3 py-3 text-slate-700">Rp {{ number_format((float) $log->buy_price, 0, ',', '.') }}</td>
                                <td class="px-3 py-3 font-bold text-rose-600">Rp {{ number_format((float) $log->quantity * (float) $log->buy_price, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada barang masuk yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white/90 p-4 shadow-sm sm:p-6">
            <h3 class="mb-4 text-lg font-extrabold text-blue-900 sm:text-xl">Ringkasan Harian</h3>
            <div class="space-y-3">
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Transaksi Hari Ini</p>
                    <h4 class="mt-1 text-xl font-black text-emerald-600 sm:text-2xl">{{ number_format((int) ($masterStats['today_sales'] ?? 0)) }}</h4>
                    <p class="mt-1 text-xs text-slate-500">Transaksi penjualan pada hari ini.</p>
                </div>
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">User Nonaktif</p>
                    <h4 class="mt-1 text-xl font-black text-rose-600 sm:text-2xl">{{ number_format((int) ($masterStats['inactive_users'] ?? 0)) }}</h4>
                    <p class="mt-1 text-xs text-slate-500">Akun yang dinonaktifkan sementara.</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-amber-700">Warning Stok Rendah</p>
                    <h4 class="mt-1 text-xl font-black text-amber-700 sm:text-2xl">{{ number_format((int) ($masterStats['low_stock_medicines'] ?? 0)) }}</h4>
                    <p class="mt-1 text-xs text-amber-800/90">Obat stok 1-10 butuh restock.</p>
                    <a href="{{ route('master-admin.medicines.index', ['status' => 'low_stock']) }}" class="mt-3 inline-flex rounded-lg bg-amber-700 px-3 py-2 text-[11px] font-bold text-white hover:bg-amber-800 transition-colors">
                        Buka Filter Stok Rendah
                    </a>
                </div>
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-red-700">Warning Kadaluarsa</p>
                    <h4 class="mt-1 text-xl font-black text-red-700 sm:text-2xl">
                        {{ number_format((int) (($masterStats['expired_medicines'] ?? 0) + ($masterStats['expiring_soon_medicines'] ?? 0))) }}
                    </h4>
                    <p class="mt-1 text-xs text-red-800/90">
                        Expired: {{ number_format((int) ($masterStats['expired_medicines'] ?? 0)) }} •
                        Segera expired: {{ number_format((int) ($masterStats['expiring_soon_medicines'] ?? 0)) }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('master-admin.medicines.index', ['status' => 'expiring']) }}" class="inline-flex rounded-lg bg-red-600 px-3 py-2 text-[11px] font-bold text-white hover:bg-red-700 transition-colors">
                            Segera Expired
                        </a>
                        <a href="{{ route('master-admin.medicines.index', ['status' => 'expired']) }}" class="inline-flex rounded-lg border border-red-300 bg-white px-3 py-2 text-[11px] font-bold text-red-700 hover:bg-red-100 transition-colors">
                            Sudah Expired
                        </a>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <article class="rounded-[2rem] border border-slate-200 bg-white/90 p-4 shadow-sm sm:p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-extrabold text-blue-900 sm:text-xl">Obat Terakhir Diupdate</h3>
                <a href="{{ route('master-admin.medicines.index') }}" class="text-xs font-bold text-primary sm:text-sm">Lihat semua</a>
            </div>
            <div class="space-y-3">
                @forelse ($topMedicines as $medicine)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3 sm:p-4">
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $medicine->name }}</p>
                            <p class="text-xs text-slate-500">{{ $medicine->trade_name ?: '-' }} | {{ $medicine->category ?: '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500">Stok {{ $medicine->stock }} {{ $medicine->unit }}</p>
                            <p class="text-xs font-semibold text-slate-700">Beli Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                        Belum ada data obat pada master.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white/90 p-4 shadow-sm sm:p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-extrabold text-blue-900 sm:text-xl">Penjualan Terbaru</h3>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700">
                    Total: {{ number_format((int) ($masterStats['sales_transactions'] ?? 0)) }}
                </span>
            </div>

            <div class="space-y-3 md:hidden">
                @forelse ($recentSales as $sale)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-bold text-slate-800">{{ $sale->invoice_number }}</p>
                            <p class="text-xs font-bold text-emerald-700">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</p>
                        </div>
                        <p class="mt-1 text-xs text-slate-600">Kasir: {{ $sale->cashier?->name ?? '-' }}</p>
                        <p class="text-xs text-slate-600">Item: {{ number_format((int) $sale->total_items) }}</p>
                        <p class="text-[11px] text-slate-500">{{ optional($sale->sold_at)->format('d M Y H:i') ?: '-' }}</p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                        Belum ada transaksi penjualan.
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <th class="py-3">Invoice</th>
                            <th class="py-3">Kasir</th>
                            <th class="py-3">Item</th>
                            <th class="py-3">Total</th>
                            <th class="py-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($recentSales as $sale)
                            <tr>
                                <td class="py-4 font-bold">{{ $sale->invoice_number }}</td>
                                <td class="py-4 text-slate-600">{{ $sale->cashier?->name ?? '-' }}</td>
                                <td class="py-4">{{ number_format((int) $sale->total_items) }}</td>
                                <td class="py-4 font-bold text-emerald-700">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</td>
                                <td class="py-4 text-slate-500">{{ optional($sale->sold_at)->format('d M Y H:i') ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">Belum ada transaksi penjualan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
