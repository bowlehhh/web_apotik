@extends('ui.dokter.layout')

@section('dokter_title', 'Riwayat Pasien')
@section('dokter_heading', 'Riwayat Pasien')
@section('dokter_subheading', 'Riwayat kunjungan berlapis, update data kunjungan, dan pemberian resep obat pasien.')

@section('dokter_content')
<section class="bg-white rounded-[2.5rem] p-8 shadow-sm">
    <div class="mb-6">
        <h3 class="text-xl font-extrabold text-blue-900">Riwayat Pasien (Layer / Menumpuk)</h3>
        <p class="text-sm text-slate-500">Tampilan dibuat ringkas: klik nama pasien untuk melihat popup biodata lengkap dan seluruh riwayat kunjungan.</p>
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

        $visitsByPatient = $visits
            ->filter(fn ($visit) => $visit->patient !== null)
            ->groupBy('patient_id')
            ->map(function ($patientVisits) {
                return $patientVisits->sortByDesc(function ($visit) {
                    return optional($visit->visit_date)->timestamp ?? 0;
                })->values();
            })
            ->sortBy(function ($patientVisits) {
                return strtolower((string) ($patientVisits->first()?->patient?->name ?? ''));
            });
    @endphp

    @if ($visitsByPatient->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
            <p class="text-sm text-slate-500">Belum ada riwayat kunjungan pasien. Tambahkan konsultasi pertama dari halaman konsultasi.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($visitsByPatient as $patientId => $patientVisits)
                @php
                    $latestVisit = $patientVisits->first();
                    $patient = $latestVisit?->patient;
                @endphp
                <button
                    type="button"
                    class="w-full rounded-2xl border border-slate-100 bg-slate-50/70 p-4 text-left transition-colors hover:border-blue-200 hover:bg-blue-50/60"
                    data-open-patient-modal
                    data-target-modal="patient-history-modal-{{ $patientId }}"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-base font-bold text-slate-800">{{ $patient?->name ?? 'Pasien tidak ditemukan' }}</p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $patient?->medical_record_number ?? '-' }} |
                                Kunjungan: {{ $patientVisits->count() }} kali |
                                Terakhir: {{ optional($latestVisit?->visit_date)->format('d M Y H:i') ?? '-' }}
                            </p>
                        </div>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-[11px] font-bold text-blue-700">Lihat Riwayat</span>
                    </div>
                </button>
            @endforeach
        </div>
    @endif
</section>

@foreach ($visitsByPatient as $patientId => $patientVisits)
    @php
        $latestVisit = $patientVisits->first();
        $patient = $latestVisit?->patient;
        $age = $patient?->age;
        $height = $patient?->height_cm !== null ? rtrim(rtrim(number_format((float) $patient->height_cm, 2, '.', ''), '0'), '.') . ' cm' : '-';
        $weight = $patient?->weight_kg !== null ? rtrim(rtrim(number_format((float) $patient->weight_kg, 2, '.', ''), '0'), '.') . ' kg' : '-';
    @endphp
    <div
        id="patient-history-modal-{{ $patientId }}"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/55 px-4 py-6"
        data-patient-modal
    >
        <div class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h4 class="text-xl font-extrabold text-blue-900">{{ $patient?->name ?? 'Pasien tidak ditemukan' }}</h4>
                    <p class="text-sm text-slate-500 mt-1">Biodata pasien dan seluruh layer kunjungan ditampilkan di bawah.</p>
                </div>
                <button type="button" class="rounded-lg border border-slate-200 px-3 py-1 text-sm font-bold text-slate-600 hover:bg-slate-50" data-close-patient-modal>
                    Tutup
                </button>
            </div>

            <div class="max-h-[calc(92vh-84px)] overflow-y-auto px-6 py-5">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">No RM</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $patient?->medical_record_number ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Umur</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $age !== null ? $age.' tahun' : '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Tinggi / Berat</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $height }} / {{ $weight }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">No HP</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $patient?->phone ?: '-' }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Alamat</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $patient?->address ?: '-' }}</p>
                </div>

                <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Catatan Pasien</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $patient?->notes ?: '-' }}</p>
                </div>

                <div class="mt-6">
                    <h5 class="text-base font-extrabold text-blue-900">Riwayat Sakit dan Kunjungan</h5>
                    <p class="text-xs text-slate-500 mt-1">Urutan terbaru ke terlama, lengkap dengan tanggal dan resep obat.</p>
                </div>

                <div class="mt-3 space-y-3">
                    @foreach ($patientVisits as $visit)
                        @php
                            $items = $visit->prescriptions->flatMap->items;
                        @endphp
                        <article class="rounded-xl border border-slate-100 bg-white p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ optional($visit->visit_date)->format('d M Y H:i') ?? '-' }}</p>
                                    <p class="text-xs text-slate-500 mt-1">Dokter: {{ $visit->doctor?->name ?? '-' }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-[10px] font-bold {{ $visit->status === 'lanjutan' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ strtoupper($visit->status) }}
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-3">
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Keluhan</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ $visit->complaint ?: '-' }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Diagnosis</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ $visit->diagnosis ?: '-' }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Tindakan</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ $visit->action_taken ?: '-' }}</p>
                                </div>
                            </div>

                            <div class="mt-2 rounded-lg bg-slate-50 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Catatan Kunjungan</p>
                                <p class="mt-1 text-sm text-slate-700">{{ $visit->notes ?: '-' }}</p>
                            </div>

                            <div class="mt-3 rounded-lg border border-blue-100 bg-blue-50/50 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600">Resep Obat</p>
                                <div class="mt-2 space-y-2">
                                    @forelse ($items as $item)
                                        @php
                                            $isDispensed = (bool) ($item->prescription?->is_dispensed ?? false);
                                        @endphp
                                        <div class="rounded-lg bg-white p-3">
                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                <div>
                                                    <p class="text-xs font-bold text-slate-700">{{ $item->medicine?->name ?? '-' }} ({{ $item->quantity }})</p>
                                                    <p class="text-[11px] text-slate-500">
                                                        {{ $item->dosage_instructions ?: '-' }} |
                                                        {{ $resolveMedicineTypeLabel($item->medicine?->category ?? '') }} |
                                                        Stok saat ini: {{ $item->medicine?->stock ?? 0 }}
                                                    </p>
                                                    @if ($item->note)
                                                        <p class="text-[11px] text-slate-500 mt-1">Catatan: {{ $item->note }}</p>
                                                    @endif
                                                </div>
                                                @if ($isDispensed)
                                                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-700">Sudah diproses kasir</span>
                                                @endif
                                            </div>

                                            @if (! $isDispensed)
                                                <details class="mt-2 rounded-lg border border-slate-200 p-2">
                                                    <summary class="cursor-pointer text-[11px] font-bold text-blue-700">Edit item resep ini</summary>
                                                    <form method="POST" action="{{ route('dokter.prescriptions.items.update', $item) }}" class="mt-2 space-y-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="medicine_id" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs" required>
                                                            @foreach ($medicines as $medicine)
                                                                @php
                                                                    $medicineLabel = $resolveMedicineTypeLabel($medicine->category ?? '');
                                                                @endphp
                                                                <option value="{{ $medicine->id }}" @selected((int) $item->medicine_id === (int) $medicine->id)>
                                                                    {{ $medicine->name }} | {{ $medicineLabel }} | stok: {{ $medicine->stock }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                                                            <input type="number" name="quantity" min="1" value="{{ (int) $item->quantity }}" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs" required />
                                                            <input type="text" name="dosage_instructions" value="{{ $item->dosage_instructions }}" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs" placeholder="Aturan pakai" required />
                                                        </div>
                                                        <input type="text" name="item_note" value="{{ $item->note }}" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs" placeholder="Catatan item (opsional)" />
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <button type="submit" class="rounded-lg bg-blue-600 px-3 py-2 text-[11px] font-bold text-white hover:bg-blue-700 transition-colors">
                                                                Simpan Edit
                                                            </button>
                                                        </div>
                                                    </form>
                                                </details>

                                                <form method="POST" action="{{ route('dokter.prescriptions.items.destroy', $item) }}" class="mt-2" onsubmit="return confirm('Yakin hapus item resep ini? Stok obat akan dikembalikan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg bg-rose-600 px-3 py-2 text-[11px] font-bold text-white hover:bg-rose-700 transition-colors">
                                                        Hapus Item Resep
                                                    </button>
                                                </form>
                                            @else
                                                <p class="text-[11px] text-slate-500 mt-2">Item resep ini tidak bisa diubah karena sudah diproses kasir.</p>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-500">Tidak ada resep pada kunjungan ini.</p>
                                    @endforelse
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const openButtons = document.querySelectorAll('[data-open-patient-modal]');
        const modalElements = document.querySelectorAll('[data-patient-modal]');

        const closeModal = function (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        const openModal = function (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        openButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target-modal');
                if (!targetId) {
                    return;
                }
                const targetModal = document.getElementById(targetId);
                if (targetModal) {
                    openModal(targetModal);
                }
            });
        });

        modalElements.forEach(function (modal) {
            modal.querySelectorAll('[data-close-patient-modal]').forEach(function (closeButton) {
                closeButton.addEventListener('click', function () {
                    closeModal(modal);
                });
            });

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
            modalElements.forEach(function (modal) {
                if (!modal.classList.contains('hidden')) {
                    closeModal(modal);
                }
            });
        });
    });
</script>
@endsection
