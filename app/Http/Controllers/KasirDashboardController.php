<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KasirDashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $today = now()->toDateString();

        $todaySalesBaseQuery = Sale::query()->whereDate('sold_at', $today);
        $todayPrescriptionBaseQuery = Sale::query()
            ->whereDate('sold_at', $today)
            ->where('sale_type', 'prescription');
        $todayNonPrescriptionBaseQuery = Sale::query()
            ->whereDate('sold_at', $today)
            ->where('sale_type', 'non_prescription');

        $stats = [
            'total_medicines' => Medicine::query()->count(),
            'ready_medicines' => Medicine::query()->where('stock', '>', 0)->count(),
            'not_ready_medicines' => Medicine::query()->where('stock', '<=', 0)->count(),
            'pending_prescriptions' => Prescription::query()->where('is_dispensed', false)->count(),
            'today_sales' => (clone $todaySalesBaseQuery)->count(),
            'today_total_revenue' => (float) ((clone $todaySalesBaseQuery)->sum('total_amount') ?? 0),
            'today_prescription_sales' => (clone $todayPrescriptionBaseQuery)->count(),
            'today_non_prescription_sales' => (clone $todayNonPrescriptionBaseQuery)->count(),
            'today_non_prescription_revenue' => (float) ((clone $todayNonPrescriptionBaseQuery)->sum('total_amount') ?? 0),
        ];

        $pendingPrescriptions = $this->prescriptionsQuery()
            ->where('is_dispensed', false)
            ->limit(10)
            ->get();

        $recentSales = $this->salesQuery()
            ->limit(10)
            ->get();

        $recentNonPrescriptionSales = $this->salesQuery()
            ->where('sale_type', 'non_prescription')
            ->limit(10)
            ->get();

        $topNonPrescriptionMedicinesToday = SaleItem::query()
            ->select([
                'medicine_name_snapshot',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_total) as total_amount'),
            ])
            ->whereHas('sale', function (Builder $query) use ($today): void {
                $query->whereDate('sold_at', $today)
                    ->where('sale_type', 'non_prescription');
            })
            ->groupBy('medicine_name_snapshot')
            ->orderByDesc(DB::raw('SUM(quantity)'))
            ->orderBy('medicine_name_snapshot')
            ->limit(8)
            ->get();

        $lowStockMedicines = Medicine::query()
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return view('ui.kasir.dashboard', [
            'stats' => $stats,
            'pendingPrescriptions' => $pendingPrescriptions,
            'recentSales' => $recentSales,
            'recentNonPrescriptionSales' => $recentNonPrescriptionSales,
            'topNonPrescriptionMedicinesToday' => $topNonPrescriptionMedicinesToday,
            'lowStockMedicines' => $lowStockMedicines,
            'cashier' => $request->user(),
        ]);
    }

    public function transactions(Request $request): View
    {
        $historyFilters = $this->resolveSalesHistoryFilters($request);

        $medicines = $this->medicinesQuery()
            ->where('is_active', true)
            ->get();

        $pendingPrescriptions = $this->prescriptionsQuery()
            ->where('is_dispensed', false)
            ->get();

        $salesHistoryQuery = $this->filteredSalesHistoryQuery($historyFilters);
        $salesSummaryQuery = clone $salesHistoryQuery;

        $historySummary = [
            'total_transactions' => (clone $salesSummaryQuery)->count(),
            'total_items' => (int) ((clone $salesSummaryQuery)->sum('total_items') ?? 0),
            'total_amount' => (float) ((clone $salesSummaryQuery)->sum('total_amount') ?? 0),
        ];

        $recentSales = $salesHistoryQuery
            ->paginate($historyFilters['per_page'])
            ->withQueryString();

        return view('ui.kasir.transactions', [
            'medicines' => $medicines,
            'pendingPrescriptions' => $pendingPrescriptions,
            'recentSales' => $recentSales,
            'historyFilters' => $historyFilters,
            'historySummary' => $historySummary,
            'historyPerPageOptions' => $this->historyPerPageOptions(),
            'cashier' => $request->user(),
        ]);
    }

    public function medicines(Request $request): View
    {
        $medicines = $this->medicinesQuery()->get();

        $stats = [
            'total_medicines' => $medicines->count(),
            'active_medicines' => $medicines->where('is_active', true)->count(),
            'ready_medicines' => $medicines->where('stock', '>', 0)->count(),
            'not_ready_medicines' => $medicines->where('stock', '<=', 0)->count(),
        ];

        return view('ui.kasir.medicines', [
            'medicines' => $medicines,
            'stats' => $stats,
            'cashier' => $request->user(),
        ]);
    }

    public function storeNonPrescriptionSale(Request $request): RedirectResponse
    {
        $this->normalizeFormattedNumericInputs($request, ['quantity'], ['unit_price']);

        $validated = $request->validate([
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'patient_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'medicine_id.required' => 'Obat wajib dipilih.',
            'quantity.required' => 'Jumlah obat wajib diisi.',
            'unit_price.required' => 'Harga jual wajib diisi oleh kasir.',
        ]);

        $soldMedicine = null;
        $createdSaleId = null;
        $lineTotal = 0;

        DB::transaction(function () use ($request, $validated, &$soldMedicine, &$createdSaleId, &$lineTotal): void {
            $medicine = Medicine::query()
                ->lockForUpdate()
                ->findOrFail((int) $validated['medicine_id']);

            if (! $medicine->is_active) {
                throw ValidationException::withMessages([
                    'medicine_id' => 'Obat ini nonaktif dan tidak bisa dijual.',
                ]);
            }

            $quantity = (int) $validated['quantity'];
            $buyPrice = (float) $medicine->buy_price;
            if ($buyPrice <= 0) {
                $buyPrice = (float) $medicine->sell_price;
            }

            $unitPrice = (float) $validated['unit_price'];

            if ($medicine->stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok {$medicine->name} tidak mencukupi. Sisa stok: {$medicine->stock}.",
                ]);
            }

            $lineTotal = $quantity * $unitPrice;
            $priceNote = "Harga beli: Rp ".number_format($buyPrice, 0, ',', '.').
                ", harga jual: Rp ".number_format($unitPrice, 0, ',', '.');
            $notes = trim(($validated['notes'] ?? '')."\n".$priceNote);

            $sale = Sale::query()->create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'cashier_id' => $request->user()->id,
                'patient_name' => $validated['patient_name'] ?? null,
                'sale_type' => 'non_prescription',
                'total_items' => $quantity,
                'total_amount' => $lineTotal,
                'sold_at' => now(),
                'notes' => $notes !== '' ? $notes : null,
            ]);

            $createdSaleId = $sale->id;
            $soldMedicine = $medicine;

            SaleItem::query()->create([
                'sale_id' => $sale->id,
                'medicine_id' => $medicine->id,
                'medicine_name_snapshot' => $medicine->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'note' => 'Harga beli '.number_format($buyPrice, 0, ',', '.'),
            ]);

            $medicine->decrement('stock', $quantity);
        });

        ActivityLogger::log(
            $request->user(),
            'kasir.penjualan',
            'create_non_prescription_sale',
            'Kasir memproses transaksi non resep.',
            $soldMedicine,
            [
                'sale_id' => $createdSaleId,
                'medicine_name' => $soldMedicine?->name,
                'quantity' => (int) $validated['quantity'],
                'unit_price' => (float) $validated['unit_price'],
                'total_amount' => (float) $lineTotal,
                'patient_name' => $validated['patient_name'] ?? null,
            ]
        );

        return back()->with('status', 'Transaksi obat tanpa resep berhasil disimpan.');
    }

    public function dispensePrescription(Request $request, Prescription $prescription): RedirectResponse
    {
        $this->normalizeFormattedNumericInputs($request, [], ['markup_amount']);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
            'markup_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $dispensedPrescription = null;
        $processedSaleId = null;
        $processedTotalAmount = 0;

        DB::transaction(function () use ($request, $prescription, $validated, &$dispensedPrescription, &$processedSaleId, &$processedTotalAmount): void {
            $lockedPrescription = Prescription::query()
                ->with(['patient', 'items.medicine'])
                ->lockForUpdate()
                ->findOrFail($prescription->id);

            if ($lockedPrescription->is_dispensed) {
                throw ValidationException::withMessages([
                    'notes' => 'Resep ini sudah ditebus sebelumnya.',
                ]);
            }

            $items = PrescriptionItem::query()
                ->with('medicine')
                ->where('prescription_id', $lockedPrescription->id)
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'notes' => 'Resep belum memiliki item obat.',
                ]);
            }

            $totalItems = $items->sum('quantity');
            $totalAmount = 0;
            $markupAmount = (float) ($validated['markup_amount'] ?? 0);
            $priceNote = "Markup kasir per item: Rp ".number_format($markupAmount, 0, ',', '.');
            $notes = trim(($validated['notes'] ?? $lockedPrescription->notes ?? '')."\n".$priceNote);

            $sale = Sale::query()->create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'cashier_id' => $request->user()->id,
                'prescription_id' => $lockedPrescription->id,
                'patient_id' => $lockedPrescription->patient_id,
                'patient_name' => $lockedPrescription->patient?->name,
                'sale_type' => 'prescription',
                'total_items' => (int) $totalItems,
                'total_amount' => 0,
                'sold_at' => now(),
                'notes' => $notes !== '' ? $notes : null,
            ]);

            $processedSaleId = $sale->id;
            $dispensedPrescription = $lockedPrescription;

            foreach ($items as $item) {
                $buyPrice = (float) ($item->medicine?->buy_price ?? 0);
                if ($buyPrice <= 0) {
                    $buyPrice = (float) ($item->medicine?->sell_price ?? 0);
                }

                $unitPrice = $buyPrice + $markupAmount;
                $lineTotal = $unitPrice * (int) $item->quantity;
                $totalAmount += $lineTotal;

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'medicine_id' => $item->medicine_id,
                    'medicine_name_snapshot' => $item->medicine?->name ?? 'Obat tidak diketahui',
                    'quantity' => (int) $item->quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'note' => $item->dosage_instructions.' | beli '.number_format($buyPrice, 0, ',', '.').' + markup '.number_format($markupAmount, 0, ',', '.'),
                ]);
            }

            $sale->update([
                'total_amount' => $totalAmount,
            ]);

            $processedTotalAmount = $totalAmount;

            $lockedPrescription->update([
                'is_dispensed' => true,
                'dispensed_at' => now(),
                'dispensed_by' => $request->user()->id,
            ]);
        });

        ActivityLogger::log(
            $request->user(),
            'kasir.resep',
            'dispense_prescription',
            'Kasir memproses resep dokter menjadi transaksi penjualan.',
            $dispensedPrescription,
            [
                'sale_id' => $processedSaleId,
                'prescription_id' => $dispensedPrescription?->id,
                'patient_id' => $dispensedPrescription?->patient_id,
                'patient_name' => $dispensedPrescription?->patient?->name,
                'total_amount' => (float) $processedTotalAmount,
                'markup_amount' => (float) ($validated['markup_amount'] ?? 0),
            ]
        );

        return back()->with('status', 'Resep dokter berhasil diproses dan tercatat oleh kasir.');
    }

    public function printPrescription(Prescription $prescription): View
    {
        $prescription->load([
            'patient',
            'doctor',
            'visit',
            'items.medicine',
            'dispensedBy',
            'sale.items',
        ]);

        return view('ui.kasir.prescription-print', [
            'prescription' => $prescription,
        ]);
    }

    public function printSale(Sale $sale): View
    {
        $sale->load([
            'cashier',
            'patient',
            'prescription.doctor',
            'prescription.visit',
            'items.medicine',
        ]);

        return view('ui.kasir.sale-print', [
            'sale' => $sale,
        ]);
    }

    public function printSalesHistory(Request $request): View
    {
        $historyFilters = $this->resolveSalesHistoryFilters($request);
        $sales = $this->filteredSalesHistoryQuery($historyFilters)->get();

        $historySummary = [
            'total_transactions' => $sales->count(),
            'total_items' => (int) $sales->sum('total_items'),
            'total_amount' => (float) $sales->sum('total_amount'),
        ];

        return view('ui.kasir.sales-history-print', [
            'sales' => $sales,
            'historyFilters' => $historyFilters,
            'historySummary' => $historySummary,
            'printedAt' => now(),
        ]);
    }

    public function storeMedicine(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'stock' => ['required', 'integer', 'min:0'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0', 'gte:buy_price'],
            'unit' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $createdMedicine = Medicine::query()->create([
            'name' => $validated['name'],
            'trade_name' => $validated['trade_name'] ?? null,
            'dosage' => $validated['dosage'] ?? null,
            'category' => $validated['category'] ?? null,
            'stock' => (int) $validated['stock'],
            'buy_price' => (float) $validated['buy_price'],
            'sell_price' => (float) $validated['sell_price'],
            'unit' => $validated['unit'],
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLogger::log(
            $request->user(),
            'kasir.obat',
            'create',
            'Kasir menambahkan data obat baru.',
            $createdMedicine,
            [
                'medicine_name' => $validated['name'],
                'stock' => (int) $validated['stock'],
                'buy_price' => (float) $validated['buy_price'],
                'sell_price' => (float) $validated['sell_price'],
                'unit' => $validated['unit'],
            ]
        );

        return back()->with('status', 'Data obat baru berhasil ditambahkan.');
    }

    public function updateMedicine(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'stock' => ['required', 'integer', 'min:0'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0', 'gte:buy_price'],
            'unit' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = [
            'name' => $medicine->name,
            'stock' => (int) $medicine->stock,
            'buy_price' => (float) $medicine->buy_price,
            'sell_price' => (float) $medicine->sell_price,
            'unit' => $medicine->unit,
            'is_active' => (bool) $medicine->is_active,
        ];

        $medicine->update([
            'name' => $validated['name'],
            'trade_name' => $validated['trade_name'] ?? null,
            'dosage' => $validated['dosage'] ?? null,
            'category' => $validated['category'] ?? null,
            'stock' => (int) $validated['stock'],
            'buy_price' => (float) $validated['buy_price'],
            'sell_price' => (float) $validated['sell_price'],
            'unit' => $validated['unit'],
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLogger::log(
            $request->user(),
            'kasir.obat',
            'update',
            "Kasir memperbarui data obat {$medicine->name}.",
            $medicine,
            [
                'before' => $before,
                'after' => [
                    'name' => $medicine->name,
                    'stock' => (int) $medicine->stock,
                    'buy_price' => (float) $medicine->buy_price,
                    'sell_price' => (float) $medicine->sell_price,
                    'unit' => $medicine->unit,
                    'is_active' => (bool) $medicine->is_active,
                ],
            ]
        );

        return back()->with('status', "Data obat {$medicine->name} berhasil diperbarui.");
    }

    public function destroyMedicine(Medicine $medicine): RedirectResponse
    {
        if ($medicine->prescriptionItems()->exists() || $medicine->saleItems()->exists()) {
            $medicine->update(['is_active' => false]);

            ActivityLogger::log(
                request()->user(),
                'kasir.obat',
                'deactivate',
                "Kasir menonaktifkan obat {$medicine->name}.",
                $medicine,
                [
                    'reason' => 'has_related_transactions',
                ]
            );

            return back()->with(
                'status',
                "Obat {$medicine->name} tidak bisa dihapus permanen karena sudah dipakai. Statusnya diubah menjadi nonaktif."
            );
        }

        $name = $medicine->name;

        try {
            $medicine->delete();

            ActivityLogger::log(
                request()->user(),
                'kasir.obat',
                'delete',
                "Kasir menghapus obat {$name}.",
                $medicine,
                [
                    'medicine_name' => $name,
                ]
            );
        } catch (QueryException) {
            return back()->with('error', "Data obat {$name} gagal dihapus.");
        }

        return back()->with('status', "Data obat {$name} berhasil dihapus.");
    }

    private function prescriptionsQuery(): Builder
    {
        return Prescription::query()
            ->with(['patient', 'doctor', 'visit', 'items.medicine', 'dispensedBy'])
            ->orderByDesc('prescribed_at')
            ->orderByDesc('id');
    }

    private function medicinesQuery(): Builder
    {
        return Medicine::query()
            ->orderByRaw('stock > 0 DESC')
            ->orderBy('name');
    }

    private function salesQuery(): Builder
    {
        return Sale::query()
            ->with(['cashier', 'prescription.patient', 'items.medicine'])
            ->orderByDesc('sold_at')
            ->orderByDesc('id');
    }

    private function filteredSalesHistoryQuery(array $historyFilters): Builder
    {
        $query = $this->salesQuery();

        if ($historyFilters['q'] !== '') {
            $search = $historyFilters['q'];

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('patient_name', 'like', "%{$search}%")
                    ->orWhereHas('cashier', function (Builder $cashierQuery) use ($search): void {
                        $cashierQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($historyFilters['type'] !== 'all') {
            $query->where('sale_type', $historyFilters['type']);
        }

        if ($historyFilters['from'] !== null) {
            $query->whereDate('sold_at', '>=', $historyFilters['from']);
        }

        if ($historyFilters['to'] !== null) {
            $query->whereDate('sold_at', '<=', $historyFilters['to']);
        }

        return $query;
    }

    private function resolveSalesHistoryFilters(Request $request): array
    {
        $allowedTypes = ['all', 'prescription', 'non_prescription'];
        $allowedPerPage = $this->historyPerPageOptions();

        $saleType = (string) $request->query('history_type', 'all');
        if (! in_array($saleType, $allowedTypes, true)) {
            $saleType = 'all';
        }

        $q = trim((string) $request->query('history_q', ''));
        $from = trim((string) $request->query('history_from', ''));
        $to = trim((string) $request->query('history_to', ''));
        $perPage = (int) $request->query('history_per_page', 25);

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 25;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = '';
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = '';
        }

        if ($from !== '' && $to !== '' && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [
            'q' => $q,
            'type' => $saleType,
            'from' => $from !== '' ? $from : null,
            'to' => $to !== '' ? $to : null,
            'per_page' => $perPage,
        ];
    }

    private function historyPerPageOptions(): array
    {
        return [10, 25, 50, 100];
    }

    private function normalizeFormattedNumericInputs(Request $request, array $integerFields = [], array $decimalFields = []): void
    {
        $payload = [];

        foreach ($integerFields as $field) {
            if (! $request->exists($field)) {
                continue;
            }

            $payload[$field] = $this->normalizeIntegerInput($request->input($field));
        }

        foreach ($decimalFields as $field) {
            if (! $request->exists($field)) {
                continue;
            }

            $payload[$field] = $this->normalizeDecimalInput($request->input($field));
        }

        if ($payload !== []) {
            $request->merge($payload);
        }
    }

    private function normalizeIntegerInput(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^\d]/', '', trim((string) $value)) ?? '';

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeDecimalInput(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^\d]/', '', trim((string) $value)) ?? '';

        return $normalized === '' ? null : $normalized;
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd');

        $lastNumber = Sale::query()
            ->where('invoice_number', 'like', $prefix.'-%')
            ->count() + 1;

        return $prefix.'-'.str_pad((string) $lastNumber, 4, '0', STR_PAD_LEFT);
    }
}
