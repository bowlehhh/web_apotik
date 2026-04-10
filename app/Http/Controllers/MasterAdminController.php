<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\Medicine;
use App\Models\MedicinePurchaseLog;
use App\Models\Sale;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
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

class MasterAdminController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $approvalWorkflowService,
        private readonly InventoryMutationService $inventoryMutationService,
    ) {
    }

    public function dashboard(Request $request): View
    {
        $today = now()->toDateString();

        $purchaseLogs = MedicinePurchaseLog::query()
            ->with(['medicine', 'createdBy'])
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $recentSales = Sale::query()
            ->with(['cashier'])
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $topMedicines = Medicine::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $masterStats = [
            'purchase_entries' => MedicinePurchaseLog::query()->count(),
            'purchase_spending' => (float) MedicinePurchaseLog::query()
                ->selectRaw('COALESCE(SUM(quantity * buy_price), 0) as total_spending')
                ->value('total_spending'),
            'sales_total' => (float) Sale::query()
                ->selectRaw('COALESCE(SUM(total_amount), 0) as total_sales')
                ->value('total_sales'),
            'sales_transactions' => Sale::query()->count(),
            'today_purchases' => MedicinePurchaseLog::query()
                ->whereDate('purchased_at', $today)
                ->count(),
            'today_sales' => Sale::query()
                ->whereDate('sold_at', $today)
                ->count(),
            'low_stock_medicines' => Medicine::query()
                ->where('stock', '<=', 10)
                ->count(),
            'active_users' => User::query()->where('is_active', true)->count(),
            'inactive_users' => User::query()->where('is_active', false)->count(),
        ];

        return view('ui.master-admin-dashboard', [
            'purchaseLogs' => $purchaseLogs,
            'recentSales' => $recentSales,
            'topMedicines' => $topMedicines,
            'masterStats' => $masterStats,
        ]);
    }

    public function activities(Request $request): View
    {
        $perPage = $this->resolvePerPage($request, 'per_page', 50, 10, 500);

        $recentActivities = ActivityLog::query()
            ->with('actor')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $activityRoleStats = [];
        foreach (User::roles() as $role) {
            $activityRoleStats[$role] = ActivityLog::query()->where('actor_role', $role)->count();
        }

        return view('ui.master-admin-activities', [
            'recentActivities' => $recentActivities,
            'activityRoleStats' => $activityRoleStats,
            'roleLabels' => User::roleLabels(),
            'perPageOptions' => $this->perPageOptions(),
            'perPage' => $perPage,
        ]);
    }

    public function rolePermission(): View
    {
        return view('ui.master-admin-role-permission', [
            'roleLabels' => User::roleLabels(),
            'permissionMatrix' => (array) config('rbac.role_permissions', []),
        ]);
    }

    public function changePasswordPage(Request $request): View
    {
        return view('ui.master-admin-password-change', [
            'currentUser' => $request->user(),
        ]);
    }

    public function updateOwnPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        ActivityLogger::log(
            $request->user(),
            'master_admin.security',
            'change_own_password',
            'Master admin mengganti password akun sendiri.',
            $request->user(),
        );

        return back()->with('status', 'Password akun Anda berhasil diperbarui.');
    }

    public function resetPasswordPage(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $perPage = $this->resolvePerPage($request, 'per_page', 25, 10, 250);

        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->orderByRaw($this->roleSortExpression())
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('ui.master-admin-password-reset', [
            'users' => $users,
            'roleLabels' => User::roleLabels(),
            'perPageOptions' => [10, 25, 50, 100, 250],
            'filters' => [
                'q' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function medicines(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $status = (string) $request->query('status', 'all');
        $source = (string) $request->query('source', 'all');
        $perPage = $this->resolvePerPage($request, 'per_page', 25, 5, 500);
        $hasEntrySourceColumn = Schema::hasColumn('medicines', 'entry_source');
        $today = now()->toDateString();
        $expiringUntil = now()->addDays(30)->toDateString();

        $medicines = Medicine::query()
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
            ->orderByRaw('stock > 0 DESC')
            ->orderBy('name')
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
            'expiring_soon_medicines' => Medicine::query()
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $expiringUntil)
                ->count(),
        ];

        return view('ui.master-admin-medicines', [
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
                'purchase_source' => $validated['purchase_source'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'photo_path' => $photoPath,
                'purchased_at' => now(),
                'notes' => 'Input obat baru dari master admin.',
            ]);
        });

        ActivityLogger::log(
            $request->user(),
            'master_admin.master_obat',
            'create',
            "Master admin menambahkan obat baru {$validated['name']}.",
            $createdMedicine,
            [
                'medicine_name' => $validated['name'],
                'barcode' => $validated['barcode'] ?? null,
                'stock' => (int) $validated['stock'],
                'unit' => $validated['unit'],
                'buy_price' => (float) $validated['buy_price'],
                'sell_price' => (float) ($validated['sell_price'] ?? 0),
                'purchase_source' => $validated['purchase_source'],
            ]
        );

        return back()->with('status', 'Data obat master berhasil ditambahkan.');
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
            'master_admin.master_obat',
            'update',
            "Master admin memperbarui data obat {$updatedMedicine->name}.",
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

    public function destroyMedicine(Request $request, Medicine $medicine): RedirectResponse
    {
        if (
            $medicine->prescriptionItems()->exists() ||
            $medicine->saleItems()->exists() ||
            $medicine->purchaseLogs()->exists()
        ) {
            $medicine->update(['is_active' => false]);

            ActivityLogger::log(
                $request->user(),
                'master_admin.master_obat',
                'deactivate',
                "Master admin menonaktifkan obat {$medicine->name}.",
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
                $request->user(),
                'master_admin.master_obat',
                'delete',
                "Master admin menghapus obat {$name} dari master.",
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

    public function updateUserPassword(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'Password akun yang sedang dipakai tidak bisa diubah dari menu ini.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        ActivityLogger::log(
            $request->user(),
            'master_admin.user_management',
            'update_password',
            "Master admin mengubah password pengguna {$user->name}.",
            $user,
            [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
                'target_user_role' => $user->role,
            ]
        );

        return back()->with('status', "Password untuk {$user->name} berhasil diperbarui.");
    }

    public function toggleUserActive(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'Akun yang sedang dipakai tidak bisa dinonaktifkan.');
        }

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $isActive = (bool) $validated['is_active'];

        $user->update([
            'is_active' => $isActive,
            'deactivated_at' => $isActive ? null : now(),
            'deactivation_reason' => $isActive ? null : ($validated['reason'] ?? 'Dinonaktifkan oleh master admin.'),
        ]);

        ActivityLogger::log(
            $request->user(),
            'master_admin.user_management',
            $isActive ? 'activate_user' : 'deactivate_user',
            $isActive
                ? "Master admin mengaktifkan kembali akun {$user->name}."
                : "Master admin menonaktifkan akun {$user->name}.",
            $user,
            [
                'target_user_id' => $user->id,
                'target_user_name' => $user->name,
                'target_user_role' => $user->role,
                'reason' => $validated['reason'] ?? null,
            ]
        );

        return back()->with('status', $isActive
            ? "Akun {$user->name} berhasil diaktifkan kembali."
            : "Akun {$user->name} berhasil dinonaktifkan.");
    }

    public function submitUserCreationRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(User::roles())],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'request_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $approval = $this->approvalWorkflowService->submit(
            requester: $request->user(),
            type: ApprovalRequest::TYPE_USER_CREATION,
            module: 'master_admin.user_management',
            title: "Pembuatan user baru {$validated['name']} ({$validated['role']})",
            payload: [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => $validated['password'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ],
            requestNote: $validated['request_note'] ?? null,
        );

        ActivityLogger::log(
            $request->user(),
            'master_admin.user_management',
            'request_user_creation',
            "Master admin membuat permintaan pembuatan user {$validated['name']}.",
            $approval,
            [
                'approval_id' => $approval->id,
                'email' => $validated['email'],
                'role' => $validated['role'],
            ]
        );

        return back()->with('status', "Permintaan pembuatan user berhasil diajukan (Approval #{$approval->id}).");
    }

    public function submitRoleChangeRequest(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'Perubahan role akun yang sedang dipakai harus dilakukan oleh master admin lain.');
        }

        $validated = $request->validate([
            'new_role' => ['required', 'string', Rule::in(User::roles())],
            'request_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['new_role'] === $user->role) {
            return back()->with('error', 'Role baru sama dengan role saat ini. Tidak ada perubahan yang diajukan.');
        }

        $approval = $this->approvalWorkflowService->submit(
            requester: $request->user(),
            type: ApprovalRequest::TYPE_ROLE_PERMISSION_CHANGE,
            module: 'master_admin.role_permission',
            title: "Perubahan role {$user->name} dari {$user->role} ke {$validated['new_role']}",
            payload: [
                'target_user_id' => $user->id,
                'old_role' => $user->role,
                'new_role' => $validated['new_role'],
            ],
            beforeData: [
                'target_user_id' => $user->id,
                'old_role' => $user->role,
            ],
            requestNote: $validated['request_note'] ?? null,
        );

        ActivityLogger::log(
            $request->user(),
            'master_admin.role_permission',
            'request_role_change',
            "Master admin membuat permintaan perubahan role untuk {$user->name}.",
            $approval,
            [
                'approval_id' => $approval->id,
                'target_user_id' => $user->id,
                'from_role' => $user->role,
                'to_role' => $validated['new_role'],
            ]
        );

        return back()->with('status', "Permintaan perubahan role berhasil diajukan (Approval #{$approval->id}).");
    }

    public function approveApproval(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $validated = $request->validate([
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->approvalWorkflowService->approve(
            approvalRequest: $approvalRequest,
            approver: $request->user(),
            decisionNote: $validated['decision_note'] ?? null,
        );

        return back()->with('status', "Approval #{$approvalRequest->id} berhasil disetujui.");
    }

    public function rejectApproval(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $validated = $request->validate([
            'decision_note' => ['required', 'string', 'max:1000'],
        ], [
            'decision_note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $this->approvalWorkflowService->reject(
            approvalRequest: $approvalRequest,
            approver: $request->user(),
            decisionNote: $validated['decision_note'],
        );

        return back()->with('status', "Approval #{$approvalRequest->id} berhasil ditolak.");
    }

    private function perPageOptions(): array
    {
        return [10, 25, 50, 100, 250, 500];
    }

    private function resolvePerPage(
        Request $request,
        string $queryKey,
        int $default = 25,
        int $min = 5,
        int $max = 500
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

    private function roleSortExpression(): string
    {
        return "CASE role
            WHEN 'master_admin' THEN 1
            WHEN 'owner_viewer' THEN 2
            WHEN 'admin' THEN 3
            WHEN 'admin_apotek' THEN 4
            WHEN 'admin_gudang' THEN 5
            WHEN 'apoteker' THEN 6
            WHEN 'dokter' THEN 7
            WHEN 'kasir' THEN 8
            WHEN 'staf_purchasing' THEN 9
            WHEN 'staf_dokumentasi' THEN 10
            ELSE 11
        END";
    }
}
