<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicinePurchaseLog;
use App\Services\InventoryMutationService;
use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly InventoryMutationService $inventoryMutationService,
    ) {
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isMasterAdmin()) {
            return redirect()->route('master-admin.dashboard');
        }

        $today = now()->toDateString();
        $thirtyDaysAhead = now()->addDays(30)->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $recentMedicinesLimit = $this->resolvePerPage($request, 'medicines_limit', 10, 5, 1000);
        $purchasePerPage = $this->resolvePerPage($request, 'purchase_per_page', 25, 5, 1000);
        $topSourcesLimit = $this->resolvePerPage($request, 'sources_limit', 5, 5, 1000);

        $stats = [
            'total_medicines' => Medicine::query()->count(),
            'active_medicines' => Medicine::query()->where('is_active', true)->count(),
            'low_stock_medicines' => Medicine::query()->where('stock', '<=', 10)->count(),
            'expiring_soon_medicines' => Medicine::query()
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $thirtyDaysAhead)
                ->count(),
            'today_purchase_entries' => MedicinePurchaseLog::query()
                ->whereDate('purchased_at', $today)
                ->count(),
            'today_purchase_spending' => (float) MedicinePurchaseLog::query()
                ->whereDate('purchased_at', $today)
                ->selectRaw('COALESCE(SUM(quantity * buy_price), 0) as total_spending')
                ->value('total_spending'),
            'month_purchase_spending' => (float) MedicinePurchaseLog::query()
                ->whereDate('purchased_at', '>=', $monthStart)
                ->selectRaw('COALESCE(SUM(quantity * buy_price), 0) as total_spending')
                ->value('total_spending'),
            'purchase_sources_count' => MedicinePurchaseLog::query()
                ->whereNotNull('purchase_source')
                ->where('purchase_source', '!=', '')
                ->distinct()
                ->count('purchase_source'),
        ];

        $expiringAlertMedicines = Medicine::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $thirtyDaysAhead)
            ->orderBy('expiry_date')
            ->orderBy('name')
            ->limit(12)
            ->get([
                'id',
                'name',
                'trade_name',
                'stock',
                'unit',
                'expiry_date',
            ]);

        $recentMedicines = Medicine::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit($recentMedicinesLimit)
            ->get();

        $recentPurchaseLogs = MedicinePurchaseLog::query()
            ->with(['medicine', 'createdBy'])
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate($purchasePerPage, ['*'], 'purchase_page')
            ->withQueryString();

        $topPurchaseSources = MedicinePurchaseLog::query()
            ->selectRaw('purchase_source, COUNT(*) as total_logs, COALESCE(SUM(quantity * buy_price), 0) as total_spending')
            ->whereNotNull('purchase_source')
            ->where('purchase_source', '!=', '')
            ->groupBy('purchase_source')
            ->orderByDesc('total_spending')
            ->limit($topSourcesLimit)
            ->get();

        return view('ui.admin-dashboard', [
            'stats' => $stats,
            'expiringAlertMedicines' => $expiringAlertMedicines,
            'recentMedicines' => $recentMedicines,
            'recentPurchaseLogs' => $recentPurchaseLogs,
            'topPurchaseSources' => $topPurchaseSources,
            'perPageOptions' => $this->perPageOptions(),
            'tableControls' => [
                'medicines_limit' => $recentMedicinesLimit,
                'purchase_per_page' => $purchasePerPage,
                'sources_limit' => $topSourcesLimit,
            ],
            'admin' => $request->user(),
        ]);
    }

    public function documentation(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $source = trim((string) $request->query('source', ''));
        $fromDate = (string) $request->query('from', '');
        $toDate = (string) $request->query('to', '');
        $perPage = $this->resolvePerPage($request, 'per_page', 50, 5, 1000);

        $this->syncDocumentationWithMasterPhotos($request->user()?->id);

        $photoLogs = MedicinePurchaseLog::query()
            ->with(['medicine', 'createdBy'])
            ->whereNotNull('photo_path')
            ->where('photo_path', '!=', '')
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder
                    ->where(function (Builder $query) use ($search): void {
                        $query
                            ->where('purchase_source', 'like', "%{$search}%")
                            ->orWhere('notes', 'like', "%{$search}%")
                            ->orWhereHas('medicine', function (Builder $medicineQuery) use ($search): void {
                                $medicineQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('barcode', 'like', "%{$search}%")
                                    ->orWhere('trade_name', 'like', "%{$search}%");
                            });
                    });
            })
            ->when($source !== '', function (Builder $builder) use ($source): void {
                $builder->where('purchase_source', $source);
            })
            ->when($fromDate !== '', function (Builder $builder) use ($fromDate): void {
                $builder->whereDate('purchased_at', '>=', $fromDate);
            })
            ->when($toDate !== '', function (Builder $builder) use ($toDate): void {
                $builder->whereDate('purchased_at', '<=', $toDate);
            })
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $sourceOptions = MedicinePurchaseLog::query()
            ->whereNotNull('purchase_source')
            ->where('purchase_source', '!=', '')
            ->distinct()
            ->orderBy('purchase_source')
            ->pluck('purchase_source');

        $stats = [
            'total_documents' => MedicinePurchaseLog::query()
                ->whereNotNull('photo_path')
                ->where('photo_path', '!=', '')
                ->count(),
            'today_documents' => MedicinePurchaseLog::query()
                ->whereNotNull('photo_path')
                ->where('photo_path', '!=', '')
                ->whereDate('purchased_at', now()->toDateString())
                ->count(),
            'document_sources' => MedicinePurchaseLog::query()
                ->whereNotNull('photo_path')
                ->where('photo_path', '!=', '')
                ->whereNotNull('purchase_source')
                ->where('purchase_source', '!=', '')
                ->distinct()
                ->count('purchase_source'),
            'document_purchase_value' => (float) MedicinePurchaseLog::query()
                ->whereNotNull('photo_path')
                ->where('photo_path', '!=', '')
                ->selectRaw('COALESCE(SUM(quantity * buy_price), 0) as total_spending')
                ->value('total_spending'),
        ];

        return view('ui.admin-dokumentasi', [
            'photoLogs' => $photoLogs,
            'sourceOptions' => $sourceOptions,
            'stats' => $stats,
            'filters' => [
                'q' => $search,
                'source' => $source,
                'from' => $fromDate,
                'to' => $toDate,
                'per_page' => $perPage,
            ],
            'perPageOptions' => $this->perPageOptions(),
            'admin' => $request->user(),
        ]);
    }

    public function medicines(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $status = (string) $request->query('status', 'all');
        $source = (string) $request->query('source', 'all');
        $perPage = $this->resolvePerPage($request, 'per_page', 25, 5, 1000);
        $hasEntrySourceColumn = Schema::hasColumn('medicines', 'entry_source');
        $today = now()->toDateString();
        $expiringUntil = now()->addDays(30)->toDateString();

        $medicines = $this->medicinesQuery()
            ->with(['purchaseLogs' => function ($query): void {
                $query->orderByDesc('purchased_at')->orderByDesc('id');
            }])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('barcode', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%")
                        ->orWhere('dosage', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%")
                        ->orWhere('purchase_source', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', function (Builder $builder) use ($category): void {
                $builder->where('category', $category);
            })
            ->when($status === 'active', function (Builder $builder): void {
                $builder->where('is_active', true);
            })
            ->when($status === 'inactive', function (Builder $builder): void {
                $builder->where('is_active', false);
            })
            ->when($status === 'low_stock', function (Builder $builder): void {
                $builder
                    ->where('stock', '>', 0)
                    ->where('stock', '<=', 10);
            })
            ->when($status === 'expiring', function (Builder $builder) use ($today, $expiringUntil): void {
                $builder->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', $today)
                    ->whereDate('expiry_date', '<=', $expiringUntil);
            })
            ->when($status === 'expired', function (Builder $builder) use ($today): void {
                $builder->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<', $today);
            })
            ->when(
                $hasEntrySourceColumn
                && in_array($source, [Medicine::ENTRY_SOURCE_BARCODE, Medicine::ENTRY_SOURCE_MANUAL], true),
                function (Builder $builder) use ($source): void {
                    $builder->where('entry_source', $source);
                }
            )
            ->when($source === 'with_photo', function (Builder $builder): void {
                $builder->whereNotNull('photo_path')->where('photo_path', '!=', '');
            })
            ->when($source === 'without_photo', function (Builder $builder): void {
                $builder->where(function (Builder $query): void {
                    $query->whereNull('photo_path')->orWhere('photo_path', '');
                });
            })
            ->paginate($perPage)
            ->withQueryString();

        $categories = Medicine::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $stats = [
            'total_medicines' => Medicine::query()->count(),
            'ready_medicines' => Medicine::query()->where('stock', '>', 0)->count(),
            'not_ready_medicines' => Medicine::query()->where('stock', '<=', 0)->count(),
            'low_stock_medicines' => Medicine::query()->where('stock', '<=', 10)->count(),
        ];

        return view('ui.data-obat', [
            'medicines' => $medicines,
            'categories' => $categories,
            'filters' => [
                'q' => $search,
                'category' => $category,
                'status' => $status,
                'source' => $source,
                'per_page' => $perPage,
            ],
            'hasEntrySourceColumn' => $hasEntrySourceColumn,
            'stats' => $stats,
            'perPageOptions' => $this->perPageOptions(),
            'admin' => $request->user(),
        ]);
    }

    public function storeMedicine(Request $request): RedirectResponse
    {
        $this->normalizeFormattedNumericInputs($request, ['stock'], ['buy_price', 'sell_price']);

        $validated = $request->validate([
            'barcode' => ['nullable', 'string', 'max:120', 'unique:medicines,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'stock' => ['required', 'integer', 'min:0'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'purchase_source' => ['required', 'string', 'max:255'],
            'sell_price' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['required', 'date'],
            'unit' => ['required', 'string', 'max:30'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,avif', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama obat wajib diisi.',
            'stock.required' => 'Stok awal wajib diisi.',
            'buy_price.required' => 'Harga beli wajib diisi.',
            'purchase_source.required' => 'Outlet atau tempat beli obat wajib diisi.',
            'expiry_date.required' => 'Tanggal kadaluarsa wajib diisi.',
            'photo.mimes' => 'Format foto belum didukung. Gunakan JPG, PNG, WEBP, HEIC, HEIF, atau AVIF.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('medicines/photos', 'public')
            : null;

        $createdMedicine = null;

        DB::transaction(function () use ($request, $validated, $photoPath, &$createdMedicine): void {
            $medicinePayload = [
                'name' => $validated['name'],
                'barcode' => $validated['barcode'] ?? null,
                'trade_name' => $validated['trade_name'] ?? null,
                'dosage' => $validated['dosage'] ?? null,
                'category' => $validated['category'] ?? null,
                'stock' => (int) $validated['stock'],
                'buy_price' => (float) $validated['buy_price'],
                'sell_price' => array_key_exists('sell_price', $validated) ? (float) $validated['sell_price'] : 0,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'photo_path' => $photoPath,
                'unit' => $validated['unit'],
                'purchase_source' => $validated['purchase_source'],
                'is_active' => $request->boolean('is_active', true),
            ];

            if (Schema::hasColumn('medicines', 'entry_source')) {
                $medicinePayload['entry_source'] = Medicine::ENTRY_SOURCE_MANUAL;
            }

            $createdMedicine = Medicine::query()->create($medicinePayload);

            MedicinePurchaseLog::query()->create([
                'medicine_id' => $createdMedicine->id,
                'created_by' => $request->user()->id,
                'quantity' => (int) $validated['stock'],
                'buy_price' => (float) $validated['buy_price'],
                'purchase_source' => $validated['purchase_source'] ?? 'Input master admin',
                'expiry_date' => $validated['expiry_date'] ?? null,
                'photo_path' => $photoPath,
                'purchased_at' => now(),
                'notes' => 'Input obat baru dari admin.',
            ]);
        });

        ActivityLogger::log(
            $request->user(),
            'admin.master_obat',
            'create',
            'Admin menambahkan obat baru ke master.',
            $createdMedicine,
            [
                'medicine_name' => $validated['name'],
                'barcode' => $validated['barcode'] ?? null,
                'stock' => (int) $validated['stock'],
                'unit' => $validated['unit'],
                'buy_price' => (float) $validated['buy_price'],
                'sell_price' => (float) ($validated['sell_price'] ?? 0),
                'purchase_source' => $validated['purchase_source'],
                'expiry_date' => $validated['expiry_date'] ?? null,
            ]
        );

        return back()->with('status', 'Data obat baru berhasil ditambahkan ke master.');
    }

    public function updateMedicine(Request $request, Medicine $medicine): RedirectResponse
    {
        $this->normalizeFormattedNumericInputs($request, ['stock'], ['buy_price', 'sell_price']);
        $hasNewPhotoUpload = $request->hasFile('photo');

        $incomingPurchaseSource = trim((string) $request->input('purchase_source', ''));
        if ($incomingPurchaseSource === '') {
            $fallbackPurchaseSource = trim((string) ($medicine->purchase_source ?? ''));
            if ($fallbackPurchaseSource === '') {
                $fallbackPurchaseSource = trim((string) ($medicine->purchaseLogs()
                    ->orderByDesc('purchased_at')
                    ->orderByDesc('id')
                    ->value('purchase_source') ?? ''));
            }

            $request->merge([
                'purchase_source' => $fallbackPurchaseSource !== '' ? $fallbackPurchaseSource : 'Update data obat',
            ]);
        }

        $validated = $request->validate([
            'barcode' => ['nullable', 'string', 'max:120', Rule::unique('medicines', 'barcode')->ignore($medicine->id)],
            'name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'stock' => ['required', 'integer', 'min:0'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'purchase_source' => ['required', 'string', 'max:255'],
            'sell_price' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['required', 'date'],
            'unit' => ['required', 'string', 'max:30'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,avif', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'purchase_source.required' => 'Outlet atau tempat beli obat wajib diisi.',
            'expiry_date.required' => 'Tanggal kadaluarsa wajib diisi.',
        ]);

        $photoPath = $medicine->photo_path;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('medicines/photos', 'public');
        }

        $actor = $request->user();
        $medicineUpdatePayload = [
            'name' => $validated['name'],
            'barcode' => $validated['barcode'] ?? null,
            'trade_name' => $validated['trade_name'] ?? null,
            'dosage' => $validated['dosage'] ?? null,
            'category' => $validated['category'] ?? null,
            'stock' => (int) $validated['stock'],
            'buy_price' => (float) $validated['buy_price'],
            'sell_price' => array_key_exists('sell_price', $validated) ? (float) $validated['sell_price'] : 0,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'photo_path' => $photoPath,
            'unit' => $validated['unit'],
            'purchase_source' => $validated['purchase_source'],
            'is_active' => $request->boolean('is_active'),
            'has_new_photo_upload' => $hasNewPhotoUpload,
        ];
        $previousSnapshot = $this->inventoryMutationService->medicineSnapshot($medicine);

        $result = $this->inventoryMutationService->updateMedicineMaster($actor, $medicine, $medicineUpdatePayload);
        $updatedMedicine = $result['medicine'];
        $documentationLogCreated = (bool) $result['documentation_log_created'];

        ActivityLogger::log(
            $actor,
            'admin.master_obat',
            'update',
            "Admin memperbarui data obat {$updatedMedicine->name}.",
            $updatedMedicine,
            [
                'before' => $previousSnapshot,
                'after' => $this->inventoryMutationService->medicineSnapshot($updatedMedicine),
            ]
        );

        $statusMessage = "Data obat {$updatedMedicine->name} berhasil diperbarui.";
        if ($documentationLogCreated) {
            $statusMessage .= ' Dokumentasi foto otomatis ditambahkan.';
        }

        return back()->with('status', $statusMessage);
    }

    public function destroyMedicine(Medicine $medicine): RedirectResponse
    {
        if (
            $medicine->prescriptionItems()->exists() ||
            $medicine->saleItems()->exists() ||
            $medicine->purchaseLogs()->exists()
        ) {
            $medicine->update(['is_active' => false]);

            ActivityLogger::log(
                request()->user(),
                'admin.master_obat',
                'deactivate',
                "Admin menonaktifkan obat {$medicine->name}.",
                $medicine,
                [
                    'reason' => 'has_related_transactions',
                ]
            );

            return back()->with(
                'status',
                "Obat {$medicine->name} tidak dihapus permanen karena sudah dipakai. Status diubah menjadi nonaktif."
            );
        }

        $name = $medicine->name;

        try {
            if ($medicine->photo_path && Storage::disk('public')->exists($medicine->photo_path)) {
                Storage::disk('public')->delete($medicine->photo_path);
            }

            $medicine->delete();

            ActivityLogger::log(
                request()->user(),
                'admin.master_obat',
                'delete',
                "Admin menghapus obat {$name} dari master.",
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

    public function warehouse(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $status = (string) $request->query('status', 'all');
        $source = (string) $request->query('source', 'all');
        $masterPerPage = $this->resolvePerPage($request, 'master_per_page', 25, 5, 1000);
        $purchasePerPage = $this->resolvePerPage($request, 'purchase_per_page', 50, 5, 1000);
        $hasEntrySourceColumn = Schema::hasColumn('medicines', 'entry_source');
        $today = now()->toDateString();
        $expiringUntil = now()->addDays(30)->toDateString();

        $masterQuery = $this->medicinesQuery()
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('barcode', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%")
                        ->orWhere('dosage', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%")
                        ->orWhere('purchase_source', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', function (Builder $builder) use ($category): void {
                $builder->where('category', $category);
            })
            ->when($status === 'active', function (Builder $builder): void {
                $builder->where('is_active', true);
            })
            ->when($status === 'inactive', function (Builder $builder): void {
                $builder->where('is_active', false);
            })
            ->when($status === 'low_stock', function (Builder $builder): void {
                $builder
                    ->where('stock', '>', 0)
                    ->where('stock', '<=', 10);
            })
            ->when($status === 'expiring', function (Builder $builder) use ($today, $expiringUntil): void {
                $builder->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', $today)
                    ->whereDate('expiry_date', '<=', $expiringUntil);
            })
            ->when($status === 'expired', function (Builder $builder) use ($today): void {
                $builder->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<', $today);
            })
            ->when(
                $hasEntrySourceColumn
                && in_array($source, [Medicine::ENTRY_SOURCE_BARCODE, Medicine::ENTRY_SOURCE_MANUAL], true),
                function (Builder $builder) use ($source): void {
                    $builder->where('entry_source', $source);
                }
            )
            ->when($source === 'with_photo', function (Builder $builder): void {
                $builder->whereNotNull('photo_path')->where('photo_path', '!=', '');
            })
            ->when($source === 'without_photo', function (Builder $builder): void {
                $builder->where(function (Builder $query): void {
                    $query->whereNull('photo_path')->orWhere('photo_path', '');
                });
            });

        $medicines = $masterQuery
            ->paginate($masterPerPage, ['*'], 'master_page')
            ->withQueryString();

        $categories = Medicine::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $inventoryMedicines = $this->medicinesQuery()
            ->get();

        $purchaseLogs = MedicinePurchaseLog::query()
            ->with(['medicine', 'createdBy'])
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate($purchasePerPage, ['*'], 'purchase_page')
            ->withQueryString();

        $stats = [
            'total_stock_units' => (int) Medicine::query()->sum('stock'),
            'ready_medicines' => Medicine::query()->where('stock', '>', 0)->count(),
            'not_ready_medicines' => Medicine::query()->where('stock', '<=', 0)->count(),
            'expiring_soon_medicines' => Medicine::query()
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
                ->count(),
            'purchase_entries' => MedicinePurchaseLog::query()->count(),
        ];

        return view('ui.admin-gudang', [
            'medicines' => $medicines,
            'categories' => $categories,
            'filters' => [
                'q' => $search,
                'category' => $category,
                'status' => $status,
                'source' => $source,
                'master_per_page' => $masterPerPage,
                'purchase_per_page' => $purchasePerPage,
            ],
            'hasEntrySourceColumn' => $hasEntrySourceColumn,
            'inventoryMedicines' => $inventoryMedicines,
            'purchaseLogs' => $purchaseLogs,
            'stats' => $stats,
            'perPageOptions' => $this->perPageOptions(),
            'admin' => $request->user(),
        ]);
    }

    public function storeWarehousePurchase(Request $request): RedirectResponse
    {
        $this->normalizeFormattedNumericInputs($request, ['quantity'], ['buy_price']);

        $validated = $request->validate([
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'purchase_source' => ['required', 'string', 'max:255'],
            'expiry_date' => ['required', 'date'],
            'purchased_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,avif', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'medicine_id.required' => 'Obat wajib dipilih.',
            'quantity.required' => 'Jumlah pembelian wajib diisi.',
            'buy_price.required' => 'Harga beli wajib diisi.',
            'purchase_source.required' => 'Outlet atau tempat beli obat wajib diisi.',
            'expiry_date.required' => 'Tanggal kadaluarsa wajib diisi.',
            'photo.mimes' => 'Format foto belum didukung. Gunakan JPG, PNG, WEBP, HEIC, HEIF, atau AVIF.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('medicines/photos', 'public')
            : null;

        $actor = $request->user();
        $payload = [
            'medicine_id' => (int) $validated['medicine_id'],
            'quantity' => (int) $validated['quantity'],
            'buy_price' => (float) $validated['buy_price'],
            'purchase_source' => $validated['purchase_source'],
            'expiry_date' => $validated['expiry_date'] ?? null,
            'purchased_at' => $validated['purchased_at'] ?? now()->toDateTimeString(),
            'photo_path' => $photoPath,
            'notes' => $validated['notes'] ?? null,
        ];

        $updatedMedicine = $this->inventoryMutationService->createWarehousePurchase($actor, $payload);

        ActivityLogger::log(
            $actor,
            'admin.gudang',
            'stock_in',
            "Admin menambah stok gudang untuk obat ".($updatedMedicine?->name ?? '#'.$validated['medicine_id']).'.',
            $updatedMedicine,
            [
                'quantity' => (int) $validated['quantity'],
                'buy_price' => (float) $validated['buy_price'],
                'purchase_source' => $validated['purchase_source'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'purchased_at' => $validated['purchased_at'] ?? now()->toDateTimeString(),
            ]
        );

        if ($request->boolean('reset_to_barcode')) {
            return redirect()
                ->route('admin.barcode.index')
                ->with('status', 'Berhasil menambahkan stok ke gudang. Silakan scan atau input barang baru.');
        }

        return back()->with('status', 'Data pembelian gudang berhasil disimpan dan stok sudah diperbarui.');
    }

    public function updateWarehousePurchase(Request $request, MedicinePurchaseLog $purchaseLog): RedirectResponse
    {
        $this->normalizeFormattedNumericInputs($request, ['quantity'], ['buy_price']);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'purchase_source' => ['required', 'string', 'max:255'],
            'expiry_date' => ['required', 'date'],
            'purchased_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,avif', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'quantity.required' => 'Jumlah pembelian wajib diisi.',
            'buy_price.required' => 'Harga beli wajib diisi.',
            'purchase_source.required' => 'Outlet atau tempat beli obat wajib diisi.',
            'expiry_date.required' => 'Tanggal kadaluarsa wajib diisi.',
            'photo.mimes' => 'Format foto belum didukung. Gunakan JPG, PNG, WEBP, HEIC, HEIF, atau AVIF.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $photoPath = $purchaseLog->photo_path;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('medicines/photos', 'public');
        }

        $actor = $request->user();
        $payload = [
            'purchase_log_id' => $purchaseLog->id,
            'quantity' => (int) $validated['quantity'],
            'buy_price' => (float) $validated['buy_price'],
            'purchase_source' => $validated['purchase_source'],
            'expiry_date' => $validated['expiry_date'] ?? null,
            'purchased_at' => $validated['purchased_at'] ?? optional($purchaseLog->purchased_at)->toDateTimeString(),
            'photo_path' => $photoPath,
            'notes' => $validated['notes'] ?? null,
        ];

        $updatedMedicine = $this->inventoryMutationService->updateWarehousePurchase($actor, $purchaseLog, $payload);

        ActivityLogger::log(
            $actor,
            'admin.gudang',
            'stock_in_update',
            "Admin mengubah catatan pembelian gudang untuk obat ".($updatedMedicine?->name ?? '-').'.',
            $updatedMedicine,
            [
                'purchase_log_id' => $purchaseLog->id,
                'quantity' => (int) $validated['quantity'],
                'buy_price' => (float) $validated['buy_price'],
                'purchase_source' => $validated['purchase_source'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'purchased_at' => $validated['purchased_at'] ?? optional($purchaseLog->purchased_at)->toDateTimeString(),
            ]
        );

        return back()->with('status', 'Data pembelian gudang berhasil diperbarui.');
    }

    private function medicinesQuery(): Builder
    {
        return Medicine::query()
            ->orderByRaw('stock > 0 DESC')
            ->orderBy('name');
    }

    private function perPageOptions(): array
    {
        return [5, 10, 25, 50, 100, 250, 500, 1000];
    }

    private function syncDocumentationWithMasterPhotos(?int $actorId = null): void
    {
        $medicinesWithPhoto = Medicine::query()
            ->whereNotNull('photo_path')
            ->where('photo_path', '!=', '')
            ->get([
                'id',
                'stock',
                'buy_price',
                'purchase_source',
                'expiry_date',
                'photo_path',
                'updated_at',
            ]);

        foreach ($medicinesWithPhoto as $medicine) {
            $photoPath = trim((string) ($medicine->photo_path ?? ''));
            if ($photoPath === '') {
                continue;
            }

            $alreadyExists = MedicinePurchaseLog::query()
                ->where('medicine_id', $medicine->id)
                ->where('photo_path', $photoPath)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $latestLog = MedicinePurchaseLog::query()
                ->where('medicine_id', $medicine->id)
                ->orderByDesc('purchased_at')
                ->orderByDesc('id')
                ->first();

            $quantity = (int) ($latestLog?->quantity ?? 0);
            if ($quantity < 1) {
                $quantity = max(1, (int) $medicine->stock);
            }

            $buyPrice = (float) ($latestLog?->buy_price ?? $medicine->buy_price ?? 0);
            $purchaseSource = trim((string) ($latestLog?->purchase_source ?? $medicine->purchase_source ?? ''));
            if ($purchaseSource === '') {
                $purchaseSource = 'Sinkronisasi dokumentasi';
            }

            MedicinePurchaseLog::query()->create([
                'medicine_id' => $medicine->id,
                'created_by' => $actorId,
                'quantity' => $quantity,
                'buy_price' => $buyPrice,
                'purchase_source' => $purchaseSource,
                'expiry_date' => $latestLog?->expiry_date ?? $medicine->expiry_date,
                'photo_path' => $photoPath,
                'purchased_at' => $medicine->updated_at ?? now(),
                'notes' => 'Dokumentasi foto tersinkron otomatis dari foto master obat.',
            ]);
        }
    }

    private function resolvePerPage(
        Request $request,
        string $queryKey,
        int $default = 25,
        int $min = 5,
        int $max = 1000
    ): int {
        $rawValue = $request->query($queryKey);

        if ($rawValue === null || $rawValue === '') {
            return $default;
        }

        $validated = filter_var($rawValue, FILTER_VALIDATE_INT);

        if ($validated === false || $validated < $min || $validated > $max) {
            return $default;
        }

        return (int) $validated;
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

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(' ', '', $normalized);

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $normalized = str_replace('.', '', $normalized);
        }

        return $normalized === '' ? null : $normalized;
    }
}
