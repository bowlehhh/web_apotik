@extends('ui.kasir.layout')

@section('kasir_title', 'Transaksi Kasir')
@section('kasir_heading', 'Transaksi Kasir')
@section('kasir_subheading', 'Jual obat tanpa resep, proses resep dokter, dan cetak resep dari data yang terhubung.')

@section('kasir_content')
<section class="grid grid-cols-1 xl:grid-cols-12 gap-8">
    <article class="xl:col-span-5 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Penjualan Tanpa Resep</h3>
            <p class="text-sm text-slate-500">Kasir dapat menjual obat non resep dan stok otomatis berkurang.</p>
        </div>

        <form method="POST" action="{{ route('kasir.sales.non-prescription.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Pilih Obat</label>
                    <select id="medicine_id_select" name="medicine_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" required>
                        <option value="">- Pilih obat -</option>
                        @foreach ($medicines as $medicine)
                            <option
                                value="{{ $medicine->id }}"
                                data-stock="{{ (int) $medicine->stock }}"
                                data-buy="{{ (float) $medicine->buy_price }}"
                            >
                                {{ $medicine->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-3 min-w-0">
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
                    <input type="number" min="1" name="quantity" value="1" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" required />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Harga Jual Kasir</label>
                    <input id="unit_price_input" type="text" inputmode="numeric" name="unit_price" value="{{ old('unit_price') }}" data-currency-input class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Wajib diisi" required />
                </div>
            </div>
            <p class="text-xs text-slate-500 -mt-1">Harga dasar jual kosong dan hanya diisi dari input kasir.</p>

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Nama Pasien/Pembeli (Opsional)</label>
                <input type="text" name="patient_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: Budi Santoso" />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Catatan</label>
                <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Catatan transaksi (opsional)"></textarea>
            </div>

            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary-container transition-colors">
                Simpan Transaksi Non Resep
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
                        <form method="POST" action="{{ route('kasir.prescriptions.dispense', $prescription) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="text" inputmode="numeric" name="markup_amount" value="0" data-currency-input class="w-28 rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs" placeholder="Tambahan" />
                            <button type="submit" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition-colors">
                                Proses Tebus Resep
                            </button>
                        </form>
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
        const select = document.getElementById('medicine_id_select');
        const unitPriceInput = document.getElementById('unit_price_input');
        const nameElement = document.getElementById('medicine_summary_name');
        const stockElement = document.getElementById('medicine_summary_stock');
        const buyElement = document.getElementById('medicine_summary_buy');
        const sellElement = document.getElementById('medicine_summary_sell');
        const listSellElements = Array.from(document.querySelectorAll('[data-list-sell]'));

        if (!select || !unitPriceInput || !nameElement || !stockElement || !buyElement || !sellElement) {
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
            const selectedMedicineId = select.value;
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

        const updateMedicineSummary = () => {
            const option = select.options[select.selectedIndex];
            const hasSelection = option && option.value !== '';

            if (!hasSelection) {
                nameElement.textContent = 'Belum pilih obat';
                stockElement.textContent = '-';
                buyElement.textContent = '-';
                sellElement.textContent = '-';
                syncSellingPricePreview();
                return;
            }

            nameElement.textContent = option.text;
            stockElement.textContent = option.dataset.stock || '-';
            buyElement.textContent = formatRupiah(option.dataset.buy);
            syncSellingPricePreview();
        };

        document.querySelectorAll('[data-pick-medicine]').forEach((button) => {
            button.addEventListener('click', () => {
                const medicineId = button.getAttribute('data-pick-medicine');
                if (!medicineId) {
                    return;
                }

                select.value = medicineId;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                select.focus();
            });
        });

        unitPriceInput.addEventListener('input', syncSellingPricePreview);
        select.addEventListener('change', updateMedicineSummary);
        updateMedicineSummary();
    })();
</script>
@endsection
