@extends('ui.admin.layout')

@section('admin_title', 'Data Obat')
@section('admin_heading', 'Data Obat & Stok')
@section('admin_subheading', 'Admin bisa tambah obat baru, ubah detail master, dan pantau stok dari satu halaman.')

@section('admin_actions')
    <a href="{{ route('admin.warehouse') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50 transition-colors">
        Buka Gudang
    </a>
@endsection

@section('admin_content')
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    <article class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Total Data Obat</p>
        <h3 class="text-3xl font-black mt-2 text-blue-900">{{ number_format((int) $stats['total_medicines']) }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Tersedia</p>
        <h3 class="text-3xl font-black mt-2 text-emerald-600">{{ number_format((int) $stats['ready_medicines']) }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Stok Rendah</p>
        <h3 class="text-3xl font-black mt-2 text-amber-600">{{ number_format((int) $stats['low_stock_medicines']) }}</h3>
    </article>
    <article class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100">
        <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Obat Habis</p>
        <h3 class="text-3xl font-black mt-2 text-red-600">{{ number_format((int) $stats['not_ready_medicines']) }}</h3>
    </article>
</section>

<section class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h3 class="text-xl font-extrabold text-blue-900">Daftar Obat</h3>
            <p class="text-sm text-slate-500">Barang yang disimpan dari scan barcode atau input barang otomatis masuk ke master dan bisa diperiksa dari daftar ini.</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
            Total: {{ number_format((int) $medicines->total()) }} obat
        </span>
    </div>

    <form method="GET" action="{{ route('admin.data-obat') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6">
        <input
            type="text"
            name="q"
            value="{{ $filters['q'] }}"
            placeholder="Cari barcode, nama, merek, dosis"
            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm md:col-span-2"
        />
        <select name="category" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <option value="">Semua kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected($filters['category'] === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <option value="all" @selected($filters['status'] === 'all')>Semua status</option>
            <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
            <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
            <option value="low_stock" @selected($filters['status'] === 'low_stock')>Stok rendah</option>
            <option value="expiring" @selected($filters['status'] === 'expiring')>Segera expired</option>
            <option value="expired" @selected($filters['status'] === 'expired')>Sudah expired</option>
        </select>
        @if (($hasEntrySourceColumn ?? false) === true)
            <select name="source" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <option value="all" @selected(($filters['source'] ?? 'all') === 'all')>Semua sumber</option>
                <option value="barcode" @selected(($filters['source'] ?? 'all') === 'barcode')>Dari barcode</option>
                <option value="manual" @selected(($filters['source'] ?? 'all') === 'manual')>Input biasa</option>
                <option value="with_photo" @selected(($filters['source'] ?? 'all') === 'with_photo')>Ada foto</option>
                <option value="without_photo" @selected(($filters['source'] ?? 'all') === 'without_photo')>Tanpa foto</option>
            </select>
        @else
            <input type="hidden" name="source" value="all" />
        @endif
        <select name="per_page" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            @foreach (($perPageOptions ?? [10, 25, 50, 100]) as $option)
                <option value="{{ $option }}" @selected((int) ($filters['per_page'] ?? 25) === (int) $option)>
                    {{ number_format((int) $option) }} baris / halaman
                </option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-slate-100 text-slate-700 text-sm font-bold px-4 py-3 hover:bg-slate-200 transition-colors md:col-span-6">
            Terapkan Filter
        </button>
    </form>

    <div class="space-y-4">
        @forelse ($medicines as $medicine)
            @php
                $latestPurchaseLog = $medicine->purchaseLogs->first();
            @endphp
            <article class="rounded-[2rem] border border-slate-100 bg-slate-50/70 p-5">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                    <div class="flex items-start gap-4">
                        @if ($medicine->photo_path)
                            <img src="{{ Storage::url($medicine->photo_path) }}" alt="{{ $medicine->name }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-200" />
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-white text-slate-500 flex items-center justify-center border border-slate-200">
                                <span class="material-symbols-outlined">medication</span>
                            </div>
                        @endif
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg">{{ $medicine->name }}</h4>
                            <p class="text-sm text-slate-500">Merek dagang: {{ $medicine->trade_name ?: '-' }}</p>
                            <p class="text-sm text-slate-500">Dosis: {{ $medicine->dosage ?: '-' }} | Kategori: {{ $medicine->category ?: '-' }}</p>
                            <p class="text-sm text-slate-500">Barcode: {{ $medicine->barcode ?: '-' }} | Harga beli: Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if (($hasEntrySourceColumn ?? false) === true)
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $medicine->entry_source === 'barcode' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $medicine->entrySourceLabel() }}
                            </span>
                        @endif
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $medicine->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $medicine->is_active ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $medicine->stock > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $medicine->stock > 0 ? 'READY' : 'HABIS' }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.medicines.update', $medicine) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="name" value="{{ $medicine->name }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm xl:col-span-2" placeholder="Nama obat" required />
                    <input type="text" name="trade_name" value="{{ $medicine->trade_name }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Merek dagang" />
                    <input type="text" name="dosage" value="{{ $medicine->dosage }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Dosis" />
                    <input type="text" name="category" value="{{ $medicine->category }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Kategori" />
                    <input type="text" inputmode="numeric" name="stock" value="{{ $medicine->stock }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Stok" required />

                    <input type="text" name="barcode" value="{{ $medicine->barcode }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm xl:col-span-2" placeholder="Barcode" />
                    <input type="text" name="unit" value="{{ $medicine->unit }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Satuan" required />
                    <input type="text" inputmode="numeric" name="buy_price" value="{{ number_format((float) $medicine->buy_price, 0, ',', '.') }}" data-currency-input class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Harga beli" required />
                    <input type="text" inputmode="numeric" name="sell_price" value="{{ number_format((float) $medicine->sell_price, 0, ',', '.') }}" data-currency-input class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Harga jual" />
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tanggal Kadaluarsa</label>
                        <input type="date" name="expiry_date" value="{{ optional($medicine->expiry_date)->format('Y-m-d') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" required />
                    </div>
                    <div class="xl:col-span-2">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Outlet / Tempat Beli</label>
                        <input type="text" name="purchase_source" value="{{ old('purchase_source', $latestPurchaseLog?->purchase_source) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Outlet / tempat beli obat" required />
                    </div>

                    <input type="file" name="photo" accept="image/*" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm xl:col-span-2" />
                    <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                        <input type="hidden" name="is_active" value="0" />
                        <input type="checkbox" name="is_active" value="1" {{ $medicine->is_active ? 'checked' : '' }} class="rounded border-slate-300" />
                        Obat aktif
                    </label>
                    <button type="submit" class="rounded-xl bg-primary text-white text-sm font-bold px-4 py-3 hover:bg-primary-container transition-colors xl:col-span-2">
                        Update Obat
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.medicines.destroy', $medicine) }}" class="mt-3" onsubmit="return confirm('Yakin ingin menghapus/nonaktifkan obat ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl bg-red-50 text-red-700 text-sm font-bold px-4 py-3 hover:bg-red-100 transition-colors">
                        Hapus / Nonaktifkan
                    </button>
                </form>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <p class="text-sm text-slate-500">Belum ada data obat pada master.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $medicines->links() }}
    </div>
</section>
@endsection
