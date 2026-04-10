@extends('layouts.ui-shell')

@section('title', 'APOTEK SUMBER SEHAT - Data Obat Master')
@section('body_class', 'bg-slate-100 text-slate-900')

@section('content')
@php
    $filters = $filters ?? [];
    $stats = $stats ?? [];
    $categories = $categories ?? collect();
    $perPageOptions = $perPageOptions ?? [10, 25, 50, 100];
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-cyan-50">
    <aside class="fixed inset-y-0 left-0 hidden w-72 border-r border-slate-200 bg-slate-950 text-slate-100 xl:flex xl:flex-col">
        <div class="px-6 py-7 border-b border-slate-800">
            <p class="text-[10px] uppercase tracking-[0.22em] text-cyan-300 font-bold">Master Panel</p>
            <h1 class="mt-2 text-2xl font-black leading-tight">APOTEK SUMBER SEHAT</h1>
            <p class="mt-2 text-xs text-slate-400">Kontrol data obat khusus master admin.</p>
        </div>

        <nav class="flex-1 space-y-1 px-4 py-5">
            <a href="{{ route('master-admin.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 transition hover:bg-slate-800 hover:text-white">
                <span class="material-symbols-outlined">space_dashboard</span>
                <span class="text-sm font-semibold">Dashboard Master</span>
            </a>
            <a href="{{ route('master-admin.medicines.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 bg-cyan-400/20 text-cyan-200">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="text-sm font-semibold">Data Obat Master</span>
            </a>
            <a href="{{ route('master-admin.activities.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 transition hover:bg-slate-800 hover:text-white">
                <span class="material-symbols-outlined">monitoring</span>
                <span class="text-sm font-semibold">Aktivitas Role</span>
            </a>
            <a href="{{ route('master-admin.role-permission.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 transition hover:bg-slate-800 hover:text-white">
                <span class="material-symbols-outlined">admin_panel_settings</span>
                <span class="text-sm font-semibold">Role & Permission</span>
            </a>
            <a href="{{ route('master-admin.password.change') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 transition hover:bg-slate-800 hover:text-white">
                <span class="material-symbols-outlined">password</span>
                <span class="text-sm font-semibold">Ubah Password</span>
            </a>
            <a href="{{ route('master-admin.password.reset') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-300 transition hover:bg-slate-800 hover:text-white">
                <span class="material-symbols-outlined">lock_reset</span>
                <span class="text-sm font-semibold">Reset Password</span>
            </a>
        </nav>

        <div class="border-t border-slate-800 p-4">
            <a href="{{ route('logout.get') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-rose-200 transition hover:bg-rose-500/20">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm font-semibold">Logout</span>
            </a>
        </div>
    </aside>

    <main class="xl:ml-72">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 px-5 py-4 backdrop-blur sm:px-7 lg:px-9">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.22em] text-cyan-700 font-bold">Master Admin</p>
                    <h2 class="text-2xl font-black text-slate-900">Data Obat Khusus Master</h2>
                    <p class="text-sm text-slate-500">Halaman ini berdiri sendiri dan tidak memakai tampilan dashboard admin.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('master-admin.dashboard') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100">
                        Kembali ke Dashboard
                    </a>
                    <a href="{{ route('admin.barcode.index') }}" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-bold text-white hover:bg-cyan-700">
                        Input Barcode
                    </a>
                </div>
            </div>
        </header>

        <div class="space-y-6 px-5 py-6 sm:px-7 lg:px-9">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-5">
                <article class="rounded-3xl bg-white px-5 py-5 shadow-sm border border-slate-200">
                    <p class="text-[11px] uppercase tracking-[0.2em] font-bold text-slate-400">Total Obat</p>
                    <h3 class="mt-2 text-3xl font-black text-slate-900">{{ number_format((int) ($stats['total_medicines'] ?? 0)) }}</h3>
                </article>
                <article class="rounded-3xl bg-white px-5 py-5 shadow-sm border border-slate-200">
                    <p class="text-[11px] uppercase tracking-[0.2em] font-bold text-slate-400">Tersedia</p>
                    <h3 class="mt-2 text-3xl font-black text-emerald-600">{{ number_format((int) ($stats['ready_medicines'] ?? 0)) }}</h3>
                </article>
                <article class="rounded-3xl bg-white px-5 py-5 shadow-sm border border-slate-200">
                    <p class="text-[11px] uppercase tracking-[0.2em] font-bold text-slate-400">Stok Rendah</p>
                    <h3 class="mt-2 text-3xl font-black text-amber-600">{{ number_format((int) ($stats['low_stock_medicines'] ?? 0)) }}</h3>
                </article>
                <article class="rounded-3xl bg-white px-5 py-5 shadow-sm border border-slate-200">
                    <p class="text-[11px] uppercase tracking-[0.2em] font-bold text-slate-400">Segera Expired</p>
                    <h3 class="mt-2 text-3xl font-black text-rose-600">{{ number_format((int) ($stats['expiring_soon_medicines'] ?? 0)) }}</h3>
                </article>
                <article class="rounded-3xl bg-white px-5 py-5 shadow-sm border border-slate-200">
                    <p class="text-[11px] uppercase tracking-[0.2em] font-bold text-slate-400">Stok Habis</p>
                    <h3 class="mt-2 text-3xl font-black text-slate-700">{{ number_format((int) ($stats['not_ready_medicines'] ?? 0)) }}</h3>
                </article>
            </section>

            <section class="grid grid-cols-1 gap-6 2xl:grid-cols-3">
                <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-xl font-black text-slate-900">Tambah Obat Master</h3>
                    <p class="mt-1 text-sm text-slate-500">Input manual khusus master admin. Data langsung masuk katalog obat.</p>

                    <form method="POST" action="{{ route('master-admin.medicines.store') }}" enctype="multipart/form-data" class="mt-5 grid grid-cols-1 gap-3">
                        @csrf
                        <input type="text" name="name" value="{{ old('name') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Nama obat" required />
                        <input type="text" name="trade_name" value="{{ old('trade_name') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Merek dagang" />
                        <input type="text" name="dosage" value="{{ old('dosage') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Dosis (contoh: 500 mg)" />
                        <input type="text" name="category" value="{{ old('category') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Kategori" />
                        <input type="text" name="barcode" value="{{ old('barcode') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Barcode (opsional)" />
                        <input type="text" name="unit" value="{{ old('unit') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Satuan (tablet, kapsul, pcs)" required />
                        <input type="text" inputmode="numeric" name="stock" value="{{ old('stock') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Stok awal" required />
                        <input type="text" inputmode="numeric" name="buy_price" value="{{ old('buy_price') }}" data-currency-input class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Harga beli" required />
                        <input type="text" inputmode="numeric" name="sell_price" value="{{ old('sell_price') }}" data-currency-input class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Harga jual" />
                        <input type="text" name="purchase_source" value="{{ old('purchase_source') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Outlet / tempat beli" required />
                        <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" required />
                        <input type="file" name="photo" accept="image/*" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                        <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <input type="hidden" name="is_active" value="0" />
                            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" checked />
                            Obat aktif
                        </label>
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white hover:bg-slate-800">
                            Simpan Obat
                        </button>
                    </form>
                </article>

                <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm 2xl:col-span-2">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-black text-slate-900">Daftar Obat Master</h3>
                            <p class="mt-1 text-sm text-slate-500">Tampilan ini khusus master, terpisah dari dashboard admin.</p>
                        </div>
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-700">
                            Total: {{ number_format((int) $medicines->total()) }} obat
                        </span>
                    </div>

                    <form method="GET" action="{{ route('master-admin.medicines.index') }}" class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-6">
                        <input
                            type="text"
                            name="q"
                            value="{{ $filters['q'] ?? '' }}"
                            placeholder="Cari barcode, nama, merek, kategori"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm lg:col-span-2"
                        />
                        <select name="category" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" @selected(($filters['category'] ?? '') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                            <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
                            <option value="low_stock" @selected(($filters['status'] ?? '') === 'low_stock')>Stok rendah</option>
                            <option value="expiring" @selected(($filters['status'] ?? '') === 'expiring')>Segera expired</option>
                            <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>Sudah expired</option>
                        </select>
                        @if (($hasEntrySourceColumn ?? false) === true)
                            <select name="source" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                                <option value="all" @selected(($filters['source'] ?? 'all') === 'all')>Semua sumber</option>
                                <option value="barcode" @selected(($filters['source'] ?? '') === 'barcode')>Dari barcode</option>
                                <option value="manual" @selected(($filters['source'] ?? '') === 'manual')>Input manual</option>
                                <option value="with_photo" @selected(($filters['source'] ?? '') === 'with_photo')>Ada foto</option>
                                <option value="without_photo" @selected(($filters['source'] ?? '') === 'without_photo')>Tanpa foto</option>
                            </select>
                        @else
                            <input type="hidden" name="source" value="all" />
                        @endif
                        <select name="per_page" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                            @foreach ($perPageOptions as $option)
                                <option value="{{ $option }}" @selected((int) ($filters['per_page'] ?? 25) === (int) $option)>
                                    {{ number_format((int) $option) }} / halaman
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-xl bg-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-300 lg:col-span-6">
                            Terapkan Filter
                        </button>
                    </form>

                    <div class="mt-5 space-y-4">
                        @forelse ($medicines as $medicine)
                            @php
                                $latestPurchaseLog = $medicine->purchaseLogs->first();
                            @endphp
                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        @if ($medicine->photo_path)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($medicine->photo_path) }}" alt="{{ $medicine->name }}" class="h-14 w-14 rounded-xl object-cover border border-slate-200" />
                                        @else
                                            <div class="flex h-14 w-14 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500">
                                                <span class="material-symbols-outlined">medication</span>
                                            </div>
                                        @endif
                                        <div>
                                            <h4 class="text-lg font-bold text-slate-900">{{ $medicine->name }}</h4>
                                            <p class="text-xs text-slate-500">Merek: {{ $medicine->trade_name ?: '-' }} | Dosis: {{ $medicine->dosage ?: '-' }}</p>
                                            <p class="text-xs text-slate-500">Barcode: {{ $medicine->barcode ?: '-' }} | Kategori: {{ $medicine->category ?: '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if (($hasEntrySourceColumn ?? false) === true)
                                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $medicine->entry_source === 'barcode' ? 'bg-amber-100 text-amber-700' : 'bg-cyan-100 text-cyan-700' }}">
                                                {{ $medicine->entrySourceLabel() }}
                                            </span>
                                        @endif
                                        <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $medicine->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-300 text-slate-700' }}">
                                            {{ $medicine->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                        </span>
                                        <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $medicine->stock > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ $medicine->stock > 0 ? 'READY' : 'HABIS' }}
                                        </span>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('master-admin.medicines.update', $medicine) }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
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
                                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Kadaluarsa</label>
                                        <input type="date" name="expiry_date" value="{{ optional($medicine->expiry_date)->format('Y-m-d') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" required />
                                    </div>
                                    <div class="xl:col-span-2">
                                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Outlet / Tempat Beli</label>
                                        <input type="text" name="purchase_source" value="{{ old('purchase_source', $latestPurchaseLog?->purchase_source ?? $medicine->purchase_source) }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm" placeholder="Outlet / tempat beli obat" required />
                                    </div>

                                    <input type="file" name="photo" accept="image/*" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm xl:col-span-2" />
                                    <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                        <input type="hidden" name="is_active" value="0" />
                                        <input type="checkbox" name="is_active" value="1" {{ $medicine->is_active ? 'checked' : '' }} class="rounded border-slate-300" />
                                        Obat aktif
                                    </label>
                                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white hover:bg-slate-800 xl:col-span-2">
                                        Update Obat
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('master-admin.medicines.destroy', $medicine) }}" class="mt-3" onsubmit="return confirm('Yakin ingin menghapus/nonaktifkan obat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 hover:bg-rose-100">
                                        Hapus / Nonaktifkan
                                    </button>
                                </form>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center text-sm text-slate-500">
                                Belum ada data obat di katalog master.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $medicines->links() }}
                    </div>
                </article>
            </section>
        </div>
    </main>
</div>
@endsection
