@extends('ui.admin.layout')

@section('admin_title', 'Laporan Harga Obat')
@section('admin_heading', 'Laporan Harga Obat')
@section('admin_subheading', 'Fokus ke harga beli, harga jual, nama obat, dan outlet atau asal pembelian.')
@section('admin_actions')
@endsection

@section('admin_content')
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    <article class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Nilai Beli</p>
        <h3 class="text-3xl font-black mt-2 text-rose-600">Rp {{ number_format((float) ($reportStats['total_purchase_value'] ?? 0), 0, ',', '.') }}</h3>
        <p class="text-xs text-slate-500 mt-2">Akumulasi `jumlah x harga beli` dari semua obat masuk.</p>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Nilai Jual</p>
        <h3 class="text-3xl font-black mt-2 text-emerald-600">Rp {{ number_format((float) ($reportStats['total_sell_value'] ?? 0), 0, ',', '.') }}</h3>
        <p class="text-xs text-slate-500 mt-2">Akumulasi `jumlah x harga jual` berdasarkan data master obat.</p>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Data Obat Masuk</p>
        <h3 class="text-3xl font-black mt-2 text-blue-900">{{ number_format((int) ($reportStats['total_medicines_logged'] ?? 0)) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Jumlah transaksi penambahan obat yang tercatat.</p>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Outlet Tercatat</p>
        <h3 class="text-3xl font-black mt-2 text-sky-700">{{ number_format((int) ($reportStats['total_outlets'] ?? 0)) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Jumlah outlet atau asal beli yang sudah diinput admin.</p>
    </article>
</section>

<section class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
    <div class="mb-6">
        <h3 class="text-xl font-extrabold text-blue-900">Laporan Harga Per Obat</h3>
        <p class="text-sm text-slate-500">Tabel ini menampilkan detail lengkap obat untuk kebutuhan laporan dan export Excel, tanpa foto barang.</p>
    </div>

    <div class="overflow-x-auto rounded-3xl border border-slate-200 shadow-inner">
        <table class="min-w-[1500px] w-full text-left text-sm">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4 font-bold">Tanggal</th>
                    <th class="px-5 py-4 font-bold">Obat</th>
                    <th class="px-4 py-4 font-bold">Barcode</th>
                    <th class="px-4 py-4 font-bold">Detail</th>
                    <th class="px-4 py-4 font-bold">Outlet / Asal Beli</th>
                    <th class="px-4 py-4 font-bold">Jumlah</th>
                    <th class="px-4 py-4 font-bold">Kadaluarsa</th>
                    <th class="px-4 py-4 font-bold">Harga Beli</th>
                    <th class="px-4 py-4 font-bold">Harga Jual</th>
                    <th class="px-4 py-4 font-bold">Total Beli</th>
                    <th class="px-5 py-4 font-bold">Total Jual</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($priceReportLogs as $medicine)
                    @php
                        $latestPurchaseLog = $medicine->purchaseLogs->first();
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
                    <tr class="align-top hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-4 text-slate-700">
                            <div class="font-semibold">{{ optional($latestPurchaseLog?->purchased_at ?? $medicine->updated_at)->format('d M Y') ?: '-' }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ optional($latestPurchaseLog?->purchased_at ?? $medicine->updated_at)->format('H:i') ?: '-' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-800">{{ $medicine->name }}</div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $medicine->trade_name ?: '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-slate-700 font-mono text-xs">{{ $medicine->barcode ?: '-' }}</td>
                        <td class="px-4 py-4 text-slate-700">
                            <div>{{ $medicine->dosage ?: '-' }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $medicine->category ?: '-' }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $medicine->unit ?: '-' }}</div>
                        </td>
                        <td class="px-4 py-4 text-slate-700">
                            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                {{ $medicine->purchase_source ?: 'Belum diisi' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-slate-800">
                            <div class="font-bold">{{ number_format((int) $medicine->stock, 0, ',', '.') }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $medicine->unit ?: '-' }}</div>
                        </td>
                        <td class="px-4 py-4 text-slate-700">
                            <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold {{ $expBadgeClass }}">
                                {{ $expPrefix }} {{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-bold text-rose-600">Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-bold text-emerald-600">Rp {{ number_format((float) $medicine->sell_price, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-extrabold text-slate-800">Rp {{ number_format((float) $medicine->stock * (float) $medicine->buy_price, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-extrabold text-slate-800">Rp {{ number_format((float) $medicine->stock * (float) $medicine->sell_price, 0, ',', '.') }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="px-4 py-8 text-center text-slate-500">Belum ada laporan harga obat yang tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $priceReportLogs->links() }}
    </div>
</section>

@endsection
