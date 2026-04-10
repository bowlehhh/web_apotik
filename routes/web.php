<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminBarcodeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorDashboardController;
use App\Http\Controllers\KasirDashboardController;
use App\Http\Controllers\MasterAdminController;
use App\Models\Medicine;
use App\Models\MedicinePurchaseLog;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

    Route::middleware('role:dokter')->prefix('dokter')->as('dokter.')->group(function () {
        Route::get('/dashboard', [DoctorDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/konsultasi', [DoctorDashboardController::class, 'consultations'])->name('consultations.index');
        Route::get('/riwayat', [DoctorDashboardController::class, 'histories'])->name('histories.index');
        Route::get('/obat', [DoctorDashboardController::class, 'medicines'])->name('medicines.index');

        Route::post('/konsultasi', [DoctorDashboardController::class, 'storeConsultation'])->name('consultations.store');
        Route::patch('/riwayat/{visit}', [DoctorDashboardController::class, 'updateVisit'])->name('visits.update');
        Route::post('/riwayat/{visit}/resep', [DoctorDashboardController::class, 'storePrescription'])->name('prescriptions.store');
        Route::patch('/obat/{medicine}', [DoctorDashboardController::class, 'updateMedicine'])->name('medicines.update');
    });

    Route::middleware('role:kasir,master_admin')->prefix('kasir')->as('kasir.')->group(function () {
        Route::get('/dashboard', [KasirDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/transaksi', [KasirDashboardController::class, 'transactions'])->name('transaksi');
        Route::get('/transaksi/cetak-riwayat', [KasirDashboardController::class, 'printSalesHistory'])->name('sales.history.print');
        Route::get('/transaksi/{sale}/cetak', [KasirDashboardController::class, 'printSale'])->name('sales.print');
        Route::post('/transaksi/non-resep', [KasirDashboardController::class, 'storeNonPrescriptionSale'])->name('sales.non-prescription.store');
        Route::post('/resep/{prescription}/proses', [KasirDashboardController::class, 'dispensePrescription'])->name('prescriptions.dispense');
        Route::get('/resep/{prescription}/cetak', [KasirDashboardController::class, 'printPrescription'])->name('prescriptions.print');

        Route::get('/obat', [KasirDashboardController::class, 'medicines'])->name('medicines.index');
    });

    Route::middleware('role:admin,master_admin')->prefix('admin')->as('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/dokumentasi', [AdminDashboardController::class, 'documentation'])->name('dokumentasi');
        Route::get('/barcode-input', [AdminBarcodeController::class, 'index'])->name('barcode.index');
        Route::get('/barcode-input/cari', [AdminBarcodeController::class, 'lookup'])->name('barcode.lookup');
        Route::post('/barcode-input', [AdminBarcodeController::class, 'store'])->name('barcode.store');

        Route::get('/data-obat', [AdminDashboardController::class, 'medicines'])->name('data-obat');
        Route::post('/data-obat', [AdminDashboardController::class, 'storeMedicine'])->name('medicines.store');
        Route::patch('/data-obat/{medicine}', [AdminDashboardController::class, 'updateMedicine'])->name('medicines.update');
        Route::delete('/data-obat/{medicine}', [AdminDashboardController::class, 'destroyMedicine'])->name('medicines.destroy');

        Route::get('/gudang', [AdminDashboardController::class, 'warehouse'])->name('warehouse');
        Route::post('/gudang/pembelian', [AdminDashboardController::class, 'storeWarehousePurchase'])->name('warehouse.purchases.store');
        Route::patch('/gudang/pembelian/{purchaseLog}', [AdminDashboardController::class, 'updateWarehousePurchase'])->name('warehouse.purchases.update');

        Route::get('/laporan', function () {
            $priceReportLogs = Medicine::query()
                ->with(['purchaseLogs' => function ($query): void {
                    $query->orderByDesc('purchased_at')->orderByDesc('id');
                }])
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->paginate(15);

            $reportStats = [
                'total_purchase_value' => (float) Medicine::query()
                    ->selectRaw('COALESCE(SUM(stock * buy_price), 0) as total_purchase_value')
                    ->value('total_purchase_value'),
                'total_sell_value' => (float) Medicine::query()
                    ->selectRaw('COALESCE(SUM(stock * sell_price), 0) as total_sell_value')
                    ->value('total_sell_value'),
                'total_medicines_logged' => Medicine::query()->count(),
                'total_outlets' => Medicine::query()
                    ->whereNotNull('purchase_source')
                    ->where('purchase_source', '!=', '')
                    ->distinct()
                    ->count('purchase_source'),
            ];

            return view('ui.laporan-analitik', [
                'priceReportLogs' => $priceReportLogs,
                'reportStats' => $reportStats,
            ]);
        })->name('laporan');
    });

    Route::middleware('role:master_admin')->prefix('master-admin')->as('master-admin.')->group(function () {
        Route::get('/dashboard', [MasterAdminController::class, 'dashboard'])
            ->middleware('permission:dashboard.view.global')
            ->name('dashboard');
        Route::get('/aktivitas-role', [MasterAdminController::class, 'activities'])
            ->middleware('permission:audit_logs.view')
            ->name('activities.index');
        Route::get('/role-permission', [MasterAdminController::class, 'rolePermission'])
            ->middleware('permission:roles_permissions.view')
            ->name('role-permission.index');
        Route::get('/password/ubah', [MasterAdminController::class, 'changePasswordPage'])
            ->middleware('permission:users.update')
            ->name('password.change');
        Route::patch('/password/ubah', [MasterAdminController::class, 'updateOwnPassword'])
            ->middleware('permission:users.update')
            ->name('password.change.update');
        Route::get('/password/reset', [MasterAdminController::class, 'resetPasswordPage'])
            ->middleware('permission:users.reset_password')
            ->name('password.reset');
        Route::get('/data-obat', [MasterAdminController::class, 'medicines'])
            ->middleware('permission:medicines.view')
            ->name('medicines.index');
        Route::post('/data-obat', [MasterAdminController::class, 'storeMedicine'])
            ->middleware('permission:medicines.create')
            ->name('medicines.store');
        Route::patch('/data-obat/{medicine}', [MasterAdminController::class, 'updateMedicine'])
            ->middleware('permission:medicines.update')
            ->name('medicines.update');
        Route::delete('/data-obat/{medicine}', [MasterAdminController::class, 'destroyMedicine'])
            ->middleware('permission:medicines.delete')
            ->name('medicines.destroy');
        Route::patch('/users/{user}/password', [MasterAdminController::class, 'updateUserPassword'])
            ->middleware('permission:users.reset_password')
            ->name('users.password.update');
    });

    Route::prefix('ui')->as('ui.')->group(function () {
        Route::get('/', function () {
            return view('ui.index');
        })->name('index');

        Route::get('/kasir-dashboard', function () {
            return redirect()->route('kasir.dashboard');
        })->name('kasir-dashboard');

        Route::get('/master-admin-dashboard', function () {
            return redirect()->route('master-admin.dashboard');
        })->name('master-admin-dashboard');

        Route::get('/transaksi-penjualan', function () {
            return redirect()->route('kasir.transaksi');
        })->name('transaksi-penjualan');

        Route::get('/laporan-analitik', function () {
            return view('ui.laporan-analitik');
        })->name('laporan-analitik');

        Route::get('/data-obat', function () {
            if (auth()->user()?->isMasterAdmin()) {
                return redirect()->route('master-admin.medicines.index');
            }

            return redirect()->route('admin.warehouse');
        })->name('data-obat');

        Route::get('/admin-dashboard', function () {
            return redirect()->route('admin.dashboard');
        })->name('admin-dashboard');

        Route::get('/barcode-input', function () {
            return redirect()->route('admin.barcode.index');
        })->name('barcode-input');

        Route::get('/mobile-dashboard', function () {
            return redirect()->route('dokter.dashboard');
        })->name('mobile-dashboard');
    });
});
