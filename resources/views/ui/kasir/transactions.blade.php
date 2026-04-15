@extends('ui.kasir.layout')

@section('kasir_title', 'Transaksi Kasir')
@section('kasir_heading', 'Transaksi Kasir')
@section('kasir_subheading', 'Jual obat tanpa resep, proses resep dokter, dan cetak resep dari data yang terhubung.')

@section('kasir_content')
<section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
    <article id="transaksi-form" class="xl:col-span-5 bg-white rounded-[2.5rem] p-8 shadow-sm">
        @php
            $selectedFirstItem = $selectedPrescription?->items->first();
            $selectedFirstMedicineId = $selectedFirstItem?->medicine_id;
            $selectedFirstQuantity = (int) ($selectedFirstItem?->quantity ?? 1);
            $selectedFirstBuyPrice = (float) ($selectedFirstItem?->medicine?->buy_price ?? 0);
            if ($selectedFirstBuyPrice <= 0) {
                $selectedFirstBuyPrice = (float) ($selectedFirstItem?->medicine?->sell_price ?? 0);
            }
            $selectedMedicineId = (int) old('medicine_id', $selectedFirstMedicineId);
            $selectedMedicine = $selectedMedicineId > 0 ? $medicines->firstWhere('id', $selectedMedicineId) : null;
            $selectedMedicineLabel = null;
            if ($selectedMedicine) {
                $selectedMedicineLabel = $selectedMedicine->name
                    .' | Barcode: '.($selectedMedicine->barcode ?: '-')
                    .' | Exp: '.(optional($selectedMedicine->expiry_date)->format('d M Y') ?: '-')
                    .' | Kategori: '.($selectedMedicine->category ?: '-');
            }
        @endphp
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Transaksi</h3>
            <p class="text-sm text-slate-500">Satu form transaksi untuk non resep dan resep dokter. Klik resep untuk memuat data ke form ini.</p>
        </div>

        <form
            method="POST"
            action="{{ route('kasir.transactions.store') }}"
            class="space-y-4"
        >
            @csrf
            <input type="hidden" name="prescription_id" value="{{ $selectedPrescription?->id ?? '' }}" />
            @if ($selectedPrescription)
                <input type="hidden" name="markup_amount" value="0" />
                <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                    <div class="mb-3">
                        <p class="font-bold text-blue-900">{{ $selectedPrescription->patient?->name ?? '-' }}</p>
                        <p class="text-xs text-slate-600">
                            {{ $selectedPrescription->patient?->medical_record_number ?? '-' }} |
                            Dokter: {{ $selectedPrescription->doctor?->name ?? '-' }} |
                            {{ optional($selectedPrescription->prescribed_at)->format('d M Y H:i') }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        @foreach ($selectedPrescription->items as $item)
                            @php
                                $autoUnitPrice = (float) ($item->medicine?->buy_price ?? 0);
                                if ($autoUnitPrice <= 0) {
                                    $autoUnitPrice = (float) ($item->medicine?->sell_price ?? 0);
                                }
                            @endphp
                            <input type="hidden" name="confirm_items[{{ $item->id }}]" value="1" />
                            <p class="text-xs text-slate-700">
                                {{ $item->medicine?->name ?? '-' }} ({{ (int) $item->quantity }}) | beli: Rp {{ number_format((float) ($item->medicine?->buy_price ?? 0), 0, ',', '.') }}
                            </p>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($selectedPrescription)
                <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Form Transaksi Resep (Per Obat)</p>
                    @foreach ($selectedPrescription->items as $item)
                        @php
                            $editableUnitPrice = (float) ($item->medicine?->buy_price ?? 0);
                            if ($editableUnitPrice <= 0) {
                                $editableUnitPrice = (float) ($item->medicine?->sell_price ?? 0);
                            }
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-white p-3" data-prescription-row data-qty="{{ (int) $item->quantity }}">
                            <div class="mb-2">
                                <p class="text-sm font-bold text-slate-800">{{ $item->medicine?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">Qty: {{ (int) $item->quantity }} | Aturan: {{ $item->dosage_instructions ?: '-' }}</p>
                            </div>
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Harga Jual Item</label>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="unit_prices[{{ $item->id }}]"
                                        value="{{ old("unit_prices.{$item->id}", (int) round($editableUnitPrice)) }}"
                                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
                                        data-prescription-unit-price
                                        data-currency-input
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Subtotal Item</label>
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700" data-prescription-line-total>Rp 0</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-3">
                        <p class="text-xs font-bold uppercase tracking-widest text-blue-700">Subtotal Transaksi Resep</p>
                        <p class="mt-1 text-xl font-black text-blue-900" id="prescription-subtotal-display">Rp 0</p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Cari & Pilih Obat</label>
                        <input
                            id="medicine_search_input"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
                            placeholder="Ketik nama, barcode, kategori, atau exp..."
                            value="{{ $selectedMedicineLabel }}"
                            autocomplete="off"
                        />
                        <input id="medicine_id_input" type="hidden" name="medicine_id" value="{{ $selectedMedicineId > 0 ? $selectedMedicineId : '' }}" required />
                        <div id="medicine_picker_list" class="mt-3 max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white">
                            @foreach ($medicines as $medicine)
                                @php
                                    $expLabel = optional($medicine->expiry_date)->format('d M Y') ?: '-';
                                    $daysLeft = $medicine->expiry_date
                                        ? now()->startOfDay()->diffInDays($medicine->expiry_date->copy()->startOfDay(), false)
                                        : null;
                                    $isExpired = $daysLeft !== null && $daysLeft < 0;
                                    $isExpiringSoon = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30;
                                    $expBadgeClass = 'bg-emerald-100 text-emerald-800';
                                    $expPrefix = 'Belum Exp:';

                                    if ($isExpired) {
                                        $expBadgeClass = 'bg-amber-200 text-amber-900';
                                        $expPrefix = 'Sudah Exp:';
                                    } elseif ($isExpiringSoon) {
                                        $expBadgeClass = 'bg-amber-100 text-amber-800';
                                        $expPrefix = 'Mau Exp:';
                                    }

                                    $medicineLabel = $medicine->name.' | '.$expPrefix.' '.$expLabel.' | Kategori: '.($medicine->category ?: '-');
                                @endphp
                                <button
                                    type="button"
                                    data-medicine-picker-item
                                    data-id="{{ $medicine->id }}"
                                    data-name="{{ $medicine->name }}"
                                    data-barcode="{{ $medicine->barcode }}"
                                    data-stock="{{ (int) $medicine->stock }}"
                                    data-buy="{{ (float) $medicine->buy_price }}"
                                    data-label="{{ $medicineLabel }}"
                                    data-search="{{ strtolower(trim($medicine->name.' '.$medicine->barcode.' '.$medicine->category.' '.$expLabel)) }}"
                                    class="w-full border-b border-slate-100 px-3 py-2 text-left text-sm text-slate-700 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none {{ $loop->last ? 'border-b-0' : '' }}"
                                >
                                    <span class="font-medium text-slate-800">{{ $medicine->name }}</span>
                                    <span class="mx-1 text-slate-300">|</span>
                                    <span class="inline-flex rounded px-2 py-0.5 text-xs font-bold {{ $expBadgeClass }}">{{ $expPrefix }} {{ $expLabel }}</span>
                                    <span class="mx-1 text-slate-300">|</span>
                                    <span class="text-slate-600">Kategori: {{ $medicine->category ?: '-' }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Scan Barcode</label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_auto]">
                            <input
                                id="medicine_barcode_input"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
                                placeholder="Scan barcode di sini, lalu tekan Enter"
                                autocomplete="off"
                            />
                            <button
                                id="start_barcode_camera"
                                type="button"
                                class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs font-bold uppercase tracking-widest text-blue-700 hover:bg-blue-100 transition-colors"
                            >
                                Scan Kamera
                            </button>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500">Barcode cocok otomatis pilih obat, tanpa perlu scroll daftar.</p>
                        <div id="barcode_camera_panel" class="mt-3 hidden rounded-xl border border-blue-100 bg-blue-50/60 p-3">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <p class="text-[11px] font-bold uppercase tracking-widest text-blue-700">Kamera Scan Barcode</p>
                                <button
                                    id="stop_barcode_camera"
                                    type="button"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-600 hover:bg-slate-100"
                                >
                                    Tutup
                                </button>
                            </div>
                            <video id="barcode_camera_video" class="w-full rounded-lg border border-slate-200 bg-black/80" autoplay muted playsinline></video>
                            <p id="barcode_camera_status" class="mt-2 text-[11px] text-slate-600">Mengaktifkan kamera...</p>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Detail Obat</p>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 space-y-3">
                            <p id="medicine_summary_name" class="text-sm font-semibold text-slate-700 break-words">Belum pilih obat</p>
                            <div class="grid grid-cols-2 xl:grid-cols-3 gap-2 text-[11px]">
                                <div class="rounded-lg border border-slate-200 bg-white p-2 min-w-0">
                                    <p class="text-slate-500 uppercase tracking-wide">Stok</p>
                                    <p id="medicine_summary_stock" class="font-bold text-slate-700 leading-tight break-words">-</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-white p-2 min-w-0">
                                    <p class="text-slate-500 uppercase tracking-wide">Beli</p>
                                    <p id="medicine_summary_buy" class="font-bold text-slate-700 leading-tight break-words">-</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-white p-2 min-w-0">
                                    <p class="text-slate-500 uppercase tracking-wide">ID Barcode</p>
                                    <p id="medicine_summary_barcode" class="font-bold text-slate-700 leading-tight break-words">-</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-white p-2 min-w-0">
                                    <p class="text-slate-500 uppercase tracking-wide">Jual Dasar</p>
                                    <p id="medicine_summary_sell" class="font-bold text-slate-700 leading-tight break-words">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Jumlah</label>
                        <input type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Harga Jual Kasir</label>
                        <input id="unit_price_input" type="text" inputmode="numeric" name="unit_price" value="{{ old('unit_price') }}" data-currency-input class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Wajib diisi" required />
                    </div>
                </div>
                <p class="text-xs text-slate-500 -mt-1">Harga dasar jual kosong dan hanya diisi dari input kasir.</p>
            @endif

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Nama Pasien/Pembeli (Opsional)</label>
                <input type="text" name="patient_name" value="{{ old('patient_name', $selectedPrescription?->patient?->name ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: Budi Santoso" />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Catatan</label>
                <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Catatan transaksi (opsional)">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary-container transition-colors">
                {{ $selectedPrescription ? 'Simpan Transaksi Resep' : 'Simpan Transaksi' }}
            </button>
        </form>
    </article>

    <article class="xl:col-span-4 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Resep Dokter Menunggu Tebus</h3>
            <p class="text-sm text-slate-500">Resep dari dokter langsung tampil di kasir. Kasir bisa proses tebus dan cetak resep.</p>
        </div>

        <div class="space-y-4 max-h-[780px] overflow-y-auto pr-1">
            @forelse ($pendingPrescriptions as $prescription)
                <article class="rounded-2xl border border-slate-100 bg-slate-50/60 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                        <div>
                            <p class="font-bold text-slate-800">{{ $prescription->patient?->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $prescription->patient?->medical_record_number ?? '-' }} |
                                Dokter: {{ $prescription->doctor?->name ?? '-' }} |
                                {{ optional($prescription->prescribed_at)->format('d M Y H:i') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">BELUM DITEBUS</span>
                    </div>

                    <div class="rounded-xl bg-white border border-slate-100 p-3 space-y-1 mb-3">
                        @foreach ($prescription->items as $item)
                            <p class="text-sm text-slate-700">
                                {{ $item->medicine?->name ?? '-' }} ({{ $item->quantity }}) - {{ $item->dosage_instructions }}
                                <span class="text-xs text-slate-500">| beli: Rp {{ number_format((float) ($item->medicine?->buy_price ?? 0), 0, ',', '.') }}</span>
                            </p>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('kasir.transaksi', ['prescription_id' => $prescription->id]) }}#transaksi-form" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition-colors">
                            Proses Tebus Resep
                        </a>
                        <a target="_blank" href="{{ route('kasir.prescriptions.print', $prescription) }}" class="px-3 py-2 rounded-lg bg-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-300 transition-colors">
                            Cetak Resep
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <p class="text-sm text-slate-500">Belum ada resep dokter yang menunggu tebus.</p>
                </div>
            @endforelse
        </div>
    </article>

    <article class="xl:col-span-3 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-xl font-extrabold text-blue-900">Daftar Harga Obat</h3>
                <p class="text-sm text-slate-500">Daftar ini membantu kasir melihat harga tanpa membuka dropdown panjang.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700">
                {{ $medicines->count() }} obat
            </span>
        </div>

        <div class="space-y-3 max-h-[780px] overflow-y-auto pr-1">
            @forelse ($medicines->take(20) as $medicine)
                <article class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-bold text-slate-800">{{ $medicine->name }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $medicine->trade_name ?: '-' }} |
                                Stok: {{ $medicine->stock }} {{ $medicine->unit }}
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $medicine->stock > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $medicine->stock > 0 ? 'READY' : 'KOSONG' }}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                        <div class="rounded-lg border border-slate-200 bg-white p-2">
                            <p class="text-slate-500 uppercase tracking-wide">Harga Beli</p>
                            <p class="font-bold text-slate-700">Rp {{ number_format((float) $medicine->buy_price, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-2">
                            <p class="text-slate-500 uppercase tracking-wide">Jual Dasar</p>
                            <p class="font-bold text-blue-700" data-list-sell="{{ $medicine->id }}">-</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        data-pick-medicine="{{ $medicine->id }}"
                        class="mt-3 w-full rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100 transition-colors"
                    >
                        Pilih Obat Ini
                    </button>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <p class="text-sm text-slate-500">Belum ada obat aktif yang bisa dipilih.</p>
                </div>
            @endforelse
        </div>
    </article>
</section>

<section class="bg-white rounded-[2.5rem] p-8 shadow-sm">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-xl font-extrabold text-blue-900">Riwayat Transaksi Kasir</h3>
            <p class="text-sm text-slate-500">Menampilkan transaksi resep dan non resep yang sudah diproses.</p>
        </div>
        <a
            target="_blank"
            href="{{ route('kasir.sales.history.print', request()->only(['history_q', 'history_type', 'history_from', 'history_to'])) }}"
            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition-colors"
        >
            Cetak Riwayat
        </a>
    </div>

    <form method="GET" action="{{ route('kasir.transaksi') }}" class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
            <div class="xl:col-span-2">
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Cari Nota/Pasien/Kasir</label>
                <input
                    type="text"
                    name="history_q"
                    value="{{ $historyFilters['q'] }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm"
                    placeholder="Contoh: INV-20260410 / nama pasien"
                />
            </div>
            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Jenis</label>
                <select name="history_type" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
                    <option value="all" @selected($historyFilters['type'] === 'all')>Semua Transaksi</option>
                    <option value="prescription" @selected($historyFilters['type'] === 'prescription')>Resep Dokter</option>
                    <option value="non_prescription" @selected($historyFilters['type'] === 'non_prescription')>Tanpa Resep</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Dari Tanggal</label>
                <input type="date" name="history_from" value="{{ $historyFilters['from'] }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm" />
            </div>
            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Sampai Tanggal</label>
                <input type="date" name="history_to" value="{{ $historyFilters['to'] }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm" />
            </div>
            <div>
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Baris</label>
                <select name="history_per_page" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
                    @foreach ($historyPerPageOptions as $option)
                        <option value="{{ $option }}" @selected((int) $historyFilters['per_page'] === (int) $option)>
                            {{ $option }} per halaman
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
            <a href="{{ route('kasir.transaksi') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                Reset
            </a>
            <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition-colors">
                Terapkan Filter
            </button>
        </div>
    </form>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
            <p class="text-[10px] uppercase tracking-widest font-bold text-blue-700">Total Transaksi</p>
            <p class="text-xl font-black text-blue-900 mt-1">{{ number_format((int) $historySummary['total_transactions']) }}</p>
        </div>
        <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3">
            <p class="text-[10px] uppercase tracking-widest font-bold text-indigo-700">Total Item</p>
            <p class="text-xl font-black text-indigo-900 mt-1">{{ number_format((int) $historySummary['total_items']) }}</p>
        </div>
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
            <p class="text-[10px] uppercase tracking-widest font-bold text-emerald-700">Total Uang Masuk</p>
            <p class="text-xl font-black text-emerald-900 mt-1">Rp {{ number_format((float) $historySummary['total_amount'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[10px] uppercase tracking-widest text-slate-400 font-black border-b border-slate-100">
                    <th class="py-3">Nomor Nota</th>
                    <th class="py-3">Tipe</th>
                    <th class="py-3">Pasien</th>
                    <th class="py-3">Kasir</th>
                    <th class="py-3">Item</th>
                    <th class="py-3">Total</th>
                    <th class="py-3">Waktu</th>
                    <th class="py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm">
                @forelse ($recentSales as $sale)
                    <tr>
                        <td class="py-4 font-bold">{{ $sale->invoice_number }}</td>
                        <td class="py-4">{{ $sale->sale_type === 'prescription' ? 'Resep Dokter' : 'Tanpa Resep' }}</td>
                        <td class="py-4">{{ $sale->patient_name ?: '-' }}</td>
                        <td class="py-4">{{ $sale->cashier?->name ?? '-' }}</td>
                        <td class="py-4">{{ $sale->total_items }}</td>
                        <td class="py-4">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</td>
                        <td class="py-4">{{ optional($sale->sold_at)->format('d M Y H:i') }}</td>
                        <td class="py-4">
                            <a target="_blank" href="{{ route('kasir.sales.print', $sale) }}" class="inline-flex items-center rounded-lg bg-slate-200 px-3 py-2 text-[11px] font-bold text-slate-700 hover:bg-slate-300 transition-colors">
                                Cetak
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">
                            Belum ada transaksi kasir.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($recentSales instanceof \Illuminate\Contracts\Pagination\Paginator && $recentSales->hasPages())
        <div class="mt-5">
            {{ $recentSales->onEachSide(1)->links() }}
        </div>
    @endif
</section>

<script>
    (function () {
        const medicineIdInput = document.getElementById('medicine_id_input');
        const barcodeInput = document.getElementById('medicine_barcode_input');
        const startCameraButton = document.getElementById('start_barcode_camera');
        const stopCameraButton = document.getElementById('stop_barcode_camera');
        const cameraPanel = document.getElementById('barcode_camera_panel');
        const cameraVideo = document.getElementById('barcode_camera_video');
        const cameraStatus = document.getElementById('barcode_camera_status');
        const searchInput = document.getElementById('medicine_search_input');
        const pickerItems = Array.from(document.querySelectorAll('[data-medicine-picker-item]'));
        const unitPriceInput = document.getElementById('unit_price_input');
        const nameElement = document.getElementById('medicine_summary_name');
        const stockElement = document.getElementById('medicine_summary_stock');
        const buyElement = document.getElementById('medicine_summary_buy');
        const barcodeElement = document.getElementById('medicine_summary_barcode');
        const sellElement = document.getElementById('medicine_summary_sell');
        const listSellElements = Array.from(document.querySelectorAll('[data-list-sell]'));
        const barcodeDetector = 'BarcodeDetector' in window
            ? new BarcodeDetector({
                formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a', 'upc_e', 'itf', 'codabar', 'qr_code'],
            })
            : null;
        let cameraStream = null;
        let cameraScanFrameId = null;

        if (
            !medicineIdInput
            || !unitPriceInput
            || !nameElement
            || !stockElement
            || !buyElement
            || !barcodeElement
            || !sellElement
            || pickerItems.length === 0
        ) {
            return;
        }

        const parseCurrencyNumber = (value) => {
            const numericDigits = String(value ?? '').replace(/[^\d]/g, '');
            if (numericDigits === '') {
                return Number.NaN;
            }

            return Number(numericDigits);
        };

        const formatRupiah = (value) => {
            const numeric = Number(value);
            if (!Number.isFinite(numeric)) {
                return '-';
            }

            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 0,
            }).format(numeric);
        };

        const syncSellingPricePreview = () => {
            const selectedMedicineId = medicineIdInput.value;
            const numericInput = parseCurrencyNumber(unitPriceInput.value);
            const hasInput = Number.isFinite(numericInput) && numericInput >= 0;
            const displayValue = hasInput ? formatRupiah(numericInput) : '-';

            sellElement.textContent = selectedMedicineId ? displayValue : '-';

            listSellElements.forEach((element) => {
                element.textContent = selectedMedicineId && element.getAttribute('data-list-sell') === selectedMedicineId
                    ? displayValue
                    : '-';
            });
        };

        const getSelectedMedicineItem = () => {
            const selectedId = medicineIdInput.value;
            if (!selectedId) {
                return null;
            }

            return pickerItems.find((item) => item.dataset.id === selectedId) || null;
        };

        const updateMedicineSummary = () => {
            const selectedItem = getSelectedMedicineItem();
            const hasSelection = !!selectedItem;

            if (!hasSelection) {
                nameElement.textContent = 'Belum pilih obat';
                stockElement.textContent = '-';
                buyElement.textContent = '-';
                barcodeElement.textContent = '-';
                sellElement.textContent = '-';
                syncSellingPricePreview();
                return;
            }

            nameElement.textContent = selectedItem.dataset.name || selectedItem.textContent || '-';
            stockElement.textContent = selectedItem.dataset.stock || '-';
            buyElement.textContent = formatRupiah(selectedItem.dataset.buy);
            barcodeElement.textContent = selectedItem.dataset.barcode || '-';
            syncSellingPricePreview();
        };

        const filterMedicineItems = () => {
            if (!searchInput) {
                return;
            }

            const keyword = searchInput.value.trim().toLowerCase();
            const selectedValue = medicineIdInput.value;

            pickerItems.forEach((item) => {
                const haystack = (item.dataset.search || item.textContent || '').toLowerCase();
                const matches = keyword === '' || haystack.includes(keyword);
                item.classList.toggle('hidden', !matches && item.dataset.id !== selectedValue);
            });
        };

        const setSelectedMedicine = (medicineId) => {
            const selectedItem = pickerItems.find((item) => item.dataset.id === medicineId) || null;
            if (!selectedItem) {
                return;
            }

            medicineIdInput.value = selectedItem.dataset.id || '';
            if (searchInput) {
                searchInput.value = selectedItem.dataset.label || selectedItem.textContent || '';
            }
            if (barcodeInput) {
                barcodeInput.value = selectedItem.dataset.barcode || '';
            }
            updateMedicineSummary();
            filterMedicineItems();
        };

        pickerItems.forEach((item) => {
            item.addEventListener('click', () => {
                setSelectedMedicine(item.dataset.id || '');
            });
        });

        document.querySelectorAll('[data-pick-medicine]').forEach((button) => {
            button.addEventListener('click', () => {
                const medicineId = button.getAttribute('data-pick-medicine');
                if (!medicineId) {
                    return;
                }

                setSelectedMedicine(medicineId);
                searchInput?.focus();
            });
        });

        const handleBarcodeScan = (rawValue = null) => {
            if (!barcodeInput && rawValue === null) {
                return;
            }

            const scannedValue = String(rawValue ?? barcodeInput.value).trim().toLowerCase();
            if (scannedValue === '') {
                return;
            }

            const exactMatches = pickerItems.filter((item) => (item.dataset.barcode || '').trim().toLowerCase() === scannedValue);
            let targetItem = exactMatches.find((item) => Number(item.dataset.stock || 0) > 0) || exactMatches[0] || null;

            if (!targetItem) {
                targetItem = pickerItems.find((item) => {
                    const itemBarcode = (item.dataset.barcode || '').trim().toLowerCase();
                    return itemBarcode !== '' && itemBarcode.includes(scannedValue);
                }) || null;
            }

            if (!targetItem) {
                return;
            }

            setSelectedMedicine(targetItem.dataset.id || '');
            barcodeInput.value = targetItem.dataset.barcode || '';
            unitPriceInput.focus();
            unitPriceInput.select();
        };

        const stopCameraScanLoop = () => {
            if (cameraScanFrameId) {
                cancelAnimationFrame(cameraScanFrameId);
                cameraScanFrameId = null;
            }
        };

        const stopBarcodeCamera = () => {
            stopCameraScanLoop();

            if (cameraStream) {
                cameraStream.getTracks().forEach((track) => track.stop());
                cameraStream = null;
            }

            if (cameraVideo) {
                cameraVideo.srcObject = null;
            }

            if (cameraPanel) {
                cameraPanel.classList.add('hidden');
            }
        };

        const startCameraScanLoop = () => {
            if (!barcodeDetector || !cameraVideo) {
                return;
            }

            const scanFrame = async () => {
                if (!cameraVideo || !cameraStream) {
                    return;
                }

                try {
                    const barcodes = await barcodeDetector.detect(cameraVideo);
                    const first = barcodes?.[0];
                    const rawValue = String(first?.rawValue ?? '').trim();

                    if (rawValue !== '') {
                        if (barcodeInput) {
                            barcodeInput.value = rawValue;
                        }
                        handleBarcodeScan(rawValue);
                        if (cameraStatus) {
                            cameraStatus.textContent = `Barcode terbaca: ${rawValue}`;
                        }
                        stopBarcodeCamera();
                        return;
                    }
                } catch (_error) {
                    // Abaikan error deteksi sesaat, lanjut frame berikutnya.
                }

                cameraScanFrameId = requestAnimationFrame(scanFrame);
            };

            cameraScanFrameId = requestAnimationFrame(scanFrame);
        };

        const startBarcodeCamera = async () => {
            if (!navigator.mediaDevices?.getUserMedia) {
                if (cameraStatus) {
                    cameraStatus.textContent = 'Perangkat ini tidak mendukung akses kamera.';
                }
                return;
            }

            if (!barcodeDetector) {
                if (cameraStatus) {
                    cameraStatus.textContent = 'Browser belum mendukung deteksi barcode kamera. Gunakan scanner biasa.';
                }
                if (cameraPanel) {
                    cameraPanel.classList.remove('hidden');
                }
                return;
            }

            stopBarcodeCamera();

            if (cameraPanel) {
                cameraPanel.classList.remove('hidden');
            }
            if (cameraStatus) {
                cameraStatus.textContent = 'Arahkan kamera ke barcode...';
            }

            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: 'environment' },
                    },
                    audio: false,
                });

                if (!cameraVideo) {
                    return;
                }

                cameraVideo.srcObject = cameraStream;
                await cameraVideo.play();
                startCameraScanLoop();
            } catch (_error) {
                if (cameraStatus) {
                    cameraStatus.textContent = 'Kamera gagal dibuka. Pastikan izin kamera sudah diberikan.';
                }
            }
        };

        if (barcodeInput) {
            barcodeInput.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                handleBarcodeScan();
            });

            barcodeInput.addEventListener('change', handleBarcodeScan);
        }
        startCameraButton?.addEventListener('click', () => {
            startBarcodeCamera();
        });
        stopCameraButton?.addEventListener('click', () => {
            stopBarcodeCamera();
        });
        window.addEventListener('beforeunload', () => {
            stopBarcodeCamera();
        });

        unitPriceInput.addEventListener('input', syncSellingPricePreview);
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                medicineIdInput.value = '';
                filterMedicineItems();
                updateMedicineSummary();
            });
        }
        if (medicineIdInput.value) {
            setSelectedMedicine(medicineIdInput.value);
        } else {
            filterMedicineItems();
            updateMedicineSummary();
        }
        updateMedicineSummary();
    })();

    (function () {
        const rows = Array.from(document.querySelectorAll('[data-prescription-row]'));
        const subtotalDisplay = document.getElementById('prescription-subtotal-display');

        if (!rows.length || !subtotalDisplay) {
            return;
        }

        const formatRupiah = (value) => {
            const numeric = Number(value);
            if (!Number.isFinite(numeric)) {
                return 'Rp 0';
            }

            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 0,
            }).format(numeric);
        };

        const parseDigits = (value) => {
            const digits = String(value ?? '').replace(/[^\d]/g, '');
            return digits === '' ? 0 : Number(digits);
        };

        const updateSubtotal = () => {
            let subtotal = 0;

            rows.forEach((row) => {
                const quantity = Number(row.getAttribute('data-qty') || 0);
                const unitInput = row.querySelector('[data-prescription-unit-price]');
                const lineTotalEl = row.querySelector('[data-prescription-line-total]');
                const unitPrice = parseDigits(unitInput?.value);
                const lineTotal = Math.max(0, quantity) * Math.max(0, unitPrice);

                if (lineTotalEl) {
                    lineTotalEl.textContent = formatRupiah(lineTotal);
                }

                subtotal += lineTotal;
            });

            subtotalDisplay.textContent = formatRupiah(subtotal);
        };

        rows.forEach((row) => {
            const unitInput = row.querySelector('[data-prescription-unit-price]');
            if (unitInput) {
                unitInput.addEventListener('input', updateSubtotal);
            }
        });

        updateSubtotal();
    })();
</script>
@endsection
