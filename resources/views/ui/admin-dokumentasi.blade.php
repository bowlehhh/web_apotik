@extends('ui.admin.layout')

@section('admin_title', 'Dashboard Dokumentasi')
@section('admin_heading', 'Dashboard Dokumentasi Gudang')
@section('admin_subheading', 'Semua foto pembelian barang masuk gudang ditampilkan rapi untuk audit dan arsip admin.')
@section('admin_actions')
    <a href="{{ route('admin.barcode.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50 transition-colors">
        Tambah Barcode Barang
    </a>
@endsection

@section('admin_content')
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Dokumen Foto</p>
        <h3 class="text-3xl font-black text-blue-900 mt-2">{{ number_format((int) ($stats['total_documents'] ?? 0)) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Foto pembelian yang sudah tersimpan dari gudang.</p>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Dokumen Hari Ini</p>
        <h3 class="text-3xl font-black text-emerald-600 mt-2">{{ number_format((int) ($stats['today_documents'] ?? 0)) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Foto yang ditambahkan di tanggal hari ini.</p>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Asal Beli Tercatat</p>
        <h3 class="text-3xl font-black text-sky-700 mt-2">{{ number_format((int) ($stats['document_sources'] ?? 0)) }}</h3>
        <p class="text-xs text-slate-500 mt-2">Jumlah outlet atau supplier dengan bukti foto.</p>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Nilai Beli Berdokumen</p>
        <h3 class="text-3xl font-black text-rose-600 mt-2">Rp {{ number_format((float) ($stats['document_purchase_value'] ?? 0), 0, ',', '.') }}</h3>
        <p class="text-xs text-slate-500 mt-2">Akumulasi `jumlah x harga beli` dari transaksi berfoto.</p>
    </article>
</section>

<section class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
    <form method="GET" action="{{ route('admin.dokumentasi') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
        <input
            type="text"
            name="q"
            value="{{ $filters['q'] ?? '' }}"
            placeholder="Cari nama obat, barcode, outlet, atau catatan"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm"
        />
        <select name="source" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
            <option value="">Semua outlet / asal beli</option>
            @foreach ($sourceOptions as $source)
                <option value="{{ $source }}" @selected(($filters['source'] ?? '') === $source)>{{ $source }}</option>
            @endforeach
        </select>
        <input
            type="date"
            name="from"
            value="{{ $filters['from'] ?? '' }}"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm"
        />
        <input
            type="date"
            name="to"
            value="{{ $filters['to'] ?? '' }}"
            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm"
        />
        <select name="per_page" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm">
            @foreach (($perPageOptions ?? [15, 25, 50, 100]) as $option)
                <option value="{{ $option }}" @selected((int) ($filters['per_page'] ?? 15) === (int) $option)>
                    Tampilkan {{ number_format((int) $option) }} baris
                </option>
            @endforeach
        </select>
        <button type="submit" class="w-full rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-800 transition-colors">
            Filter Dokumentasi
        </button>
    </form>
</section>

<section class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
    <div class="mb-4">
        <h3 class="text-xl font-extrabold text-blue-900">Tabel Dokumentasi Foto Pembelian</h3>
        <p class="text-sm text-slate-500">Data ini diambil otomatis dari setiap simpan pembelian gudang yang berisi foto bukti. Pembelian tanpa foto tidak ditampilkan.</p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200">
        <table class="min-w-[1400px] w-full text-left text-sm">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-bold">No</th>
                    <th class="px-4 py-3 font-bold">Waktu</th>
                    <th class="px-4 py-3 font-bold">Foto Bukti</th>
                    <th class="px-4 py-3 font-bold">Nama Obat</th>
                    <th class="px-4 py-3 font-bold">Barcode</th>
                    <th class="px-4 py-3 font-bold">Outlet / Asal Beli</th>
                    <th class="px-4 py-3 font-bold">Jumlah</th>
                    <th class="px-4 py-3 font-bold">Harga Beli</th>
                    <th class="px-4 py-3 font-bold">Total Beli</th>
                    <th class="px-4 py-3 font-bold">Kadaluarsa</th>
                    <th class="px-4 py-3 font-bold">Petugas Input</th>
                    <th class="px-4 py-3 font-bold">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($photoLogs as $log)
                    @php
                        $daysLeft = $log->expiry_date
                            ? now()->startOfDay()->diffInDays($log->expiry_date->copy()->startOfDay(), false)
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
                        <td class="px-4 py-3 text-slate-700 font-semibold">
                            {{ ($photoLogs->firstItem() ?? 1) + $loop->index }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            <div class="font-semibold">{{ optional($log->purchased_at)->format('d M Y') ?: '-' }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ optional($log->purchased_at)->format('H:i') ?: '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ Storage::url($log->photo_path) }}" target="_blank" class="inline-flex">
                                <img
                                    src="{{ Storage::url($log->photo_path) }}"
                                    alt="{{ $log->medicine?->name ?? 'Foto pembelian' }}"
                                    class="h-16 w-16 rounded-xl object-cover border border-slate-200"
                                />
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            <div class="font-bold text-slate-800">{{ $log->medicine?->name ?? 'Obat tidak ditemukan' }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $log->medicine?->trade_name ?: '-' }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $log->medicine?->barcode ?: '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                {{ $log->purchase_source ?: 'Belum diisi' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-800">
                            <div class="font-bold">{{ number_format((int) $log->quantity, 0, ',', '.') }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $log->medicine?->unit ?: '-' }}</div>
                        </td>
                        <td class="px-4 py-3 font-bold text-rose-600">Rp {{ number_format((float) $log->buy_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-extrabold text-slate-800">
                            Rp {{ number_format((float) $log->buy_price * (float) $log->quantity, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold {{ $expBadgeClass }}">
                                {{ $expPrefix }} {{ optional($log->expiry_date)->format('d M Y') ?: '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $log->createdBy?->name ?: '-' }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs max-w-[230px]">
                            {{ $log->notes ?: '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-8 text-center text-slate-500">
                            Belum ada dokumentasi foto pembelian barang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $photoLogs->links() }}
    </div>
</section>
@endsection
