<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DoctorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        return $this->dashboard($request);
    }

    public function dashboard(Request $request): View
    {
        $stats = $this->stats();
        $thirtyDaysAhead = now()->addDays(30)->toDateString();
        $recentVisits = $this->visitsQuery()
            ->limit(8)
            ->get();
        $lowStockMedicines = Medicine::query()
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(8)
            ->get();
        $expiringAlertMedicines = Medicine::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', $thirtyDaysAhead)
            ->orderBy('expiry_date')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('ui.dokter.dashboard', [
            'stats' => $stats,
            'recentVisits' => $recentVisits,
            'lowStockMedicines' => $lowStockMedicines,
            'expiringAlertMedicines' => $expiringAlertMedicines,
            'doctor' => $request->user(),
        ]);
    }

    public function consultations(Request $request): View
    {
        $patients = $this->patientsQuery()->get([
            'id',
            'medical_record_number',
            'name',
            'gender',
            'date_of_birth',
            'height_cm',
            'weight_kg',
            'phone',
            'address',
            'notes',
        ]);

        $patientVisitHistories = PatientVisit::query()
            ->with(['doctor:id,name'])
            ->whereIn('patient_id', $patients->pluck('id'))
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->get([
                'id',
                'patient_id',
                'doctor_id',
                'visit_date',
                'complaint',
                'diagnosis',
                'action_taken',
                'notes',
                'status',
            ])
            ->groupBy('patient_id');

        $recentVisits = $this->visitsQuery()
            ->limit(8)
            ->get();
        $medicines = $this->medicinesQuery()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->get([
                'id',
                'name',
                'trade_name',
                'category',
                'stock',
                'unit',
                'expiry_date',
            ]);

        return view('ui.dokter.consultations', [
            'patients' => $patients,
            'patientVisitHistories' => $patientVisitHistories,
            'medicines' => $medicines,
            'recentVisits' => $recentVisits,
            'doctor' => $request->user(),
        ]);
    }

    public function histories(Request $request): View
    {
        $visits = $this->visitsQuery()->get();
        $medicines = $this->medicinesQuery()->get();

        return view('ui.dokter.histories', [
            'visits' => $visits,
            'medicines' => $medicines,
            'doctor' => $request->user(),
        ]);
    }

    public function medicines(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');
        $today = now()->toDateString();
        $expiringUntil = now()->addDays(30)->toDateString();

        $medicines = $this->medicinesQuery()
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%")
                        ->orWhere('dosage', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($status === 'ready', function (Builder $builder): void {
                $builder->where('stock', '>', 0);
            })
            ->when($status === 'not_ready', function (Builder $builder): void {
                $builder->where('stock', '<=', 0);
            })
            ->when($status === 'low_stock', function (Builder $builder): void {
                $builder->where('stock', '>', 0)->where('stock', '<=', 10);
            })
            ->when($status === 'expiring', function (Builder $builder) use ($today, $expiringUntil): void {
                $builder->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', $today)
                    ->whereDate('expiry_date', '<=', $expiringUntil);
            })
            ->when($status === 'expired', function (Builder $builder) use ($today): void {
                $builder->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today);
            })
            ->get();
        $stats = $this->stats();

        return view('ui.dokter.medicines', [
            'medicines' => $medicines,
            'stats' => $stats,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
            'doctor' => $request->user(),
        ]);
    }

    public function storeConsultation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'patient_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:laki_laki,perempuan,lainnya'],
            'date_of_birth' => ['nullable', 'date'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'patient_notes' => ['nullable', 'string', 'max:2000'],
            'visit_date' => ['nullable', 'date'],
            'complaint' => ['required', 'string', 'max:4000'],
            'diagnosis' => ['nullable', 'string', 'max:4000'],
            'action_taken' => ['nullable', 'string', 'max:4000'],
            'visit_notes' => ['nullable', 'string', 'max:4000'],
            'prescription_items' => ['nullable', 'array'],
            'prescription_items.*.medicine_id' => ['nullable', 'integer', 'exists:medicines,id'],
            'prescription_items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'prescription_items.*.dosage_instructions' => ['nullable', 'string', 'max:255'],
            'prescription_items.*.item_note' => ['nullable', 'string', 'max:255'],
            'prescription_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'complaint.required' => 'Keluhan pasien wajib diisi.',
            'prescription_items.*.medicine_id.exists' => 'Salah satu obat resep yang dipilih tidak ditemukan.',
        ]);

        if (! $validated['patient_id'] && blank($validated['patient_name'])) {
            throw ValidationException::withMessages([
                'patient_name' => 'Pilih pasien yang sudah ada atau isi nama pasien baru.',
            ]);
        }

        $prescriptionItemsPayload = collect((array) ($validated['prescription_items'] ?? []))
            ->map(function ($item): array {
                return [
                    'medicine_id' => isset($item['medicine_id']) ? (int) $item['medicine_id'] : 0,
                    'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 0,
                    'dosage_instructions' => trim((string) ($item['dosage_instructions'] ?? '')),
                    'item_note' => isset($item['item_note']) ? trim((string) $item['item_note']) : null,
                ];
            })
            ->filter(function (array $item): bool {
                return $item['medicine_id'] > 0;
            })
            ->values();

        foreach ($prescriptionItemsPayload as $index => $item) {
            if ($item['quantity'] < 1) {
                throw ValidationException::withMessages([
                    "prescription_items.{$index}.quantity" => 'Jumlah obat resep wajib diisi minimal 1.',
                ]);
            }

            if ($item['dosage_instructions'] === '') {
                throw ValidationException::withMessages([
                    "prescription_items.{$index}.dosage_instructions" => 'Aturan pakai untuk setiap obat resep wajib diisi.',
                ]);
            }
        }

        $patient = null;
        $createdVisit = null;
        $createdPrescription = null;
        $prescribedMedicines = [];
        $isNewPatient = false;

        DB::transaction(function () use (
            $request,
            $validated,
            &$patient,
            &$createdVisit,
            &$createdPrescription,
            &$prescribedMedicines,
            $prescriptionItemsPayload,
            &$isNewPatient
        ): void {
            if (! empty($validated['patient_id'])) {
                $patient = Patient::query()->findOrFail((int) $validated['patient_id']);

                $patient->update([
                    'gender' => $validated['gender'] ?? $patient->gender,
                    'date_of_birth' => $validated['date_of_birth'] ?? $patient->date_of_birth,
                    'height_cm' => $validated['height_cm'] ?? $patient->height_cm,
                    'weight_kg' => $validated['weight_kg'] ?? $patient->weight_kg,
                    'phone' => $validated['phone'] ?? $patient->phone,
                    'address' => $validated['address'] ?? $patient->address,
                    'notes' => $validated['patient_notes'] ?? $patient->notes,
                ]);
            } else {
                $isNewPatient = true;
                $patient = Patient::query()->create([
                    'medical_record_number' => $this->generateMedicalRecordNumber(),
                    'name' => (string) $validated['patient_name'],
                    'gender' => $validated['gender'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'height_cm' => $validated['height_cm'] ?? null,
                    'weight_kg' => $validated['weight_kg'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'notes' => $validated['patient_notes'] ?? null,
                ]);
            }

            $createdVisit = PatientVisit::query()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $request->user()->id,
                'visit_date' => $validated['visit_date'] ?? now(),
                'complaint' => $validated['complaint'],
                'diagnosis' => $validated['diagnosis'] ?? null,
                'action_taken' => $validated['action_taken'] ?? null,
                'notes' => $validated['visit_notes'] ?? null,
                'status' => 'selesai',
            ]);

            if ($prescriptionItemsPayload->isNotEmpty()) {
                $createdPrescription = Prescription::query()->create([
                    'patient_visit_id' => $createdVisit->id,
                    'patient_id' => $createdVisit->patient_id,
                    'doctor_id' => $request->user()->id,
                    'prescribed_at' => now(),
                    'notes' => $validated['prescription_notes'] ?? null,
                ]);

                foreach ($prescriptionItemsPayload as $index => $item) {
                    $medicine = Medicine::query()
                        ->lockForUpdate()
                        ->findOrFail($item['medicine_id']);

                    if ($medicine->stock < $item['quantity']) {
                        throw ValidationException::withMessages([
                            "prescription_items.{$index}.quantity" => "Stok {$medicine->name} tidak mencukupi. Sisa stok: {$medicine->stock}.",
                        ]);
                    }

                    PrescriptionItem::query()->create([
                        'prescription_id' => $createdPrescription->id,
                        'medicine_id' => $medicine->id,
                        'quantity' => $item['quantity'],
                        'dosage_instructions' => $item['dosage_instructions'],
                        'note' => $item['item_note'] !== '' ? $item['item_note'] : null,
                    ]);

                    $medicine->decrement('stock', $item['quantity']);

                    $prescribedMedicines[] = [
                        'id' => $medicine->id,
                        'name' => $medicine->name,
                        'quantity' => $item['quantity'],
                        'dosage_instructions' => $item['dosage_instructions'],
                    ];
                }
            }
        });

        ActivityLogger::log(
            $request->user(),
            'dokter.konsultasi',
            'create',
            'Dokter mencatat konsultasi pasien.',
            $createdVisit,
            [
                'patient_id' => $patient->id,
                'patient_name' => $patient->name,
                'is_new_patient' => $isNewPatient,
                'visit_date' => optional($createdVisit->visit_date)->toDateTimeString(),
                'complaint' => $validated['complaint'],
            ]
        );

        if ($createdPrescription && count($prescribedMedicines) > 0) {
            ActivityLogger::log(
                $request->user(),
                'dokter.resep',
                'create',
                "Dokter menambahkan resep multi-obat saat konsultasi untuk pasien {$patient->name}.",
                $createdVisit,
                [
                    'prescription_id' => $createdPrescription->id,
                    'patient_id' => $patient->id,
                    'patient_name' => $patient->name,
                    'prescription_items' => $prescribedMedicines,
                ]
            );
        }

        $statusMessage = 'Konsultasi pasien berhasil dicatat ke riwayat.';
        if ($createdPrescription) {
            $statusMessage .= ' Resep obat juga berhasil disimpan.';
        }

        return back()->with('status', $statusMessage);
    }

    public function updateVisit(Request $request, PatientVisit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'visit_date' => ['required', 'date'],
            'complaint' => ['required', 'string', 'max:4000'],
            'diagnosis' => ['nullable', 'string', 'max:4000'],
            'action_taken' => ['nullable', 'string', 'max:4000'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'status' => ['required', 'in:selesai,lanjutan'],
        ]);

        $previousStatus = $visit->status;
        $previousVisitDate = optional($visit->visit_date)->toDateTimeString();

        $visit->update($validated);

        ActivityLogger::log(
            $request->user(),
            'dokter.riwayat',
            'update',
            'Dokter memperbarui riwayat konsultasi pasien.',
            $visit,
            [
                'patient_id' => $visit->patient_id,
                'patient_name' => $visit->patient?->name,
                'before' => [
                    'status' => $previousStatus,
                    'visit_date' => $previousVisitDate,
                ],
                'after' => [
                    'status' => $visit->status,
                    'visit_date' => optional($visit->visit_date)->toDateTimeString(),
                ],
            ]
        );

        return back()->with('status', 'Riwayat konsultasi pasien berhasil diperbarui.');
    }

    public function storePrescription(Request $request, PatientVisit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'dosage_instructions' => ['required', 'string', 'max:255'],
            'item_note' => ['nullable', 'string', 'max:255'],
            'prescription_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'medicine_id.required' => 'Pilih obat untuk resep pasien.',
            'dosage_instructions.required' => 'Aturan pakai obat wajib diisi.',
        ]);

        $prescribedMedicine = null;
        $prescriptionId = null;

        DB::transaction(function () use ($request, $visit, $validated, &$prescribedMedicine, &$prescriptionId): void {
            $medicine = Medicine::query()
                ->lockForUpdate()
                ->findOrFail((int) $validated['medicine_id']);

            if ($medicine->stock < (int) $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok {$medicine->name} tidak mencukupi. Sisa stok: {$medicine->stock}.",
                ]);
            }

            $prescription = Prescription::query()->firstOrCreate(
                ['patient_visit_id' => $visit->id],
                [
                    'patient_id' => $visit->patient_id,
                    'doctor_id' => $request->user()->id,
                    'prescribed_at' => now(),
                    'notes' => $validated['prescription_notes'] ?? null,
                ]
            );

            if (! empty($validated['prescription_notes'])) {
                $prescription->update([
                    'notes' => $validated['prescription_notes'],
                ]);
            }

            $prescriptionId = $prescription->id;
            $prescribedMedicine = $medicine;

            PrescriptionItem::query()->create([
                'prescription_id' => $prescription->id,
                'medicine_id' => $medicine->id,
                'quantity' => (int) $validated['quantity'],
                'dosage_instructions' => $validated['dosage_instructions'],
                'note' => $validated['item_note'] ?? null,
            ]);

            $medicine->decrement('stock', (int) $validated['quantity']);
        });

        ActivityLogger::log(
            $request->user(),
            'dokter.resep',
            'create',
            "Dokter menambahkan resep untuk pasien {$visit->patient?->name}.",
            $visit,
            [
                'prescription_id' => $prescriptionId,
                'patient_id' => $visit->patient_id,
                'patient_name' => $visit->patient?->name,
                'medicine_id' => $prescribedMedicine?->id,
                'medicine_name' => $prescribedMedicine?->name,
                'quantity' => (int) $validated['quantity'],
                'dosage_instructions' => $validated['dosage_instructions'],
            ]
        );

        return back()->with('status', 'Resep obat pasien berhasil disimpan.');
    }

    public function updatePrescriptionItem(Request $request, PrescriptionItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'dosage_instructions' => ['required', 'string', 'max:255'],
            'item_note' => ['nullable', 'string', 'max:255'],
        ], [
            'medicine_id.required' => 'Pilih obat untuk resep pasien.',
            'dosage_instructions.required' => 'Aturan pakai obat wajib diisi.',
        ]);

        $previousMedicineName = $item->medicine?->name;
        $previousQuantity = (int) $item->quantity;
        $updatedMedicineName = null;

        DB::transaction(function () use ($item, $validated, &$updatedMedicineName): void {
            $lockedItem = PrescriptionItem::query()
                ->with('prescription.visit.patient')
                ->lockForUpdate()
                ->findOrFail($item->id);

            $prescription = Prescription::query()
                ->lockForUpdate()
                ->findOrFail($lockedItem->prescription_id);

            if ($prescription->is_dispensed) {
                throw ValidationException::withMessages([
                    'medicine_id' => 'Resep sudah diproses kasir, item tidak bisa diubah lagi.',
                ]);
            }

            $oldMedicine = Medicine::query()
                ->lockForUpdate()
                ->findOrFail($lockedItem->medicine_id);

            $newMedicineId = (int) $validated['medicine_id'];
            $newQuantity = (int) $validated['quantity'];

            if ($newMedicineId === (int) $lockedItem->medicine_id) {
                $quantityDiff = $newQuantity - (int) $lockedItem->quantity;

                if ($quantityDiff > 0) {
                    if ($oldMedicine->stock < $quantityDiff) {
                        throw ValidationException::withMessages([
                            'quantity' => "Stok {$oldMedicine->name} tidak mencukupi. Sisa stok: {$oldMedicine->stock}.",
                        ]);
                    }

                    $oldMedicine->decrement('stock', $quantityDiff);
                } elseif ($quantityDiff < 0) {
                    $oldMedicine->increment('stock', abs($quantityDiff));
                }

                $updatedMedicineName = $oldMedicine->name;
            } else {
                $newMedicine = Medicine::query()
                    ->lockForUpdate()
                    ->findOrFail($newMedicineId);

                // Rollback stok item lama, lalu ambil stok item baru.
                $oldMedicine->increment('stock', (int) $lockedItem->quantity);

                if ($newMedicine->stock < $newQuantity) {
                    throw ValidationException::withMessages([
                        'quantity' => "Stok {$newMedicine->name} tidak mencukupi. Sisa stok: {$newMedicine->stock}.",
                    ]);
                }

                $newMedicine->decrement('stock', $newQuantity);
                $updatedMedicineName = $newMedicine->name;
            }

            $lockedItem->update([
                'medicine_id' => $newMedicineId,
                'quantity' => $newQuantity,
                'dosage_instructions' => $validated['dosage_instructions'],
                'note' => $validated['item_note'] ?? null,
            ]);
        });

        $item->refresh();
        $item->loadMissing('prescription.visit.patient');

        ActivityLogger::log(
            $request->user(),
            'dokter.resep',
            'update',
            "Dokter memperbarui item resep pasien {$item->prescription?->visit?->patient?->name}.",
            $item->prescription?->visit,
            [
                'prescription_id' => $item->prescription_id,
                'prescription_item_id' => $item->id,
                'patient_id' => $item->prescription?->visit?->patient_id,
                'patient_name' => $item->prescription?->visit?->patient?->name,
                'before' => [
                    'medicine_name' => $previousMedicineName,
                    'quantity' => $previousQuantity,
                ],
                'after' => [
                    'medicine_name' => $updatedMedicineName,
                    'quantity' => (int) $item->quantity,
                    'dosage_instructions' => $item->dosage_instructions,
                ],
            ]
        );

        return back()->with('status', 'Item resep berhasil diperbarui.');
    }

    public function destroyPrescriptionItem(Request $request, PrescriptionItem $item): RedirectResponse
    {
        $item->loadMissing(['medicine', 'prescription.visit.patient']);

        $medicineName = $item->medicine?->name;
        $quantity = (int) $item->quantity;
        $prescriptionId = (int) $item->prescription_id;
        $patientName = $item->prescription?->visit?->patient?->name;
        $patientId = $item->prescription?->visit?->patient_id;
        $visit = $item->prescription?->visit;

        DB::transaction(function () use ($item): void {
            $lockedItem = PrescriptionItem::query()
                ->with('prescription')
                ->lockForUpdate()
                ->findOrFail($item->id);

            $prescription = Prescription::query()
                ->lockForUpdate()
                ->findOrFail($lockedItem->prescription_id);

            if ($prescription->is_dispensed) {
                throw ValidationException::withMessages([
                    'medicine_id' => 'Resep sudah diproses kasir, item tidak bisa dihapus lagi.',
                ]);
            }

            $medicine = Medicine::query()
                ->lockForUpdate()
                ->findOrFail($lockedItem->medicine_id);

            $medicine->increment('stock', (int) $lockedItem->quantity);
            $lockedItem->delete();

            if (! PrescriptionItem::query()->where('prescription_id', $prescription->id)->exists()) {
                $prescription->delete();
            }
        });

        ActivityLogger::log(
            $request->user(),
            'dokter.resep',
            'delete',
            "Dokter menghapus item resep pasien {$patientName}.",
            $visit,
            [
                'prescription_id' => $prescriptionId,
                'patient_id' => $patientId,
                'patient_name' => $patientName,
                'medicine_name' => $medicineName,
                'quantity' => $quantity,
            ]
        );

        return back()->with('status', 'Item resep berhasil dihapus.');
    }

    public function updateMedicine(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'trade_name' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'stock' => ['required', 'integer', 'min:0'],
            'sell_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $before = [
            'stock' => (int) $medicine->stock,
            'sell_price' => (float) $medicine->sell_price,
            'category' => $medicine->category,
            'dosage' => $medicine->dosage,
        ];

        $medicine->update([
            'trade_name' => $validated['trade_name'] ?? $medicine->trade_name,
            'dosage' => $validated['dosage'] ?? $medicine->dosage,
            'category' => $validated['category'] ?? $medicine->category,
            'stock' => (int) $validated['stock'],
            'sell_price' => array_key_exists('sell_price', $validated)
                ? (float) $validated['sell_price']
                : $medicine->sell_price,
        ]);

        ActivityLogger::log(
            $request->user(),
            'dokter.obat',
            'update',
            "Dokter memperbarui data obat {$medicine->name}.",
            $medicine,
            [
                'before' => $before,
                'after' => [
                    'stock' => (int) $medicine->stock,
                    'sell_price' => (float) $medicine->sell_price,
                    'category' => $medicine->category,
                    'dosage' => $medicine->dosage,
                ],
            ]
        );

        return back()->with('status', "Data obat {$medicine->name} berhasil diperbarui.");
    }

    private function generateMedicalRecordNumber(): string
    {
        $prefix = 'PSN-'.now()->format('Ymd');

        $last = Patient::query()
            ->where('medical_record_number', 'like', $prefix.'-%')
            ->count() + 1;

        return $prefix.'-'.str_pad((string) $last, 4, '0', STR_PAD_LEFT);
    }

    private function stats(): array
    {
        $today = now()->toDateString();
        $thirtyDaysAhead = now()->addDays(30)->toDateString();

        return [
            'total_patients' => Patient::query()->count(),
            'today_visits' => PatientVisit::query()->whereDate('visit_date', $today)->count(),
            'ready_medicines' => Medicine::query()->where('stock', '>', 0)->count(),
            'not_ready_medicines' => Medicine::query()->where('stock', '<=', 0)->count(),
            'low_stock_medicines' => Medicine::query()->where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'expiring_soon_medicines' => Medicine::query()
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $thirtyDaysAhead)
                ->count(),
            'expired_medicines' => Medicine::query()
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', $today)
                ->count(),
        ];
    }

    private function patientsQuery(): Builder
    {
        return Patient::query()
            ->withCount('visits')
            ->withMax('visits', 'visit_date')
            ->orderBy('name');
    }

    private function visitsQuery(): Builder
    {
        return PatientVisit::query()
            ->with([
                'patient',
                'doctor',
                'prescriptions.items.medicine',
            ])
            ->orderByDesc('visit_date')
            ->orderByDesc('id');
    }

    private function medicinesQuery(): Builder
    {
        return Medicine::query()
            ->orderByRaw('stock > 0 DESC')
            ->orderBy('name');
    }
}
