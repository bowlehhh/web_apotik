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

    <div class="space-y-3">
        @forelse ($medicines as $medicine)
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
            <article class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        @if ($medicine->photo_path)
                            <img src="{{ Storage::url($medicine->photo_path) }}" alt="{{ $medicine->name }}" class="h-12 w-12 rounded-xl object-cover border border-slate-200" />
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500">
                                <span class="material-symbols-outlined text-[20px]">medication</span>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <button
                                type="button"
                                data-open-medicine-modal="{{ $medicine->id }}"
                                class="truncate text-left text-base font-bold text-blue-800 hover:text-blue-900 hover:underline"
                            >
                                {{ $medicine->name }}
                            </button>
                            <p class="truncate text-xs text-slate-500">
                                {{ $medicine->trade_name ?: '-' }} | {{ $medicine->dosage ?: '-' }} | {{ $medicine->category ?: '-' }}
                            </p>
                            <p class="truncate text-xs text-slate-500">
                                Barcode: {{ $medicine->barcode ?: '-' }} | Stok: {{ $medicine->stock }} {{ $medicine->unit }}
                            </p>
                            <p class="truncate text-xs text-slate-500">
                                <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold {{ $expBadgeClass }}">
                                    {{ $expPrefix }} {{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if (($hasEntrySourceColumn ?? false) === true)
                            <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $medicine->entry_source === 'barcode' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $medicine->entrySourceLabel() }}
                            </span>
                        @endif
                        <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $medicine->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $medicine->is_active ? 'AKTIF' : 'NONAKTIF' }}
                        </span>
                        @if ($isExpired)
                            <span class="rounded-full px-3 py-1 text-[10px] font-bold bg-red-100 text-red-700">
                                SUDAH EXP
                            </span>
                        @elseif ($isExpiringSoon)
                            <span class="rounded-full px-3 py-1 text-[10px] font-bold bg-amber-100 text-amber-700">
                                EXP {{ $daysLeft }} HARI
                            </span>
                        @endif
                        <button
                            type="button"
                            data-open-medicine-modal="{{ $medicine->id }}"
                            class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white hover:bg-primary-container transition-colors"
                        >
                            Edit
                        </button>
                    </div>
                </div>
            </article>

            <div id="medicine-modal-{{ $medicine->id }}" data-medicine-modal class="fixed inset-0 z-[120] hidden items-center justify-center bg-slate-950/50 p-3 sm:p-6">
                <div class="w-full max-w-5xl rounded-[1.75rem] border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Edit Master Obat</p>
                            <h4 class="text-lg font-extrabold text-blue-900">{{ $medicine->name }}</h4>
                        </div>
                        <button
                            type="button"
                            data-close-medicine-modal="{{ $medicine->id }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
                            aria-label="Tutup popup edit"
                        >
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>

                    <div class="max-h-[78vh] overflow-y-auto px-5 py-5 sm:px-6">
                        <form method="POST" action="{{ route('admin.medicines.update', $medicine) }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
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
                            <button type="submit" class="rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-primary-container xl:col-span-2">
                                Simpan Perubahan
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.medicines.destroy', $medicine) }}" class="mt-3" onsubmit="return confirm('Yakin ingin menghapus/nonaktifkan obat ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700 transition-colors hover:bg-red-100">
                                Hapus / Nonaktifkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const body = document.body;

        const closeModal = function (modal) {
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');

            if (!document.querySelector('[data-medicine-modal].flex')) {
                body.classList.remove('overflow-hidden');
            }
        };

        document.querySelectorAll('[data-open-medicine-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                const modalId = this.getAttribute('data-open-medicine-modal');
                const modal = document.getElementById('medicine-modal-' + modalId);
                if (!modal) {
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                body.classList.add('overflow-hidden');
            });
        });

        document.querySelectorAll('[data-close-medicine-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                const modalId = this.getAttribute('data-close-medicine-modal');
                closeModal(document.getElementById('medicine-modal-' + modalId));
            });
        });

        document.querySelectorAll('[data-medicine-modal]').forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modal);
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            const openedModal = document.querySelector('[data-medicine-modal].flex');
            closeModal(openedModal);
        });
    });
</script>
@endsection
