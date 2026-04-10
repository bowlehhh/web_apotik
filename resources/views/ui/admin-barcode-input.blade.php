@extends('ui.admin.layout')

@php
    $unitOptions = [
        'Tablet (pcs)',
        'Sirup (botol)',
        'Suspensi (botol)',
        'Tetes (botol)',
        'Salep (tube)',
        'Krim (tube)',
        'Gel (tube)',
        'Injeksi (ampul / vial)',
        'Infus (botol / bag)',
        'Suppositoria (pcs)',
        'Inhaler (unit)',
        'Patch (lembar)',
    ];
@endphp

@section('admin_title', 'Input Barang Barcode')
@section('admin_heading', 'Input Barang Dengan Barcode')
@section('admin_subheading', 'Scan barcode dari alat scanner atau kamera, lalu sistem otomatis cari barang di database.')

@section('page_style')
    .barcode-hero {
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 34%),
            radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.12), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.96), rgba(241,245,249,0.98));
    }
    .barcode-frame {
        position: absolute;
        inset: 50%;
        width: min(84vw, 560px);
        height: min(34vw, 240px);
        transform: translate(-50%, -50%);
        border: 4px solid rgba(255,255,255,0.94);
        border-radius: 1.75rem;
        box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.45);
    }
    .barcode-frame::after {
        content: "";
        position: absolute;
        left: 12px;
        right: 12px;
        top: 50%;
        height: 2px;
        transform: translateY(-50%);
        background: linear-gradient(90deg, transparent, rgba(14, 165, 233, 0.95), transparent);
        animation: scan-line 1.7s ease-in-out infinite;
    }
    @keyframes scan-line {
        0% { transform: translateY(-72px); opacity: 0.3; }
        50% { transform: translateY(72px); opacity: 1; }
        100% { transform: translateY(-72px); opacity: 0.3; }
    }
    @media (max-width: 767px) {
        body > .flex > aside,
        body > .flex > main > header {
            display: none;
        }
        body > .flex > main {
            margin-left: 0;
            width: 100%;
            min-height: 100dvh;
        }
        body > .flex > main > div {
            padding: 0;
            background: #f7f9fb;
            min-height: 100dvh;
        }
    }
@endsection

@section('admin_actions')
    <a href="{{ route('admin.warehouse') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50 transition-colors">
        Kembali Ke Gudang
    </a>
@endsection

@section('admin_content')
<div class="md:hidden min-h-screen bg-background text-on-background pb-24">
    <header class="sticky top-0 z-40 flex items-center justify-between px-5 py-4 bg-background/95 backdrop-blur-xl">
        <div class="flex items-center gap-3">
            <button type="button" class="text-primary p-2 rounded-xl bg-white/70 shadow-sm" onclick="window.history.back()">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <div>
                <h1 class="font-headline font-extrabold text-base tracking-tight text-primary">APOTEK SUMBER SEHAT</h1>
                <p class="text-[10px] uppercase tracking-[0.25em] text-slate-500 font-bold">Admin & Gudang</p>
            </div>
        </div>
        <div class="w-9 h-9 rounded-full bg-primary-container flex items-center justify-center text-on-primary text-xs font-bold">
            SS
        </div>
    </header>

    <main class="px-5 pt-4 space-y-6">
        <section class="space-y-1">
            <h2 class="font-headline font-extrabold text-2xl tracking-tight text-on-surface">Input Barang Dengan Barcode</h2>
            <p class="text-sm font-medium text-on-surface-variant">Gunakan pemindai atau input manual kode produk</p>
        </section>

        <div class="space-y-6">
            <section class="bg-surface-container-lowest rounded-[1.75rem] p-6 shadow-sm border border-outline-variant/10">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="w-24 h-24 rounded-full bg-primary/5 flex items-center justify-center relative overflow-hidden">
                        <span class="material-symbols-outlined text-primary text-5xl">barcode_scanner</span>
                        <div class="absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-transparent via-primary/20 to-transparent animate-pulse"></div>
                    </div>
                    <form data-barcode-form method="GET" action="{{ route('admin.barcode.lookup') }}" class="w-full space-y-4">
                        <div class="space-y-2">
                            <label class="block px-1 text-left text-xs font-bold uppercase tracking-widest text-secondary">Barcode ID</label>
                            <div class="relative">
                                <input
                                    data-barcode-input
                                    name="barcode"
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    value="{{ old('barcode', $barcode) }}"
                                    placeholder="Masukkan atau scan barcode..."
                                    class="w-full rounded-2xl border-none bg-surface-container-highest py-4 pl-12 pr-4 text-on-surface font-medium placeholder:text-on-surface-variant/50 focus:ring-2 focus:ring-primary/20"
                                    required
                                />
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">barcode</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <button data-open-camera type="button" class="bg-primary text-on-primary rounded-full py-4 px-6 font-bold flex items-center justify-center gap-2 shadow-lg shadow-primary/20 active:scale-95 transition-transform">
                                <span class="material-symbols-outlined text-xl">qr_code_scanner</span>
                                <span>Scan</span>
                            </button>
                            <button type="submit" class="bg-surface-container-high text-on-surface rounded-full py-4 px-6 font-bold flex items-center justify-center gap-2 hover:bg-surface-variant active:scale-95 transition-transform">
                                <span class="material-symbols-outlined text-xl">search</span>
                                <span>Cari</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <button data-open-image type="button" class="w-full rounded-full border border-outline-variant/40 bg-white py-3.5 text-sm font-bold text-on-surface">
                                Kamera
                            </button>
                            <button data-open-gallery type="button" class="w-full rounded-full border border-outline-variant/40 bg-white py-3.5 text-sm font-bold text-on-surface">
                                Galeri
                            </button>
                        </div>
                        <input data-barcode-image-input type="file" accept="image/*" capture="environment" class="hidden" />
                        <input data-barcode-gallery-input type="file" accept="image/*" class="hidden" />
                        <p class="text-xs text-on-surface-variant text-left px-1">Pilih dari kamera atau galeri. Galeri biasanya paling stabil untuk HP.</p>
                        <p data-camera-hint class="text-xs text-amber-700 text-left px-1 hidden">Akses kamera langsung bisa gagal jika halaman dibuka dari `http` biasa. Gunakan galeri atau buka lewat HTTPS/IP laptop.</p>
                    </form>
                </div>
            </section>

            @if ($searched && $medicine)
                <section class="bg-white rounded-[1.75rem] p-5 border border-emerald-100 shadow-sm space-y-4">
                    <div class="flex items-start gap-4">
                        @if ($medicine->photo_path)
                            <img src="{{ Storage::url($medicine->photo_path) }}" alt="{{ $medicine->name }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-200" />
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                                <span class="material-symbols-outlined text-[30px]">inventory_2</span>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.25em] font-bold text-emerald-700">Barang Ditemukan</p>
                            <h3 class="font-headline text-xl font-extrabold text-on-surface mt-1">{{ $medicine->name }}</h3>
                            <p class="text-sm text-on-surface-variant mt-1">{{ $medicine->trade_name ?: 'Tanpa merek' }} | {{ $medicine->dosage ?: 'Tanpa dosis' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-surface-container-low rounded-3xl p-4 space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-secondary/60 block">Barcode</span>
                            <p class="text-sm font-bold break-all">{{ $medicine->barcode ?: '-' }}</p>
                        </div>
                        <div class="bg-surface-container-low rounded-3xl p-4 space-y-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-secondary/60 block">Stok</span>
                            <p class="text-sm font-bold">{{ $medicine->stock }} {{ $medicine->unit }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.warehouse.purchases.store') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="hidden" name="medicine_id" value="{{ $medicine->id }}" />
                        <input type="hidden" name="reset_to_barcode" value="1" />
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" inputmode="numeric" name="quantity" value="{{ old('quantity', number_format((float) $medicine->stock, 0, ',', '.')) }}" class="rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Jumlah masuk, contoh 1.000" required />
                            <input type="text" inputmode="numeric" name="buy_price" value="{{ old('buy_price', number_format((float) $medicine->buy_price, 0, ',', '.')) }}" data-currency-input class="rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Harga beli, contoh 10.000.000" required />
                        </div>
                        <input type="text" name="purchase_source" value="{{ old('purchase_source', 'Scan barcode admin') }}" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Outlet / tempat beli obat" required />
                        <div class="grid grid-cols-2 gap-3">
                            <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($medicine->expiry_date)->format('Y-m-d')) }}" class="rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" />
                            <input type="datetime-local" name="purchased_at" value="{{ old('purchased_at') }}" class="rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" />
                        </div>
                        <input type="file" name="photo" accept="image/*" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" />
                        <textarea name="notes" rows="3" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Catatan gudang">{{ old('notes', 'Input stok dari scan barcode admin.') }}</textarea>
                        <button type="submit" class="w-full rounded-full bg-primary py-4 text-sm font-bold text-on-primary shadow-lg shadow-primary/20">
                            Simpan Ke Gudang
                        </button>
                    </form>
                </section>
            @endif

            @if ($searched && ! $medicine)
                <section class="bg-white rounded-[1.75rem] p-5 border border-amber-100 shadow-sm space-y-4">
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.25em] font-bold text-amber-700">Barang Baru</p>
                        <h3 class="font-headline text-xl font-extrabold text-on-surface mt-1">Tambah Barang Dari Barcode</h3>
                    </div>
                    <form method="POST" action="{{ route('admin.barcode.store') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <label class="block text-xs font-bold uppercase tracking-widest text-secondary">Kode Barang</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $barcode) }}" readonly class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm font-semibold" />
                        <label class="block text-xs font-bold uppercase tracking-widest text-secondary">Nama Barang</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Nama barang" required />
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-secondary">Merek Dagang</label>
                                <input type="text" name="trade_name" value="{{ old('trade_name') }}" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Merek dagang" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-secondary">Dosis</label>
                                <input type="text" name="dosage" value="{{ old('dosage') }}" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Dosis" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-secondary">Kategori</label>
                                <input type="text" name="category" value="{{ old('category') }}" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Kategori" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-secondary">Satuan</label>
                                @php
                                    $mobileUnit = old('unit', 'Tablet (pcs)');
                                    $mobileHasPreset = in_array($mobileUnit, $unitOptions, true);
                                @endphp
                                <div data-unit-wrapper class="space-y-2">
                                    <select data-unit-select class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm">
                                        @foreach ($unitOptions as $option)
                                            <option value="{{ $option }}" @selected($mobileUnit === $option)>{{ $option }}</option>
                                        @endforeach
                                        <option value="__other__" @selected(! $mobileHasPreset)>Lainnya, ketik manual</option>
                                    </select>
                                    <input
                                        type="text"
                                        data-unit-custom
                                        value="{{ $mobileHasPreset ? '' : $mobileUnit }}"
                                        class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm {{ $mobileHasPreset ? 'hidden' : '' }}"
                                        placeholder="Tulis satuan lain sesuai kebutuhan"
                                    />
                                    <input type="hidden" name="unit" data-unit-hidden value="{{ $mobileUnit }}" required />
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-secondary">Stok Awal</label>
                                <input type="text" inputmode="numeric" name="stock" value="{{ old('stock', 0) }}" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Stok awal, contoh 1.000" required />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-secondary">Harga Beli</label>
                                <input type="text" inputmode="numeric" name="buy_price" value="{{ old('buy_price') }}" data-currency-input class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Harga beli, contoh 10.000.000" required />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-secondary">Harga Jual</label>
                                <input type="text" inputmode="numeric" name="sell_price" value="{{ old('sell_price') }}" data-currency-input class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Harga jual, contoh 21.000" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-secondary">Tanggal Kadaluarsa</label>
                                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" required />
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-widest text-secondary">Outlet / Tempat Beli</label>
                            <input type="text" name="purchase_source" value="{{ old('purchase_source', 'Scan barcode admin') }}" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Outlet / tempat beli obat" required />
                        </div>
                        <input type="file" name="photo" accept="image/*" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" />
                        <textarea name="notes" rows="3" class="w-full rounded-2xl border-none bg-surface-container-highest py-3 px-4 text-sm" placeholder="Catatan pembelian">{{ old('notes') }}</textarea>
                        <label class="flex items-center gap-2 text-sm text-on-surface">
                            <input type="hidden" name="is_active" value="0" />
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }} class="rounded border-slate-300" />
                            Barang aktif
                        </label>
                        <button type="submit" class="w-full rounded-full bg-primary py-4 text-sm font-bold text-on-primary shadow-lg shadow-primary/20">
                            Simpan Barang Baru
                        </button>
                    </form>
                </section>
            @endif

            <section class="space-y-4">
                <h3 class="font-headline font-bold text-lg px-1">Panduan Penggunaan</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-inverse-surface text-white p-5 rounded-3xl flex gap-4 items-start shadow-xl">
                        <div class="bg-tertiary-container/20 p-3 rounded-2xl">
                            <span class="material-symbols-outlined text-white">light_mode</span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-base">Siap Untuk Scanner</h4>
                            <p class="text-white/80 text-sm leading-relaxed">Pastikan pencahayaan cukup untuk hasil pemindaian yang akurat pada kamera perangkat Anda.</p>
                        </div>
                    </div>
                    <div class="bg-surface-container-lowest p-5 rounded-3xl flex gap-4 items-center border border-outline-variant/10">
                        <div class="bg-secondary-container p-3 rounded-2xl">
                            <span class="material-symbols-outlined text-on-secondary-fixed-variant">camera</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-on-surface">Izin Kamera</h4>
                            <p class="text-on-surface-variant text-sm">Aktifkan akses kamera pada pengaturan browser.</p>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant">chevron_right</span>
                    </div>
                    <div class="bg-primary/5 p-5 rounded-3xl border border-primary/10 flex gap-4 items-start">
                        <div class="bg-primary/10 p-3 rounded-2xl">
                            <span class="material-symbols-outlined text-primary">keyboard</span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-on-surface">Input Manual</h4>
                            <p class="text-on-surface-variant text-sm">Jika barcode rusak, gunakan fitur input manual untuk memasukkan kode seri barang.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-2 gap-4">
                <div class="bg-surface-container-low p-4 rounded-3xl space-y-2">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-secondary/60">Status Inventaris</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold text-on-surface">{{ $searched && $medicine ? number_format($medicine->stock) : '1.2k' }}</span>
                        <span class="text-xs font-medium text-primary">{{ $searched && $medicine ? $medicine->unit : 'Barang' }}</span>
                    </div>
                    <div class="w-full h-1 rounded-full overflow-hidden bg-surface-container-highest">
                        <div class="h-full w-2/3 bg-primary"></div>
                    </div>
                </div>
                <div class="bg-surface-container-low p-4 rounded-3xl space-y-2">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-secondary/60">Scan Hari Ini</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold text-on-surface">{{ $searched ? '1' : '42' }}</span>
                        <span class="text-xs font-medium text-tertiary">Unit</span>
                    </div>
                    <div class="w-full h-1 rounded-full overflow-hidden bg-surface-container-highest">
                        <div class="h-full w-1/2 bg-tertiary"></div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <nav class="fixed bottom-0 left-0 z-50 flex w-full items-center justify-around border-t border-[#f2f4f6]/10 bg-white/80 px-4 py-3 backdrop-blur-xl shadow-[0_-4px_20px_0_rgba(0,0,0,0.05)]">
        <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center justify-center p-2 rounded-xl text-[#5c5f62]">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="mt-1 text-[11px] font-semibold uppercase tracking-wider">Dashboard</span>
        </a>
        <a href="{{ route('admin.warehouse') }}" class="flex flex-col items-center justify-center p-2 rounded-xl text-[#5c5f62]">
            <span class="material-symbols-outlined">inventory_2</span>
            <span class="mt-1 text-[11px] font-semibold uppercase tracking-wider">Inventory</span>
        </a>
        <a href="{{ route('admin.barcode.index') }}" class="flex flex-col items-center justify-center rounded-xl bg-[#2563eb]/10 px-4 py-1.5 text-[#2563eb]">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">barcode_scanner</span>
            <span class="mt-1 text-[11px] font-semibold uppercase tracking-wider">Scan</span>
        </a>
    </nav>

    <button type="button" data-open-gallery class="fixed bottom-24 right-6 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-primary text-on-primary shadow-2xl active:scale-95 transition-transform">
        <span class="material-symbols-outlined">photo_library</span>
    </button>
</div>

<div class="hidden md:block">
<section class="barcode-hero rounded-[2rem] border border-white/70 shadow-sm p-6 md:p-8">
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 items-start">
        <article class="xl:col-span-3 bg-white/90 rounded-[1.75rem] border border-slate-100 p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.3em] font-bold text-blue-700">Scan Barang</p>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">Cari Barang Dari Barcode</h3>
                    <p class="text-sm text-slate-500 mt-2">Scanner USB atau Bluetooth bisa langsung dipakai di kolom kode barang. Kamera perangkat juga bisa dipakai dari tombol scan.</p>
                </div>
                <div class="hidden md:flex h-14 w-14 rounded-2xl bg-blue-600 text-white items-center justify-center shadow-lg shadow-blue-200">
                    <span class="material-symbols-outlined text-[30px]">barcode_scanner</span>
                </div>
            </div>

            @if ($searched && $medicine)
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                    <p class="text-sm font-extrabold">Barcode berhasil dibaca dan barang ditemukan.</p>
                    <p class="text-sm mt-1">Data barang langsung ditampilkan di bawah.</p>
                </div>
            @elseif ($searched && ! $medicine)
                <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">
                    <p class="text-sm font-extrabold">Barcode belum terdaftar.</p>
                    <p class="text-sm mt-1">Form tambah barang baru sudah dibuka dengan kode barang hasil scan.</p>
                </div>
            @endif

            <form id="barcodeLookupForm" method="GET" action="{{ route('admin.barcode.lookup') }}" class="space-y-4">
                <div>
                    <label for="barcode-input" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Kode Barang / Barcode</label>
                    <div class="flex flex-col md:flex-row gap-3">
                        <input
                            id="barcode-input"
                            name="barcode"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            value="{{ old('barcode', $barcode) }}"
                            placeholder="Scan atau ketik kode barcode di sini"
                            class="flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-medium tracking-[0.12em] uppercase focus:border-blue-500 focus:ring-blue-500"
                            autofocus
                            required
                        />
                        <button type="submit" class="rounded-2xl bg-blue-600 text-white px-5 py-3.5 text-sm font-bold hover:bg-blue-700 transition-colors">
                            Tambah Barcode Barang
                        </button>
                        <button id="openCameraScanner" type="button" onclick="window.startAdminBarcodeScanner && window.startAdminBarcodeScanner()" class="rounded-2xl bg-slate-900 text-white px-5 py-3.5 text-sm font-bold hover:bg-slate-800 transition-colors">
                            Scan Barcode
                        </button>
                        <button id="openImageScanner" type="button" class="rounded-2xl bg-white border border-slate-200 text-slate-700 px-5 py-3.5 text-sm font-bold hover:bg-slate-50 transition-colors">
                            Kamera
                        </button>
                        <button id="openGalleryScanner" type="button" class="rounded-2xl bg-white border border-slate-200 text-slate-700 px-5 py-3.5 text-sm font-bold hover:bg-slate-50 transition-colors">
                            Galeri
                        </button>
                        <input id="barcodeImageInput" type="file" accept="image/*" capture="environment" class="hidden" />
                        <input id="barcodeGalleryInput" type="file" accept="image/*" class="hidden" />
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Scanner hardware yang bekerja seperti keyboard bisa langsung mengisi kolom ini. Jika scanner mengirim `Enter`, pencarian akan langsung berjalan.</p>
                    <p id="barcodeCameraHint" class="text-xs text-amber-700 mt-2 hidden">Di HP, live camera bisa diblok jika halaman dibuka lewat `http` biasa. Gunakan tombol `Galeri` sebagai cara tes yang paling aman.</p>
                </div>
            </form>

            @if ($medicine)
                <article class="mt-6 rounded-[1.5rem] border border-blue-100 bg-blue-50/70 p-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            @if ($medicine->photo_path)
                                <img src="{{ Storage::url($medicine->photo_path) }}" alt="{{ $medicine->name }}" class="w-20 h-20 rounded-2xl object-cover border border-blue-100" />
                            @else
                                <div class="w-20 h-20 rounded-2xl bg-white border border-blue-100 text-blue-700 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[34px]">inventory_2</span>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] font-bold text-blue-700">Barang Ditemukan</p>
                                <h4 class="text-2xl font-black text-slate-900">{{ $medicine->name }}</h4>
                                <p class="text-sm text-slate-600 mt-1">{{ $medicine->trade_name ?: 'Tanpa merek dagang' }} | {{ $medicine->dosage ?: 'Tanpa dosis' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.warehouse') }}" class="inline-flex items-center justify-center rounded-2xl bg-white border border-blue-200 px-4 py-3 text-sm font-bold text-blue-700 hover:bg-blue-100 transition-colors">
                            Kelola Di Gudang
                        </a>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-5">
                        <div class="rounded-2xl bg-white p-4 border border-blue-100">
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Barcode</p>
                            <p class="text-sm font-bold text-slate-800 mt-2 break-all">{{ $medicine->barcode ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-4 border border-blue-100">
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Kategori</p>
                            <p class="text-sm font-bold text-slate-800 mt-2">{{ $medicine->category ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-4 border border-blue-100">
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Stok</p>
                            <p class="text-sm font-bold text-slate-800 mt-2">{{ $medicine->stock }} {{ $medicine->unit }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-4 border border-blue-100">
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Harga Beli</p>
                            <p class="text-sm font-bold text-slate-800 mt-2">Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-4 border border-blue-100">
                            <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Kadaluarsa</p>
                            <p class="text-sm font-bold text-slate-800 mt-2">{{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-[1.5rem] border border-blue-100 bg-white p-5">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.24em] font-bold text-blue-700">Catat Gudang</p>
                                <h5 class="text-lg font-black text-slate-900 mt-1">Tambah Stok Barang Ini Ke Gudang</h5>
                            </div>
                            <span class="rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs font-bold">
                                Stok saat ini: {{ $medicine->stock }} {{ $medicine->unit }}
                            </span>
                        </div>

                        <form method="POST" action="{{ route('admin.warehouse.purchases.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @csrf
                            <input type="hidden" name="medicine_id" value="{{ $medicine->id }}" />
                            <input type="hidden" name="reset_to_barcode" value="1" />
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Jumlah Masuk</label>
                                <input type="text" inputmode="numeric" name="quantity" value="{{ old('quantity', number_format((float) $medicine->stock, 0, ',', '.')) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh 1.000" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Harga Beli</label>
                                <input type="text" inputmode="numeric" name="buy_price" value="{{ old('buy_price', number_format((float) $medicine->buy_price, 0, ',', '.')) }}" data-currency-input class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh 10.000.000" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Kadaluarsa</label>
                                <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($medicine->expiry_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Tanggal Pembelian</label>
                                <input type="datetime-local" name="purchased_at" value="{{ old('purchased_at') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Outlet / Tempat Beli</label>
                                <input type="text" name="purchase_source" value="{{ old('purchase_source', $medicine->purchase_source ?: 'Scan barcode admin') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: Outlet Panakkukang / Supplier Farma" required />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Foto Pembelian</label>
                                <input type="file" name="photo" accept="image/*" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Catatan Gudang</label>
                                <textarea name="notes" rows="3" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: pembelian dari supplier A">{{ old('notes', 'Input stok dari scan barcode admin.') }}</textarea>
                            </div>
                            <div class="md:col-span-2 flex justify-end">
                                <button type="submit" class="rounded-2xl bg-blue-600 text-white px-6 py-3.5 text-sm font-bold hover:bg-blue-700 transition-colors">
                                    Simpan Ke Gudang
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            @endif
        </article>

        <article class="xl:col-span-2 bg-slate-950 text-white rounded-[1.75rem] p-6 shadow-sm overflow-hidden relative">
            <div class="absolute -top-16 -right-8 h-36 w-36 rounded-full bg-cyan-400/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-28 w-28 rounded-full bg-blue-500/10 blur-3xl"></div>
            <p class="text-[11px] uppercase tracking-[0.32em] font-bold text-cyan-300 relative">2 Metode Scan</p>
            <h3 class="text-2xl font-black mt-2 relative">Siap Untuk Scanner dan Kamera</h3>
            <div class="space-y-4 mt-6 relative">
                <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
                    <p class="font-bold text-white">Hardware Scanner</p>
                    <p class="text-sm text-slate-300 mt-1">Colok scanner USB atau hubungkan scanner Bluetooth, lalu arahkan kursor ke field `kode barang` dan scan seperti mengetik biasa.</p>
                </div>
                <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
                    <p class="font-bold text-white">Kamera Perangkat</p>
                    <p class="text-sm text-slate-300 mt-1">Klik tombol `Scan Barcode`, izinkan akses kamera, lalu arahkan barcode ke bingkai scan sampai terbaca otomatis.</p>
                </div>
                <div class="rounded-2xl bg-emerald-400/10 border border-emerald-400/20 p-4">
                    <p class="font-bold text-emerald-300">Alur Otomatis</p>
                    <p class="text-sm text-emerald-50/90 mt-1">Jika barang ditemukan, data langsung tampil. Jika belum ada, admin bisa lanjut isi form barang baru tanpa mengetik ulang barcode.</p>
                </div>
            </div>
        </article>
    </div>
</section>
</div>

@if ($searched && ! $medicine)
    <section class="hidden md:block bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 md:p-8">
        <div class="mb-6">
            <p class="text-[11px] uppercase tracking-[0.28em] font-bold text-amber-700">Barang Baru</p>
            <h3 class="text-2xl font-black text-slate-900 mt-1">Tambah Barang Dari Barcode</h3>
            <p class="text-sm text-slate-500 mt-2">Kode barcode hasil scan sudah dikunci supaya admin tinggal melengkapi data barang.</p>
        </div>

        <form method="POST" action="{{ route('admin.barcode.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Kode Barang</label>
                <input type="text" name="barcode" value="{{ old('barcode', $barcode) }}" readonly class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Nama Barang</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" required />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Merek Dagang</label>
                <input type="text" name="trade_name" value="{{ old('trade_name') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Dosis</label>
                <input type="text" name="dosage" value="{{ old('dosage') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: 500 mg" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Kategori</label>
                <input type="text" name="category" value="{{ old('category') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: Antibiotik" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Satuan</label>
                @php
                    $desktopUnit = old('unit', 'Tablet (pcs)');
                    $desktopHasPreset = in_array($desktopUnit, $unitOptions, true);
                @endphp
                <div data-unit-wrapper class="space-y-2">
                    <select data-unit-select class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        @foreach ($unitOptions as $option)
                            <option value="{{ $option }}" @selected($desktopUnit === $option)>{{ $option }}</option>
                        @endforeach
                        <option value="__other__" @selected(! $desktopHasPreset)>Lainnya, ketik manual</option>
                    </select>
                    <input
                        type="text"
                        data-unit-custom
                        value="{{ $desktopHasPreset ? '' : $desktopUnit }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm {{ $desktopHasPreset ? 'hidden' : '' }}"
                        placeholder="Tulis satuan lain sesuai kebutuhan"
                    />
                    <input type="hidden" name="unit" data-unit-hidden value="{{ $desktopUnit }}" required />
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Stok Awal</label>
                <input type="text" inputmode="numeric" name="stock" value="{{ old('stock', 0) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh 1.000" required />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Harga Beli</label>
                <input type="text" inputmode="numeric" name="buy_price" value="{{ old('buy_price') }}" data-currency-input class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh 10.000.000" required />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Harga Jual</label>
                <input type="text" inputmode="numeric" name="sell_price" value="{{ old('sell_price') }}" data-currency-input class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh 21.000" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Tanggal Kadaluarsa</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" required />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Outlet / Tempat Beli</label>
                <input type="text" name="purchase_source" value="{{ old('purchase_source', 'Scan barcode admin') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: Outlet Panakkukang" required />
            </div>
            <div class="xl:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Foto Barang</label>
                <input type="file" name="photo" accept="image/*" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
            </div>
            <div class="xl:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Catatan</label>
                <textarea name="notes" rows="3" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Catatan pembelian atau sumber barang">{{ old('notes') }}</textarea>
            </div>
            <div class="xl:col-span-2 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }} class="rounded border-slate-300" />
                    Barang aktif
                </label>
                <button type="submit" class="rounded-2xl bg-blue-600 text-white px-6 py-3.5 text-sm font-bold hover:bg-blue-700 transition-colors">
                    Simpan Barang Baru
                </button>
            </div>
        </form>
    </section>
@endif

<div id="cameraScannerModal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/70 backdrop-blur-sm px-4">
    <div class="w-full max-w-5xl rounded-[2rem] bg-slate-950 text-white border border-white/10 overflow-hidden shadow-2xl">
        <div class="flex items-center justify-between px-6 py-5 border-b border-white/10">
            <div>
                <p class="text-[11px] uppercase tracking-[0.28em] font-bold text-cyan-300">Camera Scanner</p>
                <h3 class="text-xl font-black mt-1">Arahkan Kamera Ke Barcode</h3>
            </div>
            <button id="closeCameraScanner" type="button" class="h-11 w-11 rounded-full bg-white/10 hover:bg-white/15 transition-colors flex items-center justify-center">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-6">
            <div class="relative rounded-[1.5rem] overflow-hidden border border-white/10 bg-black min-h-[460px]">
                <div id="cameraScannerRegion" class="w-full h-[540px]"></div>
                <div class="barcode-frame pointer-events-none"></div>
            </div>
            <p id="cameraScannerStatus" class="text-sm text-slate-300 mt-4">Kamera akan membaca barcode dan langsung mengisi field kode barang.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-unit-wrapper]').forEach((wrapper) => {
        const select = wrapper.querySelector('[data-unit-select]');
        const custom = wrapper.querySelector('[data-unit-custom]');
        const hidden = wrapper.querySelector('[data-unit-hidden]');

        const syncUnitValue = () => {
            const isOther = select.value === '__other__';
            custom.classList.toggle('hidden', !isOther);
            custom.required = isOther;
            hidden.value = isOther ? custom.value.trim() : select.value;
        };

        select.addEventListener('change', syncUnitValue);
        custom.addEventListener('input', syncUnitValue);
        syncUnitValue();
    });

    const barcodeInputs = Array.from(document.querySelectorAll('[data-barcode-input], #barcode-input'));
    const barcodeForms = Array.from(document.querySelectorAll('[data-barcode-form], #barcodeLookupForm'));
    const openCameraButtons = Array.from(document.querySelectorAll('[data-open-camera], #openCameraScanner'));
    const closeCameraButton = document.getElementById('closeCameraScanner');
    const openImageButtons = Array.from(document.querySelectorAll('[data-open-image], #openImageScanner'));
    const openGalleryButtons = Array.from(document.querySelectorAll('[data-open-gallery], #openGalleryScanner'));
    const imageInputs = Array.from(document.querySelectorAll('[data-barcode-image-input], #barcodeImageInput'));
    const galleryInputs = Array.from(document.querySelectorAll('[data-barcode-gallery-input], #barcodeGalleryInput'));
    const cameraHints = Array.from(document.querySelectorAll('[data-camera-hint], #barcodeCameraHint'));
    const modal = document.getElementById('cameraScannerModal');
    const statusText = document.getElementById('cameraScannerStatus');
    const readerRegionId = 'cameraScannerRegion';

    let BrowserMultiFormatReaderClass = null;
    let BarcodeFormatEnum = null;
    let DecodeHintTypeEnum = null;
    let NotFoundExceptionClass = null;
    let TesseractModule = null;
    let barcodeReader = null;
    let activeControls = null;
    let hardwareInputTimer = null;
    let previousValue = '';
    let previousTimestamp = 0;
    let burstIntervals = [];

    const notify = (message, tone = 'success') => {
        const tones = {
            success: 'bg-emerald-50 border-emerald-200 text-emerald-800',
            warning: 'bg-amber-50 border-amber-200 text-amber-900',
            error: 'bg-red-50 border-red-200 text-red-800',
        };

        const box = document.createElement('div');
        box.className = `fixed top-6 right-6 z-[90] max-w-sm rounded-2xl border px-5 py-4 shadow-xl ${tones[tone] || tones.success}`;
        box.innerHTML = `<p class="text-sm font-bold">${message}</p>`;
        document.body.appendChild(box);

        window.setTimeout(() => {
            box.style.opacity = '0';
            box.style.transform = 'translateY(-8px)';
            box.style.transition = 'all 220ms ease';
        }, 2400);

        window.setTimeout(() => box.remove(), 2800);
    };

    const isProbablyMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '');
    const isLoopbackHost = ['127.0.0.1', 'localhost'].includes(window.location.hostname);
    const canUseDirectCamera = window.isSecureContext || isLoopbackHost;
    const liveCameraLikelyBlocked = isProbablyMobile && ! canUseDirectCamera;

    const isVisible = (element) => element && window.getComputedStyle(element).display !== 'none' && element.offsetParent !== null;
    const getVisibleInput = () => barcodeInputs.find(isVisible) || barcodeInputs[0] || null;
    const getVisibleForm = () => barcodeForms.find(isVisible) || barcodeForms[0] || null;
    const getVisibleImageInput = () => imageInputs.find(isVisible) || imageInputs[0] || null;
    const getVisibleGalleryInput = () => galleryInputs.find(isVisible) || galleryInputs[0] || null;

    if (liveCameraLikelyBlocked) {
        cameraHints.forEach((hint) => hint?.classList.remove('hidden'));
    }

    const extractBestBarcodeValue = (rawValue) => {
        const cleanValue = String(rawValue || '').trim();
        if (! cleanValue) {
            return '';
        }

        const numericMatches = cleanValue.match(/\d{8,14}/g) || [];
        if (numericMatches.length) {
            numericMatches.sort((left, right) => right.length - left.length);
            return numericMatches[0];
        }

        const compactAlphaNumeric = cleanValue.replace(/[^A-Za-z0-9]/g, '');
        if (compactAlphaNumeric.length >= 6) {
            return compactAlphaNumeric.slice(0, 120);
        }

        return cleanValue;
    };

    const submitLookup = (barcodeValue) => {
        const cleanValue = extractBestBarcodeValue(barcodeValue);
        const activeInput = getVisibleInput();
        const activeForm = getVisibleForm();
        if (! cleanValue) {
            return;
        }
        if (! activeInput || ! activeForm) {
            return;
        }

        activeInput.value = cleanValue;
        notify('Barcode berhasil dibaca, sistem sedang mencari barang.', 'success');
        activeForm.submit();
    };

    barcodeInputs.forEach((barcodeInput) => {
        barcodeInput?.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                submitLookup(barcodeInput.value);
            }
        });

        barcodeInput?.addEventListener('input', function (event) {
            const currentValue = event.target.value;
            const now = Date.now();
            const deltaLength = currentValue.length - previousValue.length;

            if (deltaLength > 0 && previousTimestamp > 0) {
                burstIntervals.push(now - previousTimestamp);
                burstIntervals = burstIntervals.slice(-12);
            }

            previousValue = currentValue;
            previousTimestamp = now;

            clearTimeout(hardwareInputTimer);
            hardwareInputTimer = window.setTimeout(() => {
                if (extractBestBarcodeValue(currentValue).length < 6 || burstIntervals.length < 3) {
                    return;
                }

                const averageInterval = burstIntervals.reduce((sum, item) => sum + item, 0) / burstIntervals.length;
                if (averageInterval < 45) {
                    submitLookup(currentValue);
                }
            }, 180);
        });
    });

    const stopCameraScanner = async () => {
        if (activeControls) {
            try {
                activeControls.stop();
            } catch (error) {
                console.warn(error);
            }
        }

        activeControls = null;
        barcodeReader = null;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        statusText.textContent = 'Kamera akan membaca barcode dan langsung mengisi field kode barang.';
        const region = document.getElementById(readerRegionId);
        if (region) {
            region.innerHTML = '';
        }
    };

    const startCameraScanner = async () => {
        if (! canUseDirectCamera) {
            notify('Scan langsung butuh HTTPS. Buka aplikasi lewat HTTPS atau gunakan tombol Galeri.', 'warning');
            return;
        }

        if (liveCameraLikelyBlocked) {
            notify('Live camera di HP kemungkinan diblok browser pada alamat ini. Pakai tombol Galeri atau buka lewat HTTPS/IP laptop.', 'warning');
        }

        await stopCameraScanner();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        statusText.textContent = 'Memulai kamera...';

        try {
            if (! BrowserMultiFormatReaderClass) {
                statusText.textContent = 'Memuat modul scanner barcode...';
                const zxingModule = await import('https://unpkg.com/@zxing/browser@0.1.5/+esm');
                BrowserMultiFormatReaderClass = zxingModule.BrowserMultiFormatReader;
                BarcodeFormatEnum = zxingModule.BarcodeFormat;
                DecodeHintTypeEnum = zxingModule.DecodeHintType;
                NotFoundExceptionClass = zxingModule.NotFoundException;
            }

            const hints = new Map();
            hints.set(DecodeHintTypeEnum.POSSIBLE_FORMATS, [
                BarcodeFormatEnum.EAN_13,
                BarcodeFormatEnum.EAN_8,
                BarcodeFormatEnum.UPC_A,
                BarcodeFormatEnum.UPC_E,
                BarcodeFormatEnum.CODE_128,
                BarcodeFormatEnum.CODE_39,
                BarcodeFormatEnum.ITF,
            ]);

            barcodeReader = new BrowserMultiFormatReaderClass(hints);
            const cameras = await BrowserMultiFormatReaderClass.listVideoInputDevices();
            if (! cameras.length) {
                notify('Kamera tidak ditemukan pada perangkat ini.', 'warning');
                await stopCameraScanner();
                return;
            }

            const preferredCamera = cameras.find((camera) => /back|rear|environment/i.test(camera.label)) || cameras[0];
            activeControls = await barcodeReader.decodeFromVideoDevice(
                preferredCamera.deviceId,
                readerRegionId,
                async (result, error) => {
                    if (result) {
                        statusText.textContent = 'Barcode berhasil terbaca. Menyiapkan pencarian barang...';
                        await stopCameraScanner();
                        submitLookup(result.getText());
                        return;
                    }

                    if (error && ! (error instanceof NotFoundExceptionClass)) {
                        console.warn(error);
                    }
                }
            );

            const video = document.querySelector(`#${readerRegionId} video`);
            if (video) {
                video.setAttribute('playsinline', 'true');
                video.muted = true;
                video.classList.add('w-full', 'h-full', 'object-cover');
            }

            statusText.textContent = 'Arahkan barcode ke tengah bingkai. Sistem akan membaca otomatis.';
        } catch (error) {
            console.error(error);
            const errorName = String(error?.name || '');
            if (errorName === 'NotAllowedError') {
                notify('Akses kamera ditolak. Izinkan kamera di browser lalu coba lagi.', 'error');
            } else if (errorName === 'SecurityError') {
                notify('Browser memblokir kamera pada alamat ini. Gunakan HTTPS agar scan langsung aktif.', 'error');
            } else if (errorName === 'NotFoundError') {
                notify('Kamera perangkat tidak ditemukan.', 'error');
            } else if (errorName === 'NotReadableError') {
                notify('Kamera sedang dipakai aplikasi lain. Tutup aplikasi kamera lalu coba lagi.', 'error');
            } else {
                notify('Scan langsung belum berhasil. Coba dekatkan barcode atau gunakan Galeri sebagai cadangan.', 'error');
            }
            await stopCameraScanner();
        }
    };

    const decodeImageFile = async (file) => {
        if (! file) {
            return;
        }

        try {
            statusText.textContent = 'Membaca foto barcode...';
            const supportedFormats = [
                'ean_13',
                'ean_8',
                'upc_a',
                'upc_e',
                'code_128',
                'code_39',
                'itf',
            ];

            if ('BarcodeDetector' in window) {
                try {
                    const detector = new window.BarcodeDetector({ formats: supportedFormats });
                    const bitmap = await createImageBitmap(file);
                    const detections = await detector.detect(bitmap);
                    bitmap.close?.();

                    if (detections.length > 0 && detections[0].rawValue) {
                        statusText.textContent = 'Barcode berhasil dibaca dari foto.';
                        submitLookup(detections[0].rawValue);
                        return;
                    }
                } catch (nativeError) {
                    console.warn(nativeError);
                }
            }

            if (! BrowserMultiFormatReaderClass) {
                const zxingModule = await import('https://unpkg.com/@zxing/browser@0.1.5/+esm');
                BrowserMultiFormatReaderClass = zxingModule.BrowserMultiFormatReader;
                BarcodeFormatEnum = zxingModule.BarcodeFormat;
                DecodeHintTypeEnum = zxingModule.DecodeHintType;
            }

            const hints = new Map();
            hints.set(DecodeHintTypeEnum.POSSIBLE_FORMATS, [
                BarcodeFormatEnum.EAN_13,
                BarcodeFormatEnum.EAN_8,
                BarcodeFormatEnum.UPC_A,
                BarcodeFormatEnum.UPC_E,
                BarcodeFormatEnum.CODE_128,
                BarcodeFormatEnum.CODE_39,
                BarcodeFormatEnum.ITF,
            ]);

            const reader = new BrowserMultiFormatReaderClass(hints);
            const imageUrl = URL.createObjectURL(file);

            try {
                const result = await reader.decodeFromImageUrl(imageUrl);
                statusText.textContent = 'Barcode berhasil dibaca dari foto.';
                submitLookup(result.getText());
                return;
            } finally {
                URL.revokeObjectURL(imageUrl);
            }
        } catch (error) {
            console.error(error);
        }

        try {
            statusText.textContent = 'Barcode sulit dibaca, mencoba OCR angka barcode...';
            const ocrBarcode = await extractBarcodeWithOcr(file);
            if (ocrBarcode) {
                notify(`Barcode terbaca dari angka cetak: ${ocrBarcode}`, 'success');
                statusText.textContent = 'Barcode berhasil dibaca dari angka cetak.';
                submitLookup(ocrBarcode);
                return;
            }
        } catch (ocrError) {
            console.error(ocrError);
        }

        statusText.textContent = 'Foto barcode belum berhasil dibaca.';
        notify('Foto barcode belum terbaca. Coba foto lebih dekat dan lurus, atau ulangi dengan scan langsung.', 'error');
    };

    const loadTesseract = async () => {
        if (! TesseractModule) {
            TesseractModule = await import('https://cdn.jsdelivr.net/npm/tesseract.js@5/+esm');
        }

        return TesseractModule;
    };

    const extractBarcodeWithOcr = async (file) => {
        const image = await new Promise((resolve, reject) => {
            const objectUrl = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => {
                URL.revokeObjectURL(objectUrl);
                resolve(img);
            };
            img.onerror = (error) => {
                URL.revokeObjectURL(objectUrl);
                reject(error);
            };
            img.src = objectUrl;
        });

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d', { willReadFrequently: true });
        const width = image.naturalWidth || image.width;
        const height = image.naturalHeight || image.height;
        const cropTop = Math.max(0, Math.floor(height * 0.5));
        const cropHeight = Math.floor(height * 0.5);

        canvas.width = width;
        canvas.height = cropHeight;
        context.drawImage(image, 0, cropTop, width, cropHeight, 0, 0, width, cropHeight);

        const tesseractModule = await loadTesseract();
        const tesseract = tesseractModule.default || tesseractModule;
        let candidates = [];
        const thresholds = [120, 140, 165, 185];

        for (const threshold of thresholds) {
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            for (let index = 0; index < data.length; index += 4) {
                const gray = Math.round((data[index] + data[index + 1] + data[index + 2]) / 3);
                const boosted = gray > threshold ? 255 : 0;
                data[index] = boosted;
                data[index + 1] = boosted;
                data[index + 2] = boosted;
            }
            context.putImageData(imageData, 0, 0);

            const result = await tesseract.recognize(canvas.toDataURL('image/png'), 'eng', {
                tessedit_char_whitelist: '0123456789',
            });
            const text = String(result?.data?.text || '');
            candidates = candidates.concat(text.match(/\d{8,14}/g) || []);

            if (candidates.length > 0) {
                break;
            }
        }

        if (! candidates.length) {
            return null;
        }

        candidates.sort((left, right) => right.length - left.length);
        return candidates[0];
    };

    const openBarcodeImagePicker = () => {
        const activeImageInput = getVisibleImageInput();
        activeImageInput?.click();
    };

    const openBarcodeGalleryPicker = () => {
        const activeGalleryInput = getVisibleGalleryInput();
        activeGalleryInput?.click();
    };

    window.startAdminBarcodeScanner = startCameraScanner;
    openCameraButtons.forEach((button) => button?.addEventListener('click', function () {
        if (! canUseDirectCamera) {
            notify('Scan langsung belum bisa dipakai di HTTP. Gunakan HTTPS atau pilih Galeri.', 'warning');
            return;
        }

        if (liveCameraLikelyBlocked) {
            notify('Jika kamera gagal dibuka pada alamat ini, gunakan tombol Galeri sebagai cadangan.', 'warning');
        }

        startCameraScanner();
    }));
    openImageButtons.forEach((button) => button?.addEventListener('click', openBarcodeImagePicker));
    openGalleryButtons.forEach((button) => button?.addEventListener('click', openBarcodeGalleryPicker));
    imageInputs.forEach((imageInput) => imageInput?.addEventListener('change', function (event) {
        const file = event.target.files?.[0];
        decodeImageFile(file);
        event.target.value = '';
    }));
    galleryInputs.forEach((galleryInput) => galleryInput?.addEventListener('change', function (event) {
        const file = event.target.files?.[0];
        decodeImageFile(file);
        event.target.value = '';
    }));
    closeCameraButton?.addEventListener('click', stopCameraScanner);
    modal?.addEventListener('click', function (event) {
        if (event.target === modal) {
            stopCameraScanner();
        }
    });
});
</script>
@endsection
