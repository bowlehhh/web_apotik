@extends('ui.admin.layout')

@section('admin_title', 'Gudang Obat')
@section('admin_heading', 'Gudang Obat')
@section('admin_subheading', 'Kelola stok masuk gudang sekaligus master obat dalam satu halaman.')

@section('admin_content')
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Unit Stok</p>
        <h3 class="text-3xl font-black text-blue-900 mt-2">{{ number_format((int) $stats['total_stock_units']) }}</h3>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Ready</p>
        <h3 class="text-3xl font-black text-emerald-600 mt-2">{{ number_format((int) $stats['ready_medicines']) }}</h3>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Habis</p>
        <h3 class="text-3xl font-black text-red-600 mt-2">{{ number_format((int) $stats['not_ready_medicines']) }}</h3>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Segera Expired</p>
        <h3 class="text-3xl font-black text-amber-600 mt-2">{{ number_format((int) $stats['expiring_soon_medicines']) }}</h3>
    </article>
    <article class="bg-white rounded-[1.75rem] p-6 shadow-sm border border-slate-50">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Riwayat Pembelian</p>
        <h3 class="text-3xl font-black text-indigo-700 mt-2">{{ number_format((int) $stats['purchase_entries']) }}</h3>
    </article>
</section>

<section class="grid grid-cols-1 xl:grid-cols-5 gap-8">
    <article class="xl:col-span-2 bg-white rounded-[2rem] p-6 shadow-sm border border-slate-50">
        <h3 class="text-lg font-extrabold text-blue-900 mb-1">Input Pembelian Gudang</h3>
        <p class="text-sm text-slate-500 mb-5">Stok masuk, harga beli, expiry, dan foto pembelian akan tercatat.</p>

        <form method="POST" action="{{ route('admin.warehouse.purchases.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Obat</label>
                <select name="medicine_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" required>
                    <option value="">- Pilih obat -</option>
                    @foreach ($inventoryMedicines as $medicine)
                        <option value="{{ $medicine->id }}" @selected((string) old('medicine_id') === (string) $medicine->id)>
                            {{ $medicine->name }} | stok: {{ $medicine->stock }} {{ $medicine->unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Jumlah Masuk</label>
                    <input type="text" inputmode="numeric" name="quantity" value="{{ old('quantity', 1) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" placeholder="Contoh 1.000" required />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Harga Beli</label>
                    <input type="text" inputmode="numeric" name="buy_price" value="{{ old('buy_price') }}" data-currency-input class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" placeholder="Contoh 10.000.000" required />
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Outlet / Tempat Beli</label>
                <input type="text" name="purchase_source" value="{{ old('purchase_source') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" placeholder="Contoh: Outlet Sudiang / Supplier Farma Makassar" />
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Kadaluarsa</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tanggal Pembelian</label>
                    <input type="datetime-local" name="purchased_at" value="{{ old('purchased_at') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Foto Pembelian Obat</label>
                <input type="file" name="photo" accept="image/*" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Catatan Gudang</label>
                <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm" placeholder="Opsional">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="w-full rounded-xl bg-primary text-white text-sm font-bold py-3 hover:bg-primary-container transition-colors">
                Simpan Pembelian Gudang
            </button>
        </form>
    </article>

    <article class="xl:col-span-3 bg-white rounded-[2rem] p-6 shadow-sm border border-slate-50">
        <div class="mb-4">
            <h3 class="text-lg font-extrabold text-blue-900">Isi Gudang Saat Ini</h3>
            <p class="text-sm text-slate-500">Daftar obat beserta stok, harga beli terakhir, dan tanggal kadaluarsa.</p>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-100 max-h-[380px]">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Obat</th>
                        <th class="px-3 py-3">Stok</th>
                        <th class="px-3 py-3">Harga Beli</th>
                        <th class="px-3 py-3">Kadaluarsa</th>
                        <th class="px-3 py-3">Foto Master</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inventoryMedicines as $medicine)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-800">{{ $medicine->name }}</p>
                                <p class="text-xs text-slate-500">{{ $medicine->trade_name ?: '-' }} | {{ $medicine->category ?: '-' }}</p>
                            </td>
                            <td class="px-3 py-3 font-semibold text-slate-700">{{ $medicine->stock }} {{ $medicine->unit }}</td>
                            <td class="px-3 py-3 text-slate-700">Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</td>
                            @php
                                $inventoryDaysLeft = $medicine->expiry_date
                                    ? now()->startOfDay()->diffInDays($medicine->expiry_date->copy()->startOfDay(), false)
                                    : null;
                                $inventoryExpiryClass = 'text-slate-700';
                                if ($inventoryDaysLeft !== null && $inventoryDaysLeft < 0) {
                                    $inventoryExpiryClass = 'text-red-600 font-bold';
                                } elseif ($inventoryDaysLeft !== null && $inventoryDaysLeft <= 30) {
                                    $inventoryExpiryClass = 'text-amber-600 font-bold';
                                }
                            @endphp
                            <td class="px-3 py-3 {{ $inventoryExpiryClass }}">{{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }}</td>
                            <td class="px-3 py-3">
                                @if ($medicine->photo_path)
                                    <img src="{{ Storage::url($medicine->photo_path) }}" alt="{{ $medicine->name }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200" />
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data obat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="grid grid-cols-1 xl:grid-cols-5 gap-8">
    <article class="xl:col-span-5 bg-white rounded-[2rem] p-6 shadow-sm border border-slate-50">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <h3 class="text-lg font-extrabold text-blue-900">Daftar Master Obat</h3>
                <p class="text-sm text-slate-500">Semua barang yang masuk dari scan barcode atau input obat otomatis tercatat ke master dan bisa dipantau dari sini.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                Total: {{ number_format((int) $medicines->total()) }} obat
            </span>
        </div>

        <form method="GET" action="{{ route('admin.warehouse') }}" class="grid grid-cols-1 md:grid-cols-6 gap-2 mb-4">
            <input
                type="text"
                name="q"
                value="{{ $filters['q'] }}"
                placeholder="Cari barcode, nama, merek, atau dosis"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm md:col-span-2"
            />
            <select name="category" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" @selected($filters['category'] === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                <option value="all" @selected($filters['status'] === 'all')>Semua status</option>
                <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                <option value="low_stock" @selected($filters['status'] === 'low_stock')>Stok rendah</option>
                <option value="expiring" @selected($filters['status'] === 'expiring')>Segera expired</option>
                <option value="expired" @selected($filters['status'] === 'expired')>Sudah expired</option>
            </select>
            @if (($hasEntrySourceColumn ?? false) === true)
                <select name="source" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                    <option value="all" @selected(($filters['source'] ?? 'all') === 'all')>Semua sumber</option>
                    <option value="barcode" @selected(($filters['source'] ?? 'all') === 'barcode')>Dari barcode</option>
                    <option value="manual" @selected(($filters['source'] ?? 'all') === 'manual')>Input biasa</option>
                    <option value="with_photo" @selected(($filters['source'] ?? 'all') === 'with_photo')>Ada foto</option>
                    <option value="without_photo" @selected(($filters['source'] ?? 'all') === 'without_photo')>Tanpa foto</option>
                </select>
            @else
                <input type="hidden" name="source" value="all" />
            @endif
            <select name="master_per_page" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                @foreach (($perPageOptions ?? [10, 25, 50, 100]) as $option)
                    <option value="{{ $option }}" @selected((int) ($filters['master_per_page'] ?? 25) === (int) $option)>
                        {{ number_format((int) $option) }} baris master
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="purchase_per_page" value="{{ $filters['purchase_per_page'] ?? 50 }}" />
            <button type="submit" class="md:col-span-6 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold py-2.5 hover:bg-slate-200 transition-colors">
                Terapkan Filter
            </button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Obat</th>
                        <th class="px-3 py-3">Barcode</th>
                        <th class="px-3 py-3">Kategori</th>
                        @if (($hasEntrySourceColumn ?? false) === true)
                            <th class="px-3 py-3">Sumber</th>
                        @endif
                        <th class="px-3 py-3">Stok</th>
                        <th class="px-3 py-3">Harga Beli</th>
                        <th class="px-3 py-3">Kadaluarsa</th>
                        <th class="px-3 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($medicines as $medicine)
                        <tr class="align-top">
                            <td class="px-4 py-3">
                                <div class="flex items-start gap-3">
                                    @if ($medicine->photo_path)
                                        <img
                                            src="{{ Storage::url($medicine->photo_path) }}"
                                            alt="{{ $medicine->name }}"
                                            class="w-12 h-12 rounded-lg object-cover border border-slate-200"
                                        />
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center border border-slate-200">
                                            <span class="material-symbols-outlined text-[20px]">medication</span>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-slate-800">{{ $medicine->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $medicine->trade_name ?: '-' }}</p>
                                        <p class="text-xs text-slate-500">Dosis: {{ $medicine->dosage ?: '-' }}</p>
                                        <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $medicine->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                            {{ $medicine->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-slate-700 font-mono text-xs">{{ $medicine->barcode ?: '-' }}</td>
                            <td class="px-3 py-3 text-slate-700">{{ $medicine->category ?: '-' }}</td>
                            @if (($hasEntrySourceColumn ?? false) === true)
                                <td class="px-3 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $medicine->entry_source === 'barcode' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $medicine->entrySourceLabel() }}
                                    </span>
                                </td>
                            @endif
                            <td class="px-3 py-3 text-slate-700 font-semibold">{{ $medicine->stock }} {{ $medicine->unit }}</td>
                            <td class="px-3 py-3 text-slate-700">Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</td>
                            @php
                                $masterDaysLeft = $medicine->expiry_date
                                    ? now()->startOfDay()->diffInDays($medicine->expiry_date->copy()->startOfDay(), false)
                                    : null;
                                $masterExpiryClass = 'text-slate-700';
                                if ($masterDaysLeft !== null && $masterDaysLeft < 0) {
                                    $masterExpiryClass = 'text-red-600 font-bold';
                                } elseif ($masterDaysLeft !== null && $masterDaysLeft <= 30) {
                                    $masterExpiryClass = 'text-amber-600 font-bold';
                                }
                            @endphp
                            <td class="px-3 py-3 {{ $masterExpiryClass }}">{{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }}</td>
                            <td class="px-3 py-3">
                                <details class="group">
                                    <summary class="list-none cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">
                                        Edit
                                        <span class="material-symbols-outlined text-[16px] group-open:rotate-180 transition-transform">expand_more</span>
                                    </summary>
                                    <div class="mt-2 p-3 rounded-xl border border-slate-200 bg-slate-50 w-[320px] max-w-[70vw]">
                                        <form method="POST" action="{{ route('admin.medicines.update', $medicine) }}" enctype="multipart/form-data" class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="barcode" value="{{ $medicine->barcode }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs font-mono" placeholder="Barcode" />
                                            <input type="text" name="name" value="{{ $medicine->name }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" required />
                                            <input type="text" name="trade_name" value="{{ $medicine->trade_name }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" placeholder="Merek dagang" />
                                            <input type="text" name="dosage" value="{{ $medicine->dosage }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" placeholder="Dosis" />
                                            <input type="text" name="category" value="{{ $medicine->category }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" placeholder="Kategori" />
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="text" inputmode="numeric" name="stock" value="{{ number_format((float) $medicine->stock, 0, ',', '.') }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" required />
                                                <input type="text" name="unit" value="{{ $medicine->unit }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" required />
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="text" inputmode="numeric" name="buy_price" value="{{ number_format((float) $medicine->buy_price, 0, ',', '.') }}" data-currency-input class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" required />
                                                <input type="text" inputmode="numeric" name="sell_price" value="{{ number_format((float) $medicine->sell_price, 0, ',', '.') }}" data-currency-input class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" />
                                            </div>
                                            <input type="date" name="expiry_date" value="{{ optional($medicine->expiry_date)->format('Y-m-d') }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" />
                                            <input type="file" name="photo" accept="image/*" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" />
                                            <label class="flex items-center gap-2 text-xs text-slate-600">
                                                <input type="hidden" name="is_active" value="0" />
                                                <input type="checkbox" name="is_active" value="1" {{ $medicine->is_active ? 'checked' : '' }} class="rounded border-slate-300" />
                                                Obat aktif
                                            </label>
                                            <button type="submit" class="w-full rounded-lg bg-blue-600 text-white py-2 text-xs font-bold hover:bg-blue-700 transition-colors">
                                                Simpan Perubahan
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.medicines.destroy', $medicine) }}" class="mt-2" onsubmit="return confirm('Yakin ingin menghapus/nonaktifkan obat ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full rounded-lg bg-red-50 text-red-700 py-2 text-xs font-bold hover:bg-red-100 transition-colors">
                                                Hapus / Nonaktifkan
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($hasEntrySourceColumn ?? false) ? 8 : 7 }}" class="px-4 py-8 text-center text-slate-500">Belum ada data obat pada master.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $medicines->links() }}
        </div>
    </article>
</section>

<section class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-50">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h3 class="text-lg font-extrabold text-blue-900">Riwayat Foto Obat Saat Pembelian</h3>
            <p class="text-sm text-slate-500">Audit pembelian gudang lengkap dengan foto, harga beli, dan petugas input.</p>
        </div>
        <form method="GET" action="{{ route('admin.warehouse') }}" class="flex items-center gap-2">
            <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}" />
            <input type="hidden" name="category" value="{{ $filters['category'] ?? '' }}" />
            <input type="hidden" name="status" value="{{ $filters['status'] ?? 'all' }}" />
            <input type="hidden" name="source" value="{{ $filters['source'] ?? 'all' }}" />
            <input type="hidden" name="master_per_page" value="{{ $filters['master_per_page'] ?? 25 }}" />
            <label for="purchase_per_page" class="text-xs font-semibold text-slate-600">Tampilkan</label>
            <select id="purchase_per_page" name="purchase_per_page" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                @foreach (($perPageOptions ?? [10, 25, 50, 100]) as $option)
                    <option value="{{ $option }}" @selected((int) ($filters['purchase_per_page'] ?? 50) === (int) $option)>
                        {{ number_format((int) $option) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition-colors">
                Update
            </button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-100">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">ID Pembelian</th>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-3 py-3">Obat</th>
                    <th class="px-3 py-3">Jumlah</th>
                    <th class="px-3 py-3">Harga Beli</th>
                    <th class="px-3 py-3">Total Keluar</th>
                    <th class="px-3 py-3">Asal</th>
                    <th class="px-3 py-3">Kadaluarsa</th>
                    <th class="px-3 py-3">Foto</th>
                    <th class="px-3 py-3">Petugas</th>
                    <th class="px-3 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($purchaseLogs as $log)
                    <tr class="align-top">
                        <td class="px-4 py-3 text-slate-700 font-semibold">#{{ $log->id }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ optional($log->purchased_at)->format('d M Y H:i') }}</td>
                        <td class="px-3 py-3">
                            <p class="font-bold text-slate-800">{{ $log->medicine?->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $log->notes ?: '-' }}</p>
                        </td>
                        <td class="px-3 py-3 font-semibold text-emerald-700">+{{ $log->quantity }}</td>
                        <td class="px-3 py-3 text-slate-700">Rp {{ number_format((float) $log->buy_price, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 font-semibold text-slate-800">Rp {{ number_format((float) $log->quantity * (float) $log->buy_price, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-slate-700">{{ $log->purchase_source ?: '-' }}</td>
                        @php
                            $purchaseDaysLeft = $log->expiry_date
                                ? now()->startOfDay()->diffInDays($log->expiry_date->copy()->startOfDay(), false)
                                : null;
                            $purchaseExpiryClass = 'text-slate-700';
                            if ($purchaseDaysLeft !== null && $purchaseDaysLeft < 0) {
                                $purchaseExpiryClass = 'text-red-600 font-bold';
                            } elseif ($purchaseDaysLeft !== null && $purchaseDaysLeft <= 30) {
                                $purchaseExpiryClass = 'text-amber-600 font-bold';
                            }
                        @endphp
                        <td class="px-3 py-3 {{ $purchaseExpiryClass }}">{{ optional($log->expiry_date)->format('d M Y') ?: '-' }}</td>
                        <td class="px-3 py-3">
                            @if ($log->photo_path)
                                <a href="{{ Storage::url($log->photo_path) }}" target="_blank" class="inline-block">
                                    <img src="{{ Storage::url($log->photo_path) }}" alt="Foto Pembelian" class="w-14 h-14 rounded-lg object-cover border border-slate-200" />
                                </a>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-slate-700">{{ $log->createdBy?->name ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <details class="group">
                                <summary class="list-none cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">
                                    Edit
                                    <span class="material-symbols-outlined text-[16px] group-open:rotate-180 transition-transform">expand_more</span>
                                </summary>
                                <div class="mt-2 p-3 rounded-xl border border-slate-200 bg-slate-50 w-[340px] max-w-[75vw] space-y-2">
                                    <form method="POST" action="{{ route('admin.warehouse.purchases.update', $log) }}" enctype="multipart/form-data" class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" inputmode="numeric" name="quantity" value="{{ number_format((float) $log->quantity, 0, ',', '.') }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" placeholder="Jumlah masuk" required />
                                            <input type="text" inputmode="numeric" name="buy_price" value="{{ number_format((float) $log->buy_price, 0, ',', '.') }}" data-currency-input class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" placeholder="Harga beli" required />
                                        </div>
                                        <input type="text" name="purchase_source" value="{{ $log->purchase_source }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" placeholder="Outlet / tempat beli" required />
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="date" name="expiry_date" value="{{ optional($log->expiry_date)->format('Y-m-d') }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" required />
                                            <input type="datetime-local" name="purchased_at" value="{{ optional($log->purchased_at)->format('Y-m-d\\TH:i') }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" />
                                        </div>
                                        <input type="file" name="photo" accept="image/*" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" />
                                        <textarea name="notes" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs" placeholder="Catatan gudang">{{ $log->notes }}</textarea>
                                        <button type="submit" class="w-full rounded-lg bg-blue-600 text-white py-2 text-xs font-bold hover:bg-blue-700 transition-colors">
                                            Simpan Edit Gudang
                                        </button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat pembelian gudang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $purchaseLogs->links() }}
    </div>
</section>
@endsection
