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
    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-8">
        <article class="rounded-[2rem] bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Barang Masuk</p>
            <h3 class="mt-1 text-2xl font-black text-blue-900">{{ number_format((int) ($masterStats['purchase_entries'] ?? 0)) }}</h3>
            <p class="mt-2 text-xs text-slate-500">Total log obat masuk tersimpan di sistem.</p>
        </article>
        <article class="rounded-[2rem] bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Belanja Pembelian</p>
            <h3 class="mt-1 text-2xl font-black text-rose-600">Rp {{ number_format((float) ($masterStats['purchase_spending'] ?? 0), 0, ',', '.') }}</h3>
            <p class="mt-2 text-xs text-slate-500">Akumulasi biaya pembelian obat.</p>
        </article>
        <article class="rounded-[2rem] bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Transaksi Jual</p>
            <h3 class="mt-1 text-2xl font-black text-emerald-600">{{ number_format((int) ($masterStats['sales_transactions'] ?? 0)) }}</h3>
            <p class="mt-2 text-xs text-slate-500">Jumlah transaksi yang diproses kasir.</p>
        </article>
        <article class="rounded-[2rem] bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Omzet Total</p>
            <h3 class="mt-1 text-2xl font-black text-indigo-700">Rp {{ number_format((float) ($masterStats['sales_total'] ?? 0), 0, ',', '.') }}</h3>
            <p class="mt-2 text-xs text-slate-500">Akumulasi nominal penjualan obat.</p>
        </article>
        <article class="rounded-[2rem] bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Pemasukan Bulan Ini</p>
            <h3 class="mt-1 text-2xl font-black text-emerald-700">Rp {{ number_format((float) ($masterStats['sales_month_total'] ?? 0), 0, ',', '.') }}</h3>
            <p class="mt-2 text-xs text-slate-500">Total pemasukan pada bulan berjalan.</p>
        </article>
        <article class="rounded-[2rem] bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Pemasukan Tahun Ini</p>
            <h3 class="mt-1 text-2xl font-black text-sky-700">Rp {{ number_format((float) ($masterStats['sales_year_total'] ?? 0), 0, ',', '.') }}</h3>
            <p class="mt-2 text-xs text-slate-500">Total pemasukan pada tahun berjalan.</p>
        </article>
        <article class="rounded-[2rem] bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Stok Rendah</p>
            <h3 class="mt-1 text-2xl font-black text-amber-600">{{ number_format((int) ($masterStats['low_stock_medicines'] ?? 0)) }}</h3>
            <p class="mt-2 text-xs text-slate-500">Obat dengan stok 10 atau kurang.</p>
        </article>
        <article class="rounded-[2rem] bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">User Aktif</p>
            <h3 class="mt-1 text-2xl font-black text-sky-700">{{ number_format((int) ($masterStats['active_users'] ?? 0)) }}</h3>
            <p class="mt-2 text-xs text-slate-500">Akun aktif pada sistem.</p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-8 xl:grid-cols-3">
        <article class="xl:col-span-2 rounded-[2.5rem] bg-white p-8 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-extrabold text-blue-900">Barang Masuk Terbaru</h3>
                    <p class="text-sm text-slate-500">Pantauan item obat masuk terbaru.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    Hari ini: {{ number_format((int) ($masterStats['today_purchases'] ?? 0)) }}
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-100">
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

        <article class="rounded-[2.5rem] bg-white p-8 shadow-sm">
            <h3 class="mb-6 text-xl font-extrabold text-blue-900">Ringkasan Harian</h3>
            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Transaksi Hari Ini</p>
                    <h4 class="mt-1 text-2xl font-black text-emerald-600">{{ number_format((int) ($masterStats['today_sales'] ?? 0)) }}</h4>
                    <p class="mt-1 text-xs text-slate-500">Transaksi penjualan pada hari ini.</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">User Nonaktif</p>
                    <h4 class="mt-1 text-2xl font-black text-rose-600">{{ number_format((int) ($masterStats['inactive_users'] ?? 0)) }}</h4>
                    <p class="mt-1 text-xs text-slate-500">Akun yang dinonaktifkan sementara.</p>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-amber-700">Warning Stok Rendah</p>
                    <h4 class="mt-1 text-2xl font-black text-amber-700">{{ number_format((int) ($masterStats['low_stock_medicines'] ?? 0)) }}</h4>
                    <p class="mt-1 text-xs text-amber-800/90">Obat stok 1-10 butuh restock.</p>
                    <a href="{{ route('master-admin.medicines.index', ['status' => 'low_stock']) }}" class="mt-3 inline-flex rounded-lg bg-amber-700 px-3 py-2 text-[11px] font-bold text-white hover:bg-amber-800 transition-colors">
                        Buka Filter Stok Rendah
                    </a>
                </div>
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-red-700">Warning Kadaluarsa</p>
                    <h4 class="mt-1 text-2xl font-black text-red-700">
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

    <section class="grid grid-cols-1 gap-8 xl:grid-cols-2">
        <article class="rounded-[2.5rem] bg-white p-8 shadow-sm">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-extrabold text-blue-900">Obat Terakhir Diupdate</h3>
                <a href="{{ route('master-admin.medicines.index') }}" class="text-sm font-bold text-primary">Lihat semua</a>
            </div>
            <div class="space-y-3">
                @forelse ($topMedicines as $medicine)
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                        <div>
                            <p class="font-bold text-slate-800">{{ $medicine->name }}</p>
                            <p class="text-xs text-slate-500">{{ $medicine->trade_name ?: '-' }} | {{ $medicine->category ?: '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500">Stok {{ $medicine->stock }} {{ $medicine->unit }}</p>
                            <p class="text-xs font-semibold text-slate-700">Beli Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                        Belum ada data obat pada master.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="rounded-[2.5rem] bg-white p-8 shadow-sm">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-extrabold text-blue-900">Penjualan Terbaru</h3>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                    Total: {{ number_format((int) ($masterStats['sales_transactions'] ?? 0)) }}
                </span>
            </div>
            <div class="overflow-x-auto">
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
