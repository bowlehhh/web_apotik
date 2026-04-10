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
        $recentVisits = $this->visitsQuery()
            ->limit(8)
            ->get();
        $lowStockMedicines = Medicine::query()
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('ui.dokter.dashboard', [
            'stats' => $stats,
            'recentVisits' => $recentVisits,
            'lowStockMedicines' => $lowStockMedicines,
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
        ]);

        $recentVisits = $this->visitsQuery()
            ->limit(8)
            ->get();

        return view('ui.dokter.consultations', [
            'patients' => $patients,
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
        $medicines = $this->medicinesQuery()->get();
        $stats = $this->stats();

        return view('ui.dokter.medicines', [
            'medicines' => $medicines,
            'stats' => $stats,
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
        ], [
            'complaint.required' => 'Keluhan pasien wajib diisi.',
        ]);

        if (! $validated['patient_id'] && blank($validated['patient_name'])) {
            throw ValidationException::withMessages([
                'patient_name' => 'Pilih pasien yang sudah ada atau isi nama pasien baru.',
            ]);
        }

        $patient = null;
        $isNewPatient = false;

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

        return back()->with('status', 'Konsultasi pasien berhasil dicatat ke riwayat.');
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
        return [
            'total_patients' => Patient::query()->count(),
            'today_visits' => PatientVisit::query()->whereDate('visit_date', now()->toDateString())->count(),
            'ready_medicines' => Medicine::query()->where('stock', '>', 0)->count(),
            'not_ready_medicines' => Medicine::query()->where('stock', '<=', 0)->count(),
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
