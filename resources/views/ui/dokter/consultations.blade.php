@extends('ui.dokter.layout')

@section('dokter_title', 'Konsultasi Pasien')
@section('dokter_heading', 'Konsultasi Pasien')
@section('dokter_subheading', 'Input konsultasi pasien baru atau pasien lama. Setiap kunjungan akan tercatat sebagai layer riwayat.')

@section('dokter_content')
<section class="grid grid-cols-1 xl:grid-cols-5 gap-8">
    <article class="xl:col-span-3 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Form Konsultasi</h3>
            <p class="text-sm text-slate-500">Ketik nama lengkap pasien. Jika pasien lama, biodata dan riwayat sebelumnya langsung muncul otomatis.</p>
        </div>

        <form method="POST" action="{{ route('dokter.consultations.store') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}" />

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Nama Pasien Lengkap</label>
                <div class="relative">
                    <input
                        type="text"
                        name="patient_name"
                        id="patient_name"
                        value="{{ old('patient_name') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
                        placeholder="Ketik nama lengkap pasien, contoh: Afri Setiawan"
                        autocomplete="off"
                    />
                    <div id="patient-search-results" class="absolute left-0 right-0 top-full z-20 mt-1 hidden max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                </div>
                <p class="mt-2 text-xs text-slate-500">Tulis nama atau kata kunci pasien lama. Klik hasil yang muncul agar biodata terisi otomatis.</p>
            </div>

            <div id="patient-history-preview" class="hidden rounded-2xl border border-blue-100 bg-blue-50/60 p-4">
                <p class="text-[10px] uppercase tracking-widest font-bold text-blue-700">Riwayat Pasien Sebelumnya</p>
                <p id="patient-history-title" class="mt-1 text-sm font-bold text-blue-900"></p>
                <div id="patient-history-list" class="mt-3 space-y-2 max-h-60 overflow-y-auto pr-1"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Jenis Kelamin</label>
                    <select name="gender" id="patient_gender" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <option value="">- Pilih -</option>
                        <option value="laki_laki" @selected(old('gender') === 'laki_laki')>Laki-laki</option>
                        <option value="perempuan" @selected(old('gender') === 'perempuan')>Perempuan</option>
                        <option value="lainnya" @selected(old('gender') === 'lainnya')>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Tanggal Lahir</label>
                    <input type="date" name="date_of_birth" id="patient_date_of_birth" value="{{ old('date_of_birth') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Tinggi Badan (cm)</label>
                    <input type="number" step="0.1" min="0" name="height_cm" id="patient_height_cm" value="{{ old('height_cm') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: 168.5" />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Berat Badan (kg)</label>
                    <input type="number" step="0.1" min="0" name="weight_kg" id="patient_weight_kg" value="{{ old('weight_kg') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Contoh: 62.3" />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">No HP</label>
                    <input type="text" name="phone" id="patient_phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="08xxxxxxxxxx" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Alamat Pasien</label>
                <input type="text" name="address" id="patient_address" value="{{ old('address') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Alamat lengkap pasien" />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Catatan Pasien</label>
                <textarea name="patient_notes" id="patient_notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('patient_notes') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Tanggal/Jam Kunjungan</label>
                <input type="datetime-local" name="visit_date" value="{{ old('visit_date', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Keluhan Pasien</label>
                <textarea name="complaint" id="visit_complaint" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" required>{{ old('complaint') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Diagnosis</label>
                <textarea name="diagnosis" id="visit_diagnosis" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('diagnosis') }}</textarea>
            </div>

            @php
                $resolveMedicineTypeLabel = function ($category): string {
                    $medicineCategory = trim((string) ($category ?? ''));
                    $normalizedCategory = strtolower($medicineCategory);

                    if (str_contains($normalizedCategory, 'tidak keras')) {
                        return 'Obat Tidak Keras';
                    }

                    if (str_contains($normalizedCategory, 'keras')) {
                        return 'Obat Keras';
                    }

                    return $medicineCategory !== '' ? $medicineCategory : 'Kategori belum diisi';
                };

                $oldPrescriptionItems = old('prescription_items', [
                    [
                        'medicine_id' => '',
                        'quantity' => 1,
                        'dosage_instructions' => '',
                        'item_note' => '',
                    ],
                ]);
                if (! is_array($oldPrescriptionItems) || count($oldPrescriptionItems) === 0) {
                    $oldPrescriptionItems = [[
                        'medicine_id' => '',
                        'quantity' => 1,
                        'dosage_instructions' => '',
                        'item_note' => '',
                    ]];
                }

                $medicineOptions = $medicines->map(function ($medicine) use ($resolveMedicineTypeLabel) {
                    $medicineTypeLabel = $resolveMedicineTypeLabel($medicine->category ?? '');

                    return [
                        'id' => $medicine->id,
                        'label' => $medicine->name.' | '.$medicineTypeLabel.' | stok '.$medicine->stock.' '.$medicine->unit,
                        'search' => strtolower(trim($medicine->name.' '.$medicine->trade_name.' '.$medicine->category)),
                    ];
                })->values();

                $patientOptions = $patients->map(function ($patient) use ($patientVisitHistories) {
                    $patientVisits = ($patientVisitHistories->get($patient->id) ?? collect())
                        ->take(20)
                        ->map(function ($visit) {
                            return [
                                'visit_date' => optional($visit->visit_date)->format('d M Y H:i'),
                                'complaint' => (string) ($visit->complaint ?? '-'),
                                'diagnosis' => (string) ($visit->diagnosis ?? '-'),
                                'action_taken' => (string) ($visit->action_taken ?? '-'),
                                'status' => strtoupper((string) ($visit->status ?? '-')),
                                'doctor_name' => (string) ($visit->doctor?->name ?? '-'),
                            ];
                        })
                        ->values();

                    return [
                        'id' => $patient->id,
                        'name' => (string) $patient->name,
                        'medical_record_number' => (string) ($patient->medical_record_number ?? '-'),
                        'gender' => $patient->gender,
                        'date_of_birth' => optional($patient->date_of_birth)->format('Y-m-d'),
                        'height_cm' => $patient->height_cm,
                        'weight_kg' => $patient->weight_kg,
                        'phone' => (string) ($patient->phone ?? ''),
                        'address' => (string) ($patient->address ?? ''),
                        'notes' => (string) ($patient->notes ?? ''),
                        'visits' => $patientVisits,
                        'visit_count' => $patientVisits->count(),
                        'last_visit' => $patientVisits->first()['visit_date'] ?? null,
                        'search' => strtolower(trim($patient->name.' '.$patient->medical_record_number.' '.$patient->phone)),
                    ];
                })->values();
            @endphp

            <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest font-bold text-blue-700">Tindakan Saat Kasih Resep Obat</p>
                        <h4 class="text-base font-extrabold text-blue-900 mt-1">Pilih Beberapa Obat Resep Dari Form Konsultasi</h4>
                        <p class="text-xs text-slate-600 mt-1">Tambahkan lebih dari satu obat resep sesuai kebutuhan pasien.</p>
                    </div>
                    <span class="material-symbols-outlined text-blue-700">medication</span>
                </div>

                <div class="space-y-4" id="prescription-items-container">
                    @foreach ($oldPrescriptionItems as $index => $oldItem)
                        @php
                            $selectedMedicineId = (int) ($oldItem['medicine_id'] ?? 0);
                            $selectedMedicine = $medicines->firstWhere('id', $selectedMedicineId);
                            $selectedMedicineLabel = '';
                            if ($selectedMedicine) {
                                $selectedTypeLabel = $resolveMedicineTypeLabel($selectedMedicine->category ?? '');
                                $selectedMedicineLabel = $selectedMedicine->name.' | '.$selectedTypeLabel.' | stok '.$selectedMedicine->stock.' '.$selectedMedicine->unit;
                            }
                        @endphp
                        <article class="rounded-xl border border-blue-100 bg-white/90 p-4" data-prescription-item>
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <p class="text-xs font-bold uppercase tracking-widest text-blue-700">Item Resep #{{ $loop->iteration }}</p>
                                <button
                                    type="button"
                                    data-remove-prescription-item
                                    class="rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] font-bold text-rose-700 hover:bg-rose-100 transition-colors {{ $loop->first ? 'hidden' : '' }}"
                                >
                                    Hapus Item
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Obat Resep (Cari Kata Kunci)</label>
                                    <div class="relative" data-medicine-picker>
                                        <input
                                            type="text"
                                            data-medicine-keyword
                                            value="{{ $selectedMedicineLabel }}"
                                            class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm"
                                            placeholder="Ketik nama/merek/kategori, contoh: am"
                                            autocomplete="off"
                                        />
                                        <input type="hidden" name="prescription_items[{{ $index }}][medicine_id]" data-medicine-id value="{{ $selectedMedicineId > 0 ? $selectedMedicineId : '' }}" />
                                        <div data-medicine-results class="absolute left-0 right-0 top-full z-20 mt-1 hidden max-h-52 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Jumlah Obat</label>
                                    <input
                                        type="number"
                                        min="1"
                                        name="prescription_items[{{ $index }}][quantity]"
                                        value="{{ old("prescription_items.$index.quantity", $oldItem['quantity'] ?? 1) }}"
                                        class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm"
                                        placeholder="Contoh: 10"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Aturan Pakai</label>
                                    <input
                                        type="text"
                                        name="prescription_items[{{ $index }}][dosage_instructions]"
                                        value="{{ old("prescription_items.$index.dosage_instructions", $oldItem['dosage_instructions'] ?? '') }}"
                                        class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm"
                                        placeholder="Contoh: 3x1 sesudah makan"
                                    />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Catatan Item Resep</label>
                                    <input
                                        type="text"
                                        name="prescription_items[{{ $index }}][item_note]"
                                        value="{{ old("prescription_items.$index.item_note", $oldItem['item_note'] ?? '') }}"
                                        class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm"
                                        placeholder="Opsional, contoh: diminum malam hari"
                                    />
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <button
                        type="button"
                        id="add-prescription-item"
                        class="inline-flex rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 transition-colors"
                    >
                        + Tambah Obat Resep
                    </button>
                    <p class="text-xs text-slate-500">Dokter bisa menambah beberapa obat dalam satu konsultasi.</p>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Catatan Resep</label>
                    <textarea name="prescription_notes" rows="2" class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm" placeholder="Opsional, contoh: kontrol 3 hari lagi">{{ old('prescription_notes') }}</textarea>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Catatan Kunjungan</label>
                <textarea name="visit_notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('visit_notes') }}</textarea>
            </div>

            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary-container transition-colors">
                Simpan Konsultasi Pasien
            </button>
        </form>
    </article>

    <article class="xl:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-xl font-extrabold text-blue-900">Kunjungan Terbaru</h3>
            <p class="text-sm text-slate-500">Rekam konsultasi terakhir untuk referensi cepat.</p>
        </div>

        <div class="space-y-3 max-h-[760px] overflow-y-auto pr-1">
            @forelse ($recentVisits as $visit)
                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    @php
                        $age = $visit->patient?->age;
                        $height = $visit->patient?->height_cm !== null ? rtrim(rtrim(number_format((float) $visit->patient->height_cm, 2, '.', ''), '0'), '.') . ' cm' : '-';
                        $weight = $visit->patient?->weight_kg !== null ? rtrim(rtrim(number_format((float) $visit->patient->weight_kg, 2, '.', ''), '0'), '.') . ' kg' : '-';
                    @endphp
                    <p class="font-bold text-slate-800">{{ $visit->patient?->name ?? '-' }}</p>
                    <p class="text-xs text-slate-500">{{ $visit->patient?->medical_record_number ?? '-' }}</p>
                    <p class="text-xs text-slate-500 mt-1">Umur: {{ $age !== null ? $age.' tahun' : '-' }} | TB: {{ $height }} | BB: {{ $weight }}</p>
                    <p class="text-sm text-slate-600 mt-2">Keluhan: {{ $visit->complaint }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ optional($visit->visit_date)->format('d M Y H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada data kunjungan pasien.</p>
            @endforelse
        </div>
    </article>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const normalize = function (value) {
            return String(value || '').trim().toLowerCase();
        };

        const patientInput = document.getElementById('patient_name');
        const patientIdInput = document.getElementById('patient_id');
        const patientResultBox = document.getElementById('patient-search-results');
        const patientHistoryPreview = document.getElementById('patient-history-preview');
        const patientHistoryTitle = document.getElementById('patient-history-title');
        const patientHistoryList = document.getElementById('patient-history-list');
        const fieldGender = document.getElementById('patient_gender');
        const fieldDateOfBirth = document.getElementById('patient_date_of_birth');
        const fieldHeight = document.getElementById('patient_height_cm');
        const fieldWeight = document.getElementById('patient_weight_kg');
        const fieldPhone = document.getElementById('patient_phone');
        const fieldAddress = document.getElementById('patient_address');
        const fieldNotes = document.getElementById('patient_notes');
        const fieldComplaint = document.getElementById('visit_complaint');
        const fieldDiagnosis = document.getElementById('visit_diagnosis');
        const fieldActionTaken = document.querySelector('[name="action_taken"]');
        const patients = @json($patientOptions);

        const renderPatientHistory = function (patient) {
            if (!patient || !patientHistoryPreview || !patientHistoryTitle || !patientHistoryList) {
                return;
            }

            patientHistoryTitle.textContent = patient.name + ' | RM: ' + (patient.medical_record_number || '-') + ' | Total kunjungan: ' + (patient.visit_count || 0);
            patientHistoryList.innerHTML = '';

            if (!Array.isArray(patient.visits) || patient.visits.length === 0) {
                const empty = document.createElement('p');
                empty.className = 'text-xs text-slate-500';
                empty.textContent = 'Belum ada riwayat kunjungan sebelumnya.';
                patientHistoryList.appendChild(empty);
                patientHistoryPreview.classList.remove('hidden');
                return;
            }

            patient.visits.forEach(function (visit) {
                const detail = document.createElement('details');
                detail.className = 'rounded-lg border border-blue-100 bg-white px-3 py-2';

                const summary = document.createElement('summary');
                summary.className = 'cursor-pointer text-xs font-bold text-blue-800';
                summary.textContent = (visit.visit_date || '-') + ' | Dokter: ' + (visit.doctor_name || '-') + ' | Status: ' + (visit.status || '-');
                detail.appendChild(summary);

                const body = document.createElement('div');
                body.className = 'mt-2 space-y-1 text-xs text-slate-600';

                const complaint = document.createElement('p');
                complaint.innerHTML = '<span class="font-bold text-slate-700">Keluhan:</span> ' + (visit.complaint || '-');
                body.appendChild(complaint);

                const diagnosis = document.createElement('p');
                diagnosis.innerHTML = '<span class="font-bold text-slate-700">Diagnosis:</span> ' + (visit.diagnosis || '-');
                body.appendChild(diagnosis);

                const action = document.createElement('p');
                action.innerHTML = '<span class="font-bold text-slate-700">Tindakan:</span> ' + (visit.action_taken || '-');
                body.appendChild(action);

                detail.appendChild(body);
                patientHistoryList.appendChild(detail);
            });

            patientHistoryPreview.classList.remove('hidden');
        };

        const fillPatientFields = function (patient) {
            if (!patient) {
                return;
            }

            if (patientIdInput) {
                patientIdInput.value = String(patient.id || '');
            }
            if (patientInput) {
                patientInput.value = patient.name || '';
            }
            if (fieldGender) {
                fieldGender.value = patient.gender || '';
            }
            if (fieldDateOfBirth) {
                fieldDateOfBirth.value = patient.date_of_birth || '';
            }
            if (fieldHeight) {
                fieldHeight.value = patient.height_cm ?? '';
            }
            if (fieldWeight) {
                fieldWeight.value = patient.weight_kg ?? '';
            }
            if (fieldPhone) {
                fieldPhone.value = patient.phone || '';
            }
            if (fieldAddress) {
                fieldAddress.value = patient.address || '';
            }
            if (fieldNotes) {
                fieldNotes.value = patient.notes || '';
            }
            if (fieldComplaint) {
                fieldComplaint.value = '';
            }
            if (fieldDiagnosis) {
                fieldDiagnosis.value = '';
            }
            if (fieldActionTaken) {
                fieldActionTaken.value = '';
            }

            renderPatientHistory(patient);
        };

        const renderPatientResults = function () {
            if (!patientInput || !patientResultBox || !patientIdInput) {
                return;
            }

            const keyword = normalize(patientInput.value);
            patientResultBox.innerHTML = '';

            if (keyword.length < 2) {
                patientResultBox.classList.add('hidden');
                return;
            }

            const matched = patients.filter(function (patient) {
                return patient.search.includes(keyword);
            }).slice(0, 10);

            if (matched.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'px-3 py-2 text-xs text-slate-500';
                empty.textContent = 'Nama tidak ditemukan. Lanjutkan isi sebagai pasien baru.';
                patientResultBox.appendChild(empty);
                patientResultBox.classList.remove('hidden');
                return;
            }

            matched.forEach(function (patient) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'block w-full border-b border-slate-100 px-3 py-2 text-left hover:bg-blue-50';
                button.innerHTML = ''
                    + '<p class="text-xs font-bold text-slate-800">' + (patient.name || '-') + '</p>'
                    + '<p class="text-[11px] text-slate-500">RM: ' + (patient.medical_record_number || '-') + ' | Kunjungan: ' + (patient.visit_count || 0) + ' | Terakhir: ' + (patient.last_visit || '-') + '</p>';
                button.addEventListener('click', function () {
                    fillPatientFields(patient);
                    patientResultBox.classList.add('hidden');
                });
                patientResultBox.appendChild(button);
            });

            patientResultBox.classList.remove('hidden');
        };

        if (patientInput && patientIdInput && patientResultBox) {
            patientInput.addEventListener('input', function () {
                patientIdInput.value = '';
                if (patientHistoryPreview) {
                    patientHistoryPreview.classList.add('hidden');
                }
                renderPatientResults();
            });
            patientInput.addEventListener('focus', function () {
                renderPatientResults();
            });
        }

        if (patientIdInput && patientIdInput.value) {
            const selectedPatient = patients.find(function (patient) {
                return String(patient.id) === String(patientIdInput.value);
            });
            if (selectedPatient) {
                renderPatientHistory(selectedPatient);
            }
        }

        const container = document.getElementById('prescription-items-container');
        const addButton = document.getElementById('add-prescription-item');
        const firstItem = container ? container.querySelector('[data-prescription-item]') : null;
        const medicines = @json($medicineOptions);

        if (!container || !addButton || !firstItem) {
            return;
        }

        const renderResults = function (resultsContainer, input, hiddenInput) {
            const keyword = normalize(input.value);
            resultsContainer.innerHTML = '';

            if (keyword.length === 0) {
                resultsContainer.classList.add('hidden');
                return;
            }

            const matched = medicines.filter(function (medicine) {
                return medicine.search.includes(keyword) || medicine.label.toLowerCase().includes(keyword);
            }).slice(0, 12);

            if (matched.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'px-3 py-2 text-xs text-slate-500';
                empty.textContent = 'Obat tidak ditemukan untuk kata kunci ini.';
                resultsContainer.appendChild(empty);
                resultsContainer.classList.remove('hidden');
                return;
            }

            matched.forEach(function (medicine) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'block w-full border-b border-slate-100 px-3 py-2 text-left text-xs text-slate-700 hover:bg-blue-50';
                button.textContent = medicine.label;
                button.addEventListener('click', function () {
                    input.value = medicine.label;
                    hiddenInput.value = String(medicine.id);
                    resultsContainer.classList.add('hidden');
                });
                resultsContainer.appendChild(button);
            });

            resultsContainer.classList.remove('hidden');
        };

        const reindexRows = function () {
            const rows = container.querySelectorAll('[data-prescription-item]');
            rows.forEach(function (row, index) {
                const label = row.querySelector('p');
                if (label) {
                    label.textContent = 'Item Resep #' + (index + 1);
                }

                row.querySelectorAll('[name]').forEach(function (input) {
                    const currentName = input.getAttribute('name');
                    if (!currentName) {
                        return;
                    }

                    input.setAttribute('name', currentName.replace(/prescription_items\[\d+\]/, 'prescription_items[' + index + ']'));
                });

                const removeButton = row.querySelector('[data-remove-prescription-item]');
                if (removeButton) {
                    if (index === 0 && rows.length === 1) {
                        removeButton.classList.add('hidden');
                    } else {
                        removeButton.classList.remove('hidden');
                    }
                }
            });
        };

        const wireRow = function (row) {
            const searchInput = row.querySelector('[data-medicine-keyword]');
            const hiddenInput = row.querySelector('[data-medicine-id]');
            const resultsContainer = row.querySelector('[data-medicine-results]');
            const removeButton = row.querySelector('[data-remove-prescription-item]');

            if (searchInput && hiddenInput && resultsContainer) {
                searchInput.addEventListener('input', function () {
                    hiddenInput.value = '';
                    renderResults(resultsContainer, searchInput, hiddenInput);
                });
                searchInput.addEventListener('focus', function () {
                    renderResults(resultsContainer, searchInput, hiddenInput);
                });
            }
            if (removeButton) {
                removeButton.addEventListener('click', function () {
                    row.remove();
                    reindexRows();
                });
            }
        };

        container.querySelectorAll('[data-prescription-item]').forEach(function (row) {
            wireRow(row);
        });

        addButton.addEventListener('click', function () {
            const rows = container.querySelectorAll('[data-prescription-item]');
            const clone = rows[rows.length - 1].cloneNode(true);

            clone.querySelectorAll('input').forEach(function (input) {
                const type = (input.getAttribute('type') || '').toLowerCase();
                if (type === 'number') {
                    input.value = '1';
                    return;
                }
                if (type === 'hidden') {
                    input.value = '';
                    return;
                }
                input.value = '';
            });
            const resultsContainer = clone.querySelector('[data-medicine-results]');
            if (resultsContainer) {
                resultsContainer.innerHTML = '';
                resultsContainer.classList.add('hidden');
            }

            container.appendChild(clone);
            wireRow(clone);
            reindexRows();
        });

        document.addEventListener('click', function (event) {
            if (patientResultBox && patientInput && !patientInput.parentElement.contains(event.target)) {
                patientResultBox.classList.add('hidden');
            }
            container.querySelectorAll('[data-medicine-results]').forEach(function (resultBox) {
                if (!resultBox.parentElement.contains(event.target)) {
                    resultBox.classList.add('hidden');
                }
            });
        });
    });
</script>
@endsection
